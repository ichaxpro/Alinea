<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ulasan Buku — Alinea</title>
    <meta name="description" content="Jelajahi dan temukan ulasan buku terbaik di Alinea. Cari berdasarkan genre, rating, dan kata kunci." />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/custom-select.js'])

    <style>
        /* ── Skeleton shimmer ── */
        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 800px 100%;
            animation: shimmer 1.5s infinite linear;
            border-radius: 12px;
        }

        /* ── Custom dropdown arrow ── */
        .custom-select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23444' stroke-width='2.5' stroke-linecap='round' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 38px;
        }

        /* ── Scroll-to-top button ── */
        #scrollTopBtn {
            opacity: 0; pointer-events: none;
            transition: all 0.3s ease;
        }
        #scrollTopBtn.visible {
            opacity: 1; pointer-events: all;
        }

        /* ── Mobile Filter Bottom Sheet ── */
        #mobile-filter-dialog {
            display: none !important;
            transform: translateY(100%) !important;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), display 0.3s allow-discrete, overlay 0.3s allow-discrete;
            border: none !important;
            margin: auto auto 0 auto !important;
        }
        #mobile-filter-dialog[open] {
            display: flex !important;
            flex-direction: column !important;
            transform: translateY(0) !important;
        }
        #mobile-filter-dialog::backdrop {
            background-color: rgba(0, 0, 0, 0);
            backdrop-filter: blur(0px);
            transition: background-color 0.3s ease, backdrop-filter 0.3s ease, display 0.3s allow-discrete, overlay 0.3s allow-discrete;
        }
        #mobile-filter-dialog[open]::backdrop {
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(2px);
        }
        @starting-style {
            #mobile-filter-dialog[open] {
                transform: translateY(100%);
            }
            #mobile-filter-dialog[open]::backdrop {
                background-color: rgba(0, 0, 0, 0);
                backdrop-filter: blur(0px);
            }
        }
    </style>
</head>

