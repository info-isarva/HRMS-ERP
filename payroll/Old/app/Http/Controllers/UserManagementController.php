<?php

namespace App\Http\Controllers;

use App\Models\UserEmergencyContact;
use App\Models\PersonalInformation;
use App\Models\ProfileInformation;
use App\Rules\MatchOldPassword;
use App\Models\BankInformation;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Form;
use App\Models\User;
use Carbon\Carbon;
use Session;
use Hash;
use Auth;
use DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\ActivityLogService;

class UserManagementController extends Controller
{
    /** Index page */
    public function index()
    {
       // if (Session::get('role_name') == 'Admin' || Session::get('role_name') == 'Super Admin')
       // {
            $result      = DB::table('users')->get();
            
            // Get roles from roles table (master table) - same as employee module
            $role_name   = DB::table('roles')
                          ->where('status', 1) // Get only active roles
                          ->orderBy('role_name')
                          ->get();
            
            // Get positions (designations) from position_types table
            $position    = DB::table('position_types')
                          ->where('status', 1) // Get only active positions
                          ->orderBy('position')
                          ->get();
            
            // Get departments from departments table
            $department  = DB::table('departments')
                          ->where('status', 1) // Get only active departments
                          ->orderBy('department')
                          ->get();
            
            // Get user status from employee_statuses table (master table)
            $status_user = DB::table('employee_statuses')
                          ->where('status', 1) // Get only active statuses
                          ->orderBy('status_name')
                          ->get();
                          
            return view('usermanagement.user_control',compact('result','role_name','position','department','status_user'));
        // } else {
        //     return redirect()->route('home');
        // }
    }

