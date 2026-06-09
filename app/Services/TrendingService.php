<?php

namespace App\Services;

use App\Models\TimelinePost;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrendingService
{
    public function getWeeklyTrending(int $limit = 5): array {
        // Use Jakarta timezone so "this week" matches the user's perspective
        $now = Carbon::now('Asia/Jakarta');
        $startOfWeek = $now->copy()->startOfWeek();

        $cacheKey = "trending_weekly_{$startOfWeek->year}_W{$startOfWeek->weekOfYear}";

        return Cache::remember($cacheKey, 3600, function () use ($startOfWeek, $now, $limit) {
            // Convert to UTC for database query since created_at is stored in UTC
            $startUtc = $startOfWeek->copy()->utc();
            $nowUtc = $now->copy()->utc();

            $items = TimelinePost::whereNull('id_klub')
                ->whereNotNull('judul_buku_dibahas')
                ->where('judul_buku_dibahas', '!=', '')
                ->whereBetween('created_at', [$startUtc, $nowUtc])
                ->select(
                    DB::raw('LOWER(TRIM(judul_buku_dibahas)) as normalized'),
                    DB::raw('MAX(judul_buku_dibahas) as judul'),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy('normalized')
                ->orderByDesc('count')
                ->limit($limit)
                ->get();
            
            // Fallback: if current week is empty, show last week's data
            if ($items->isEmpty()) {
                $prevWeekStart = $startOfWeek->copy()->subWeek();
                $prevWeekEnd = $startOfWeek->copy();
                $prevStartUtc = $prevWeekStart->copy()->utc();
                $prevEndUtc = $prevWeekEnd->copy()->utc();

                $items = TimelinePost::whereNull('id_klub')
                    ->whereNotNull('judul_buku_dibahas')
                    ->where('judul_buku_dibahas', '!=', '')
                    ->whereBetween('created_at', [$prevStartUtc, $prevEndUtc])
                    ->select(
                        DB::raw('LOWER(TRIM(judul_buku_dibahas)) as normalized'),
                        DB::raw('MAX(judul_buku_dibahas) as judul'),
                        DB::raw('COUNT(*) as count')
                    )
                    ->groupBy('normalized')
                    ->orderByDesc('count')
                    ->limit($limit)
                    ->get();
            }

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
