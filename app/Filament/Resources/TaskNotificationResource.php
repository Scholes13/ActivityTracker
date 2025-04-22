<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskNotificationResource\Pages;
use App\Filament\Resources\TaskNotificationResource\RelationManagers;
use App\Models\TaskNotification;
use App\Models\Activity;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;

class TaskNotificationResource extends Resource
{
    protected static ?string $model = TaskNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Notifications';

    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->options([
                        'task_completion' => 'Task Completion',
                        'deadline_approaching' => 'Deadline Approaching',
                    ])
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Recipient')
                    ->options(function () {
                        return User::query()
                            ->whereIn('role', ['sc', 'captain', 'leader'])
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('activity_id')
                    ->label('Related Activity')
                    ->options(function () {
                        return Activity::query()
                            ->pluck('title', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('is_read')
                    ->default(false)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'task_completion' => 'success',
                        'deadline_approaching' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Recipient')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity.title')
                    ->label('Activity')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_read')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'task_completion' => 'Task Completion',
                        'deadline_approaching' => 'Deadline Approaching',
                    ]),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name'),
                Tables\Filters\Filter::make('is_read')
                    ->query(fn (Builder $query): Builder => $query->where('is_read', true))
                    ->toggle(),
                Tables\Filters\Filter::make('unread')
                    ->query(fn (Builder $query): Builder => $query->where('is_read', false))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('mark_as_read')
                    ->label('Mark as Read')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (TaskNotification $record) {
                        $record->markAsRead();
                    })
                    ->visible(fn (TaskNotification $record) => !$record->is_read),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('mark_as_read')
                        ->label('Mark as Read')
                        ->icon('heroicon-o-check')
                        ->action(function (Collection $records) {
                            $records->each->markAsRead();
                        }),
                ]),
            ]);
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
            'index' => Pages\ListTaskNotifications::route('/'),
            'create' => Pages\CreateTaskNotification::route('/create'),
            'edit' => Pages\EditTaskNotification::route('/{record}/edit'),
        ];
    }
}
