<?php

namespace App\Filament\Resources\BookReviewResource\Pages;

use App\Filament\Resources\BookReviewResource;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBookReview extends EditRecord
{
    /**
     * Create a new class instance.
     */
    protected static string $resource = BookReviewResource::class;

    protected function getHeaderActions(): array {
        return [
            DeleteAction::make(),
        ];
    }
}
