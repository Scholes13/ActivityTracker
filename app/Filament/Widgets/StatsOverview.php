<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\SubTask;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = '10s';
    
    protected function getStats(): array
    {
        $user = auth()->user();
        
        // Log the current user information
        Log::info('StatsOverview - User Info', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_name' => $user->name,
        ]);
        
        // Base query sesuai role
        $subTaskQuery = SubTask::query();
        
        // *** CRITICAL FIX: Use fresh queries for each count to avoid query pollution ***
        $activityBaseQuery = Activity::query();
        
        // Filter data berdasarkan role
        if ($user->role === 'leader') {
            $subTaskQuery->where('leader_id', $user->id);
            $subtaskIds = $subTaskQuery->pluck('id')->toArray();
            
            Log::info('StatsOverview - Leader Filter', [
                'leader_id' => $user->id,
                'subtask_count' => count($subtaskIds),
                'subtask_ids' => $subtaskIds,
            ]);
        } elseif ($user->role === 'member') {
            if ($user->parent_id) {
                $subTaskQuery->where('leader_id', $user->parent_id);
                $subtaskIds = $subTaskQuery->pluck('id')->toArray();
                
                Log::info('StatsOverview - Member Filter', [
                    'member_id' => $user->id,
                    'parent_id' => $user->parent_id,
                    'subtask_count' => count($subtaskIds),
                ]);
            } else {
                $subtaskIds = [];
            }
        } elseif ($user->role === 'captain' || $user->role === 'sc') {
            // For captain and SC, get all subtask IDs but don't filter the query
            $subtaskIds = SubTask::pluck('id')->toArray();
            
            Log::info('StatsOverview - Captain/SC viewing all data', [
                'role' => $user->role,
                'total_subtasks_available' => count($subtaskIds)
            ]);
        } else {
            // Admin - get all subtask IDs
            $subtaskIds = SubTask::pluck('id')->toArray();
        }
        
        // Hitung data statistik
        $totalSubTasks = $subTaskQuery->count();
        $activeSubTasks = (clone $subTaskQuery)->where('status', 'active')->count();
        
        // *** CRITICAL FIX: Create new activity queries for each status to avoid filter contamination ***
        $totalActivities = 0;
        $activitiesWaiting = 0;
        $activitiesInProgress = 0;
        $activitiesCompleted = 0;
        
        // Get proper counts based on role
        if ($user->role === 'captain' || $user->role === 'sc' || $user->role === 'admin') {
            // For captain/sc/admin roles, we count all activities
            $totalActivities = Activity::count();
            $activitiesWaiting = Activity::where('status', 'waiting')->count();
            $activitiesInProgress = Activity::where('status', 'inprogress')->count();
            $activitiesCompleted = Activity::where('status', 'done')->count();
            
            // Log raw SQL for debugging
            $inProgressQuery = Activity::where('status', 'inprogress');
            
            Log::info('StatsOverview - Captain Activity Counts', [
                'total' => $totalActivities,
                'waiting' => $activitiesWaiting,
                'inprogress' => $activitiesInProgress,
                'completed' => $activitiesCompleted,
                'inprogress_sql' => $inProgressQuery->toSql(),
                'inprogress_bindings' => $inProgressQuery->getBindings(),
            ]);
        } else {
            // For leader/member, filter by subtask IDs
            if (!empty($subtaskIds)) {
                $totalActivities = Activity::whereIn('sub_task_id', $subtaskIds)->count();
                $activitiesWaiting = Activity::whereIn('sub_task_id', $subtaskIds)->where('status', 'waiting')->count();
                $activitiesInProgress = Activity::whereIn('sub_task_id', $subtaskIds)->where('status', 'inprogress')->count();
                $activitiesCompleted = Activity::whereIn('sub_task_id', $subtaskIds)->where('status', 'done')->count();
            }
        }
        
        // Double-check inprogress count with direct query for debugging
        $directInProgressCount = DB::table('activities')->where('status', 'inprogress')->count();
        
        // Log the calculation results
        Log::info('StatsOverview - Stats Calculation', [
            'total_subtasks' => $totalSubTasks,
            'active_subtasks' => $activeSubTasks,
            'total_activities' => $totalActivities,
            'waiting_activities' => $activitiesWaiting,
            'inprogress_activities' => $activitiesInProgress,
            'completed_activities' => $activitiesCompleted,
            'direct_inprogress_count' => $directInProgressCount,
        ]);
        
        $completionRate = $totalActivities > 0 
            ? round(($activitiesCompleted / $totalActivities) * 100) 
            : 0;
        
        // Set up a fresh query for upcoming deadlines
        $deadlineQuery = Activity::query();
        
        if (!in_array($user->role, ['captain', 'sc', 'admin']) && !empty($subtaskIds)) {
            $deadlineQuery->whereIn('sub_task_id', $subtaskIds);
        }
        
        // Deadline insights - aktivitas dengan deadline dalam 7 hari
        $upcomingDeadlines = $deadlineQuery->whereDate('deadline', '>=', now())
            ->whereDate('deadline', '<=', now()->addDays(7))
            ->where('status', '!=', 'done')
            ->count();
        
        // Menghitung jumlah sub task berdasarkan tipe
        $subTasksByType = $subTaskQuery->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
        
        $typesData = '';
        foreach ($subTasksByType as $type => $count) {
            $typesData .= "$type: $count, ";
        }
        $typesData = rtrim($typesData, ', ');
        
        return [
            Stat::make('Total Sub Tasks', $totalSubTasks)
                ->description('All tracked tasks')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->chart([7, 4, 6, 8, 10, $totalSubTasks])
                ->color('info'),
                
            Stat::make('Active Tasks', $activeSubTasks)
                ->description('Tasks in progress')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->chart([2, 3, 4, 5, 5, $activeSubTasks])
                ->color('success'),
                
            Stat::make('Total Activities', $totalActivities)
                ->description('Activities in all tasks')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([5, 10, 15, 12, 8, $totalActivities])
                ->color('warning'),
            
            Stat::make('Activities Waiting', $activitiesWaiting)
                ->description('Not started yet')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
                
            Stat::make('Activities In Progress', $activitiesInProgress)
                ->description('Currently in progress')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
                
            Stat::make('Activities Completed', $activitiesCompleted)
                ->description('Successfully completed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Completion Rate', "$completionRate%")
                ->description('Overall progress')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([0, 10, 20, 30, 40, $completionRate])
                ->color($completionRate < 50 ? 'danger' : 'success'),
                
            Stat::make('Upcoming Deadlines', $upcomingDeadlines)
                ->description('Due in next 7 days')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($upcomingDeadlines > 5 ? 'danger' : 'warning'),
                
            Stat::make('Sub Tasks by Type', $totalSubTasks > 0 ? $typesData : 'No data')
                ->description('Distribution by type')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray'),
        ];
    }
}
