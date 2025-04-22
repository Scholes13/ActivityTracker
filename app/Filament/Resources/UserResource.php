<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description('Manage user details and permissions')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                            ->required(fn ($livewire) => $livewire instanceof \App\Filament\Resources\UserResource\Pages\CreateUser)
                            ->dehydrated(fn ($state) => !empty($state))
                            ->maxLength(255),
                        Forms\Components\Select::make('role')
                            ->options([
                                'sc' => 'System Coordinator',
                                'captain' => 'Captain',
                                'leader' => 'Leader',
                                'member' => 'Member',
                                'admin' => 'Admin',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => 
                                $set('parent_id', null)),
                        Forms\Components\Select::make('parent_id')
                            ->label('Reports To')
                            ->relationship('parent', 'name')
                            ->options(function (Forms\Get $get) {
                                $role = $get('role');
                                $query = User::query();
                                
                                if ($role === 'captain') {
                                    // Captain reports to SC
                                    return User::where('role', 'sc')->pluck('name', 'id');
                                } elseif ($role === 'leader') {
                                    // Leader reports to a Captain
                                    return User::where('role', 'captain')->pluck('name', 'id');
                                } elseif ($role === 'member') {
                                    // Member reports to a Leader
                                    return User::where('role', 'leader')->pluck('name', 'id');
                                }
                                
                                return [];
                            })
                            ->visible(fn (Forms\Get $get) => in_array($get('role'), ['captain', 'leader', 'member']))
                            ->required(fn (Forms\Get $get) => in_array($get('role'), ['captain', 'leader', 'member'])),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sc' => 'danger',
                        'captain' => 'warning',
                        'leader' => 'success',
                        'member' => 'info',
                        'admin' => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Reports To')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'sc' => 'Steering Committee',
                        'captain' => 'Captain',
                        'leader' => 'Leader',
                        'member' => 'Member',
                        'admin' => 'Admin',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
