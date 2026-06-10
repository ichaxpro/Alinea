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
            Stat::make('Total Users', User::count())
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Pending User Reports', Report::where('status', 'pending')->count())
                ->description('Action required')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            Stat::make('Pending Post Reports', ReportPost::where('status', 'pending')->count())
                ->description('Action required')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Timeline Posts', TimelinePost::count())
                ->description('Total posts across the platform')
                ->descriptionIcon('heroicon-m-chat-bubble-bottom-center-text')
                ->color('info'),
        ];
    }
}
