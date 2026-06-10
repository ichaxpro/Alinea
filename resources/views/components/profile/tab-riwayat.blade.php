@props(['readingNow', 'finishedBooks', 'wantToRead'])

<div data-profile-panel="riwayat" class="hidden mt-5 flex flex-col gap-8">
    {{-- ── Sedang Dibaca ── --}}
    <section>
        <div class="flex items-end justify-between mb-3">
            <h3 class="text-sm font-bold text-[#222]">Sedang Dibaca</h3>
            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-[#C7E7FF] text-[#444]">
                {{ $readingNow->count() }} buku
            </span>
        </div>
        @if($readingNow->isNotEmpty())
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach ($readingNow as $book)
            <article class="group">
                <div class="relative aspect-[2/3] rounded-2xl border-2 border-[#444] overflow-hidden shadow-sm bg-gray-100">
                    @if($book->cover_url)
                    @php
                        $cover = str_starts_with($book->cover_url, 'http') ? $book->cover_url : (str_starts_with($book->cover_url, '/') ? asset(ltrim($book->cover_url, '/')) : asset('storage/' . $book->cover_url));
                    @endphp
                    <img src="{{ $cover }}" alt="Sampul {{ $book->judul }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <div style="display: none;" class="w-full h-full items-center justify-center bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF] text-[#444] text-sm font-bold p-4 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF] text-[#444] text-sm font-bold p-4 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                    @endif
                    <span class="absolute top-2 left-2 text-[10px] font-bold tracking-wide px-2 py-0.5 rounded-full border border-[#444] bg-[#C7E7FF]/90 text-[#333]">Sedang Dibaca</span>
                </div>
                <div class="pt-2 px-0.5">
                    <h4 class="text-[13px] leading-tight font-bold text-[#2a2a2a] line-clamp-1">{{ $book->judul }}</h4>
                    <p class="text-[11px] mt-0.5 text-gray-500 line-clamp-1">{{ $book->penulis }}</p>
                </div>
            </article>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400">Belum ada buku yang sedang dibaca.</p>
        @endif
    </section>

    {{-- ── Sudah Dibaca ── --}}
    <section>
        <div class="flex items-end justify-between mb-3">
            <h3 class="text-sm font-bold text-[#222]">Sudah Dibaca</h3>
            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-[#D4F6FF] text-[#444]">
                {{ $finishedBooks->count() }} buku
            </span>
        </div>
        @if($finishedBooks->isNotEmpty())
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
            @foreach ($finishedBooks as $book)
            <article class="group">
                <div class="relative aspect-[2/3] rounded-xl border-[1.5px] border-[#444] overflow-hidden bg-gray-100 transition-transform duration-200 group-hover:translate-y-[-2px]">
                    @if($book->cover_url)
                    @php
                        $cover = str_starts_with($book->cover_url, 'http') ? $book->cover_url : (str_starts_with($book->cover_url, '/') ? asset(ltrim($book->cover_url, '/')) : asset('storage/' . $book->cover_url));
                    @endphp
                    <img src="{{ $cover }}" alt="Sampul {{ $book->judul }}" class="w-full h-full object-cover [filter:sepia(0.38)_saturate(0.8)_brightness(0.9)]" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <div style="display: none;" class="w-full h-full items-center justify-center bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF] text-[#444] text-xs font-bold p-2 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF] text-[#444] text-xs font-bold p-2 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                    @endif
                    <span class="absolute top-1.5 left-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-[#D4F6FF]/90 border border-[#444] text-[#333]">Selesai</span>
                </div>
                <p class="pt-1 text-[11px] font-medium text-gray-500 leading-tight line-clamp-1">{{ $book->judul }}</p>
            </article>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400">Belum ada buku yang selesai dibaca.</p>
        @endif
    </section>

    {{-- ── Ingin Dibaca ── --}}
    <section>
        <div class="flex items-end justify-between mb-3">
            <h3 class="text-sm font-bold text-[#222]">Ingin Dibaca</h3>
            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-gray-200 text-gray-500">
                {{ $wantToRead->count() }} buku
            </span>
        </div>
        @if($wantToRead->isNotEmpty())
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
            @foreach ($wantToRead as $book)
            <article class="group">
                <div class="relative aspect-[2/3] rounded-xl border-[1.5px] border-dashed border-gray-400 overflow-hidden bg-gray-50 transition-transform duration-200 group-hover:translate-y-[-2px]">
                    @if($book->cover_url)
                    @php
                        $cover = str_starts_with($book->cover_url, 'http') ? $book->cover_url : (str_starts_with($book->cover_url, '/') ? asset(ltrim($book->cover_url, '/')) : asset('storage/' . $book->cover_url));
                    @endphp
                    <img src="{{ $cover }}" alt="Sampul {{ $book->judul }}" class="w-full h-full object-cover opacity-60" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <div style="display: none;" class="w-full h-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400 text-xs font-bold p-2 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                    @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400 text-xs font-bold p-2 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                    @endif
                    <span class="absolute top-1.5 left-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-white/90 border border-gray-400 text-gray-500">Ingin Dibaca</span>
                </div>
                <p class="pt-1 text-[11px] font-medium text-gray-500 leading-tight line-clamp-1">{{ $book->judul }}</p>
            </article>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-400">Belum ada buku yang ingin dibaca.</p>
        @endif
    </section>
</div>
