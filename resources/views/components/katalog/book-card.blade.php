@props(['book'])

@php
    $avg = (float) ($book['rating_avg'] ?? 0);
    $count = (int) ($book['rating_count'] ?? 0);
    $genres = $book['genres'] ?? [];
@endphp

<article class="card-animate group bg-white border-[1.5px] border-[#e8e8e8] rounded-2xl overflow-hidden cursor-pointer
                flex flex-col hover:border-[#444] hover:-translate-y-1 transition-all duration-300"
         data-book-id="{{ $book['id'] }}"
         data-google-id="{{ $book['google_id'] ?? '' }}"
         onclick="window.location.href='{{ route('detail_buku', ['param' => $book['google_id'] ?? $book['id']]) }}'">
    
    <!-- Cover -->
    <div class="relative aspect-[2/3] overflow-hidden bg-gray-100">
        @if(!empty($book['cover']))
            <img src="{{ $book['cover'] }}" alt="Sampul {{ $book['judul'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
        @else
            <div class="w-full h-full flex items-center justify-center text-3xl font-black text-white/20 group-hover:scale-105 transition-transform duration-500"
                 style="background: linear-gradient(135deg, {{ $book['gradient_from'] ?? '#C7E7FF' }}, {{ $book['gradient_to'] ?? '#FFDDAF' }})">
                {{ substr($book['judul'], 0, 1) }}
            </div>
        @endif
        
        <!-- Rating Badge on Cover (Mobile) -->
        <div class="md:hidden absolute top-2 left-2 z-10 flex items-center gap-1 px-2 py-1 rounded-lg bg-black/60 backdrop-blur-md text-white text-[0.68rem] font-bold">
            <span class="text-[#F5C518]">★</span>
            <span>{{ $avg > 0 ? number_format($avg, 1) : '-' }}</span>
        </div>
        
        <!-- Hover overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden md:flex items-end p-4">
            <span class="text-white text-xs font-semibold bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full">
                Lihat Detail →
            </span>
        </div>
    </div>

    <!-- Info -->
    <div class="p-3 md:p-5 flex flex-col flex-1">
        <h3 class="text-xs md:text-base font-bold text-text leading-tight mb-0.5 md:mb-1 line-clamp-2">{{ $book['judul'] }}</h3>
        <p class="text-[0.68rem] md:text-[0.78rem] text-text/50 mb-2 line-clamp-1">{{ $book['penulis'] }}</p>

        <!-- Rating (desktop only) -->
        <div class="hidden md:flex items-center gap-1.5 mb-3">
            <div class="flex gap-0.5">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= floor($avg))
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="#F5C518" stroke="#F5C518" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    @elseif ($i == ceil($avg) && $avg - floor($avg) >= 0.5)
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="url(#half_star)" stroke="#F5C518" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><defs><linearGradient id="half_star"><stop offset="50%" stop-color="#F5C518"/><stop offset="50%" stop-color="transparent"/></linearGradient></defs><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    @else
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#e0e0e0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                    @endif
                @endfor
            </div>
            <span class="text-[0.75rem] font-bold text-text">{{ $avg > 0 ? number_format($avg, 1) : '0' }}</span>
            <span class="text-[0.7rem] text-text/35">({{ $count }} Ulasan)</span>
        </div>
        
        <!-- Rating (mobile only) -->
        <div class="flex md:hidden items-center gap-1 mb-2">
            <span class="text-[0.65rem] text-text/40">({{ $count }} Ulasan)</span>
        </div>

        <!-- Synopsis (desktop only) -->
        <p class="hidden md:block text-[0.78rem] text-text/55 leading-relaxed mb-4 flex-1 line-clamp-3">{{ Str::limit($book['sinopsis'], 150) }}</p>

        <!-- Genre pills (desktop only) -->
        <div class="hidden md:flex gap-1.5 flex-wrap mb-4">
            @foreach($genres as $g)
                <span class="px-3 py-1 text-[0.68rem] font-medium text-text/70 border border-[#e0e0e0] rounded-full">{{ $g }}</span>
            @endforeach
        </div>

        <!-- CTA (desktop only) -->
        <a href="{{ route('detail_buku', ['param' => $book['google_id'] ?? $book['id']]) }}" 
           class="hidden md:inline-flex items-center justify-center gap-2 px-5 py-2 text-[0.8rem] font-bold text-text bg-[#FFDDAF] rounded-full border-[1.5px] border-text
                  hover:bg-amber-300 hover:-translate-y-px transition-all duration-200 mt-auto self-start"
           onclick="event.stopPropagation()">
            Lihat Ulasan
        </a>
    </div>
</article>
