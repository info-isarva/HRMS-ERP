<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a notification as read (single id) or mark all as read.
     */
    public function markRead(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $id = $request->input('id');
        $markAll = $request->input('all');

        if ($markAll) {
            $user->unreadNotifications->markAsRead();
            return response()->json(['success' => true, 'unread' => 0]);
        }

        if ($id) {
            $notif = $user->unreadNotifications->where('id', $id)->first();
            if ($notif) {
                $notif->markAsRead();
                $unreadCount = $user->unreadNotifications()->count();
                return response()->json(['success' => true, 'unread' => $unreadCount]);
            }
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
    }

    /**
     * Show paginated list of notifications for the current user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Return recent notifications and unread count as JSON for AJAX polling.
     */
    public function recent(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $notes = $user->notifications()->orderBy('created_at', 'desc')->limit(10)->get();
        $items = $notes->map(function($n){
            $data = $n->data ?? [];
            return [
                'id' => $n->id,
                'message' => $data['message'] ?? ($data['title'] ?? 'Notification'),
                'created_at' => $n->created_at->diffForHumans(),
                'read_at' => $n->read_at ? true : false,
                'task_link' => $data['task_link'] ?? ($data['related_link'] ?? $data['meeting_link'] ?? ''),
            ];
        });

        return response()->json([
            'success' => true,
            'unread' => $user->unreadNotifications()->count(),
            'items' => $items,
        ]);
    }
}
