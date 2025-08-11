<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;



class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    //  Lấy danh sách công việc (có thể lọc theo nhóm)


    public function index(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $query = Task::query();

            if ($request->has('group_id')) {
                // Trả về task thuộc nhóm được truyền và được giao cho user hiện tại
                $query->where('group_id', $request->group_id)
                      ->where('assigned_to', Auth::id());
            } else {
                // Trả về task được giao cho user hiện tại
                $query->where('assigned_to', Auth::id());
            }

            // Gắn thêm thông tin người được giao nếu muốn render frontend đẹp
            $tasks = $query->with('assignee')->get();

            return response()->json($tasks);
        } catch (\Exception $e) {
            Log::error('Error fetching tasks: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi khi lấy danh sách công việc'], 500);
        }
    }

    // Lấy toàn bộ công việc của nhóm, không phân chia người nhận, dùng cho dashboard
    public function indexAllGroupTasks(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $userId = Auth::id();

            $groupIds = $request->input('group_ids');
            if (!$groupIds || !is_array($groupIds) || count($groupIds) === 0) {
                return response()->json(['message' => 'Thiếu group_ids hoặc không hợp lệ'], 400);
            }

            // Kiểm tra user có phải thành viên của tất cả các nhóm được yêu cầu
            $memberGroupIds = \App\Models\GroupMember::where('user_id', $userId)
                ->whereIn('group_id', $groupIds)
                ->pluck('group_id')
                ->toArray();

            if (count($memberGroupIds) !== count($groupIds)) {
                return response()->json(['message' => 'Bạn không phải thành viên của tất cả các nhóm'], 403);
            }

            $tasks = Task::whereIn('group_id', $groupIds)
                         ->with(['assignee', 'group'])
                         ->get();

            return response()->json($tasks);
        } catch (\Exception $e) {
            Log::error('Error fetching all group tasks: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi khi lấy danh sách công việc nhóm'], 500);
        }
    }


    //  Tạo công việc mới

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,in_progress,completed',
                'due_date' => 'nullable|date',
                'group_id' => 'required|exists:groups,id',
                'assigned_to' => 'required|exists:users,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in store task: ' . json_encode($e->errors()));
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $e->errors()], 422);
        }

        try {
            $group = \App\Models\Group::findOrFail($request->group_id);
            // Kiểm tra user có phải admin nhóm không
            $isAdmin = \App\Models\GroupMember::where('group_id', $group->id)
                ->where('user_id', Auth::id())
                ->where('role', 'admin')
                ->exists();
            if (!$isAdmin) {
                return response()->json(['message' => 'Bạn không có quyền giao nhiệm vụ'], 403);
            }
            // Chỉ giao cho thành viên trong nhóm
            $isMember = \App\Models\GroupMember::where('group_id', $group->id)
                ->where('user_id', $request->assigned_to)
                ->exists();
            if (!$isMember) {
                return response()->json(['message' => 'Người này không phải thành viên nhóm'], 422);
            }

            $task = Task::create([
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status,
                'due_date' => $request->due_date,
                'group_id' => $request->group_id,
                'assigned_to' => $request->assigned_to,
                'created_by' => Auth::id()
            ]);

            // Create notification for task assignment
            $group = \App\Models\Group::find($request->group_id);
            $creator = Auth::user();

            \App\Models\Notification::create([
                'user_id' => $request->assigned_to,
                'message' => "Bạn được giao công việc '{$request->title}' trong nhóm '{$group->name}' bởi {$creator->name}",
                'is_read' => false,
                'type' => 'task_assigned',
                'data' => json_encode([
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'assigned_by' => $creator->id,
                    'assigned_by_name' => $creator->name,
                    'due_date' => $task->due_date
                ])
            ]);

            return response()->json($task, 201);
        } catch (\Exception $e) {
            Log::error('Error creating task: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi khi tạo công việc'], 500);
        }
    }

    //  Hiển thị chi tiết công việc
    public function show($id)
    {
        $task = Task::with('group', 'assignee')
            ->where('id', $id)
            ->where(function ($query) {
                $query->where('assigned_to', Auth::id());
            })
            ->firstOrFail();

        return response()->json($task);
    }

    //  Cập nhật thông tin công việc
    public function update(Request $request, $id)
    {
        try {
            $task = Task::where('id', $id)
                ->where(function ($query) {
                    $query->where('created_by', Auth::id())
                        ->orWhere('assigned_to', Auth::id());
                })
                ->firstOrFail();

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'in:pending,in_progress,completed',
                'due_date' => 'nullable|date',
                'group_id' => 'nullable|exists:groups,id',
                'assigned_to' => 'nullable|exists:users,id',
            ]);

            $oldAssignedTo = $task->assigned_to;
            $task->update($request->only([
                'title',
                'description',
                'status',
                'due_date',
                'group_id',
                'assigned_to'
            ]));

            // Create notification if task is reassigned to a different user
            if ($request->has('assigned_to') && $request->assigned_to != $oldAssignedTo) {
                $group = \App\Models\Group::find($task->group_id);
                $updater = Auth::user();

                \App\Models\Notification::create([
                    'user_id' => $request->assigned_to,
                    'message' => "Bạn được giao công việc '{$task->title}' trong nhóm '{$group->name}' bởi {$updater->name}",
                    'is_read' => false,
                    'type' => 'task_assigned',
                    'data' => json_encode([
                        'task_id' => $task->id,
                        'task_title' => $task->title,
                        'group_id' => $group->id,
                        'group_name' => $group->name,
                        'assigned_by' => $updater->id,
                        'assigned_by_name' => $updater->name,
                        'due_date' => $task->due_date
                    ])
                ]);
            }

            return response()->json($task);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Task update error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Lỗi hệ thống', 'error' => $e->getMessage()], 500);
        }
    }

    // Xoá công việc
    public function destroy($id)
    {
        $task = Task::where('id', $id)
            ->where('created_by', Auth::id())
            ->firstOrFail();

        $task->delete();

        return response()->json(['message' => 'Xoá thành công']);
    }
}
