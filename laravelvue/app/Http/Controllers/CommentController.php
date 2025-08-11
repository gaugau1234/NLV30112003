<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Illuminate\Routing\Controller;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // Lấy danh sách bình luận của task
    public function index($taskId)
    {
        try {
            $task = Task::findOrFail($taskId);

            // Kiểm tra user có quyền xem task (được giao hoặc tạo)
            $userId = Auth::id();
            if ($task->assigned_to !== $userId && $task->created_by !== $userId) {
                return response()->json(['message' => 'Không có quyền truy cập'], 403);
            }

            $comments = Comment::where('task_id', $taskId)
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'user_name' => $comment->user->name ?? 'Không rõ',
                        'created_at' => $comment->created_at,
                    ];
                });

            return response()->json($comments);
        } catch (\Exception $e) {
            Log::error('Error fetching comments: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi khi lấy bình luận', 'error' => $e->getMessage()], 500);
        }
    }

    // Thêm bình luận mới cho task
    public function store(Request $request, $taskId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        try {
            $task = Task::findOrFail($taskId);

            // Kiểm tra user có quyền bình luận (được giao hoặc tạo)
            $userId = Auth::id();
            if ($task->assigned_to !== $userId && $task->created_by !== $userId) {
                return response()->json(['message' => 'Không có quyền bình luận'], 403);
            }

            $comment = Comment::create([
                'task_id' => $taskId,
                'user_id' => $userId,
                'content' => $request->content,
            ]);

            // Gửi thông báo cho người đang làm công việc (assigned_to)
            $task = Task::find($taskId);
            if ($task && $task->assigned_to) {
                // Tạo thông báo trực tiếp
                \App\Models\Notification::create([
                    'user_id' => $task->assigned_to,
                'message' => 'Có bình luận mới cho công việc "' . $task->title . '": "' . $comment->content . '"',
                    'is_read' => false,
                ]);
            }

            // Load user relation
            $comment->load('user');

            return response()->json([
                'id' => $comment->id,
                'content' => $comment->content,
                'user_name' => $comment->user->name ?? 'Không rõ',
                'created_at' => $comment->created_at,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating comment: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi khi thêm bình luận', 'error' => $e->getMessage()], 500);
        }
    }
}
