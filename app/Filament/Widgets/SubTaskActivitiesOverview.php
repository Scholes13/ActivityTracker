<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\SubTask;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SubTaskActivitiesOverview extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = '15s';
    
    public ?int $subTaskId = null;
    
    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'waiting' => 'Waiting',
                    'inprogress' => 'In Progress',
                    'done' => 'Done',
                ]),
        ];
    }
    
    protected function getTableFiltersFormColumns(): int
    {
        return 3;
    }
    
    public function getSubTaskOptions(): array
    {
        $user = auth()->user();
        
        Log::info('SubTaskActivitiesOverview - Getting SubTask Options', [
            'user_id' => $user->id,
            'user_role' => $user->role
        ]);
        
        $query = SubTask::query();
        
        if ($user->role === 'leader') {
            // Leader hanya melihat sub task yang dia buat
            $query->where('leader_id', $user->id);
        } elseif ($user->role === 'member') {
            // Member hanya melihat sub task dari leader-nya (parent)
            if ($user->parent_id) {
                $query->where('leader_id', $user->parent_id);
            } else {
                // Jika tidak memiliki parent, tidak menampilkan data apapun
                $query->where('id', 0);
            }
        } elseif ($user->role === 'captain' || $user->role === 'sc') {
            // Captain and SC see all subtasks but we should join with users to only show 
            // subtasks created by users with leader role
            $query->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'sub_tasks.leader_id')
                    ->where('users.role', 'leader');
            });
            
            Log::info('SubTaskActivitiesOverview - Captain/SC seeing filtered subtasks in dropdown');
        }
        // Only Admin can see all without filtering
        
        $options = $query->pluck('name', 'id')->toArray();
        
        Log::info('SubTaskActivitiesOverview - SubTask Options Count', [
            'count' => count($options)
        ]);
        
        return $options;
    }
    
    protected function getTableFiltersFormSchema(): array
    {
        return [
            Select::make('subTaskId')
                ->label('Select Sub Task')
                ->options($this->getSubTaskOptions())
                ->reactive()
                ->afterStateUpdated(function ($state) {
                    $this->subTaskId = $state;
                    Log::info('SubTaskActivitiesOverview - Selected SubTask', [
                        'subTaskId' => $state
                    ]);
                }),
        ];
    }
    
    public function table(Table $table): Table
    {
        $user = auth()->user();
        
        Log::info('SubTaskActivitiesOverview - Building Table', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'selected_subtask_id' => $this->subTaskId
        ]);
        
        $query = Activity::query()
            ->with(['subTask', 'user', 'assignedBy']);
        
        if ($this->subTaskId) {
            // Jika sub task dipilih dari dropdown, tampilkan aktivitas yang terkait
            $query->where('sub_task_id', $this->subTaskId);
            
            Log::info('SubTaskActivitiesOverview - Filtering by selected subtask', [
                'subTaskId' => $this->subTaskId
            ]);
        } else {
            // Jika tidak ada sub task yang dipilih, filter berdasarkan role
            if ($user->role === 'leader') {
                // Leader melihat aktivitas dari sub task yang dia buat
                $subtaskIds = SubTask::where('leader_id', $user->id)->pluck('id');
                $query->whereIn('sub_task_id', $subtaskIds);
                
                Log::info('SubTaskActivitiesOverview - Leader Filtering', [
                    'leader_id' => $user->id,
                    'subtask_count' => $subtaskIds->count()
                ]);
            } elseif ($user->role === 'member') {
                // Member melihat aktivitas dari sub task yang dibuat oleh leader-nya
                if ($user->parent_id) {
                    $subtaskIds = SubTask::where('leader_id', $user->parent_id)->pluck('id');
                    $query->whereIn('sub_task_id', $subtaskIds);
                    
                    Log::info('SubTaskActivitiesOverview - Member Filtering', [
                        'parent_id' => $user->parent_id,
                        'subtask_count' => $subtaskIds->count()
                    ]);
                } else {
                    // Jika tidak memiliki parent, tidak menampilkan data apapun
                    $query->where('id', 0);
                    
                    Log::info('SubTaskActivitiesOverview - Member Without Parent Filtering');
                }
            } else if ($user->role === 'captain' || $user->role === 'sc' || $user->role === 'admin') {
                // Captain/SC/Admin see everything - intentionally no filters
                // Make sure we're not adding any WHERE clauses that might filter out activities
                
                // Force a fresh query to ensure no lingering conditions
                $query = Activity::query()->with(['subTask', 'user', 'assignedBy']);
                
                Log::info('SubTaskActivitiesOverview - Captain/SC/Admin viewing all activities', [
                    'user_role' => $user->role,
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings(),
                ]);
            }
        }
        
        // Debug info - capture statistics before applying any additional filters
        $waitingCount = (clone $query)->where('status', 'waiting')->count();
        $inProgressCount = (clone $query)->where('status', 'inprogress')->count();
        $doneCount = (clone $query)->where('status', 'done')->count();
        
        // Debug info - direct database count for validation
        $directInProgressCount = DB::table('activities')->where('status', 'inprogress')->count();
        
        Log::info('SubTaskActivitiesOverview - Query Info', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'total_activities' => $query->count(),
            'waiting' => $waitingCount,
            'inprogress' => $inProgressCount,
            'done' => $doneCount,
            'direct_inprogress_count' => $directInProgressCount,
        ]);
        
        return $table
            ->heading('Sub Task Activities')
            ->description('Activities and their progress for selected sub task')
            ->query($query)
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
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
                Tables\Columns\TextColumn::make('subTask.name')
                    ->label('Sub Task')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned To')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->alignCenter()
                    ->color(fn (string $state): string => match ($state) {
                        'waiting' => 'warning',
                        'inprogress' => 'info',
                        'done' => 'success',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'waiting' => 'heroicon-o-clock',
                        'inprogress' => 'heroicon-o-arrow-path',
                        'done' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                Tables\Columns\TextColumn::make('deadline')
                    ->date()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn ($record) => $record->deadline?->isPast() && $record->status !== 'done' ? 'danger' : null),
            ])
            ->defaultSort('deadline')
            ->filters($this->getTableFilters())
            ->filtersFormColumns($this->getTableFiltersFormColumns())
            ->emptyStateHeading('No Activities Found')
            ->emptyStateDescription('Activities related to sub tasks will appear here.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->actions([
                Tables\Actions\Action::make('mark_complete')
                    ->label('Mark Complete')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (Activity $record) => $record->status !== 'done')
                    ->action(function (Activity $record) {
                        $record->update([
                            'status' => 'done',
                            'completed_at' => now(),
                        ]);
                        
                        Log::info('SubTaskActivitiesOverview - Activity Marked Complete', [
                            'activity_id' => $record->id,
                            'activity_title' => $record->title
                        ]);
                        
                        $this->refresh();
                    }),
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
            ])
            ->emptyStateActions([
                Tables\Actions\Action::make('create_activity')
                    ->label('Create Activity')
                    ->url(route('filament.admin.resources.activities.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ])
            ->poll('15s');
    }

    public static function canView(): bool
    {
        return in_array(auth()->user()->role, ['sc', 'captain', 'leader']);
    }
} 