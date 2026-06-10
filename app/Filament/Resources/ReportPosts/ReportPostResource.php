<?php

namespace App\Filament\Resources\ReportPosts;

use App\Filament\Resources\ReportPosts\Pages\ListReportPosts;
use App\Filament\Resources\ReportPosts\Schemas\ReportPostForm;
use App\Filament\Resources\ReportPosts\Tables\ReportPostsTable;
use App\Models\ReportPost;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReportPostResource extends Resource
{
    protected static ?string $model = ReportPost::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Laporan';
    protected static ?string $modelLabel = 'Laporan Unggahan';
    protected static ?string $pluralModelLabel = 'Laporan Unggahan';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function form(Schema $schema): Schema
    {
        return ReportPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportPostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportPosts::route('/'),
        ];
    }
}
