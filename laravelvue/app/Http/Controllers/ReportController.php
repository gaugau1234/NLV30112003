<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct()
    {
        // Removed auth middleware to allow unauthenticated access
    }

    // Removed isAdmin method as no longer needed

    // Export tasks as CSV (Excel compatible)
    public function exportTasksExcel(Request $request)
    {
        // Removed admin check to allow anyone to access

        $groupIds = $request->input('group_ids', []);

        if (empty($groupIds)) {
            return response()->json(['message' => 'Thiếu group_ids'], 400);
        }

        $tasks = Task::whereIn('group_id', $groupIds)
            ->with('group', 'assignee')
            ->get();

        $response = new StreamedResponse(function () use ($tasks) {
            $handle = fopen('php://output', 'w');
            // Header row
            fputcsv($handle, [
                'ID', 'Title', 'Description', 'Group', 'Assignee', 'Status', 'Due Date', 'Created By'
            ]);

            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $task->id,
                    $task->title,
                    $task->description,
                    $task->group ? $task->group->name : '',
                    $task->assignee ? $task->assignee->name : '',
                    $task->status,
                    $task->due_date,
                    $task->created_by,
                ]);
            }

            fclose($handle);
        });

        $filename = 'tasks_report_' . date('Ymd_His') . '.csv';

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$filename\"");

        return $response;
    }

    // Evaluate group performance: % completed tasks per group
    public function evaluateGroupPerformance(Request $request)
    {
        // Removed admin check to allow anyone to access

        $groupNames = $request->input('group_names', []);

        if (empty($groupNames)) {
            return response()->json(['message' => 'Thiếu group_names'], 400);
        }

        // Find group IDs by names
        $groups = \App\Models\Group::whereIn('name', $groupNames)->get();

        if ($groups->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy nhóm nào'], 404);
        }

        $performance = [];

        foreach ($groups as $group) {
            $groupId = $group->id;
            $totalTasks = Task::where('group_id', $groupId)
                ->count();

            $completedTasks = Task::where('group_id', $groupId)
                ->where('status', 'completed')
                ->count();

            $rate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

            $performance[] = [
                'group_id' => $groupId,
                'group_name' => $group->name,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $rate,
            ];
        }

        return response()->json($performance);
    }

    // Evaluate group performance by group IDs
    public function evaluateGroupPerformanceById(Request $request)
    {
        $groupIds = $request->input('group_ids', []);

        if (empty($groupIds)) {
            return response()->json(['message' => 'Thiếu group_ids'], 400);
        }

        $groups = \App\Models\Group::whereIn('id', $groupIds)->get();

        if ($groups->isEmpty()) {
            return response()->json(['message' => 'Không tìm thấy nhóm nào'], 404);
        }

        $performance = [];

        foreach ($groups as $group) {
            $groupId = $group->id;
            $totalTasks = Task::where('group_id', $groupId)
                ->count();

            $completedTasks = Task::where('group_id', $groupId)
                ->where('status', 'completed')
                ->count();

            $rate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

            $performance[] = [
                'group_id' => $groupId,
                'group_name' => $group->name,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $rate,
            ];
        }

        return response()->json($performance);
    }
}
