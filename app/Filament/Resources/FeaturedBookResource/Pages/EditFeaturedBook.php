<?php

namespace App\Filament\Resources\FeaturedBookResource\Pages;

use App\Filament\Resources\FeaturedBookResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeaturedBook extends EditRecord
{
    protected static string $resource = FeaturedBookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
