<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Beranda — Alinea</title>
    <meta name="description" content="Temukan buku populer dan rekomendasi khusus untukmu." />
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/css/explore.css', 'resources/js/app.js', 'resources/js/explore.js'])
</head>
<body class="font-['Poppins'] bg-gray-50 text-[#444] leading-relaxed overflow-x-hidden pt-16">

    <x-navbar></x-navbar>

    {{-- HERO SECTION (Featured Books Slider) --}}
    @php
        $heroBooks = $popularBooks->take(3);
    @endphp
    @if($heroBooks->isNotEmpty())
    <section class="relative w-full h-auto md:h-[60vh] min-h-0 md:min-h-[400px] max-h-none md:max-h-[600px] bg-white border-b-[1.5px] border-[#444] overflow-hidden flex items-center" id="hero-carousel">
        <!-- Background Gradient blobs for hero -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-[#FFDDAF] rounded-full blur-[100px] opacity-40 -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-80 h-80 bg-[#C7E7FF] rounded-full blur-[100px] opacity-40 translate-y-1/4 -translate-x-1/4 pointer-events-none"></div>
        
        @foreach($heroBooks as $index => $heroBook)
        <div class="hero-slide w-full h-full flex items-center py-16 md:py-0 transition-all duration-700 ease-out {{ $index === 0 ? 'relative opacity-100 z-10 scale-100' : 'absolute inset-0 opacity-0 z-0 scale-[0.98]' }}" data-index="{{ $index }}">
            <div class="max-w-7xl mx-auto w-full px-6 lg:px-8 relative z-10 flex flex-col md:flex-row items-center gap-6 md:gap-8 lg:gap-16">
                <div class="flex-1 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 bg-[#fff176] border-2 border-[#444] text-[#444] text-xs font-bold px-3 py-1 shadow-pop rounded-full mb-4 tracking-wider">
                        Pilihan Alinea
                    </div>
                    <h1 class="text-3xl md:text-5xl lg:text-6xl font-black text-[#444] leading-tight mb-4 tracking-tight">
                        {{ $heroBook->judul }}
                    </h1>
                    <p class="text-sm md:text-base text-gray-500 font-medium mb-6 line-clamp-3 max-w-xl mx-auto md:mx-0">
                        {{ $heroBook->sinopsis ?: 'Buku yang sangat menarik dengan cerita yang mendalam dan penuh pelajaran. Wajib masuk ke dalam daftar bacaanmu!' }}
                    </p>
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                        <a href="{{ route('detail_buku', $heroBook->id) }}" class="inline-flex items-center justify-center gap-2 bg-[#444] hover:bg-black text-white font-bold text-sm px-6 py-3 rounded-full border-[1.5px] border-[#444] shadow-pop2 transition-all duration-300 hover:translate-y-1 hover:shadow-none">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            Lihat Detail
                        </a>
                        <a href="{{ route('katalog') }}" class="inline-flex items-center justify-center gap-2 bg-white hover:bg-gray-50 text-[#444] font-bold text-sm px-6 py-3 rounded-full border-[1.5px] border-[#444] shadow-pop2 transition-all duration-300 hover:translate-y-1 hover:shadow-none">
                            Jelajahi Katalog
                        </a>
                    </div>
                </div>
                
                <div class="w-40 sm:w-48 lg:w-64 flex-shrink-0 order-first md:order-last mb-2 md:mb-0">
                    <div class="relative w-full aspect-[2/3] rounded-2xl border-2 border-[#444] shadow-[8px_10px_0px_1px_#444] md:shadow-[12px_16px_0px_1px_#444] overflow-hidden rotate-2 md:rotate-3 hover:rotate-0 transition-transform duration-500">
                        @php
                            $cover = $heroBook->cover_url;
                            $coverUrl = $cover ? (str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, '/') ? asset(ltrim($cover, '/')) : asset('storage/' . $cover))) : '';
                        @endphp
                        @if($coverUrl)
                            <img src="{{ $coverUrl }}" alt="Cover" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF] flex items-center justify-center">
                                <span class="text-6xl font-black text-[#444]/20">{{ substr($heroBook->judul, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Navigation Dots -->
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
            @foreach($heroBooks as $index => $book)
            <button onclick="goToSlide({{ $index }})" class="hero-dot h-2.5 rounded-full transition-all duration-300 cursor-pointer {{ $index === 0 ? 'w-8 bg-[#444]' : 'w-2.5 bg-gray-300 hover:bg-gray-400' }}" aria-label="Go to slide {{ $index + 1 }}"></button>
            @endforeach
        </div>
    </section>
    @endif

    {{-- CONTENT SECTIONS --}}
    <main class="py-12 bg-white min-h-screen">
        
        {{-- Section 1: Populer --}}
        @if($popularBooks->isNotEmpty())
        <div class="mb-10 carousel-container relative group px-6 lg:px-8 max-w-[1400px] mx-auto">
            <div class="flex items-end justify-between mb-4">
                <h2 class="text-xl md:text-2xl font-black text-[#444]">Buku Sedang Populer</h2>
                <div class="flex gap-2">
                    <button onclick="scrollRow('populer-row', 'left')" class="carousel-btn w-8 h-8 rounded-full border-[1.5px] border-[#444] flex items-center justify-center bg-white text-[#444] hover:bg-[#FFDDAF] transition-colors shadow-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button onclick="scrollRow('populer-row', 'right')" class="carousel-btn w-8 h-8 rounded-full border-[1.5px] border-[#444] flex items-center justify-center bg-white text-[#444] hover:bg-[#FFDDAF] transition-colors shadow-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
            </div>
            
            <div id="populer-row" class="flex overflow-x-auto gap-4 md:gap-5 pb-8 pt-2 scrollbar-hide snap-x snap-mandatory scroll-smooth -mx-6 px-6 lg:-mx-8 lg:px-8 scroll-pl-6 lg:scroll-pl-8">
                @foreach($popularBooks as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        </div>
        @endif

        {{-- Section 2: Genre Based --}}
        @foreach($genreRecommendations as $genre => $books)
        <div class="mb-10 carousel-container relative group px-6 lg:px-8 max-w-[1400px] mx-auto">
            <div class="flex items-end justify-between mb-4">
                <h2 class="text-xl md:text-2xl font-black text-[#444]">Karena Anda Suka <span class="text-[#f59e0b]">{{ $genre }}</span></h2>
                <div class="flex gap-2">
                    <button onclick="scrollRow('genre-{{ Str::slug($genre) }}-row', 'left')" class="carousel-btn w-8 h-8 rounded-full border-[1.5px] border-[#444] flex items-center justify-center bg-white text-[#444] hover:bg-[#FFDDAF] transition-colors shadow-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button onclick="scrollRow('genre-{{ Str::slug($genre) }}-row', 'right')" class="carousel-btn w-8 h-8 rounded-full border-[1.5px] border-[#444] flex items-center justify-center bg-white text-[#444] hover:bg-[#FFDDAF] transition-colors shadow-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
            </div>
            
            <div id="genre-{{ Str::slug($genre) }}-row" class="flex overflow-x-auto gap-4 md:gap-5 pb-8 pt-2 scrollbar-hide snap-x snap-mandatory scroll-smooth -mx-6 px-6 lg:-mx-8 lg:px-8 scroll-pl-6 lg:scroll-pl-8">
                @foreach($books as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        </div>
        @endforeach

        {{-- Section 3: Terbaru --}}
        @if($newestBooks->isNotEmpty())
        <div class="mb-10 carousel-container relative group px-6 lg:px-8 max-w-[1400px] mx-auto">
            <div class="flex items-end justify-between mb-4">
                <h2 class="text-xl md:text-2xl font-black text-[#444]">Tambahan Baru di Alinea</h2>
                <div class="flex gap-2">
                    <button onclick="scrollRow('newest-row', 'left')" class="carousel-btn w-8 h-8 rounded-full border-[1.5px] border-[#444] flex items-center justify-center bg-white text-[#444] hover:bg-[#C7E7FF] transition-colors shadow-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                    </button>
                    <button onclick="scrollRow('newest-row', 'right')" class="carousel-btn w-8 h-8 rounded-full border-[1.5px] border-[#444] flex items-center justify-center bg-white text-[#444] hover:bg-[#C7E7FF] transition-colors shadow-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                </div>
            </div>
            
            <div id="newest-row" class="flex overflow-x-auto gap-4 md:gap-5 pb-8 pt-2 scrollbar-hide snap-x snap-mandatory scroll-smooth -mx-6 px-6 lg:-mx-8 lg:px-8 scroll-pl-6 lg:scroll-pl-8">
                @foreach($newestBooks as $book)
                    <x-book-card :book="$book" />
                @endforeach
            </div>
        </div>
        @endif

    </main>

    <x-footer />


</body>
</html>
