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

    @vite(['resources/css/app.css', 'resources/js/ulasan_list.js'])

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
    </style>
</head>

<body class="font-['Poppins'] bg-gray-100 text-text leading-relaxed overflow-x-hidden">

    <x-navbar></x-navbar>

    <main class="pt-14">
        <div class="max-w-275 mx-auto px-4 sm:px-6 py-8">

            {{-- ═══════ HERO HEADING ═══════ --}}
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-black text-text tracking-[-0.02em] mb-2">
                    Ulasan Buku
                </h1>
            </div>

            {{-- ═══════ TOOLBAR: Search + Filters ═══════ --}}
            <div class="flex flex-wrap items-center gap-3 mb-2">
                {{-- Search --}}
                <div class="flex items-center gap-2 bg-white border-[1.5px] border-text rounded-lg px-4 py-2.5 w-full sm:flex-1 sm:max-w-xl focus-within:border-[#FFDDAF] transition-colors duration-200">
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

                {{-- Genre filter --}}
                <div class="relative">
                    <select id="ulasan-filter-genre"
                            class="custom-select bg-white border-[1.5px] border-[#444] rounded-lg pl-4 pr-10 py-2.5 text-sm font-medium text-[#444] outline-none cursor-pointer hover:bg-gray-50 focus:border-[#FFDDAF] transition-colors">
                        <option value="">Semua Genre</option>
                    </select>
                </div>

                {{-- Rating filter --}}
                <div class="relative">
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
                <div class="relative">
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
                 class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
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
    <footer id="tentang" class="bg-text text-gray-400 py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                    <!-- Logo & Brand -->
                    <div class="col-span-2 lg:col-span-1">
                        <div class="flex items-center gap-2 mb-4">
                            <img src="images/Alinea_footer.svg" alt="">
                        </div>
                        <p class="text-sm text-white opacity-50 leading-relaxed mb-5 max-w-xs">
                            Platform komunitas buku pertama dari dan untuk pembaca Indonesia. Pinjam, Baca, Bagikan.
                        </p>
                    </div>

                    <!-- Fitur -->
                    <div class="pl-15 pt-5">
                        <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Fitur</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Pinjam Buku</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Timeline</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Ulasan Buku</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Book Club</a></li>
                        </ul>
                    </div>

                    <!-- Informasi -->
                    <div class="pt-5 pl-8">
                        <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Informasi</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Tentang Kami</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Blog</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Karir</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Bantuan</a></li>
                        </ul>
                    </div>

                    <!-- Quick Contact -->
                    <div class="pt-5">
                        <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Quick Contact</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="mailto:halo@alinea.id" class="hover:text-white transition-colors duration-200">halo@alinea.id</a></li>
                            <li><a href="tel:+62212345678" class="hover:text-white transition-colors duration-200">+62 21 2345 6789</a></li>
                            <li><span class="text-gray-500">Jakarta, Indonesia</span></li>
                        </ul>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-800 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-white opacity-50">© {{ date('Y') }} Alinea. All rights reserved.</p>
                    <div class="flex gap-6 text-xs">
                        <a href="#" class="hover:text-white transition-colors duration-200">Syarat & Ketentuan</a>
                        <a href="#" class="hover:text-white transition-colors duration-200">Privasi</a>
                    </div>
                </div>
            </div>
    </footer>

    {{-- ═══════ SCROLL-TO-TOP ═══════ --}}
    <button id="scrollTopBtn" aria-label="Kembali ke atas"
            class="fixed bottom-6 right-6 z-[100] w-11 h-11 rounded-full bg-text text-white border-[1.5px] border-text flex items-center justify-center shadow-lg hover:bg-[#333] transition-all duration-200">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"/>
        </svg>
    </button>

    {{-- ═══════ TOAST CONTAINER ═══════ --}}
    <div class="fixed bottom-6 left-6 z-[300] flex flex-col gap-2" id="toastContainer"></div>

</body>
</html>
