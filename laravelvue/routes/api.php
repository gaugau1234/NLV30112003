<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\TaskController;
use App\Models\GroupMember;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

//  Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Lấy thông tin người dùng hiện tại
Route::middleware('auth:sanctum')->get('/user', function (\Illuminate\Http\Request $request) {
    $user = $request->user();

    // Lấy tất cả nhóm của user
    $groupMembers = GroupMember::where('user_id', $user->id)->get();
    $group_ids = $groupMembers->pluck('group_id')->toArray();

    // Thêm thuộc tính group_ids vào user object
    $user->group_ids = $group_ids;

    return $user;
});

//  Các route yêu cầu đăng nhập
use App\Http\Controllers\CommentController;

use App\Http\Controllers\NotificationController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
    Route::post('/tasks/{task}/comments', [CommentController::class, 'store']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    Route::post('/logout', [AuthController::class, 'logout']);

    // Cập nhật thông tin tài khoản
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    // Nhóm
    Route::get('/groups', [GroupController::class, 'index']);                   // Danh sách nhóm đã tham gia
    Route::post('/groups', [GroupController::class, 'store']);                  // Tạo nhóm mới
    Route::delete('/groups/{id}', [GroupController::class, 'destroy']);        // Xóa nhóm
    Route::post('/admin/reset-groups', [GroupController::class, 'resetGroupsAndMembers']); // Chỉ admin

    //  Thành viên nhóm
    Route::get('/groups/{group}/members', [GroupMemberController::class, 'index']);           // Danh sách thành viên
    Route::post('/groups/{group}/members', [GroupMemberController::class, 'store']);          // Thêm bằng ID
    Route::post('/groups/{group}/members/email', [GroupMemberController::class, 'storeByEmail']); // Thêm bằng email
    Route::delete('/groups/{group}/members/{user}', [GroupMemberController::class, 'destroy']); // Xóa thành viên

    // Công việc (Task)
    Route::get('/groups/{group}/tasks', [TaskController::class, 'index']);      // Lấy task theo nhóm
    Route::post('/groups/{group}/tasks', [TaskController::class, 'store']);     // Giao task theo nhóm

    // Lấy toàn bộ công việc nhóm không phân chia người nhận (dùng cho dashboard)
    Route::get('/groups/{group}/tasks/all', [TaskController::class, 'indexAllGroupTasks']);
    Route::post('/groups/tasks/all', [TaskController::class, 'indexAllGroupTasks']);

    // Task chuyên biệt
    Route::put('/tasks/{task}/status', [TaskController::class, 'updateStatus']); // Đổi trạng thái nhanh
    Route::post('/tasks/{task}/submit-file', [TaskController::class, 'submitFile']); // Nộp file
    Route::post('/notifications', [TaskController::class, 'sendNotification']); // Gửi thông báo (nếu có)

    //  Task cơ bản
    Route::apiResource('tasks', TaskController::class);

    // Report routes
    Route::get('/reports/tasks/export', [\App\Http\Controllers\ReportController::class, 'exportTasksExcel']);
    Route::post('/reports/groups/performance', [\App\Http\Controllers\ReportController::class, 'evaluateGroupPerformance']);
    Route::post('/reports/groups/performance-by-id', [\App\Http\Controllers\ReportController::class, 'evaluateGroupPerformanceById']);
});
