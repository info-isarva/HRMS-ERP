<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'employees.view', 'employees.create'
            $table->string('module'); // e.g., 'employees', 'payroll'
            $table->string('action'); // e.g., 'view', 'create', 'edit', 'delete'
            $table->string('display_name'); // Human readable name
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert employee-specific permissions first
        $permissions = [
            // EmployeeController permissions
            ['name' => 'employees.view', 'module' => 'employees', 'action' => 'view', 'display_name' => 'View Employees', 'description' => 'Can view employees list and details'],
            ['name' => 'employees.create', 'module' => 'employees', 'action' => 'create', 'display_name' => 'Create Employees', 'description' => 'Can create new employees'],
            ['name' => 'employees.edit', 'module' => 'employees', 'action' => 'edit', 'display_name' => 'Edit Employees', 'description' => 'Can edit existing employees'],
            ['name' => 'employees.delete', 'module' => 'employees', 'action' => 'delete', 'display_name' => 'Delete Employees', 'description' => 'Can delete employees'],
            ['name' => 'employees.documents', 'module' => 'employees', 'action' => 'documents', 'display_name' => 'Manage Employee Documents', 'description' => 'Can manage employee documents'],
            ['name' => 'employees.letters', 'module' => 'employees', 'action' => 'letters', 'display_name' => 'Generate Employee Letters', 'description' => 'Can generate employee letters'],
            
            // Other controller permissions
            ['name' => 'dashboard.view', 'module' => 'dashboard', 'action' => 'view', 'display_name' => 'View Dashboard', 'description' => 'Can view dashboard'],
            ['name' => 'payroll.view', 'module' => 'payroll', 'action' => 'view', 'display_name' => 'View Payroll', 'description' => 'Can view payroll'],
            ['name' => 'payroll.create', 'module' => 'payroll', 'action' => 'create', 'display_name' => 'Create Payroll', 'description' => 'Can create payroll'],
            ['name' => 'payroll.edit', 'module' => 'payroll', 'action' => 'edit', 'display_name' => 'Edit Payroll', 'description' => 'Can edit payroll'],
            ['name' => 'payroll.finalize', 'module' => 'payroll', 'action' => 'finalize', 'display_name' => 'Finalize Payroll', 'description' => 'Can finalize payroll'],
            ['name' => 'reports.view', 'module' => 'reports', 'action' => 'view', 'display_name' => 'View Reports', 'description' => 'Can view reports'],
            ['name' => 'reports.generate', 'module' => 'reports', 'action' => 'generate', 'display_name' => 'Generate Reports', 'description' => 'Can generate reports'],
            ['name' => 'settings.view', 'module' => 'settings', 'action' => 'view', 'display_name' => 'View Settings', 'description' => 'Can view settings'],
            ['name' => 'settings.edit', 'module' => 'settings', 'action' => 'edit', 'display_name' => 'Edit Settings', 'description' => 'Can edit settings'],
            ['name' => 'user_management.view', 'module' => 'user_management', 'action' => 'view', 'display_name' => 'View User Management', 'description' => 'Can view user management'],
            ['name' => 'user_management.create', 'module' => 'user_management', 'action' => 'create', 'display_name' => 'Create Users', 'description' => 'Can create users'],
            ['name' => 'user_management.edit', 'module' => 'user_management', 'action' => 'edit', 'display_name' => 'Edit Users', 'description' => 'Can edit users'],
            ['name' => 'user_management.delete', 'module' => 'user_management', 'action' => 'delete', 'display_name' => 'Delete Users', 'description' => 'Can delete users'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
