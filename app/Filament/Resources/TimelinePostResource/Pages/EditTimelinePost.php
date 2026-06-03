<?php

namespace App\Filament\Resources\TimelinePostResource\Pages;

use App\Filament\Resources\TimelinePostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimelinePost extends EditRecord
{
    protected static string $resource = TimelinePostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
