<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Lấy danh sách thông báo của user
    public function index()
    {
        try {
            $userId = Auth::id();

            // Fetch existing notifications
            $notifications = Notification::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();

            // Fetch tasks assigned to user that are about to expire within 24 hours and not completed
            $now = now();
            $nextDay = $now->copy()->addDays(2);

            $expiringTasks = \App\Models\Task::where('assigned_to', $userId)
                ->where('status', '!=', 'completed')
                ->whereBetween('due_date', [$now, $nextDay])
                ->get();

            // Transform expiring tasks into notification-like objects
            foreach ($expiringTasks as $task) {
                $notifications[] = [
                    'id' => 'task-expiring-' . $task->id,
                    'user_id' => $userId,
                    'message' => "Công việc '{$task->title}' sắp hết hạn vào " . $task->due_date->format('d/m/Y H:i'),
                    'created_at' => $task->due_date,
                    'is_read' => false,
                    'is_task_expiring' => true,
                    'task_id' => $task->id,
                ];
            }

            // Sort notifications by created_at descending
            usort($notifications, function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return response()->json($notifications);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi khi lấy thông báo'], 500);
        }
    }

    // Đánh dấu thông báo đã đọc
    public function markAsRead($id)
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $notification->is_read = true;
            $notification->save();

            return response()->json(['message' => 'Đã đánh dấu là đã đọc']);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi khi đánh dấu thông báo'], 500);
        }
    }
}
