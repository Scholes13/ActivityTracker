<?php

namespace App\Filament\Resources;

use App\Exports\ActivityExport;
use App\Filament\Resources\ActivityResource\Pages;
use App\Filament\Resources\ActivityResource\RelationManagers;
use App\Helpers\PdfExport;
use App\Models\Activity;
use App\Models\SubTask;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Task Management';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Aktivitas')
                    ->description('Detail informasi aktivitas')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Aktivitas')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['default' => 1, 'md' => 2]),
                        Forms\Components\Select::make('sub_task_id')
                            ->label('Sub Task')
                            ->options(function () {
                                return SubTask::query()
                                    ->where('status', 'active')
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(['default' => 1, 'md' => 2]),
                        Forms\Components\Select::make('user_id')
                            ->label('Ditugaskan Kepada')
                            ->options(function () {
                                return User::query()
                                    ->where('role', 'member')
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(['default' => 1, 'md' => 1]),
                        Forms\Components\Select::make('assigned_by')
                            ->label('Ditugaskan Oleh')
                            ->options(function () {
                                return User::query()
                                    ->where('role', 'leader')
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(['default' => 1, 'md' => 1]),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'waiting' => 'Waiting',
                                'inprogress' => 'In Progress',
                                'done' => 'Done',
                            ])
                            ->default('waiting')
                            ->required()
                            ->columnSpan(['default' => 1, 'md' => 1]),
                        Forms\Components\DatePicker::make('deadline')
                            ->label('Deadline')
                            ->required()
                            ->columnSpan(['default' => 1, 'md' => 1]),
                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('Completed At')
                            ->columnSpan(['default' => 1, 'md' => 1]),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('attachment')
                            ->label('Lampiran')
                            ->directory('attachments')
                            ->preserveFilenames()
                            ->maxSize(5120) // 5MB
                            ->columnSpanFull(),
                    ])
                    ->columns(['default' => 1, 'md' => 4]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(30)
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
                    ->label('Ditugaskan Kepada')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
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
                    ->color(fn ($record) => $record->deadline?->isPast() && $record->status !== 'done' ? 'danger' : null),
                Tables\Columns\TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not completed'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('sub_task')
                    ->relationship('subTask', 'name', fn (Builder $query) => $query->where('status', 'active')),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name', fn (Builder $query) => $query->where('role', 'member')),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'inprogress' => 'In Progress',
                        'done' => 'Done',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver(),
                    Tables\Actions\EditAction::make()
                        ->slideOver(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('mark_as_complete')
                        ->label('Mark as Complete')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn (Activity $record) => $record->status !== 'done')
                        ->action(function (Activity $record) {
                            $record->update([
                                'status' => 'done',
                                'completed_at' => now(),
                            ]);
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (array $records) {
                            $fileName = 'activities_' . date('Y-m-d_H-i-s') . '.xlsx';
                            
                            return Excel::download(
                                new ActivityExport(), 
                                $fileName
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('mark_as_complete')
                        ->label('Mark as Complete')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (array $records) {
                            foreach ($records as $record) {
                                if ($record->status !== 'done') {
                                    $record->update([
                                        'status' => 'done',
                                        'completed_at' => now(),
                                    ]);
                                }
                            }
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->label('New Activity'),
                Tables\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $fileName = 'all_activities_' . date('Y-m-d_H-i-s') . '.xlsx';
                        
                        return Excel::download(
                            new ActivityExport(), 
                            $fileName
                        );
                    }),
                Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document')
                    ->action(function () {
                        $activities = Activity::with(['subTask', 'user', 'assignedBy'])->get();
                        $fileName = 'activities_' . date('Y-m-d_H-i-s') . '.pdf';
                        
                        return PdfExport::export(
                            $activities,
                            'exports.activities-pdf',
                            [], 
                            $fileName
                        );
                    }),
            ])
            ->emptyStateHeading('No Activities Found')
            ->emptyStateDescription('Activities will appear here once created.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->label('Create Activity'),
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10);
    }

    public static function getRelations(): array
    {
        return [
            // Akan ditambahkan nanti
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        
        if ($user->role === 'leader') {
            // Leader melihat aktivitas dari sub task yang dia buat
            $subtaskIds = SubTask::where('leader_id', $user->id)->pluck('id');
            return $query->whereIn('sub_task_id', $subtaskIds);
        } elseif ($user->role === 'member') {
            // Member melihat aktivitas dari sub task yang dibuat oleh leader-nya (parent)
            if ($user->parent_id) {
                $subtaskIds = SubTask::where('leader_id', $user->parent_id)->pluck('id');
                return $query->whereIn('sub_task_id', $subtaskIds);
            }
            // Jika tidak memiliki parent, hanya melihat aktivitas yang ditugaskan ke dia
            return $query->where('user_id', $user->id);
        } elseif ($user->role === 'captain' || $user->role === 'sc') {
            // Captain and SC should see ALL activities
            // This makes it consistent with dashboard widgets
            return $query; // Remove the filter to show all activities
        }
        
        // Admin melihat semua
        return $query;
    }
}
