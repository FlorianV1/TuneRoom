<?php

namespace App\Filament\Resources\RoomResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'activeMembers';
    protected static ?string $title = 'Active members';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')->circular()->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=fb923c&background=3d1f0d'),
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('pivot.role')
                    ->label('Role')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'host' => 'warning',
                        'cohost' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('pivot.joined_at')->label('Joined')->dateTime(),
            ]);
    }
}
