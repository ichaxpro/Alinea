<?php

namespace App\Services;

use App\Models\TimelinePost;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrendingService
{
    public function getWeeklyTrending(int $limit = 5): array {
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();

        $cacheKey = "trending_weekly_{$startOfWeek->year}_W{$startOfWeek->weekOfYear}";

        return Cache::remember($cacheKey, 3600, function () use ($startOfWeek, $now, $limit) {
            $items = TimelinePost::whereNull('id_klub')
                ->whereNotNull('judul_buku_dibahas')
                ->where('judul_buku_dibahas', '!=', '')
                ->whereBetween('created_at', [$startOfWeek, $now])
                ->select(
                    DB::raw('LOWER(TRIM(judul_buku_dibahas)) as normalized'),
                    DB::raw('MAX(judul_buku_dibahas) as judul'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('normalized')
                ->orderByDesc('count')
                ->limit($limit)
                ->get();
            
            if ($items->isEmpty()) {
                return [];
            }

            return $items->map(fn ($item) => [
                'judul' => $item->judul,
                'count' => (int) $item->count,
            ])->all();
        });
    }
}