    /** Get List Data And Search */
    public function getUsersData(Request $request) 
    {
        try {
            // Log request data for debugging
            \Log::info('getUsersData request data:', [
                'method' => $request->method(),
                'all_data' => $request->all()
            ]);
            
            // Use input() method which works for both POST and GET
            $draw            = $request->input('draw');
            $start           = $request->input("start");
            $rowPerPage      = $request->input("length"); // total number of rows per page
            $columnIndex_arr = $request->input('order');
            $columnName_arr  = $request->input('columns');
            $order_arr       = $request->input('order');
            $search_arr      = $request->input('search');
            
            // Add validation for required parameters
            if (!$draw || !isset($columnIndex_arr[0]['column'])) {
                return response()->json([
                    'error' => 'Invalid DataTables request parameters',
                    'draw' => 0,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ], 400);
            }        $columnIndex     = $columnIndex_arr[0]['column']; // Column index
        $columnName      = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue     = $search_arr['value']; // Search value
        
        // Log sorting information
        \Log::info('DataTable sorting info:', [
            'columnIndex' => $columnIndex,
            'columnName' => $columnName,
            'columnSortOrder' => $columnSortOrder
        ]);

        // Add additional debug logging for users we're having trouble with
        $debugNames = ['JAYASHEELA POOJARY', 'RAVI HOOGAR'];
        foreach ($debugNames as $debugName) {
            $checkUser = DB::table('users')
                ->where('name', 'like', "%$debugName%")
                ->first();
            
            if ($checkUser) {
                \Log::debug("Special case user found: $debugName", [
                    'user_id' => $checkUser->user_id,
                    'employee_id' => $checkUser->employee_id,
                    'phone_number' => $checkUser->phone_number
                ]);
                
                // Try to find their employee record
                $checkEmployee = DB::table('employee_basic_details')
                    ->where('name', 'like', "%$debugName%")
                    ->first();
                
                if ($checkEmployee) {
                    \Log::debug("Found employee record for: $debugName", [
                        'id' => $checkEmployee->id,
                        'employee_id' => $checkEmployee->employee_id,
                        'contact_number' => $checkEmployee->contact_number ?? 'NULL'
                    ]);
                    
                    // Update the user's employee_id to ensure they're linked
                    DB::table('users')
                        ->where('id', $checkUser->id)
                        ->update([
                            'employee_id' => $checkEmployee->id
                        ]);
                        
                    \Log::info("Updated user's employee_id for $debugName");
                } else {
                    \Log::warning("No employee record found for special case: $debugName");
                }
            }
        }
        
        // Use a left join with multiple conditions to get employee contact numbers for employee-converted users
        // We need to handle multiple ways the user and employee tables might be linked
        $users = DB::table('users')
            ->leftJoin('employee_basic_details', function($join) {
                // Match by employee_id field as string
                $join->on('users.employee_id', '=', 'employee_basic_details.employee_id')
                    // Match by employee_id as numeric id 
                    ->orOn('users.employee_id', '=', DB::raw('CAST(employee_basic_details.id AS CHAR)'))
                    // Match by user_id to employee_id
                    ->orOn('users.user_id', '=', 'employee_basic_details.employee_id')
                    // As a last resort, try matching by name (for the special cases)
                    ->orOn(DB::raw('LOWER(users.name)'), '=', DB::raw('LOWER(employee_basic_details.name)'));
            })
            ->select(
                'users.id',
                'users.name',
                'users.user_id',
                'users.email',
                'users.position',
                'users.phone_number',
                'users.join_date',
                'users.role_name',
                'users.status',
                'users.department',
                'users.avatar',
                'users.last_login',
                'users.employee_id',
                'employee_basic_details.contact_number',
                'employee_basic_details.profile_image as employee_profile_image',
                'employee_basic_details.id as employee_id_numeric',
                // Use IFNULL in case contact_number is NULL to ensure we get a value
                // For employee users, prioritize contact_number from employee_basic_details
                // For regular users, use phone_number from users table
                DB::raw("CASE 
                    WHEN users.employee_id IS NOT NULL AND employee_basic_details.contact_number IS NOT NULL 
                    THEN employee_basic_details.contact_number 
                    ELSE users.phone_number 
                END as display_phone"),
                // For profile images, prioritize employee profile image for employee-converted users
                DB::raw("CASE 
                    WHEN users.employee_id IS NOT NULL AND employee_basic_details.profile_image IS NOT NULL 
                    THEN employee_basic_details.profile_image 
                    ELSE users.avatar 
                END as display_avatar")
            );
            
        $totalRecords = DB::table('users')->count();

        // Search
        $filters = [
            'users.name'      => $request->user_name,
            'users.role_name' => $request->type_role,
            'users.status'    => $request->type_status,
        ];
        
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $users->where($field, 'like', "%$value%");
            }
        }

        $searchColumns = [
            'users.name', 
            'users.user_id', 
            'users.email', 
            'users.position', 
            'users.phone_number',
            'employee_basic_details.contact_number', 
            'users.join_date', 
            'users.role_name', 
            'users.status', 
            'users.department'
        ];
        
        // Apply search filter and get the total records with filter
        $totalRecordsWithFilter = $users->where(function ($query) use ($searchValue, $searchColumns) {
            foreach ($searchColumns as $column) {
                $query->orWhere($column, 'like', '%' . $searchValue . '%');
            }})->count();
        
        // Determine correct column for ordering (some columns might be from employee_basic_details)
        $orderColumn = 'users.'.$columnName;
        if ($columnName === 'phone_number' && strpos($columnName, '.') === false) {
            // For phone number, we need to handle both users.phone_number and employee_basic_details.contact_number
            // Using IF to prioritize contact_number for employee users
            $orderColumn = DB::raw("IF(users.employee_id IS NOT NULL, employee_basic_details.contact_number, users.phone_number)");
        }

        // Retrieve filtered and sorted records
        $records = $users->orderBy($orderColumn, $columnSortOrder)
            ->where(function ($query) use ($searchValue, $searchColumns) {
            foreach ($searchColumns as $column) {
                $query->orWhere($column, 'like', '%' . $searchValue . '%');
            }})->skip($start)->take($rowPerPage)->get();
        
        $data_arr = [];
        $roleBadges = [
            'Admin'           => 'bg-inverse-danger',
            'Super Admin'     => 'bg-inverse-warning',
            'Normal User'     => 'bg-inverse-info',
            'Client'          => 'bg-inverse-success',
            'Employee'        => 'bg-inverse-dark',
            'HR Manager'      => 'bg-inverse-primary',
            'Finance Manager' => 'bg-inverse-secondary',
        ];
        
        $statusBadges = [
            'Active'   => 'text-success',
            'Inactive' => 'text-info',
            'Disable'  => 'text-danger',
        ];
        
        foreach ($records as $key => $record) {
            $record->name = '
                <h2 class="table-avatar">
                    <a href="'.url('employee/profile/' . $record->user_id).'">
                        <img class="avatar" data-avatar="'.$record->display_avatar.'" src="'.($record->display_avatar ? (strpos($record->display_avatar, 'assets/') !== false ? url($record->display_avatar) : url('/assets/employee_profile_image/'.$record->display_avatar)) : url('/assets/img/user-icon.webp')).'">
                        '.$record->name.'
                         <span class="name" hidden>'.$record->name.'</span>
                    </a>
                </h2>';
            
            $role_name = isset($roleBadges[$record->role_name]) ? '<span class="badge '.$roleBadges[$record->role_name].' role_name">'.$record->role_name.'</span>' : 'NULL';
        
            $full_status = '
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item"><i class="fa fa-dot-circle-o text-success"></i> Active </a>
                    <a class="dropdown-item"><i class="fa fa-dot-circle-o text-warning"></i> Inactive </a>
                    <a class="dropdown-item"><i class="fa fa-dot-circle-o text-danger"></i> Disable </a>
                </div>';
        
            $status = '
                <a class="btn btn-white btn-sm btn-rounded dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-dot-circle-o '.($statusBadges[$record->status] ?? 'text-dark').'"></i>
                    <span class="status_s">'.$record->status.'</span>
                </a>
                '.$full_status;
        
            $editButton = '';
            $deleteButton = '';
            
            // Check if user has edit permission
            if (Auth::user()->hasPermission('user_management.edit')) {
                $editButton = '
                    <a href="#" class="btn-action btn-action-edit userUpdate" data-toggle="modal" data-id="'.$record->id.'" data-target="#edit_user" title="Edit User">
                        <i class="fas fa-edit"></i>
                    </a>';
            }
            
            // Check if user has delete permission
            if (Auth::user()->hasPermission('user_management.delete')) {
                $deleteButton = '
                    <a href="#" class="btn-action btn-action-delete userDelete" data-toggle="modal" data-id="'.$record->id.'" data-target="#delete_user" title="Delete User">
                        <i class="fas fa-trash-alt"></i>
                    </a>';
            }
            
            $action = '
                <div class="d-flex justify-content-center">
                    '.$editButton.'
                    '.$deleteButton.'
                </div>';
            
            // For debugging, log all phone number fields before any processing
            \Log::debug('Phone number fields before processing', [
                'user_id' => $record->user_id,
                'name' => $record->name,
                'employee_id' => $record->employee_id,
                'users.phone_number' => $record->phone_number,
                'employee_basic_details.contact_number' => $record->contact_number,
                'display_phone' => $record->display_phone
            ]);
            
            // Process the phone number display
            $phoneToDisplay = '';
            
            // Special case for our problematic users - try harder to get their phone numbers
            $specialUsers = ['JAYASHEELA POOJARY', 'RAVI HOOGAR'];
            $isSpecialUser = in_array($record->name, $specialUsers) || 
                             str_contains(strtoupper($record->name), 'JAYASHEELA') || 
                             str_contains(strtoupper($record->name), 'RAVI HOOGAR');
                             
            if ($isSpecialUser) {
                \Log::info("Special case user found in data processing: {$record->name}");
                
                // Force a direct lookup to ensure we get their data
                $empRecord = DB::table('employee_basic_details')
                    ->where('name', 'like', "%{$record->name}%")
                    ->orWhere(function($q) use ($record) {
                        if ($record->employee_id) {
                            $q->where('id', $record->employee_id)
                              ->orWhere('employee_id', $record->employee_id);
                        }
                    })
                    ->first();
                
                if ($empRecord && !empty($empRecord->contact_number)) {
                    $phoneToDisplay = $empRecord->contact_number;
                    \Log::info("Found special case phone number: {$phoneToDisplay}");
                } else {
                    $phoneToDisplay = $record->phone_number ?? '';
                    \Log::warning("Could not find special case phone in employee table", [
                        'name' => $record->name,
                        'using_phone' => $phoneToDisplay
                    ]);
                }
            }
            // Regular employee users
            else if ($record->employee_id) {
                // For employee users, prioritize contact_number from employee_basic_details
                // First check if contact_number is available from the join
                if (!empty($record->contact_number)) {
                    $phoneToDisplay = $record->contact_number;
                } 
                // If not available from join, try display_phone from the query
                else if (!empty($record->display_phone)) {
                    $phoneToDisplay = $record->display_phone;
                }
                // Last resort, use phone_number from users table
                else {
                    $phoneToDisplay = $record->phone_number ?? '';
                }
                
                \Log::debug('Employee phone after processing', [
                    'user_id' => $record->user_id,
                    'name' => $record->name,
                    'employee_id' => $record->employee_id,
                    'employee_id_numeric' => $record->employee_id_numeric ?? null,
                    'contact_number' => $record->contact_number ?? 'NULL',
                    'phone_number' => $record->phone_number ?? 'NULL',
                    'display_phone' => $record->display_phone ?? 'NULL',
                    'final_phone' => $phoneToDisplay
                ]);
            } else {
                // Regular user, use phone_number from users table
                $phoneToDisplay = !empty($record->phone_number) ? $record->phone_number : '';
            }
            
            // Ensure we always have a consistent display_phone value
            $record->display_phone = $phoneToDisplay;
            
            $last_login = $record->last_login ? Carbon::parse($record->last_login)->diffForHumans() : '<span class="text-muted">Never logged in</span>';
        
            $data_arr[] = [
                "no"           => '<span class="id" data-id="'.$record->id.'">'.($start + $key + 1).'</span>',
                "name"         => $record->name,
                "user_id"      => '<span class="user_id">'.$record->user_id.'</span>',
                "email"        => '<span class="email">'.$record->email.'</span>',
                "position"     => '<span class="position">'.$record->position.'</span>',
                "phone_number" => '<span class="phone_number" 
                    data-raw-phone="'.($record->display_phone ?? '').'" 
                    data-user-id="'.$record->user_id.'" 
                    data-is-employee="'.($record->employee_id ? 'yes' : 'no').'"
                    style="'.($isSpecialUser ? 'font-weight:bold;' : '').'">'.
                    ($record->display_phone ?? $record->contact_number ?? $record->phone_number ?? '').'</span>',
                "join_date"    => $record->join_date ? Carbon::parse($record->join_date)->format('d-m-Y') : '',
                "last_login"   => $last_login,
                "role_name"    => $role_name,
                "status"       => $status,
                "department"   => '<span class="department">'.$record->department.'</span>',
                "action"       => $action,
            ];
        }
     
        $response = [
            "draw"                 => intval($draw),
            "recordsTotal"        => $totalRecords,
            "recordsFiltered" => $totalRecordsWithFilter,
            "data"               => $data_arr
        ];
        return response()->json($response);
        
        } catch (\Exception $e) {
            \Log::error('Error in getUsersData: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while fetching data',
                'message' => $e->getMessage(),
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 500);
        }
    }

    /** Profile User */
    // public function profile()
    // {
    //     $profile = Session::get('user_id'); // Get the user ID from session

    //     // Eager load all necessary data in one go
    //     $userInformation  = PersonalInformation::where('user_id', $profile)->first();
    //     $bankInformation  = BankInformation::where('user_id', $profile)->first();
    //     $emergencyContact = UserEmergencyContact::where('user_id', $profile)->first();
    //     $users            = DB::table('users')->get();
    //     $employeeProfile = DB::table('profile_information')->where('user_id', $profile)->first();

