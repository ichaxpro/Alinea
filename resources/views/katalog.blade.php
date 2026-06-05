<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Ulasan Buku — Alinea</title>
    @if(config('services.google_books.key'))
    <meta name="google-books-key" content="{{ config('services.google_books.key') }}" />
    @endif
    <meta name="description" content="Jelajahi dan temukan ulasan buku terbaik di Alinea. Cari berdasarkan genre, rating, dan kata kunci." />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/katalog.js'])

    <script>
        window.__FEATURED_BOOKS__ = {!! json_encode($featuredBooks->map(fn($b) => [
            'id' => $b->id,
            'judul' => $b->judul,
            'penulis' => $b->penulis,
            'tahun' => $b->tahun,
            'rating_avg' => 0,
            'rating_count' => 0,
            'sinopsis' => $b->sinopsis,
            'genres' => $b->genres ?? [],
            'cover' => $b->cover_url ? (str_starts_with($b->cover_url, 'http') ? $b->cover_url : (str_starts_with($b->cover_url, '/') ? asset(ltrim($b->cover_url, '/')) : asset('storage/' . $b->cover_url))) : '',
            'gradient_from' => $b->gradient_from,
            'gradient_to' => $b->gradient_to,
        ])->values()) !!};
    </script>

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

        /* ── Toast ── */
        .toast {
            opacity: 0; transform: translateY(10px);
            transition: all 0.3s ease;
        }
        .toast.show {
            opacity: 1; transform: translateY(0);
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
        <div class="max-w-275 mx-auto px-4 sm:px-6 py-8">

            {{-- ═══════ HERO HEADING ═══════ --}}
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-black text-text tracking-[-0.02em] mb-2">
                    Katalog Buku
                </h1>
            </div>

            {{-- ═══════ TOOLBAR: Search + Filters ═══════ --}}
            <div class="flex items-center gap-3 mb-2 w-full flex-wrap sm:flex-nowrap">
                {{-- Search + Mobile Filter Button --}}
                <div class="flex items-center gap-2 w-full sm:flex-1 sm:max-w-xl">
                    <div class="flex-1 flex items-center gap-2 bg-white border-[1.5px] border-text rounded-lg px-4 py-2.5 focus-within:border-[#FFDDAF] transition-colors duration-200">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="search" id="ulasan-search-input"
                               placeholder="Cari judul, penulis, atau genre..."
                               class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" />
                        {{-- Clear button --}}
                        <button id="ulasan-search-clear" class="hidden text-gray-300 hover:text-text transition-colors" aria-label="Hapus pencarian">
                            <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M4 4l12 12M16 4L4 16"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Mobile Filter Button --}}
                    <button id="ulasan-mobile-filter-btn" class="sm:hidden flex items-center justify-center p-3 bg-white border-[1.5px] border-text rounded-lg hover:bg-gray-50 active:bg-gray-100 transition-colors" aria-label="Buka filter">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="21" x2="4" y2="14" /><line x1="4" y1="10" x2="4" y2="3" />
                            <line x1="12" y1="21" x2="12" y2="12" /><line x1="12" y1="8" x2="12" y2="3" />
                            <line x1="20" y1="21" x2="20" y2="16" /><line x1="20" y1="12" x2="20" y2="3" />
                            <line x1="2" y1="14" x2="6" y2="14" /><line x1="10" y1="8" x2="14" y2="8" /><line x1="18" y1="16" x2="22" y2="16" />
                        </svg>
                    </button>
                </div>

                {{-- Genre filter --}}
                <div class="relative hidden sm:block">
                    <select id="ulasan-filter-genre"
                            class="custom-select bg-white border-[1.5px] border-[#444] rounded-lg pl-4 pr-10 py-2.5 text-sm font-medium text-[#444] outline-none cursor-pointer hover:bg-gray-50 focus:border-[#FFDDAF] transition-colors">
                        <option value="">Semua Genre</option>
                    </select>
                </div>

                {{-- Rating filter --}}
                <div class="relative hidden sm:block">
                    <select id="ulasan-filter-rating"
                            class="custom-select bg-white border-[1.5px] border-[#444] rounded-lg pl-4 pr-10 py-2.5 text-sm font-medium text-[#444] outline-none cursor-pointer hover:bg-gray-50 focus:border-[#FFDDAF] transition-colors">
                        <option value="">Semua Rating</option>
                        <option value="5">★★★★★ (5)</option>
                        <option value="4">★★★★☆ (4+)</option>
                        <option value="3">★★★☆☆ (3+)</option>
                        <option value="2">★★☆☆☆ (2+)</option>
                    </select>
                </div>

                {{-- Sort --}}
                <div class="relative hidden sm:block">
                    <select id="ulasan-sort"
                            class="custom-select bg-white border-[1.5px] border-[#444] rounded-lg pl-4 pr-10 py-2.5 text-sm font-medium text-[#444] outline-none cursor-pointer hover:bg-gray-50 focus:border-[#FFDDAF] transition-colors">
                        <option value="rating-desc">Rating Tertinggi</option>
                        <option value="rating-asc">Rating Terendah</option>
                        <option value="reviews-desc">Ulasan Terbanyak</option>
                        <option value="title-asc">Judul A–Z</option>
                        <option value="title-desc">Judul Z–A</option>
                        <option value="newest">Terbaru</option>
                    </select>
                </div>
            </div>

            {{-- ═══════ RESULT INFO BAR ═══════ --}}
            <div class="flex items-center justify-between mb-6">
                <p class="text-[0.78rem] text-text/40" id="ulasan-result-info">
                    Menampilkan <strong class="text-text/70" id="ulasan-result-count">0</strong> Buku
                </p>
                {{-- Active filter chips --}}
                <div class="flex items-center gap-2 flex-wrap" id="ulasan-active-filters"></div>
            </div>

            {{-- ═══════ BOOK GRID ═══════ --}}
            <div id="ulasan-grid"
                 class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-5">
            </div>

            {{-- ═══════ EMPTY STATE ═══════ --}}
            <div id="ulasan-empty" class="hidden text-center py-20">
                <div class="text-5xl mb-4">📚</div>
                <p class="text-lg font-bold text-text/70 mb-2">Tidak ada buku ditemukan</p>
                <p class="text-sm text-text/40 mb-6">Coba ubah filter atau kata kunci pencarianmu.</p>
                <button id="ulasan-reset-filters"
                        class="px-6 py-2.5 text-sm font-bold text-text bg-[#FFDDAF] rounded-full border-[1.5px] border-text hover:bg-amber-300 transition-colors">
                    Reset Filter
                </button>
            </div>

            {{-- ═══════ PAGINATION ═══════ --}}
            <nav id="ulasan-pagination" class="flex items-center justify-center gap-2 mt-10" aria-label="Navigasi halaman">
            </nav>
        </div>
    </main>

    {{-- ═══════ FOOTER ═══════ --}}
    <x-footer/>

    {{-- ═══════ SCROLL-TO-TOP ═══════ --}}
    <button id="scrollTopBtn" aria-label="Kembali ke atas"
            class="fixed bottom-6 right-6 z-[100] w-11 h-11 rounded-full bg-text text-white border-[1.5px] border-text flex items-center justify-center shadow-lg hover:bg-[#333] transition-all duration-200">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"/>
        </svg>
    </button>

    {{-- ═══════ TOAST CONTAINER ═══════ --}}
    <div class="fixed bottom-6 left-6 z-[300] flex flex-col gap-2" id="toastContainer"></div>

    {{-- ═══════ MOBILE FILTER BOTTOM SHEET ═══════ --}}
    <dialog id="mobile-filter-dialog" class="fixed inset-0 m-auto z-[250] w-[calc(100%-2.5rem)] max-w-sm bg-white border-[1.5px] border-text rounded-[24px] shadow-2xl p-6 outline-none backdrop:bg-black/50 backdrop:backdrop-blur-sm">
        <div class="relative flex items-center justify-center border-b border-gray-100 pb-4 mb-5">
            <h3 class="font-extrabold text-lg text-text">Filter & Urutkan</h3>
            <button id="close-filter-dialog" class="absolute right-0 text-text/40 hover:text-text text-2xl font-bold leading-none w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors" aria-label="Tutup">&times;</button>
        </div>
        
        <form method="dialog" class="flex flex-col gap-4 w-full">
            {{-- Genre Filter --}}
            <div class="flex flex-col gap-1.5">
                <label for="mobile-filter-genre" class="text-xs font-bold uppercase tracking-wider text-text/50">Genre</label>
                <select id="mobile-filter-genre" class="custom-select w-full bg-white border-[1.5px] border-text rounded-lg pl-4 pr-10 py-3 text-sm font-medium text-text outline-none cursor-pointer">
                    <option value="">Semua Genre</option>
                </select>
            </div>
            
            {{-- Rating Filter --}}
            <div class="flex flex-col gap-1.5">
                <label for="mobile-filter-rating" class="text-xs font-bold uppercase tracking-wider text-text/50">Rating Minimum</label>
                <select id="mobile-filter-rating" class="custom-select w-full bg-white border-[1.5px] border-text rounded-lg pl-4 pr-10 py-3 text-sm font-medium text-text outline-none cursor-pointer">
                    <option value="">Semua Rating</option>
                    <option value="5">★★★★★ (5)</option>
                    <option value="4">★★★★☆ (4+)</option>
                    <option value="3">★★★☆☆ (3+)</option>
                    <option value="2">★★☆☆☆ (2+)</option>
                </select>
            </div>
            
            {{-- Sort Select --}}
            <div class="flex flex-col gap-1.5">
                <label for="mobile-sort" class="text-xs font-bold uppercase tracking-wider text-text/50">Urutkan</label>
                <select id="mobile-sort" class="custom-select w-full bg-white border-[1.5px] border-text rounded-lg pl-4 pr-10 py-3 text-sm font-medium text-text outline-none cursor-pointer">
                    <option value="rating-desc">Rating Tertinggi</option>
                    <option value="rating-asc">Rating Terendah</option>
                    <option value="reviews-desc">Ulasan Terbanyak</option>
                    <option value="title-asc">Judul A–Z</option>
                    <option value="title-desc">Judul Z–A</option>
                    <option value="newest">Terbaru</option>
                </select>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex gap-3 mt-6 pb-2">
                <button type="button" id="mobile-filter-reset" class="flex-1 py-3 text-sm font-bold text-text bg-white border-[1.5px] border-text rounded-full hover:bg-gray-50 transition-colors">Reset</button>
                <button type="submit" id="mobile-filter-submit" class="flex-1 py-3 text-sm font-bold text-text bg-[#FFDDAF] border-[1.5px] border-text rounded-full hover:bg-amber-300 transition-colors">Terapkan</button>
            </div>
        </form>
    </dialog>

</body>
</html>
