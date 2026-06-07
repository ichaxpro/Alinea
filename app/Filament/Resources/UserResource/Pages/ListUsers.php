<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Semua' => \Filament\Schemas\Components\Tabs\Tab::make(),
            'Aktif' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('is_banned', false)),
            'Diblokir' => \Filament\Schemas\Components\Tabs\Tab::make()
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('is_banned', true))
                ->badge(\App\Models\User::where('is_banned', true)->count()),
        ];
    }
}