    //     // Check if employee profile exists
    //     if ($employeeProfile) {
    //         // Profile exists, return with all the data
    //         return view('usermanagement.profile_user', [
    //             'information'       => $employeeProfile,
    //             'user'              => $users,
    //             'userInformation'   => $userInformation,
    //             'emergencyContact'  => $emergencyContact,
    //             'bankInformation'   => $bankInformation
    //         ]);
    //     } else {
    //         // No employee profile, return only the basic information
    //         return view('usermanagement.profile_user', [
    //             'information'       => null,
    //             'user'              => $users,
    //             'userInformation'   => $userInformation
    //         ]);
    //     }
    // }
    /** Profile User - Code By Ashok */
    public function profile() {
        $profile = Session::get('user_id'); // Get the user ID from session
        $user            = DB::table('users')->where('user_id', $profile)->first();
        $employeeProfile = DB::table('profile_information')->where('user_id', $profile)->first();
        
        $dpdpConsent = \App\Models\UserConsent::where('user_id', Auth::id())
            ->where('policy_type', 'dpdp_act')
            ->first();
            
        $dataRequests = \App\Models\DataChangeRequest::where('user_email', Auth::user()->email)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('usermanagement.profile_admin', [
                        'user'              => $user,
                        'information'       => $employeeProfile,
                        'dpdpConsent'       => $dpdpConsent,
                        'dataRequests'      => $dataRequests,
                    ]);
    }

    /** Save Profile Information */
    // public function profileInformation(Request $request)
    // {
    //     try {
    //         if(!empty($request->images))
    //         {
    //             $image_name = $request->hidden_image;
    //             $image = $request->file('images');
    //             if ($image_name =='photo_defaults.jpg')
    //             {
    //                 if($image != '') {
    //                     $image_name = rand() . '.' . $image->getClientOriginalExtension();
    //                     $image->move(public_path('/assets/images/'), $image_name);
    //                 }
    //             } else {
    //                 if($image != '') {
    //                     $image_name = rand() . '.' . $image->getClientOriginalExtension();
    //                     $image->move(public_path('/assets/images/'), $image_name);
    //                     unlink('assets/images/'.Auth::user()->avatar);
    //                 }
    //             }
    //             $update = [
    //                 'user_id' => $request->user_id,
    //                 'name'    => $request->name,
    //                 'avatar'  => $image_name,
    //             ];
    //             User::where('user_id',$request->user_id)->update($update);
    //         } 

    //         $information = ProfileInformation::updateOrCreate(['user_id' => $request->user_id]);
    //         $information->name         = $request->name;
    //         $information->user_id      = $request->user_id;
    //         $information->email        = $request->email;
    //         $information->birth_date   = $request->birth_date;
    //         $information->gender       = $request->gender;
    //         $information->address      = $request->address;
    //         $information->state        = $request->state;
    //         $information->country      = $request->country;
    //         $information->pin_code     = $request->pin_code;
    //         $information->phone_number = $request->phone_number;
    //         $information->department   = $request->department;
    //         $information->designation  = $request->designation;
    //         $information->reports_to   = $request->reports_to;
    //         $information->save();

    //         $employee = Employee::where('employee_id', $request->user_id)->first();
    //         if ($employee) {
    //             $employee->name         = $request->name;
    //             $employee->email        = $request->email;
    //             $employee->gender       = $request->gender;
    //             $employee->birth_date   = $request->birth_date;
    //             $employee->line_manager = $request->reports_to;
    //             $employee->save();
    //         }

    //         $user = User::updateOrCreate(['user_id' => $request->user_id]);
    //         $user->name         = $request->name;
    //         $user->user_id      = $request->user_id;
    //         $user->email        = $request->email;
    //         $user->line_manager = $request->reports_to;
    //         $user->save();
            
    //         DB::commit();
    //         flash()->success('Add Profile Information successfully :)');
    //         return redirect()->back();
    //     }catch(\Exception $e){
    //         DB::rollback();
    //         \Log::error('Failed: ' . $e->getMessage());
    //         flash()->error('Add Profile Information fail :)');
    //         return redirect()->back();
    //     }
    // }
    /** Save Profile - Ashok [04-06-2025] */
    public function profileSave(Request $request)
    {
        $request->validate([
            'name' => ['required']
        ]);
        
        try {
            DB::beginTransaction();
            
            $information = ProfileInformation::updateOrCreate(['user_id' => $request->user_id]);
            $information->name         = $request->name;
            $information->user_id      = $request->user_id;
            $information->email        = $request->email;
            $information->gender       = $request->gender;
            $information->address      = $request->address;
            $information->phone_number = $request->phone_number;
            $information->reports_to   = $request->user_id;
            $information->save();

            $user = User::updateOrCreate(['user_id' => $request->user_id]);
            $user->name         = $request->name;
            $user->user_id      = $request->user_id;
            $user->email        = $request->email;
            $user->phone_number = $request->phone_number;
            $user->line_manager = $request->user_id;

            $image_name = $request->hidden_image;
            if(!empty($request->images))
            {
                $image = $request->file('images');
                if ($image_name =='photo_defaults.jpg')
                {
                    if($image != '') {
                        $image_name = rand() . '.' . $image->getClientOriginalExtension();
                        $image->move(public_path('/assets/employee_profile_image/'), $image_name);
                    }
                } else {
                    if($image != '') {
                        $image_name = rand() . '.' . $image->getClientOriginalExtension();
                        $image->move(public_path('/assets/employee_profile_image/'), $image_name);
                        if(file_exists(public_path('assets/employee_profile_image/'.Auth::user()->avatar))) {
                            unlink('assets/employee_profile_image/'.Auth::user()->avatar);
                        }
                    }
                }
                
                $user->avatar = $image_name;
                
                // Check if this user is an employee-converted user and update employee profile image
                if ($user->employee_id) {
                    $employeeData = DB::table('employee_basic_details')
                        ->where(function($query) use ($user) {
                            if (is_numeric($user->employee_id)) {
                                $query->orWhere('id', $user->employee_id);
                            }
                            $query->orWhere('employee_id', $user->employee_id);
                            $query->orWhere('employee_id', $user->user_id);
                            $query->orWhere('name', 'like', '%' . $user->name . '%');
                        })
                        ->first();
                        
                    if ($employeeData) {
                        DB::table('employee_basic_details')
                            ->where('id', $employeeData->id)
                            ->update([
                                'profile_image' => 'assets/employee_profile_image/' . $image_name
                            ]);
                    }
                }
            } else {
                // If no new image is uploaded, ensure we preserve the existing image
                // For employee-converted users, try to get the image from employee_basic_details
                if ($user->employee_id) {
                    $employeeData = DB::table('employee_basic_details')
                        ->where(function($query) use ($user) {
                            if (is_numeric($user->employee_id)) {
                                $query->orWhere('id', $user->employee_id);
                            }
                            $query->orWhere('employee_id', $user->employee_id);
                            $query->orWhere('employee_id', $user->user_id);
                            $query->orWhere('name', 'like', '%' . $user->name . '%');
                        })
                        ->first();
                        
                    if ($employeeData && !empty($employeeData->profile_image)) {
                        // Extract just the filename from the employee profile image path
                        $image_name = basename($employeeData->profile_image);
                        $user->avatar = $image_name;
                    }
                } else if (!$image_name) {
                    // For regular users, keep the existing avatar if no hidden_image is provided
                    $image_name = $user->avatar;
                }
            } 
            $user->save();
            
            DB::commit();
            flash()->success('Profile updated successfully :)');
            return redirect()->back();
        }catch(\Exception $e){
            DB::rollback();
            \Log::error('Profile update failed: ' . $e->getMessage());
            flash()->error('Profile update failed :)');
            return redirect()->back();
        }
    }
   
    /** Save new user */
    public function addNewUserSave(Request $request)
    {
       // dd($request);
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'phone'     => 'min:10|numeric',
            'role_name' => 'required|string|max:255',
            // 'position'  => 'required|string|max:255',
            // 'department'=> 'required|string|max:255',
            'status'    => 'required|string|max:255',
            'image'     => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Added image file type and size validation
            'password'  => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        DB::beginTransaction();
       // try {
            $todayDate = Carbon::now()->toDayDateTimeString();
            $formattedJoinDate = Carbon::now()->format('d-m-Y');

           if($request->image){
                $imageName = time().'.'.$request->image->extension();
                $request->image->move(public_path('assets/images'), $imageName);
           }
            
            

            $user = new User;
            $user->name         = $request->name;
            $user->email        = $request->email;
            $user->join_date    = $formattedJoinDate;
            $user->last_login   = $todayDate;
            $user->phone_number = $request->phone;
            $user->role_name    = $request->role_name;
           // $user->position     = $request->position;
           // $user->department   = $request->department;
            $user->status       = $request->status;
           // $user->avatar       = $imageName;
            $user->password     = Hash::make($request->password);
            $user->save();

            DB::commit();

            Toastr::success('Created new account successfully!', 'Success');
            return redirect()->route('userManagement');
      //  } catch (\Exception $e) {
            // DB::rollback();
            // \Log::error('Failed to create new account', ['error' => $e->getMessage()]);
            // Toastr::error('Failed to create new account. Please try again.', 'Error');
            // return redirect()->back()->withInput();
     //   }
    }

