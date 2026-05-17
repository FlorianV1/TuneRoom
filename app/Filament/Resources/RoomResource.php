<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-musical-note';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')->required()->maxLength(60),
            Select::make('visibility')
                ->options(['invite' => 'Private (invite only)', 'public' => 'Public'])
                ->required(),
            Select::make('status')
                ->options(['active' => 'Active', 'ended' => 'Ended'])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('code')->label('Code')->fontFamily('mono')->copyable(),
                TextColumn::make('host.name')->label('Host')->searchable(),
                TextColumn::make('visibility')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'public' ? 'success' : 'gray'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'active' ? 'success' : 'gray'),
                TextColumn::make('active_members')
                    ->label('Members')
                    ->getStateUsing(fn(Room $record) => $record->activeMembers()->count()),
                TextColumn::make('created_at')->label('Created')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(['active' => 'Active', 'ended' => 'Ended']),
                SelectFilter::make('visibility')->options(['invite' => 'Private', 'public' => 'Public']),
            ])
            ->actions([
                EditAction::make(),
                Action::make('view')
                    ->label('View room')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn(Room $record) => route('rooms.show', $record->code))
                    ->openUrlInNewTab()
                    ->visible(fn(Room $record) => $record->status === 'active'),
                Action::make('force_end')
                    ->label('Force end')
                    ->icon('heroicon-o-stop-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(Room $record) => $record->status === 'active')
                    ->action(function (Room $record) {
                        $record->update(['status' => 'ended', 'ended_at' => now()]);
                        $record->playbackState?->update(['status' => 'stopped']);
                    }),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\MembersRelationManager::class,
            RelationManagers\QueueItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
