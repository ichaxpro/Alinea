<?php

namespace App\Filament\Resources\TimelinePostResource\Pages;

use App\Filament\Resources\TimelinePostResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimelinePosts extends ListRecords
{
    protected static string $resource = TimelinePostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
