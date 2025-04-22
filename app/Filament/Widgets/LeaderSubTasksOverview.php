<?php

namespace App\Filament\Widgets;

use App\Models\Activity;
use App\Models\SubTask;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LeaderSubTasksOverview extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = auth()->user();
        
        $query = SubTask::query()
            ->with(['leader', 'activities']);
        
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
            // Captain dan SC melihat SEMUA sub tasks dari SEMUA leader
            // Tidak perlu filter tambahan, tampilkan semua subtasks
            
            // Tambahkan logging untuk debugging
            \Log::info('Captain/SC viewing subtasks - showing all subtasks');
            \Log::info('SQL Query: ' . $query->toSql());
        }
        // Admin bisa melihat semua (tidak perlu filter)
        
        // Debug info
        \Log::info('User Role: ' . $user->role);
        \Log::info('User Parent ID: ' . $user->parent_id);
        \Log::info('SubTask Query Count: ' . $query->count());
        
        return $table
            ->heading('Leader Sub-Tasks Progress')
            ->description('Progress of tasks managed by each leader')
            ->query($query)
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Sub Task')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('lg'),
                Tables\Columns\TextColumn::make('leader.name')
                    ->label('Leader')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->alignCenter()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'inactive' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->state(function (SubTask $record): string {
                        $total = $record->activities()->count();
                        $completed = $record->activities()->where('status', 'done')->count();
                        
                        if ($total === 0) return '0%';
                        
                        return round(($completed / $total) * 100) . '%';
                    })
                    ->alignCenter()
                    ->color(function (string $state): string {
                        $percentage = (int) $state;
                        
                        if ($percentage < 25) return 'danger';
                        if ($percentage < 50) return 'warning';
                        if ($percentage < 75) return 'info';
                        
                        return 'success';
                    })
                    ->size('lg')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('deadline')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('leader')
                    ->relationship('leader', 'name')
                    ->visible(fn () => in_array(auth()->user()->role, ['sc', 'captain', 'admin'])),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View Activities')
                    ->url(fn (SubTask $record): string => route('filament.admin.resources.activities.index', ['tableFilters[sub_task][value]' => $record->id]))
                    ->icon('heroicon-m-clipboard-document-check')
                    ->button(),
            ])
            ->emptyStateHeading('No Sub Tasks Found')
            ->emptyStateDescription('Sub tasks created by leaders will appear here.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function canView(): bool
    {
        return in_array(auth()->user()->role, ['sc', 'captain', 'leader']);
    }
} 