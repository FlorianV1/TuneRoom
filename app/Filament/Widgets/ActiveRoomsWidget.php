<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ActiveRoomsWidget extends BaseWidget
{
    protected static ?string $heading = 'Live rooms';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Room::where('status', 'active')
                    ->with(['host', 'playbackState.currentQueueItem'])
                    ->withCount(['members as active_count' => fn(Builder $q) => $q->whereNull('left_at')])
                    ->orderByDesc('active_count')
            )
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('code')->fontFamily('mono'),
                TextColumn::make('host.name')->label('Host'),
                TextColumn::make('active_count')->label('Listeners')->sortable(),
                TextColumn::make('playbackState.currentQueueItem.title')
                    ->label('Now playing')
                    ->placeholder('Nothing playing'),
                TextColumn::make('visibility')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'public' ? 'success' : 'gray'),
                TextColumn::make('created_at')->label('Started')->since(),
            ])
            ->paginated(false);
    }
}
