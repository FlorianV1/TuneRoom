<?php

namespace App\Filament\Resources\RoomResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QueueItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'queue';
    protected static ?string $title = 'Queue';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->columns([
                TextColumn::make('position')->sortable(),
                ImageColumn::make('cover_url')->label('')->size(36),
                TextColumn::make('title')->searchable(),
                TextColumn::make('artist'),
                TextColumn::make('addedBy.name')->label('Added by'),
                TextColumn::make('source')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'fallback' ? 'gray' : 'success'),
                TextColumn::make('played_at')->label('Played')->dateTime()->placeholder('Not yet'),
            ]);
    }
}
