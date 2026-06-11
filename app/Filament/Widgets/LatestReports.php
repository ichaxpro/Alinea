<?php

namespace App\Filament\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use App\Models\Report;
use Filament\Tables\Columns\TextColumn;

class LatestReports extends TableWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Laporan Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Report::query()->latest()->limit(5)
            )
            ->columns([
                TextColumn::make('reporter.name')
                    ->label('Pelapor'),
                TextColumn::make('reportedUser.name')
                    ->label('Dilaporkan'),
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'resolved' => 'success',
                        'dismissed' => 'gray',
                        default => 'primary',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated(false);
    }
}
