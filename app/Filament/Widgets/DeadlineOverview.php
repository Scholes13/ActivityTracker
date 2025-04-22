<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\SubTask;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class DeadlineOverview extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    
    protected function getTableHeading(): string
    {
        return 'Upcoming Deadlines';
    }
    
    public function table(Table $table): Table
    {
        $user = auth()->user();
        
        // Log user info for debugging
        Log::info('DeadlineOverview - User Info', [
            'user_id' => $user->id, 
            'user_role' => $user->role
        ]);
        
        $query = Activity::query()
            ->with(['subTask', 'user', 'assignedBy'])
            ->whereDate('deadline', '>=', now())
            ->whereDate('deadline', '<=', now()->addDays(7))
            ->where('status', '!=', 'done')
            ->orderBy('deadline', 'asc');
            
        // Filter data berdasarkan role
        if ($user->role === 'leader') {
            $subtaskIds = SubTask::where('leader_id', $user->id)->pluck('id');
            $query->whereIn('sub_task_id', $subtaskIds);
            
            Log::info('DeadlineOverview - Leader Filter', [
                'subtask_ids' => $subtaskIds->toArray(),
                'count' => $subtaskIds->count()
            ]);
        } elseif ($user->role === 'member') {
            if ($user->parent_id) {
                $subtaskIds = SubTask::where('leader_id', $user->parent_id)->pluck('id');
                $query->whereIn('sub_task_id', $subtaskIds);
                
                Log::info('DeadlineOverview - Member Filter', [
                    'parent_id' => $user->parent_id,
                    'subtask_count' => $subtaskIds->count()
                ]);
            } else {
                $query->where('user_id', $user->id);
                
                Log::info('DeadlineOverview - Member without parent Filter', [
                    'user_id' => $user->id
                ]);
            }
        } elseif ($user->role === 'captain' || $user->role === 'sc') {
            // Captain and SC see all deadlines - no filtering needed
            Log::info('DeadlineOverview - Captain/SC viewing all deadlines');
        }
        // Admin melihat semua data (tidak perlu filter)
        
        // Log query info
        Log::info('DeadlineOverview - Query SQL', [
            'sql' => $query->toSql(),
            'count' => $query->count()
        ]);
        
        return $table
            ->heading('Upcoming Deadlines')
            ->description('Activities due in the next 7 days')
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30)
                    ->weight('bold')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('deadline')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(function ($record) {
                        $days = now()->diffInDays($record->deadline, false);
                        if ($days < 0) return 'danger'; // Overdue
                        if ($days <= 1) return 'danger'; // Due today or tomorrow
                        if ($days <= 3) return 'warning'; // Due in 2-3 days
                        return 'success'; // Due in 4+ days
                    }),
                Tables\Columns\TextColumn::make('subTask.name')
                    ->label('Sub Task')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting' => 'warning',
                        'inprogress' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'waiting' => 'heroicon-o-clock',
                        'inprogress' => 'heroicon-o-arrow-path',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                Tables\Columns\TextColumn::make('days_left')
                    ->label('Time Left')
                    ->state(function ($record) {
                        $days = now()->diffInDays($record->deadline, false);
                        if ($days < 0) return 'Overdue';
                        if ($days === 0) return 'Due Today!';
                        if ($days === 1) return 'Tomorrow';
                        return $days . ' days';
                    })
                    ->badge()
                    ->color(function ($record) {
                        $days = now()->diffInDays($record->deadline, false);
                        if ($days < 0) return 'danger'; // Overdue
                        if ($days <= 1) return 'danger'; // Due today or tomorrow
                        if ($days <= 3) return 'warning'; // Due in 2-3 days
                        return 'success'; // Due in 4+ days
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (Activity $record) {
                        $record->update([
                            'status' => 'done',
                            'completed_at' => now(),
                        ]);
                        $this->refresh();
                    }),
                Tables\Actions\ViewAction::make()
                    ->url(fn (Activity $record): string => route('filament.admin.resources.activities.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('No Upcoming Deadlines')
            ->emptyStateDescription('All activities are either completed or have deadlines beyond the next 7 days')
            ->emptyStateIcon('heroicon-o-calendar')
            ->defaultPaginationPageOption(5)
            ->poll('30s');
    }
} 