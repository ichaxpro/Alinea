<?php

namespace App\Filament\Resources\ReportPosts\Schemas;

use Filament\Schemas\Schema;

class ReportPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Read-only form could be added here if needed
            ]);
    }
}