    /** Update Record */
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_id   = $request->user_id;
            
            // Get the user to check if they're an employee-converted user
            $user = User::where('user_id', $user_id)->first();
            if (!$user) {
                throw new \Exception('User not found');
            }
            
            $isEmployeeConvertedUser = $user->isEmployeeUser();
            
            // For employee-converted users, restrict what can be updated
            if ($isEmployeeConvertedUser) {
                // Only allow password updates for employee-converted users
                if ($request->filled('password')) {
                    $request->validate([
                        'password' => 'required|string|min:8|confirmed',
                    ]);
                    
                    $hashedPassword = Hash::make($request->password);
                    $user->update(['password' => $hashedPassword]);
                    
                    // Sync password with attendance system
                    $this->syncPasswordWithAttendance($user_id, $request->password);
                    
                    DB::commit();
                    
                    flash()->success('Password updated successfully for employee user. Other fields are managed through the Employee module.');
                    return redirect()->route('userManagement');
                } else {
                    flash()->warning('For employee-converted users, only password can be updated here. Other details must be updated in the Employee module.');
                    return redirect()->route('userManagement');
                }
            }
            
            // Regular user update process
            $name      = $request->name;
            $email     = $request->email;
            $role_name = $request->role_name;
            $position  = $request->position;  // Now contains designation ID
            $phone     = $request->phone;
            $department= $request->department; // Now contains department ID
            $status    = $request->status;
            $image_name = $request->hidden_image;
            $gender    = $request->gender;
            $date_of_birth = $request->date_of_birth;
            $marital_status = $request->marital_status;
            $address   = $request->address;
            
            // Get department and designation names for logging purposes
            $departmentName = DB::table('departments')->where('id', $department)->value('department');
            $designationName = DB::table('position_types')->where('id', $position)->value('position');
    
            $dt = Carbon::now();
            $todayDate = $dt->toDayDateTimeString();
            
            // Capture old user data for activity logging
            $oldUserData = [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                'role_name' => $user->role_name,
                'position' => $user->position,
                'department' => $user->department,
                'status' => $user->status,
            ];
    
