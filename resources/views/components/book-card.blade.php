@props(['book'])

@php
    $isBookmark = isset($book->book_identifier);
    
    if ($isBookmark) {
        $param = $book->identifier_type === 'google' ? 'g_' . $book->book_identifier : $book->book_identifier;
        $url = route('detail_buku', $param);
        $cover = $book->foto_sampul ?? null;
    } else {
        $url = route('detail_buku', $book->id ?? 1);
        $cover = $book->cover_url ?? null;
    }

    $coverUrl = $cover ? (str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, '/') ? asset(ltrim($cover, '/')) : asset('storage/' . $cover))) : '';
@endphp

<a href="{{ $url }}" class="book-card flex-none w-36 md:w-44 lg:w-48 group cursor-pointer flex flex-col snap-start bg-white rounded-2xl shadow-md border-[1.5px] border-transparent hover:border-[#444] relative overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-gray-300">
    
    <div class="w-full aspect-[2/3] relative bg-gray-100">
        @if($coverUrl)
            <img src="{{ $coverUrl }}" alt="{{ $book->judul }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF]">
                <span class="text-4xl md:text-5xl font-black text-[#444]/20">{{ substr($book->judul, 0, 1) }}</span>
            </div>
        @endif
        

        
        <!-- Hover Overlay -->
        <div class="book-overlay absolute inset-0 flex items-center justify-center">
            <span class="bg-white text-[#444] text-xs font-bold px-3 py-1.5 rounded-full border-[1.5px] border-[#444] shadow-sm transform scale-90 group-hover:scale-100 transition-transform duration-300">
                Lihat Detail
            </span>
        </div>
    </div>
    
    <div class="p-3 flex flex-col flex-1">
        <h3 class="font-bold text-[#444] text-sm leading-tight mb-1 line-clamp-2 min-h-[36px]" title="{{ $book->judul }}">{{ $book->judul }}</h3>
        <p class="text-xs text-gray-500 mb-2 line-clamp-1">{{ $book->penulis }}</p>
        
        <div class="flex items-center justify-between mt-auto pt-1">
            <div class="flex items-center gap-1.5 bg-black/60 backdrop-blur-md px-2 py-0.5 rounded-md shadow-sm">
                <span class="text-[#F5C518] flex items-center"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></span>
                <span class="text-white text-[11px] font-bold tracking-wide leading-none mt-0.5">
                    {{ ($book->rating_avg ?? 0) > 0 ? number_format((float)$book->rating_avg, 1) : '-' }}
                </span>
            </div>
            
            @if(isset($book->kategori) || !empty($book->genres))
                @php
                    $genreTag = $book->kategori ?? (is_array($book->genres) ? ($book->genres[0] ?? '') : '');
                @endphp
                @if($genreTag)
                <span class="text-[9px] font-bold text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded-md border border-gray-200 uppercase tracking-wider truncate max-w-[60px]">
                    {{ $genreTag }}
                </span>
                @endif
            @endif
        </div>
    </div>
</a>
