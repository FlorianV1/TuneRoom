<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')->required()->maxLength(60),
            TextInput::make('email')->email()->required(),
            Toggle::make('is_admin')->label('Admin access'),
            Toggle::make('is_banned')->label('Banned'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=fb923c&background=3d1f0d'),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->copyable(),
                IconColumn::make('spotify_connected')
                    ->label('Spotify')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn($record) => !is_null($record->spotify_id)),
                ToggleColumn::make('is_admin')->label('Admin'),
                IconColumn::make('is_banned')->label('Banned')->boolean()->trueColor('danger')->falseColor('gray'),
                TextColumn::make('created_at')->label('Joined')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_admin')->label('Admin only'),
                TernaryFilter::make('is_banned')->label('Banned only'),
            ])
            ->actions([
                EditAction::make(),
                Action::make('toggle_ban')
                    ->label(fn(User $record) => $record->is_banned ? 'Unban' : 'Ban')
                    ->icon(fn(User $record) => $record->is_banned ? 'heroicon-o-check-circle' : 'heroicon-o-no-symbol')
                    ->color(fn(User $record) => $record->is_banned ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(fn(User $record) => $record->update(['is_banned' => !$record->is_banned])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
