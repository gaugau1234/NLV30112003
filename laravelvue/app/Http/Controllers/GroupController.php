<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    // 📋 Lấy tất cả nhóm mà user đang tham gia
    public function index()
    {
        $userId = Auth::id();

        // Lấy danh sách group_id từ bảng group_members
        $groupIds = GroupMember::where('user_id', $userId)->pluck('group_id');

        // Truy vấn danh sách nhóm
        $groups = Group::whereIn('id', $groupIds)->get();

        return response()->json($groups);
    }

    // ➕ Tạo nhóm mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $group = Group::create([
            'name' => $request->name,
            'created_by' => Auth::id()
        ]);

        // Thêm người tạo nhóm làm admin
        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => Auth::id(),
            'role' => 'admin'
        ]);

        return response()->json($group, 201);
    }

    // ❌ Xoá nhóm
    public function destroy($id)
    {
        $group = Group::findOrFail($id);

        if ($group->created_by !== Auth::id()) {
            return response()->json(['message' => 'Bạn không có quyền xoá nhóm này'], 403);
        }

        $group->delete();

        return response()->json(['message' => 'Đã xoá nhóm']);
    }

    // 🔄 Reset toàn bộ nhóm (chỉ dành cho admin)
    public function resetGroupsAndMembers()
    {
        $user = Auth::user();
        if (!$user || !$user->is_admin) {
            return response()->json(['message' => 'Bạn không có quyền thực hiện thao tác này'], 403);
        }

        DB::table('group_members')->truncate();
        DB::table('groups')->truncate();

        return response()->json(['message' => 'Đã xoá và reset dữ liệu thành công!']);
    }
}
