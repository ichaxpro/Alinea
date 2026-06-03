<?php

namespace App\Filament\Resources\BookClubResource\Pages;

use App\Filament\Resources\BookClubResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookClubs extends ListRecords
{
    protected static string $resource = BookClubResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