            $image = $request->file('images');
            if ($image) {
                // Delete old image if not the default one
                if ($image_name && $image_name != 'photo_defaults.jpg') {
                    // Check both possible locations for the image
                    if (file_exists(public_path('assets/images/'.$image_name))) {
                        unlink(public_path('assets/images/'.$image_name));
                    } elseif (file_exists(public_path('assets/employee_profile_image/'.$image_name))) {
                        unlink(public_path('assets/employee_profile_image/'.$image_name));
                    }
                }
    
                $image_name = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('assets/employee_profile_image'), $image_name);
            }
            
            // If no new image is uploaded, keep the existing image name
            if (!$image && !$image_name) {
                // For employee-converted users, try to get the image from employee_basic_details
                if ($user->employee_id) {
                    $employeeData = DB::table('employee_basic_details')
                        ->where(function($query) use ($user) {
                            if (is_numeric($user->employee_id)) {
                                $query->orWhere('id', $user->employee_id);
                            }
                            $query->orWhere('employee_id', $user->employee_id);
                            $query->orWhere('employee_id', $user->user_id);
                            $query->orWhere('name', 'like', '%' . $user->name . '%');
                        })
                        ->first();
                        
                    if ($employeeData && !empty($employeeData->profile_image)) {
                        // Extract just the filename from the employee profile image path
                        $image_name = basename($employeeData->profile_image);
                    } else {
                        $image_name = $user->avatar; // Fallback to existing avatar
                    }
                } else {
                    $image_name = $user->avatar; // Keep the existing avatar for regular users
                }
            }
    
            // For the users table, we don't update phone_number for employee-converted users
            // as that comes from employee_basic_details
            
            $update = [
                'user_id'       => $user_id,
                'name'          => $name,
                'role_name'     => $role_name,
                'email'         => $email,
                'position'      => $position,
                'department'    => $department,
                'status'        => $status,
            ];
            
            // Only update avatar if we have a valid image name
            if ($image_name) {
                $update['avatar'] = $image_name;
            }
            
            // Only update phone_number in users table if not an employee-converted user
            if (!$isEmployeeConvertedUser) {
                $update['phone_number'] = $phone;
            }
    
            $activityLog = [
                'user_name'    => $name,
                'email'        => $email,
                'phone_number' => $phone,
                'status'       => $status,
                'role_name'    => $role_name,
                'modify_user'  => 'Update',
                'date_time'    => $todayDate,
            ];
    
            // Get the user to check if they're an employee-converted user
            $user = User::where('user_id', $user_id)->first();
            
            // Store old data for activity logging
            $oldUserData = $user->toArray();
            
            if ($user && $user->employee_id) {
                // This is an employee-converted user, also update employee_basic_details
                $employeeData = DB::table('employee_basic_details')
                    ->where(function($query) use ($user) {
                        // Try numeric ID match
                        if (is_numeric($user->employee_id)) {
                            $query->orWhere('id', $user->employee_id);
                        }
                        
                        // Try string employee_id match
                        $query->orWhere('employee_id', $user->employee_id);
                        
                        // Try user_id to employee_id match
                        $query->orWhere('employee_id', $user->user_id);
                        
                        // Try employee name match as a last resort
                        $query->orWhere('name', 'like', '%' . $user->name . '%');
                    })
                    ->first();
                    
                \Log::debug('Employee update - employee found:', [
                    'user_id' => $user->user_id,
                    'employee_id' => $user->employee_id,
                    'found' => $employeeData ? true : false,
                    'phone_to_update' => $phone
                ]);
                    
                if ($employeeData) {
                    $employeeUpdateData = [
                        'name' => $name,
                        'email' => $email,
                        'contact_number' => $phone,
                        'gender' => $gender,
                        'date_of_birth' => $date_of_birth,
                        'marital_status' => $marital_status,
                        'designation' => $position, // Now using the ID from position_types
                        'department' => $department, // Now using the ID from departments
                        'status' => $status
                    ];
                    
                    // Update profile image in employee_basic_details if image was uploaded
                    if ($image && $image_name) {
                        $employeeUpdateData['profile_image'] = 'assets/employee_profile_image/' . $image_name;
                    }
                    
                    // Update address fields if provided
                    if ($address) {
                        $employeeUpdateData['address'] = $address;
                    }
                    
                    DB::table('employee_basic_details')
                        ->where('id', $employeeData->id)
                        ->update($employeeUpdateData);
                        
                    // Also update employee_personal_details if applicable
                    if ($address) {
                        $personalDetails = DB::table('employee_personal_details')
                            ->where('emp_id', $employeeData->id)
                            ->first();
                            
                        if ($personalDetails) {
                            DB::table('employee_personal_details')
                                ->where('emp_id', $employeeData->id)
                                ->update([
                                    'permanent_address' => $address,
                                    'permanent_city' => $request->city ?? $personalDetails->permanent_city,
                                    'permanent_state' => $request->state ?? $personalDetails->permanent_state,
                                    'permanent_country' => $request->country ?? $personalDetails->permanent_country,
                                    'permanent_postal_code' => $request->postal_code ?? $personalDetails->permanent_postal_code,
                                ]);
                        } else {
                            // Create a new personal details record if it doesn't exist
                            DB::table('employee_personal_details')->insert([
                                'emp_id' => $employeeData->id,
                                'permanent_address' => $address,
                                'permanent_city' => $request->city ?? '',
                                'permanent_state' => $request->state ?? '',
                                'permanent_country' => $request->country ?? '',
                                'permanent_postal_code' => $request->postal_code ?? '',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                    }
                }
            } else {
                // Regular user, update profile_information
                ProfileInformation::updateOrCreate(
                    ['user_id' => $user_id],
                    [
                        'name' => $name,
                        'email' => $email,
                        'phone_number' => $phone,
                        'gender' => $gender,
                        'address' => $address
                    ]
                );
            }
    
            DB::table('user_activity_logs')->insert($activityLog);
            User::where('user_id', $user_id)->update($update);
            
            // Log activity using the new activity log service
            $newUserData = array_merge($update, [
                'department_name' => $departmentName,
                'designation_name' => $designationName
            ]);
            ActivityLogService::logUserUpdated($user_id, $oldUserData, $newUserData);
    
            DB::commit();
    
            flash()->success('User updated successfully :)');
            return redirect()->route('userManagement');
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('User update failed', ['error' => $e->getMessage()]);
            flash()->error('User update failed: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /** Delete Record */
    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $dt = Carbon::now();
            $todayDate = $dt->toDayDateTimeString();
    
            // Log the deletion activity
            $activityLog = [
                'user_name'    => Session::get('name'),
                'email'        => Session::get('email'),
                'phone_number' => Session::get('phone_number'),
                'status'       => Session::get('status'),
                'role_name'    => Session::get('role_name'),
                'modify_user'  => 'Delete',
                'date_time'    => $todayDate,
            ];
    
            DB::table('user_activity_logs')->insert($activityLog);
    
            // Handle the deletion of user-related information
            $userId = $request->id;
            $avatar = $request->avatar;
    
            // Delete user and related records
            User::destroy($userId);
            PersonalInformation::destroy($userId);
            UserEmergencyContact::destroy($userId);
    
            // Delete the avatar image if it's not the default
            if ($avatar !== 'photo_defaults.jpg') {
                // Delete the file using the Storage facade
                unlink('assets/images/'.$avatar);
            }
    
            DB::commit();
            flash()->success('User deleted successfully :)');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error deleting user: ' . $e->getMessage()); // Log error details
            flash()->error('User deletion failed :)');
            return redirect()->back();
        }
    }

    /** View Change Password */
    public function changePasswordView()
    {
        return view('settings.changepassword');
    }

    /*** Admin Password Changes - Code By Ashok */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password'     => ['required', new MatchOldPassword],
            'new_password'         => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        try {
            $user_id = Session::get('user_id'); // Get the user ID from session
            
            $user = User::updateOrCreate(['user_id' => $user_id]);
            $hashedPassword = Hash::make($request->new_password);
            
            $user->password = $hashedPassword;
            $user->save();
            
            // Sync password with attendance system
            $syncResult = $this->syncPasswordWithAttendance($user_id, $request->new_password);
            
            // Commit the transaction
            DB::commit();
            
            // Show success message
            $message = 'Password changed successfully :)';
            if (!$syncResult) {
                $message .= ' (Warning: Failed to sync password with attendance system)';
            }
            
            flash()->success($message);
            // Redirect to the intended route
            return redirect()->intended('home');
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            // Optionally log the error or show an error message
            flash()->error('An error occurred while changing the password. Please try again.');
            // Redirect back
            return redirect()->back();
        }
    }
    
    /** Change Password User */
    // public function changePasswordDB(Request $request)
    // {
    //     $request->validate([
    //         'current_password'     => ['required', new MatchOldPassword],
    //         'new_password'         => ['required'],
    //         'new_confirm_password' => ['same:new_password'],
    //     ]);

    //     try {
    //         // Find the authenticated user
    //         $user = Auth::user();
    //         // Update the user's password
    //         $user->update(['password' => Hash::make($request->new_password)]);
    //         // Commit the transaction
    //         DB::commit();
    //         // Show success message
    //         flash()->success('Password changed successfully :)');
    //         // Redirect to the intended route
    //         return redirect()->intended('home');
    //     } catch (\Exception $e) {
    //         // Rollback the transaction in case of error
    //         DB::rollBack();
    //         // Optionally log the error or show an error message
    //         flash()->error('An error occurred while changing the password. Please try again.');
    //         // Redirect back
    //         return redirect()->back();
    //     }
    // }
    
    /** Change Password User with Sync */
    public function changePasswordDBWithSync(Request $request)
    {
        $request->validate([
            'current_password'     => ['required', new MatchOldPassword],
            'new_password'         => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        try {
            // Find the authenticated user
            $user = Auth::user();
            $hashedPassword = Hash::make($request->new_password);
            
            // Update the user's password in payroll system
            $user->update(['password' => $hashedPassword]);
            
            // Sync password with attendance system using new bidirectional approach
            $this->syncPasswordToAttendance($user->email, $request->new_password);
            
            // Log successful password change
            ActivityLogService::log(
                'Password changed with sync', 
                'Users', 
                $user->id,
                $user->name . ' changed password and synced to attendance',
                ['synced_to' => 'attendance', 'user_email' => $user->email]
            );
            
            // Show success message
            flash()->success('Password changed successfully and synced across systems!');
            
            // Redirect to the intended route
            return redirect()->intended('home');
            
        } catch (\Exception $e) {
            Log::error('Error changing password with sync', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Show error message
            flash()->error('An error occurred while changing the password. Please try again.');
            // Redirect back
            return redirect()->back();
        }
    }

    /**
     * Sync password change to attendance system using new bidirectional approach
     */
    private function syncPasswordToAttendance($userEmail, $newPassword)
    {
        try {
            $attendanceUrl = env('ATTENDANCE_SYNC_URL', 'https://attendancedev.isarva.in');
            $syncToken = env('ATTENDANCE_SYNC_TOKEN', 'default-token');
            
            $response = \Illuminate\Support\Facades\Http::timeout(30)->post($attendanceUrl . '/api/sync/password/from-payroll', [
                'user_email' => $userEmail,
                'new_password' => $newPassword,
                'sync_token' => $syncToken,
                'synced_from' => 'payroll',
                'synced_at' => now()->toISOString()
            ]);

            if ($response->successful()) {
                Log::info('Password successfully synced to attendance system', [
                    'user_email' => $userEmail,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::warning('Failed to sync password to attendance system', [
                    'user_email' => $userEmail,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('Error syncing password to attendance system', [
                'user_email' => $userEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /** Sync Password with Attendance Application */
    private function syncPasswordWithAttendance($userId, $plainPassword)
    {
        // Check if sync is enabled
        $syncEnabled = env('ATTENDANCE_SYNC_ENABLED', true);
        if (!$syncEnabled) {
            Log::info("Attendance sync is disabled", ['action' => 'password_update', 'user_id' => $userId]);
            return true; // Return true to avoid showing error messages
        }

        try {
            // Get user email for the new bidirectional sync approach
            $user = User::where('user_id', $userId)->first();
            if (!$user || !$user->email) {
                Log::warning("User not found or email missing for password sync", [
                    'user_id' => $userId,
                    'user_found' => $user ? 'yes' : 'no',
                    'email_present' => $user && $user->email ? 'yes' : 'no'
                ]);
                return false;
            }

            // Use the existing bidirectional sync approach with email and plain text password
            return $this->syncPasswordToAttendance($user->email, $plainPassword);

        } catch (\Exception $e) {
            Log::error("Password sync failed", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'suggestion' => 'Check if attendance application is running and accessible'
            ]);
            return false;
        }
    }

    /** User Profile Emergency Contact */
    public function emergencyContactSaveOrUpdate(Request $request)
    {
        // Validate form input
        $request->validate([
            'name_primary'           => 'required',
            'relationship_primary'   => 'required',
            'phone_primary'          => 'required',
            'phone_2_primary'        => 'required',
            'name_secondary'         => 'required',
            'relationship_secondary' => 'required',
            'phone_secondary'        => 'required',
            'phone_2_secondary'      => 'required',
        ]);

        try {
            // Save or update emergency contact
            $saveRecord = UserEmergencyContact::updateOrCreate(
                ['user_id' => $request->user_id],
                [
                    'name_primary'           => $request->name_primary,
                    'relationship_primary'   => $request->relationship_primary,
                    'phone_primary'          => $request->phone_primary,
                    'phone_2_primary'        => $request->phone_2_primary,
                    'name_secondary'         => $request->name_secondary,
                    'relationship_secondary' => $request->relationship_secondary,
                    'phone_secondary'        => $request->phone_secondary,
                    'phone_2_secondary'      => $request->phone_2_secondary,
                ]
            );

            // Success message
            flash()->success('Emergency contact updated successfully :)');
        } catch (Exception $e) {
            // Log the error and show failure message
            \Log::error('Failed to save emergency contact: ' . $e->getMessage());
            flash()->error('Failed to update emergency contact');
        }
        // Redirect back
        return redirect()->back();
    }

    // ============= USER MANAGEMENT WITH ATTENDANCE SYNC =============

    /** Add New User with Sync - for the user management form */
    public function addNewUserSaveWithSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|integer|exists:departments,id',
            'designation' => 'nullable|integer|exists:position_types,id',
            'status' => 'required|string',
            'role_name' => 'nullable|string|max:100',
            'password' => 'required|string|min:8|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle image upload
            $imageName = 'photo_defaults.jpg'; // default image
            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('assets/employee_profile_image'), $imageName);
            }

            // Hash password
            $hashedPassword = Hash::make($request->password);

            // Create user in payroll database - let the User model auto-generate user_id
            $user = new User();
            // Don't set user_id manually, let the boot method handle it
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone_number = $request->phone;
            
            // Store department and position using IDs from their respective tables
            $user->department = $request->department;
            $user->position = $request->designation;
            
            $user->status = $request->status;
            $user->role_name = $request->role_name ?? 'Employee';
            
            // Get department and designation names for sync purposes
            $departmentName = DB::table('departments')->where('id', $request->department)->value('department');
            $designationName = DB::table('position_types')->where('id', $request->designation)->value('position');
            $user->password = $hashedPassword;
            $user->avatar = $imageName;
            $user->join_date = Carbon::now()->format('d-m-Y');
            $user->last_login = Carbon::now()->toDayDateTimeString();
            $user->save();

            // Get the auto-generated user_id for sync
            $userId = $user->user_id;
            
            // Log user creation activity
            $userData = [
                'user_id' => $userId,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'department' => $request->department,
                'department_name' => $departmentName,
                'designation' => $request->designation,
                'designation_name' => $designationName,
                'status' => $request->status,
                'role_name' => $request->role_name ?? 'Employee',
                'join_date' => $user->join_date,
            ];
            ActivityLogService::logUserCreated($userData);

            // Sync with attendance application
            $syncResult = $this->syncUserWithAttendance([
                'user_id' => $userId,
                'payroll_id' => (string) $request->employee_id,       // Send employee_id as string
                'payroll_user_id' => $user->id,              // Send user id as payroll_user_id
                'name' => $request->name,
                'email' => $request->email,
                'role_name' => $request->role_name ?? 'Employee',
                'status' => $request->status,
                'department' => $departmentName ?? $request->department, // Send name, not ID
                'designation' => $designationName ?? $request->designation, // Send name, not ID
                'phone' => $request->phone,
                'password' => $hashedPassword, // Include password in sync data
            ], 'create');

            DB::commit();

            $message = 'User created successfully';
            if (!$syncResult) {
                $message .= ' (Warning: Failed to sync with attendance system)';
            }

            flash()->success($message);
            return redirect()->route('userManagement');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to create user", ['error' => $e->getMessage()]);
            
            flash()->error('Failed to create user: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /** Update User with Sync */
    public function updateUserWithSync(Request $request)
    {
       // Check if this is an employee-converted user first
        $user = User::where('user_id', $request->user_id)->first();
        if (!$user) {
            flash()->error('User not found');
            return redirect()->back();
        }

        // Define base validation rules
        $validationRules = [
            'user_id' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|integer|exists:departments,id',
            'designation' => 'nullable|integer|exists:position_types,id',
            'role_name' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8|confirmed',
            'images' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status_backup' => 'nullable|string', // Backup status field for employee users
        ];

        // For employee-converted users, status might not be sent (due to disabled field)
        // so make it optional. For manual users, status is required.
        if ($user->employee_id) {
            $validationRules['status'] = 'nullable|string'; // Employee users - status controlled by employee module
        } else {
            $validationRules['status'] = 'required|string'; // Manual users - status required
        }

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            flash()->error('Validation failed: ' . implode(', ', $validator->errors()->all()));
            return redirect()->back()->withInput();
        }

        try {
            DB::beginTransaction();

            $user = User::where('user_id', $request->user_id)->firstOrFail();

            // Handle image upload
            $imageName = $request->hidden_image;
            if ($request->hasFile('images')) {
                // Delete old image if not default
                if ($imageName && $imageName != 'photo_defaults.jpg' && file_exists(public_path('assets/employee_profile_image/' . $imageName))) {
                    unlink(public_path('assets/employee_profile_image/' . $imageName));
                }
                
                $imageName = time() . '.' . $request->images->extension();
                $request->images->move(public_path('assets/employee_profile_image'), $imageName);
            }

            // Update user record
            $user->name = $request->name;
            $user->email = $request->email;
            $user->department = $request->department; // Now storing department ID
            $user->position = $request->designation; // Now storing position/designation ID
            
            // Handle status update based on user type
            if ($user->employee_id) {
                // For employee-converted users, don't update status if not provided (it's controlled by employee module)
                $statusValue = $request->status ?? $request->status_backup ?? $user->status;
                if ($statusValue) {
                    $user->status = $statusValue;
                }
                // If no status provided, keep the current status unchanged
            } else {
                // For manual users, update status normally
                $user->status = $request->status;
            }
            
            $user->role_name = $request->role_name ?? $user->role_name;
            $user->avatar = $imageName;
            $user->updated_at = now();
            
            // Get department and designation names for sync purposes
            $departmentName = DB::table('departments')->where('id', $request->department)->value('department');
            $designationName = DB::table('position_types')->where('id', $request->designation)->value('position');
            
            // If this is an employee-converted user, update the phone number in employee_basic_details
            if ($user->employee_id) {
                // Update phone in employee_basic_details - use id or try to match by user_id in employee_id field
                $employeeDetails = DB::table('employee_basic_details')
                    ->where('id', $user->employee_id)
                    ->orWhere('employee_id', $user->user_id)
                    ->first();
                    
                if ($employeeDetails) {
                    DB::table('employee_basic_details')
                        ->where('id', $employeeDetails->id)
                        ->update([
                            'name' => $request->name,
                            'email' => $request->email,
                            'contact_number' => $request->phone,
                            'gender' => $request->gender ?? ($employeeDetails->gender ?? ''),
                            'date_of_birth' => $request->date_of_birth ?? ($employeeDetails->date_of_birth ?? ''),
                            'marital_status' => $request->marital_status ?? ($employeeDetails->marital_status ?? ''),
                            'designation' => $request->designation ?? ($employeeDetails->designation ?? ''),
                            'department' => $request->department ?? ($employeeDetails->department ?? ''),
                            'updated_at' => now(),
                        ]);
                        
                    // Address handling removed - not needed for this update
                    \Log::debug('Employee basic details updated successfully', [
                        'employee_id' => $employeeDetails->id,
                        'user_id' => $request->user_id
                    ]);
                }
            } else {
                // Regular user, update phone in users table
                $user->phone_number = $request->phone;
                
                // Also update profile_information if it exists
                $profileInfo = DB::table('profile_information')
                    ->where('user_id', $request->user_id)
                    ->first();
                    
                if ($profileInfo) {
                    DB::table('profile_information')
                        ->where('user_id', $request->user_id)
                        ->update([
                            'phone_number' => $request->phone,
                            'gender' => $request->gender ?? (property_exists($profileInfo, 'gender') ? ($profileInfo->gender ?? '') : ''),
                        ]);
                }
            }
            
            // Handle password change if provided
            $passwordChanged = false;
            $hashedPassword = null;
            if (!empty($request->password)) {
                $hashedPassword = Hash::make($request->password);
                $user->password = $hashedPassword;
                $passwordChanged = true;
            }
            
            $user->save();

            // Prepare data for attendance sync
            $syncData = [
                'user_id' => $request->user_id,
                'payroll_id' => (string) $user->employee_id,          // Send employee_id as string
                'payroll_user_id' => $user->id,              // Send user id as payroll_user_id
                'name' => $request->name,
                'email' => $request->email,
                'role_name' => $request->role_name ?? 'Employee',
                'status' => $request->status,
                'department' => $departmentName ?? $request->department, // Send name, not ID
                'designation' => $designationName ?? $request->designation, // Send name, not ID
                'phone' => $request->phone,
            ];

            // Sync with attendance application
            $syncResult = $this->syncUserWithAttendance($syncData, 'update', $request->user_id);
            
            // Sync password separately if it was changed
            $passwordSyncResult = true;
            if ($passwordChanged) {
                $passwordSyncResult = $this->syncPasswordWithAttendance($request->user_id, $request->password);
            }

            DB::commit();

            $message = 'User updated successfully';
            if (!$syncResult) {
                // Check if it's just a sync issue or a more serious problem
                $message .= ' (Warning: Failed to sync user data with attendance system - user may need to be created in attendance system first)';
            }
            if ($passwordChanged && !$passwordSyncResult) {
                $message .= ' (Warning: Failed to sync password with attendance system)';
            }

            flash()->success($message);
            return redirect()->route('userManagement');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to update user", ['user_id' => $request->user_id, 'error' => $e->getMessage()]);
            
            flash()->error('Failed to update user: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /** Delete User with Sync */
    public function deleteUserWithSync(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::where('user_id', $request->user_id)->firstOrFail();
            
            // Store user data for activity logging before deletion
            $userData = [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number,
                'role_name' => $user->role_name,
                'status' => $user->status,
            ];

            // Delete image if not default
            if ($request->avatar && $request->avatar != 'photo_defaults.jpg' && file_exists(public_path('assets/images/' . $request->avatar))) {
                unlink(public_path('assets/images/' . $request->avatar));
            }

            // Delete from payroll database (ONLY users table, NOT employees table)
            $user->delete();
            
            // Log user deletion activity
            ActivityLogService::logUserDeleted($userData);

            // Sync deletion with attendance application
            $syncResult = $this->syncUserWithAttendance([], 'delete', $request->user_id);

            DB::commit();

            $message = 'User deleted successfully';
            if (!$syncResult) {
                $message .= ' (Warning: Failed to sync deletion with attendance system)';
            }

            flash()->success($message);
            return redirect()->route('userManagement');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error("Failed to delete user", ['user_id' => $request->user_id, 'error' => $e->getMessage()]);
            
            flash()->error('Failed to delete user: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /** Store Employee with Sync - for backward compatibility with routes */
    public function storeEmployeeWithSync(Request $request)
    {
        // Forward to the new user creation method with sync functionality
        return $this->addNewUserSaveWithSync($request);
    }

    /** Update Employee with Sync - for backward compatibility with routes */
    public function updateEmployeeWithSync(Request $request)
    {
        // Forward to the user update method with sync
        return $this->updateUserWithSync($request);
    }

    /** Delete Employee with Sync - for backward compatibility with routes */
    public function deleteEmployeeWithSync(Request $request)
    {
        // Forward to the user delete method with sync
        return $this->deleteUserWithSync($request);
    }

    /** Generate User ID */
    private function generateUserId()
    {
        // Get the latest user with DRI prefix to maintain consistency
        $lastUser = User::where('user_id', 'REGEXP', '^DRI-[0-9]+$')->orderByRaw('CAST(REPLACE(user_id, "DRI-", "") AS UNSIGNED) DESC')->first();
        
        if (!$lastUser || !$lastUser->user_id) {
            return 'DRI-0001'; // Start from DRI-0001
        }

        // Extract number from user_id (DRI-#### format, handles both 3 and 4 digit)
        if (preg_match('/^DRI-0*(\d+)$/', $lastUser->user_id, $matches)) {
            $lastId = intval($matches[1]);
        } else {
            $lastId = 0;
        }
        
        $newId = str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
        
        return 'DRI-' . $newId;
    }

    /** Sync User with Attendance Application */
    private function syncUserWithAttendance($userData, $action = 'create', $userId = null)
    {
        // Check if sync is enabled
        $syncEnabled = env('ATTENDANCE_SYNC_ENABLED', true);
        if (!$syncEnabled) {
            Log::info("Attendance sync is disabled", ['action' => $action, 'user_id' => $userId ?? $userData['user_id'] ?? 'unknown']);
            return true; // Return true to avoid showing error messages
        }

        try {
            $attendanceApiUrl = env('ATTENDANCE_API_BASE_URL', 'https://attendancedemo.isarva.in/api');
            $apiToken = env('ATTENDANCE_API_TOKEN', 'hrms_sync_token_2025_secure_key');

            // Validate configuration
            if (empty($attendanceApiUrl) || empty($apiToken)) {
                Log::warning("Attendance API configuration missing", [
                    'url' => $attendanceApiUrl,
                    'token_present' => !empty($apiToken)
                ]);
                return false;
            }

            $endpoint = match($action) {
                'create' => '/users/sync-simple',
                'update' => '/users/' . $userId . '/sync-simple',
                'delete' => '/users/' . $userId . '/sync-simple'
            };

            $method = match($action) {
                'create' => 'POST',
                'update' => 'PUT',
                'delete' => 'DELETE'
            };

            $fullUrl = rtrim($attendanceApiUrl, '/') . $endpoint;

            Log::info("Attempting to sync user with attendance", [
                'action' => $action,
                'url' => $fullUrl,
                'method' => $method,
                'user_id' => $userId ?? $userData['user_id'] ?? 'unknown',
                'data' => $userData
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'HRMS-Payroll-Sync/1.0'
            ])
            ->timeout(10) // Reduced timeout for faster failure
            ->connectTimeout(5)
            ->$method($fullUrl, $userData);

            if (!$response->successful()) {
                // Check if it's a "User not found" error (404 status with specific message)
                if ($response->status() === 404) {
                    $responseBody = $response->json();
                    if (isset($responseBody['message']) && str_contains(strtolower($responseBody['message']), 'user not found')) {
                        // For user not found, try creating the user instead of updating
                        if ($action === 'update') {
                            Log::info("User not found in attendance system, attempting to create instead", [
                                'user_id' => $userId ?? $userData['user_id'] ?? 'unknown',
                                'original_action' => $action
                            ]);
                            
                            // Try creating the user instead
                            $createEndpoint = '/users/sync-simple';
                            $createUrl = rtrim($attendanceApiUrl, '/') . $createEndpoint;
                            
                            $createResponse = Http::withHeaders([
                                'Authorization' => 'Bearer ' . $apiToken,
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json',
                                'User-Agent' => 'HRMS-Payroll-Sync/1.0'
                            ])
                            ->timeout(10)
                            ->connectTimeout(5)
                            ->post($createUrl, $userData);
                            
                            if ($createResponse->successful()) {
                                Log::info("User created successfully in attendance system", [
                                    'user_id' => $userId ?? $userData['user_id'] ?? 'unknown',
                                    'response' => $createResponse->json()
                                ]);
                                return true;
                            } else {
                                Log::warning("Failed to create user in attendance system", [
                                    'user_id' => $userId ?? $userData['user_id'] ?? 'unknown',
                                    'url' => $createUrl,
                                    'status' => $createResponse->status(),
                                    'response' => $createResponse->body()
                                ]);
                                return false;
                            }
                        }
                    }
                }
                
                Log::warning("Failed to sync user with attendance", [
                    'action' => $action,
                    'user_id' => $userId ?? $userData['user_id'] ?? 'unknown',
                    'url' => $fullUrl,
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'headers' => $response->headers()
                ]);
                return false;
            }

            Log::info("User synced successfully with attendance", [
                'action' => $action,
                'user_id' => $userId ?? $userData['user_id'] ?? 'unknown',
                'response' => $response->json()
            ]);

            return true;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("Connection failed to attendance system", [
                'action' => $action,
                'user_id' => $userId ?? $userData['user_id'] ?? 'unknown',
                'error' => $e->getMessage(),
                'suggestion' => 'Check if attendance application is running and accessible'
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error("Error syncing user with attendance", [
                'action' => $action,
                'user_id' => $userId ?? $userData['user_id'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /** Generate Employee ID */
    private function generateEmployeeId()
    {
        $lastEmployee = EmployeeBasicDetail::orderBy('id', 'desc')->first();
        
        if (!$lastEmployee) {
            return 'EMP001';
        }

        $lastId = intval(substr($lastEmployee->emp_id, 3));
        $newId = str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
        
        return 'EMP' . $newId;
    }

    /** Fetch User Details for Edit Form (including employee data if applicable) */
    public function getUserDetails(Request $request)
    {
        $userId = $request->user_id;
        
        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }
        
        \Log::debug('getUserDetails called for user_id: ' . $userId);
        
        try {
            // Get the user record with complete details
            $user = DB::table('users')->where('user_id', $userId)->first();
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            \Log::debug('Found user in getUserDetails', [
                'user_id' => $userId,
                'name' => $user->name,
                'employee_id' => $user->employee_id,
                'phone' => $user->phone_number
            ]);
            
            // Get department ID from departments table
            $departmentId = null;
            try {
                $departmentId = DB::table('departments')
                    ->where('department', $user->department)
                    ->orWhere('id', $user->department)
                    ->value('id');
                
                \Log::debug('Department lookup', [
                    'department_value' => $user->department,
                    'found_id' => $departmentId
                ]);
            } catch (\Exception $e) {
                \Log::error('Error getting department ID', ['error' => $e->getMessage()]);
            }
            
            // Get designation/position ID from position_types table
            $designationId = null;
            try {
                $designationId = DB::table('position_types')
                    ->where('position', $user->position)
                    ->orWhere('id', $user->position)
                    ->value('id');
                
                \Log::debug('Position lookup', [
                    'position_value' => $user->position,
                    'found_id' => $designationId
                ]);
            } catch (\Exception $e) {
                \Log::error('Error getting designation ID', ['error' => $e->getMessage()]);
            }
            
            // Initialize data with user table values
            $data = [
                'name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->user_id,
                'phone' => $user->phone_number,
                'role_name' => $user->role_name,
                'department' => $departmentId ?? $user->department,
                'position' => $designationId ?? $user->position,
                'designation' => $designationId ?? $user->position,
                'status' => $user->status,
                'avatar' => $user->avatar,
                'is_employee' => (bool)$user->employee_id,
                'is_employee_converted' => (bool)$user->employee_id,
                'readonly_fields' => [],
                'editable_fields' => [],
            ];
            
            // Check if this is an employee-converted user
            if ($user->employee_id) {
                try {
                    // Try multiple ways to find the correct employee record
                    $employeeData = null;
                    
                    // Try every possible way to match the employee record
                    $employeeData = DB::table('employee_basic_details')
                        ->where(function($query) use ($user) {
                            // Try numeric ID match
                            if (is_numeric($user->employee_id)) {
                                $query->orWhere('id', $user->employee_id);
                            }
                            
                            // Try string employee_id match
                            $query->orWhere('employee_id', $user->employee_id);
                            
                            // Try user_id to employee_id match
                            $query->orWhere('employee_id', $user->user_id);
                            
                            // Try employee name match as a last resort
                            $query->orWhere('name', 'like', '%' . $user->name . '%');
                        })
                        ->first();
                        
                    \Log::debug('Employee lookup results', [
                        'user_id' => $user->user_id,
                        'employee_id_from_user' => $user->employee_id,
                        'user_name' => $user->name,
                        'found' => $employeeData ? true : false,
                        'found_employee_id' => $employeeData ? $employeeData->employee_id : null,
                        'found_id' => $employeeData ? $employeeData->id : null,
                        'found_name' => $employeeData ? $employeeData->name : null,
                    ]);
                    
                    \Log::debug('Employee data search result', [
                        'user_id' => $userId,
                        'employee_id_from_user' => $user->employee_id,
                        'employee_found' => $employeeData ? 'Yes' : 'No'
                    ]);
                    
                    if ($employeeData) {
                        $data['is_employee'] = true;
                        $data['is_employee_converted'] = true;
                        $data['employee_id'] = $employeeData->employee_id;
                        
                        // Set read-only and editable fields for employee-converted users
                        $data['readonly_fields'] = ['name', 'email', 'phone', 'department', 'position', 'status', 'role_name'];
                        $data['editable_fields'] = ['password'];
                        $data['employee_message'] = 'This user was created from an employee. Most fields can only be updated in the Employee module.';
                        
                        // Always set phone to employee contact_number for employee users
                        $data['phone'] = $employeeData->contact_number ?? '';
                        
                        // Set employee-specific fields from employee_basic_details
                        $data['gender'] = $employeeData->gender ?? '';
                        $data['date_of_birth'] = $employeeData->date_of_birth ?? '';
                        $data['marital_status'] = $employeeData->marital_status ?? '';
                        
                        // Address fields removed - not needed
                        
                        // Log for debugging
                        \Log::debug('Employee data in getUserDetails', [
                            'user_id' => $userId,
                            'employee_id' => $user->employee_id,
                            'employee_found_id' => $employeeData->id,
                            'employee_found_employee_id' => $employeeData->employee_id,
                            'contact_number' => $employeeData->contact_number,
                            'gender' => $employeeData->gender,
                            'date_of_birth' => $employeeData->date_of_birth,
                            'marital_status' => $employeeData->marital_status,
                            'final_phone_value' => $data['phone'],
                            'final_gender' => $data['gender'],
                            'final_marital_status' => $data['marital_status'],
                        ]);
                        
                        // Make sure designation and department are both set from employee data
                        // Using the IDs directly from employee_basic_details
                        $data['designation'] = $employeeData->designation;
                        $data['position'] = $employeeData->designation;
                        $data['department'] = $employeeData->department;
                        
                        // Address and personal details removed - not needed for this application
                    } else {
                        \Log::warning('User marked as employee but no employee record found', [
                            'user_id' => $userId,
                            'employee_id' => $user->employee_id
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error processing employee data', [
                        'user_id' => $userId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                // Not an employee, get data from profile_information
                $profileInfo = ProfileInformation::where('user_id', $userId)->first();
                
                if ($profileInfo) {
                    if ($profileInfo->phone_number) {
                        $data['phone'] = $profileInfo->phone_number;
                    }
                    $data['gender'] = $profileInfo->gender;
                    
                    \Log::debug('Profile info found for non-employee user', [
                        'user_id' => $userId,
                        'phone' => $profileInfo->phone_number
                    ]);
                }
                
                // For regular users, all fields are editable
                $data['readonly_fields'] = [];
                $data['editable_fields'] = ['name', 'email', 'phone', 'department', 'position', 'status', 'role_name', 'password'];
                $data['employee_message'] = '';
            }
            
            // Log the final data being sent back
            \Log::debug('getUserDetails response data:', $data);
            
            return response()->json($data);
            
        } catch (\Exception $e) {
            \Log::error("Error fetching user details for edit", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Failed to fetch user details'], 500);
        }
    }

    /**
     * Sync password from attendance system to payroll system
     * This method receives password changes from attendance and updates payroll user
     */
    public function syncPasswordFromAttendance(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_email' => 'required|email',
                'new_password' => 'required|string',
                'sync_token' => 'required|string' // Add security token
            ]);

            if ($validator->fails()) {
                Log::error('Password sync from attendance validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'request' => $request->all()
                ]);
                return response()->json(['error' => 'Validation failed', 'details' => $validator->errors()], 400);
            }

            // Verify sync token for security
            $expectedToken = env('ATTENDANCE_SYNC_TOKEN', 'default-token');
            if ($request->sync_token !== $expectedToken) {
                Log::warning('Invalid sync token for password sync from attendance', [
                    'provided_token' => $request->sync_token,
                    'ip' => $request->ip()
                ]);
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Find user by email in payroll system
            $user = User::where('email', $request->user_email)->first();
            
            if (!$user) {
                Log::warning('User not found in payroll system for password sync', [
                    'email' => $request->user_email
                ]);
                return response()->json(['error' => 'User not found in payroll system'], 404);
            }

            // Update password in payroll system
            $user->password = Hash::make($request->new_password);
            $user->save();

            // Log successful password sync
            ActivityLogService::log(
                'Password synced from attendance system', 
                'Users', 
                $user->id,
                $user->name . ' password updated from attendance system',
                ['synced_from' => 'attendance', 'user_email' => $request->user_email]
            );

            Log::info('Password successfully synced from attendance to payroll', [
                'user_email' => $request->user_email,
                'user_id' => $user->id,
                'synced_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password successfully synced to payroll system',
                'user_id' => $user->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing password from attendance system', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json(['error' => 'Failed to sync password'], 500);
        }
    }
}