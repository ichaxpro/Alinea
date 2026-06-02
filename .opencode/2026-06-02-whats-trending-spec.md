# What's Trending — Weekly Book Leaderboard

## Problem

Saat ini sidebar "What's Trending" di timeline menampilkan data hardcoded (5 buku statis). Padahal `timeline_posts` sudah punya kolom `judul_buku_dibahas` yang mencatat judul buku yang dibahas di setiap post. Kita bisa menghitung judul buku apa yang paling sering muncul dalam seminggu dan menampilkannya sebagai leaderboard real-time.

## Solution

Service `TrendingService` yang menghitung buku paling banyak dibahas dalam minggu berjalan (Senin-Minggu), di-cache 60 menit, lalu ditampilkan di sidebar kanan dan mobile search.

## Approach

**Opsi B — Query + Cache (`Cache::remember`)**:
- Query dijalankan sekali per jam
- Hasil di-cache dengan key unik per minggu
- Cache di-invalidate saat ada post baru dengan judul buku
- Cukup fresh, gak perlu scheduled job atau table baru

## Files Changed

| File | Action |
|------|--------|
| `app/Services/TrendingService.php` | **New** — core logic |
| `app/Http/Controllers/TimelineController.php` | Inject service, update index() + simpanan() + store() |
| `app/Http/Controllers/ProfileController.php` | Inject service, update show() |
| `resources/views/timeline_home.blade.php` | Replace hardcoded trending with `$trendingItems` |
| `resources/views/timeline_simpanan.blade.php` | Same |
| `resources/views/timeline_profile.blade.php` | Same |

## Data Flow

```
User creates post with judul_buku_dibahas
  → TimelineController::store()
    → Cache::forget(trending key minggu ini)
    
User loads timeline
  → TimelineController::index()
    → TrendingService::getWeeklyTrending()
      → Cache::remember(key, 3600, query)
    → Pass $trendingItems ke view
    → Sidebar render leaderboard

User clicks trending item (e.g. "Harry Potter")
  → timeline_home?book=harry+potter
    → TimelineController::index() with ?book= filter
    → Hanya menampilkan post dengan judul buku tersebut
```

## Detail Teknis

### TrendingService::getWeeklyTrending()

- **Time range**: Senin 00:00:00 s.d. sekarang (timezone Asia/Jakarta via `config('app.timezone')`)
- **Cache key**: `trending_weekly_{tahun}_W{mingguKe}`
- **Cache TTL**: 3600 detik (1 jam)
- **Scope**: Global posts only (`id_klub IS NULL`), `judul_buku_dibahas` not null/empty
- **Group by**: `LOWER(TRIM(judul_buku_dibahas))` — case-insensitive + trim whitespace
- **Display title**: `MAX(judul_buku_dibahas)` untuk original casing
- **Count**: `COUNT(*)` — jumlah post yang membahas buku itu minggu ini
- **Limit**: Top 5

```php
$startOfWeek = Carbon::now()->startOfWeek(); // Monday 00:00
$endOfWeek = Carbon::now();

return Cache::remember("trending_weekly_{$startOfWeek->year}_W{$startOfWeek->weekOfYear}", 3600, function () {
    return TimelinePost::whereNull('id_klub')
        ->whereNotNull('judul_buku_dibahas')
        ->where('judul_buku_dibahas', '!=', '')
        ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
        ->select(
            DB::raw('LOWER(TRIM(judul_buku_dibahas)) as normalized'),
            DB::raw('MAX(judul_buku_dibahas) as judul'),
            DB::raw('COUNT(*) as count')
        )
        ->groupBy('normalized')
        ->orderByDesc('count')
        ->limit(5)
        ->get()
        ->map(fn($item) => [
            'judul' => $item->judul,
            'count' => (int) $item->count,
        ]);
});
```

Array output: `[['judul' => 'Harry Potter', 'count' => 12], ...]` — format ini kompatibel dengan `timeline-sidebar-right.blade.php` yang menerima `[title, subtitle]` pairs. Di view kita mapping `judul → title`, `"X postingan"` → subtitle.

### Cache Invalidation

Di `TimelineController::store()`, ketika post baru dibuat dengan `judul_buku_dibahas`:

```php
if (!empty($validated['judul_buku_dibahas'])) {
    $startOfWeek = Carbon::now()->startOfWeek();
    Cache::forget("trending_weekly_{$startOfWeek->year}_W{$startOfWeek->weekOfYear}");
}
```

### Filter by Book (`?book=`)

Di `TimelineController::index()`:

```php
if ($book = $request->query('book')) {
    $query->where(DB::raw('LOWER(TRIM(judul_buku_dibahas))'), strtolower(trim($book)));
}
```

Navigasi dari sidebar: `<a href="{{ route('timeline_home', ['book' => $item['judul']]) }}">` — item trending jadi link.

### Trending Items Format ke View

```php
$trendingItems = (new TrendingService)->getWeeklyTrending();
$trendingItemsFormatted = $trendingItems->map(fn($item) => [
    $item['judul'],
    $item['count'] . ' postingan',
])->all();
```

### Mobile Search

Mobile search overlay juga punya hardcoded "What's Trending" — perlu diganti dengan `$trendingItems` yang sama, dengan navigasi link yang sama.

## UI Flow

```
┌──────────────────────┐
│  What's Trending     │
│                      │
│  1  Harry Potter     │
│     12 postingan     │ ← click → timeline_home?book=Harry+Potter
│                      │
│  2  Laskar Pelangi   │
│     8 postingan      │ ← click → timeline_home?book=Laskar+Pelangi
│                      │
│  3  ...              │
└──────────────────────┘
```

## Edge Cases

- **Minggu sepi**: Jika tidak ada post minggu ini, tampilkan empty state ("Belum ada trending minggu ini")
- **Cache cold start**: Cache pertama akan slow query, setelah itu cepat
- **Judul mirip**: Case-insensitive grouping menangani variasi kapital; variasi ejaan beda tetap dianggap beda
- **Post di klub**: Tidak dihitung trending global (hanya global posts)
- **Link trending**: URL parameter `?book=` memfilter post sesuai judul buku yang diklik

## Out of Scope

- Matching ke `featured_books` untuk canonical title — fase 2
- Trending berdasarkan engagement (likes/comments) — hanya berdasarkan post count
- Filter by author/penulis — hanya judul buku
