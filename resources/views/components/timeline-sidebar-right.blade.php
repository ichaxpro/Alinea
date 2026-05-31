@props([
    'searchPlaceholder' => 'Cari buku atau pengguna...',
    'trendingTitle'     => "What's Trending",
    'trendingItems'     => [],
])

{{-- ===== RIGHT SIDEBAR — floating sticky card ===== --}}
<aside class="hidden xl:flex flex-col gap-4 w-[280px] flex-shrink-0 sticky top-6">

    {{-- Search --}}
    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl px-4 py-3">
        <div class="flex items-center gap-2.5">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="search" id="sidebar-search-input" placeholder="{{ $searchPlaceholder }}"
                   class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" />
        </div>
    </div>

    {{-- Trending --}}
    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5">
        <h2 class="font-bold text-[15px] mb-4">{{ $trendingTitle }}</h2>

        <ol class="flex flex-col gap-3.5">
            @foreach ($trendingItems as $rank => $item)
            <li class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">{{ $rank + 1 }}</span>
                <div>
                    <span class="font-bold text-[13px] leading-tight block">{{ $item[0] }}</span>
                    <span class="text-[11px] text-gray-400">{{ $item[1] }}</span>
                </div>
            </li>
            @endforeach
        </ol>
    </div>
</aside>
