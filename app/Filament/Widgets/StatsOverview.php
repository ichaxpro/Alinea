<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\User;
use App\Models\Report;
use App\Models\ReportPost;
use App\Models\TimelinePost;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Pengguna', User::count())
                ->description('Akun terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Laporan Pengguna Tertunda', Report::where('status', 'pending')->count())
                ->description('Perlu tindakan')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make('Laporan Post Tertunda', ReportPost::where('status', 'pending')->count())
                ->description('Perlu tindakan')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Post Timeline', TimelinePost::count())
                ->description('Total post di seluruh platform')
                ->descriptionIcon('heroicon-m-chat-bubble-bottom-center-text')
                ->color('info'),
        ];
    }
}
