<?php

namespace App\Filament\Resources;

use App\Exports\SubTaskExport;
use App\Filament\Resources\SubTaskResource\Pages;
use App\Filament\Resources\SubTaskResource\RelationManagers;
use App\Helpers\PdfExport;
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

class SubTaskResource extends Resource
{
    protected static ?string $model = SubTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Task Management';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Sub Task')
                    ->description('Detail informasi sub task')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Sub Task')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['md' => 2]),
                        Forms\Components\TextInput::make('type')
                            ->label('Tipe')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(['md' => 1]),
                        Forms\Components\Select::make('leader_id')
                            ->label('Leader')
                            ->options(function () {
                                return User::query()
                                    ->where('role', 'leader')
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(['md' => 1]),
                        Forms\Components\Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required()
                            ->columnSpan(['md' => 1]),
                        Forms\Components\DatePicker::make('deadline')
                            ->label('Deadline')
                            ->columnSpan(['md' => 1]),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Sub Task')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('leader.name')
                    ->label('Leader')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'active' => 'heroicon-o-check-circle',
                        'inactive' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                Tables\Columns\TextColumn::make('deadline')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('leader')
                    ->relationship('leader', 'name', fn (Builder $query) => $query->where('role', 'leader')),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver(),
                    Tables\Actions\EditAction::make()
                        ->slideOver(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function (array $records) {
                            $ids = collect($records)->pluck('id')->toArray();
                            $fileName = 'sub_tasks_' . date('Y-m-d_H-i-s') . '.xlsx';
                            
                            return Excel::download(
                                new SubTaskExport(), 
                                $fileName
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->label('New Sub Task'),
                Tables\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $fileName = 'all_sub_tasks_' . date('Y-m-d_H-i-s') . '.xlsx';
                        
                        return Excel::download(
                            new SubTaskExport(), 
                            $fileName
                        );
                    }),
                Tables\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document')
                    ->action(function () {
                        $subTasks = SubTask::with('leader')->get();
                        $fileName = 'sub_tasks_' . date('Y-m-d_H-i-s') . '.pdf';
                        
                        return PdfExport::export(
                            $subTasks,
                            'exports.sub-tasks-pdf',
                            [], 
                            $fileName
                        );
                    }),
            ])
            ->emptyStateHeading('No Sub Tasks Found')
            ->emptyStateDescription('Sub tasks created by leaders will appear here.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->slideOver()
                    ->label('Create Sub Task'),
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
            'index' => Pages\ListSubTasks::route('/'),
            'create' => Pages\CreateSubTask::route('/create'),
            'edit' => Pages\EditSubTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        
        if ($user->role === 'leader') {
            // Leader hanya melihat sub task yang dia buat
            return $query->where('leader_id', $user->id);
        } elseif ($user->role === 'member') {
            // Member hanya melihat sub task dari leader-nya (parent)
            if ($user->parent_id) {
                return $query->where('leader_id', $user->parent_id);
            }
            // Jika tidak memiliki parent, tidak menampilkan data apapun
            return $query->where('id', 0);
        } elseif ($user->role === 'captain' || $user->role === 'sc') {
            // Captain and SC should see ALL subtasks
            // This makes it consistent with dashboard widgets
            return $query; // Remove the filter to show all subtasks
        }
        
        // Admin bisa melihat semua
        return $query;
    }
}