<body class="font-['Poppins'] bg-gray-100 text-text leading-relaxed overflow-x-hidden">

    <x-navbar></x-navbar>

    <main class="pt-14">
        <div class="max-w-275 mx-auto px-4 sm:px-6 py-8 min-h-[60vh]">


            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-black text-text tracking-[-0.02em] mb-2">
                    Katalog Buku
                </h1>
            </div>

            <form id="katalog-form" method="GET" action="{{ route('katalog') }}">

                <div class="flex items-center gap-3 mb-2 w-full flex-wrap sm:flex-nowrap">
                    {{-- Search + Mobile Filter Button --}}
                    <div class="flex items-center gap-2 w-full sm:flex-1 sm:max-w-xl">
                        <div class="flex-1 flex items-center gap-2 bg-white border-[1.5px] border-text rounded-lg px-4 py-2.5 focus-within:border-[#FFDDAF] transition-colors duration-200">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            <input type="search" name="q" id="ulasan-search-input" value="{{ $query }}"
                                placeholder="Cari judul, penulis, atau genre..."
                                class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" />
                            {{-- Clear button --}}
                            <button type="button" id="ulasan-search-clear" class="{{ empty($query) ? 'hidden' : '' }} text-gray-300 hover:text-text transition-colors" aria-label="Hapus pencarian">
                                <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <path d="M4 4l12 12M16 4L4 16"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Mobile Filter Button --}}
                        <button type="button" id="ulasan-mobile-filter-btn" class="sm:hidden flex items-center justify-center p-3 bg-white border-[1.5px] border-text rounded-lg hover:bg-gray-50 active:bg-gray-100 transition-colors" aria-label="Buka filter">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" y1="21" x2="4" y2="14" /><line x1="4" y1="10" x2="4" y2="3" />
                                <line x1="12" y1="21" x2="12" y2="12" /><line x1="12" y1="8" x2="12" y2="3" />
                                <line x1="20" y1="21" x2="20" y2="16" /><line x1="20" y1="12" x2="20" y2="3" />
                                <line x1="2" y1="14" x2="6" y2="14" /><line x1="10" y1="8" x2="14" y2="8" /><line x1="18" y1="16" x2="22" y2="16" />
                            </svg>
                        </button>
                    </div>

                    {{-- Genre filter --}}
                    <div class="relative hidden sm:block ">
                        @php
                            $genreOptions = [];
                            foreach ($availableGenres as $g) {
                                $genreOptions[$g] = $g;
                            }
                        @endphp
                        <x-custom-select 
                            id="ulasan-filter-genre" 
                            name="genre"
                            title="Filter Genre"
                            placeholder="Semua Genre" 
                            :multiple="true" 
                            :options="$genreOptions" 
                            :selected="$activeGenres"
                            columns="3"
                            align="right"
                        />
                    </div>

                    {{-- Rating filter --}}
                    <div class="relative hidden sm:block ">
                        <x-custom-select 
                            id="ulasan-filter-rating" 
                            name="rating"
                            title="Filter Rating"
                            placeholder="Semua Rating" 
                            :options="[
                                '5' => '★★★★★ (5)',
                                '4' => '★★★★☆ (4+)',
                                '3' => '★★★☆☆ (3+)',
                                '2' => '★★☆☆☆ (2+)',
                            ]" 
                            :selected="[$minRating]"
                        />
                    </div>

                    {{-- Sort --}}
                    <div class="relative hidden sm:block ">
                        <x-custom-select 
                            id="ulasan-sort" 
                            name="sort"
                            title="Urutkan"
                            :placeholder="false" 
                            :options="[
                                'rating-desc' => 'Rating Tertinggi',
                                'rating-asc' => 'Rating Terendah',
                                'reviews-desc' => 'Ulasan Terbanyak',
                                'title-asc' => 'Judul A–Z',
                                'title-desc' => 'Judul Z–A',
                                'newest' => 'Terbaru'
                            ]" 
                            :selected="[$sort]"
                        />
                    </div>
                </div>
            </form>


            <div class="flex items-center justify-between mb-6">
                <p class="text-[0.78rem] text-text/40" id="ulasan-result-info">
                    Menampilkan <strong class="text-text/70" id="ulasan-result-count">{{ $total }}</strong> Buku
                </p>
                {{-- Active filter chips --}}
                <div class="flex items-center gap-2 flex-wrap" id="ulasan-active-filters">
                    @if(!empty($query))
                        <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#FFDDAF]/40 border border-[#FFDDAF] rounded-full hover:bg-[#FFDDAF] transition-colors" onclick="document.getElementById('ulasan-search-clear').click()">
                            🔍 "{{ $query }}" <span class="text-text/40">×</span>
                        </button>
                    @endif
                    @foreach($activeGenres as $g)
                        <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#C7E7FF]/40 border border-[#C7E7FF] rounded-full hover:bg-[#C7E7FF] transition-colors" onclick="document.querySelector('input[name=\'genre[]\'][value=\'{{ $g }}\']').click()">
                            📂 {{ $g }} <span class="text-text/40">×</span>
                        </button>
                    @endforeach
                    @if(!empty($minRating))
                        <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#D4F6FF]/40 border border-[#D4F6FF] rounded-full hover:bg-[#D4F6FF] transition-colors" onclick="document.getElementById('ulasan-filter-rating-reset')?.click() || (document.getElementById('ulasan-filter-rating').value = '', document.getElementById('ulasan-filter-rating').dispatchEvent(new Event('change')))">
                            ⭐ {{ $minRating }}+ <span class="text-text/40">×</span>
                        </button>
                    @endif
                </div>
            </div>


            <div id="ulasan-grid" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5 {{ count($books) == 0 ? 'hidden' : '' }}">
                 @foreach($books as $book)
                     <x-katalog.book-card :book="$book" />
                 @endforeach
            </div>

            <div id="ulasan-grid-loading" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5 hidden">
                 @for ($i = 0; $i < 8; $i++)
                 <div class="card-animate bg-white border-[1.5px] border-[#e8e8e8] rounded-2xl overflow-hidden flex flex-col">
                   <div class="w-full aspect-[2/3] skeleton"></div>
                   <div class="p-3 md:p-5 flex flex-col flex-1 gap-2 mt-1">
                     <div class="h-4 md:h-5 skeleton w-3/4 mb-1 rounded"></div>
                     <div class="h-3 skeleton w-1/2 mb-3 rounded"></div>
                     <div class="hidden md:block h-3 skeleton w-full mb-1 mt-auto rounded"></div>
                     <div class="hidden md:block h-3 skeleton w-5/6 rounded"></div>
                   </div>
                 </div>
                 @endfor
            </div>


            <div id="ulasan-empty" class="{{ count($books) > 0 ? 'hidden' : '' }} text-center py-20">
                <div class="text-5xl mb-4">📚</div>
                <p class="text-lg font-bold text-text/70 mb-2">Tidak ada buku ditemukan</p>
                <p class="text-sm text-text/40 mb-6">Coba ubah filter atau kata kunci pencarianmu.</p>
                <a href="{{ route('katalog') }}" id="ulasan-reset-filters" class="inline-block px-6 py-2.5 text-sm font-bold text-text bg-[#FFDDAF] rounded-full border-[1.5px] border-text hover:bg-amber-300 transition-colors">
                    Reset Filter
                </a>
            </div>


            <nav id="ulasan-pagination" class="flex items-center justify-center gap-2 mt-10" aria-label="Navigasi halaman">
                @if ($totalPages > 1)
                    @php
                        $btnBase = 'w-9 h-9 rounded-full border-[1.5px] border-[#444] flex items-center justify-center text-sm font-medium transition-all duration-200 cursor-pointer';
                        $btnActive = 'bg-[#FFDDAF] text-[#444] scale-105';
                        $btnInactive = 'bg-white text-[#444] hover:bg-gray-50 hover:scale-105';
                    @endphp

                    {{-- Prev --}}
                    @if ($page > 1)
                        <button type="button" data-page="{{ $page - 1 }}" class="{{ $btnBase }} {{ $btnInactive }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                    @else
                        <button disabled class="{{ $btnBase }} opacity-30 cursor-not-allowed">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                    @endif

                    {{-- Pages --}}
                    @php
                        $pages = [];
                        if ($totalPages <= 7) {
                            $pages = range(1, $totalPages);
                        } else {
                            if ($page <= 4) {
                                $pages = [1, 2, 3, 4, 5, '...', $totalPages];
                            } elseif ($page >= $totalPages - 3) {
                                $pages = [1, '...', $totalPages - 4, $totalPages - 3, $totalPages - 2, $totalPages - 1, $totalPages];
                            } else {
                                $pages = [1, '...', $page - 1, $page, $page + 1, '...', $totalPages];
                            }
                        }
                    @endphp

                    @foreach($pages as $p)
                        @if($p === '...')
                            <span class="w-9 h-9 flex items-center justify-center text-sm text-text/30">…</span>
                        @else
                            <button type="button" data-page="{{ $p }}" class="{{ $btnBase }} {{ $p == $page ? $btnActive : $btnInactive }}">{{ $p }}</button>
                        @endif
                    @endforeach

                    {{-- Next --}}
                    @if ($page < $totalPages)
                        <button type="button" data-page="{{ $page + 1 }}" class="{{ $btnBase }} {{ $btnInactive }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    @else
                        <button disabled class="{{ $btnBase }} opacity-30 cursor-not-allowed">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    @endif
                @endif
            </nav>
        </div>
    </main>


    <x-footer/>


    <button id="scrollTopBtn" aria-label="Kembali ke atas"
            class="fixed bottom-6 right-6 z-[100] w-11 h-11 rounded-full bg-text text-white border-[1.5px] border-text flex items-center justify-center shadow-lg hover:bg-[#333] transition-all duration-200">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"/>
        </svg>
    </button>


    <dialog id="mobile-filter-dialog" class="fixed inset-0 m-auto z-[250] w-[calc(100%-2.5rem)] max-w-sm bg-white border-[1.5px] border-text rounded-[24px] shadow-2xl p-6 outline-none backdrop:bg-black/50 backdrop:backdrop-blur-sm overflow-visible">
        <div class="relative flex items-center justify-center border-b border-gray-100 pb-4 mb-5">
            <h3 class="font-extrabold text-lg text-text">Filter & Urutkan</h3>
            <button id="close-filter-dialog" class="absolute right-0 text-text/40 hover:text-text text-2xl font-bold leading-none w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" aria-label="Tutup">&times;</button>
        </div>
        
        <form method="GET" action="{{ route('katalog') }}" id="mobile-filter-form" class="flex flex-col gap-4 w-full">
            <input type="hidden" name="q" value="{{ $query }}">
            
            {{-- Genre Filter --}}
            <div class="flex flex-col gap-1.5">
                <label for="mobile-filter-genre" class="text-xs font-bold uppercase tracking-wider text-text/50">Genre</label>
                <x-custom-select 
                    id="mobile-filter-genre" 
                    name="genre"
                    title="Filter Genre"
                    placeholder="Semua Genre" 
                    :multiple="true" 
                    :options="$genreOptions" 
                    :selected="$activeGenres"
                    columns="2"
                />
            </div>
            
            {{-- Rating Filter --}}
            <div class="flex flex-col gap-1.5">
                <label for="mobile-filter-rating" class="text-xs font-bold uppercase tracking-wider text-text/50">Rating Minimum</label>
                <x-custom-select 
                    id="mobile-filter-rating" 
                    name="rating"
                    title="Filter Rating"
                    placeholder="Semua Rating" 
                    :options="[
                        '5' => '★★★★★ (5)',
                        '4' => '★★★★☆ (4+)',
                        '3' => '★★★☆☆ (3+)',
                        '2' => '★★☆☆☆ (2+)',
                    ]" 
                    :selected="[$minRating]"
                    direction="up"
                />
            </div>
            
            {{-- Sort Select --}}
            <div class="flex flex-col gap-1.5">
                <label for="mobile-sort" class="text-xs font-bold uppercase tracking-wider text-text/50">Urutkan</label>
                <x-custom-select 
                    id="mobile-sort" 
                    name="sort"
                    title="Urutkan"
                    :placeholder="false" 
                    :options="[
                        'rating-desc' => 'Rating Tertinggi',
                        'rating-asc' => 'Rating Terendah',
                        'reviews-desc' => 'Ulasan Terbanyak',
                        'title-asc' => 'Judul A–Z',
                        'title-desc' => 'Judul Z–A',
                        'newest' => 'Terbaru'
                    ]" 
                    :selected="[$sort]"
                    direction="up"
                />
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex gap-3 mt-6 pb-2">
                <a href="{{ route('katalog') }}" class="flex-1 py-3 text-sm font-bold text-center text-text bg-white border-[1.5px] border-text rounded-full hover:bg-gray-50 transition-colors">Reset</a>
                <button type="submit" class="flex-1 py-3 text-sm font-bold text-text bg-[#FFDDAF] border-[1.5px] border-text rounded-full hover:bg-amber-300 transition-colors">Terapkan</button>
            </div>
        </form>
    </dialog>

    <script type="module">
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('katalog-form');
            const searchInput = document.getElementById('ulasan-search-input');
            const searchClear = document.getElementById('ulasan-search-clear');
            const grid = document.getElementById('ulasan-grid');
            const gridLoading = document.getElementById('ulasan-grid-loading');
            const resultCount = document.getElementById('ulasan-result-count');
            const pagination = document.getElementById('ulasan-pagination');
            const emptyState = document.getElementById('ulasan-empty');
            const mobileFilterForm = document.getElementById('mobile-filter-form');
            
            let debounceTimer;

            async function fetchResults(url) {
                grid.classList.add('hidden');
                emptyState.classList.add('hidden');
                gridLoading.classList.remove('hidden');
                
                // Update URL without reloading
                window.history.pushState({}, '', url);

                try {
                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    
                    gridLoading.classList.add('hidden');
                    
                    if (data.total > 0) {
                        grid.innerHTML = data.html;
                        grid.classList.remove('hidden');
                    } else {
                        emptyState.classList.remove('hidden');
                    }
                    
                    resultCount.textContent = data.total;
                    
                    // Update pagination HTML
                    let pagHtml = '';
                    if (data.totalPages > 1) {
                        const btnBase = 'w-9 h-9 rounded-full border-[1.5px] border-[#444] flex items-center justify-center text-sm font-medium transition-all duration-200 cursor-pointer';
                        const btnActive = 'bg-[#FFDDAF] text-[#444] scale-105';
                        const btnInactive = 'bg-white text-[#444] hover:bg-gray-50 hover:scale-105';
                        
                        // Prev
                        pagHtml += `<button type="button" data-page="${data.page > 1 ? data.page - 1 : 1}" class="${btnBase} ${data.page > 1 ? btnInactive : 'opacity-30 cursor-not-allowed'}" ${data.page === 1 ? 'disabled' : ''}>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>`;
                        
                        let pages = [];
                        if (data.totalPages <= 7) {
                            for(let i=1; i<=data.totalPages; i++) pages.push(i);
                        } else {
                            if (data.page <= 4) pages = [1, 2, 3, 4, 5, '...', data.totalPages];
                            else if (data.page >= data.totalPages - 3) pages = [1, '...', data.totalPages - 4, data.totalPages - 3, data.totalPages - 2, data.totalPages - 1, data.totalPages];
                            else pages = [1, '...', data.page - 1, data.page, data.page + 1, '...', data.totalPages];
                        }
                        
                        pages.forEach(p => {
                            if (p === '...') pagHtml += `<span class="w-9 h-9 flex items-center justify-center text-sm text-text/30">…</span>`;
                            else pagHtml += `<button type="button" data-page="${p}" class="${btnBase} ${p === data.page ? btnActive : btnInactive}">${p}</button>`;
                        });
                        
                        // Next
                        pagHtml += `<button type="button" data-page="${data.page < data.totalPages ? data.page + 1 : data.totalPages}" class="${btnBase} ${data.page < data.totalPages ? btnInactive : 'opacity-30 cursor-not-allowed'}" ${data.page === data.totalPages ? 'disabled' : ''}>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>`;
                    }
                    pagination.innerHTML = pagHtml;

                } catch (e) {
                    console.error("Fetch error:", e);
                    // Fallback to normal submit on error
                    form.submit();
                }
            }
            
            function triggerSearch() {
                const url = new URL(form.action);
                const formData = new FormData(form);
                for (const [key, value] of formData.entries()) {
                    if (value) url.searchParams.append(key, value);
                }
                fetchResults(url.toString());
                
                // Update mobile form hidden inputs to sync
                mobileFilterForm.querySelector('input[name="q"]').value = searchInput.value;
            }
            
            // Auto-submit form when filters change
            form.addEventListener('change', (e) => {
                if (e.target.closest('.custom-select-container') || e.target.type === 'radio' || e.target.type === 'checkbox' || e.target.tagName === 'SELECT') {
                    triggerSearch();
                }
            });

            // Debounced Search
            searchInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                
                searchClear.classList.toggle('hidden', searchInput.value.length === 0);
                
                if (searchInput.value.length >= 3 || searchInput.value.length === 0) {
                    debounceTimer = setTimeout(() => {
                        triggerSearch();
                    }, 600);
                }
            });

            // Clear search
            searchClear.addEventListener('click', () => {
                searchInput.value = '';
                searchClear.classList.add('hidden');
                triggerSearch();
                searchInput.focus();
            });

            // Pagination click (Event Delegation)
            pagination.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-page]');
                if (!btn) return;
                
                const page = btn.dataset.page;
                const url = new URL(window.location.href);
                url.searchParams.set('page', page);
                
                fetchResults(url.toString());
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            // Mobile filter apply
            mobileFilterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const url = new URL(mobileFilterForm.action);
                const formData = new FormData(mobileFilterForm);
                for (const [key, value] of formData.entries()) {
                    if (value) url.searchParams.append(key, value);
                }
                document.getElementById('mobile-filter-dialog').close();
                fetchResults(url.toString());
            });

            // Keyboard shortcut: "/" to focus search
            document.addEventListener('keydown', (e) => {
                if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    searchInput.focus();
                }
                if (e.key === 'Escape' && document.activeElement === searchInput) {
                    searchInput.blur();
                }
            });

            // Scroll-to-top button
            const scrollTopBtn = document.getElementById('scrollTopBtn');
            window.addEventListener('scroll', () => {
                scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
            }, { passive: true });

            scrollTopBtn.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            // Mobile Filter Dialog
            const mobileFilterDialog = document.getElementById('mobile-filter-dialog');
            const mobileFilterBtn = document.getElementById('ulasan-mobile-filter-btn');
            const mobileFilterClose = document.getElementById('close-filter-dialog');

            if (mobileFilterBtn && mobileFilterDialog) {
                mobileFilterBtn.addEventListener('click', () => {
                    mobileFilterDialog.showModal();
                });
            }

            if (mobileFilterClose && mobileFilterDialog) {
                mobileFilterClose.addEventListener('click', () => {
                    mobileFilterDialog.close();
                });
            }

            if (mobileFilterDialog) {
                mobileFilterDialog.addEventListener('click', (e) => {
                    const rect = mobileFilterDialog.getBoundingClientRect();
                    const isInDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height &&
                        rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
                    if (!isInDialog) {
                        mobileFilterDialog.close();
                    }
                });
            }
        });
    </script>
</body>
</html>
