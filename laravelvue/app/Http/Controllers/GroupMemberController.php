<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupMemberController extends Controller
{
    // 🧑‍🤝‍🧑 Lấy danh sách thành viên nhóm
    public function index($groupId)
    {
        $group = Group::findOrFail($groupId);

        $members = $group->members()->select('users.id', 'users.name', 'users.email')
            ->withPivot('role')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'role' => $member->pivot->role ?? 'member',
                ];
            });

        return response()->json($members);
    }

    // ➕ Thêm thành viên theo user_id
    public function store(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);

        if ($group->created_by !== Auth::id()) {
            return response()->json(['message' => 'Bạn không có quyền thêm thành viên'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        GroupMember::updateOrCreate(
            [
                'group_id' => $groupId,
                'user_id' => $request->user_id,
            ],
            [
                'role' => 'member',
            ]
        );

        return response()->json(['message' => 'Đã thêm thành viên']);
    }

    // 📧 Thêm thành viên theo email
    public function storeByEmail(Request $request, $groupId)
    {
        $group = Group::findOrFail($groupId);

        if ($group->created_by !== Auth::id()) {
            return response()->json(['message' => 'Bạn không có quyền thêm'], 403);
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
        }

        GroupMember::updateOrCreate(
            [
                'group_id' => $group->id,
                'user_id' => $user->id,
            ],
            [
                'role' => 'member',
            ]
        );

        return response()->json(['message' => 'Đã thêm thành viên: ' . $user->name]);
    }

    // ❌ Xoá thành viên khỏi nhóm
    public function destroy($groupId, $userId)
    {
        $group = Group::findOrFail($groupId);

        if ($group->created_by !== Auth::id()) {
            return response()->json(['message' => 'Không có quyền xoá'], 403);
        }

        GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->delete();

        return response()->json(['message' => 'Đã xoá thành viên khỏi nhóm']);
    }
}
