<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Alinea — Platform Buku Komunitasmu</title>
        <meta name="description" content="Alinea adalah platform komunitas buku: pinjam, baca, ulas, dan pamerkan bacaanmu bersama ribuan pembaca lain di kotamu.">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body {
                font-family: 'Poppins', sans-serif;
            }
            .font-display {
                font-family: 'Playfair Display', serif;
            }
            /* Scrollbar styling */
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: #f1f1f1; }
            ::-webkit-scrollbar-thumb { background: #c5a96b; border-radius: 3px; }

            /* ===== KEYFRAME ANIMATIONS ===== */
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-12px); }
            }
            @keyframes floatSlow {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                33% { transform: translateY(-8px) rotate(2deg); }
                66% { transform: translateY(-14px) rotate(-1deg); }
            }
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(40px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeInDown {
                from { opacity: 0; transform: translateY(-20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes slideInLeft {
                from { opacity: 0; transform: translateX(-50px); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes slideInRight {
                from { opacity: 0; transform: translateX(50px); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes scaleIn {
                from { opacity: 0; transform: scale(0.85); }
                to { opacity: 1; transform: scale(1); }
            }
            @keyframes pulse-ring {
                0% { transform: scale(1); opacity: 0.6; }
                100% { transform: scale(1.8); opacity: 0; }
            }
            @keyframes blink {
                0%, 100% { opacity: 1; }
                50% { opacity: 0; }
            }
            @keyframes ticker-scroll {
                0% { transform: translateX(0); }
                100% { transform: translateX(-50%); }
            }
            @keyframes particle-rise {
                0% { transform: translateY(0) scale(1); opacity: 0.7; }
                100% { transform: translateY(-120px) scale(0.3); opacity: 0; }
            }
            @keyframes shimmer {
                0% { background-position: -200% center; }
                100% { background-position: 200% center; }
            }
            @keyframes spin-slow {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            @keyframes bounce-in {
                0% { transform: scale(0.3); opacity: 0; }
                50% { transform: scale(1.1); }
                70% { transform: scale(0.95); }
                100% { transform: scale(1); opacity: 1; }
            }

            /* ===== UTILITY ANIMATION CLASSES ===== */
            .animate-float { animation: float 4s ease-in-out infinite; }
            .animate-float-slow { animation: floatSlow 6s ease-in-out infinite; }
            .animate-fade-up { animation: fadeInUp 0.8s cubic-bezier(0.16,1,0.3,1) forwards; }
            .animate-fade-down { animation: fadeInDown 0.7s cubic-bezier(0.16,1,0.3,1) forwards; }
            .animate-slide-left { animation: slideInLeft 0.8s cubic-bezier(0.16,1,0.3,1) forwards; }
            .animate-slide-right { animation: slideInRight 0.8s cubic-bezier(0.16,1,0.3,1) forwards; }
            .animate-scale-in { animation: scaleIn 0.6s cubic-bezier(0.16,1,0.3,1) forwards; }
            .animate-spin-slow { animation: spin-slow 12s linear infinite; }

            /* ===== SCROLL REVEAL ===== */
            .reveal {
                opacity: 0;
                transform: translateY(30px);
                transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1);
            }
            .reveal.revealed {
                opacity: 1;
                transform: translateY(0);
            }
            .reveal-left {
                opacity: 0;
                transform: translateX(-40px);
                transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1);
            }
            .reveal-left.revealed {
                opacity: 1;
                transform: translateX(0);
            }
            .reveal-right {
                opacity: 0;
                transform: translateX(40px);
                transition: opacity 0.7s cubic-bezier(0.16,1,0.3,1), transform 0.7s cubic-bezier(0.16,1,0.3,1);
            }
            .reveal-right.revealed {
                opacity: 1;
                transform: translateX(0);
            }
            .reveal-scale {
                opacity: 0;
                transform: scale(0.9);
                transition: opacity 0.6s cubic-bezier(0.16,1,0.3,1), transform 0.6s cubic-bezier(0.16,1,0.3,1);
            }
            .reveal-scale.revealed {
                opacity: 1;
                transform: scale(1);
            }

            /* ===== SCROLL PROGRESS ===== */
            #scroll-progress {
                position: fixed;
                top: 0;
                left: 0;
                height: 3px;
                width: 0%;
                background: linear-gradient(90deg, #fbbf24, #f59e0b, #fbbf24);
                background-size: 200% 100%;
                animation: shimmer 2s linear infinite;
                z-index: 100;
                transition: width 0.1s linear;
            }

            /* ===== TYPEWRITER ===== */
            .typewriter-cursor {
                display: inline-block;
                width: 3px;
                height: 0.9em;
                background: #c7e7ff;
                vertical-align: middle;
                margin-left: 2px;
                animation: blink 1s ease infinite;
            }

            /* ===== TICKER TAPE ===== */
            .ticker-wrapper {
                overflow: hidden;
                white-space: nowrap;
            }
            .ticker-inner {
                display: inline-flex;
                gap: 0;
                animation: ticker-scroll 24s linear infinite;
            }
            .ticker-inner:hover { animation-play-state: paused; }

            /* ===== PARTICLES ===== */
            .particle {
                position: absolute;
                border-radius: 50%;
                pointer-events: none;
                animation: particle-rise var(--dur, 3s) ease-out forwards;
            }

            /* ===== REVIEW CARDS ===== */
            .review-card {
                transition: all 0.4s cubic-bezier(0.16,1,0.3,1);
            }
            .review-card:hover {
                transform: rotate(0deg) translateY(-8px) scale(1.04) !important;
                z-index: 10;
                box-shadow: 0 20px 40px rgba(0,0,0,0.12);
            }

            /* ===== TAG PILLS ===== */
            .tag-pill { transition: all 0.25s cubic-bezier(0.16,1,0.3,1); cursor: pointer; }
            .tag-pill:hover { transform: translateY(-3px) scale(1.05); }
            .tag-pill.active {
                background: #111827;
                color: #fff;
                border-color: #111827;
            }

            /* ===== STEP CARDS ===== */
            .step-card { transition: all 0.35s cubic-bezier(0.16,1,0.3,1); }
            .step-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 16px 36px rgba(0,0,0,0.1);
            }

            /* ===== FEATURE GRID CARDS ===== */
            .feature-grid-card {
                transition: all 0.35s cubic-bezier(0.16,1,0.3,1);
                cursor: default;
            }
            .feature-grid-card:hover {
                transform: translateY(-6px) scale(1.02);
                box-shadow: 0 16px 40px rgba(0,0,0,0.08);
            }

            /* ===== NAV LINK ===== */
            .nav-link::after {
                content: '';
                position: absolute;
                width: 0;
                height: 2px;
                background: #f59e0b;
                bottom: -2px;
                left: 0;
                transition: width 0.3s cubic-bezier(0.16,1,0.3,1);
            }
            .nav-link:hover::after { width: 100%; }

            /* ===== BLOB PARALLAX ===== */
            .parallax-blob {
                will-change: transform;
                transition: transform 0.1s linear;
            }

            /* ===== PULSE BUTTON ===== */
            .pulse-btn::before {
                content: '';
                position: absolute;
                inset: 0;
                border-radius: inherit;
                background: #fbbf24;
                animation: pulse-ring 1.8s ease-out infinite;
            }

            /* ===== STATS COUNTER ===== */
            .stat-number {
                background: linear-gradient(135deg, #c7e7ff, #d4f6ff);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            /* ===== MOBILE MENU TRANSITION ===== */
            #mobile-menu {
                transition: opacity 0.3s ease, transform 0.3s cubic-bezier(0.16,1,0.3,1);
            }
            #mobile-menu.hidden-menu {
                opacity: 0;
                transform: scale(0.97);
                pointer-events: none;
                display: flex !important;
            }
            #mobile-menu.visible-menu {
                opacity: 1;
                transform: scale(1);
                pointer-events: all;
            }
        </style>
    </head>
    <body class="bg-white text-gray-900 overflow-x-hidden">
        <!-- Scroll Progress Bar -->
        <div id="scroll-progress"></div>

        <!-- =================== NAVBAR =================== -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <a href="#" class="flex items-center gap-2 group py-16">
                        <div class="flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <img fill="none" src="img/alinealogo.svg" class="h-7">
                        </div>
                    </a>

                    <!-- Nav Links (Desktop) -->
                    <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                        <a href="#" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Beranda</a>
                        <a href="{{ route('pinjam') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Pinjam</a>
                        <a href="#komunitas" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Komunitas</a>
                        <a href="{{ route('klub') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Klub</a>
                        <a href="#tentang" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Ulasan</a>
                    </div>

                    <!-- CTA Button -->
                    <div class="flex items-center gap-3">
                        <button id="navbar-search-btn" aria-label="Cari" class="w-9 h-9 rounded-full border-2 border-text flex items-center justify-center text-text shadow-pop hover:bg-white/10 transition-colors">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </button>
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-sm font-medium hover:text-gray-900 transition-colors">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm bg-accent px-5 py-2 outline-2 hover:bg-amber-500 outline-text shadow-pop2 rounded-full font-bold text-text hover:text-gray-900 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">Masuk</a>
                            @endauth
                        @endif
                        
                    </div>

                    <!-- Mobile menu button -->
                    <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors" id="mobile-menu-btn" aria-label="Menu">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M3 6h14M3 10h14M3 14h14"/>
                        </svg>
                    </button>
                </div>
            </div>
        </nav>

        <!-- =================== HERO SECTION =================== -->
        <section class="min-h-screen bg-white pt-16 relative overflow-hidden" id="hero-section">
            <!-- Animated parallax blobs -->
            <div id="blob1" class="parallax-blob absolute top-20 right-0 w-96 h-96 bg-amber-50 rounded-full blur-3xl opacity-70 pointer-events-none" data-speed="0.04"></div>
            <div id="blob2" class="parallax-blob absolute bottom-20 left-0 w-72 h-72 bg-sky-50 rounded-full blur-3xl opacity-60 pointer-events-none" data-speed="0.06"></div>
            <div id="blob3" class="parallax-blob absolute top-1/2 left-1/3 w-48 h-48 bg-violet-50 rounded-full blur-2xl opacity-40 pointer-events-none" data-speed="0.03"></div>

            <!-- Floating particle canvas -->
            <canvas id="particle-canvas" class="absolute inset-0 pointer-events-none" style="opacity:0.5"></canvas>


            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16 lg:py-24">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left: Text Content -->
                    <div>
                        <!-- Animated badge -->
                        <div class="reveal flex items-center gap-2 mb-6" style="transition-delay: 0.1s">
                            <div class="relative flex">
                                <div class="w-2.5 h-2.5 bg-primary rounded-full"></div>
                                <div class="absolute inset-0 w-2.5 h-2.5 bg-secondary rounded-full animate-ping opacity-60"></div>
                            </div>
                            <div class="relative flex">
                                <div class="w-2.5 h-2.5 bg-accent rounded-full"></div>
                                <div class="absolute inset-0 w-2.5 h-2.5 bg-accent rounded-full animate-ping opacity-60" style="animation-delay:0.5s"></div>
                            </div>
                            <span class="text-xs font-semibold text-gray-400 tracking-widest uppercase ml-1">Platform Buku Komunitas</span>
                        </div>
                        <div class="reveal" style="transition-delay:0.15s">
                            <img src="images/logo_landing.svg" class="w-110"/>
                        </div>
                        
                        <!-- Typewriter subtitle -->
                        <div class="reveal mb-6" style="transition-delay:0.25s">
                            <p class="text-text opacity-70 font-bold text-lg">
                                <span id="typewriter-text"></span><span class="typewriter-cursor"></span>
                            </p>
                        </div>

                        <p class="reveal text-gray-500 text-base leading-relaxed max-w-sm mb-8 font-poppins" style="transition-delay:0.35s">
                            Borrow books from neighbors, share your reviews, and become part of a city-wide reading movement. Free, local, and beautifully simple.                        
                        </p>
                        <div class="reveal flex items-center gap-4" style="transition-delay:0.45s">
                            <a href="{{ route('login') }}" id="hero-cta" class="relative inline-flex items-center gap-2 bg-accent hover:bg-amber-500 text-text shadow-pop2 font-bold text-sm px-6 py-3 rounded-full transition-all duration-300 hover:shadow-xl hover:-translate-y-1 border-2 border-text overflow-hidden group">
                                <span class="relative z-10">GET STARTED</span>
                                <svg class="relative z-10 transition-transform duration-300 group-hover:translate-x-1" width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 7h10M8 3l4 4-4 4"/>
                                </svg>
                                <div class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                            </a>
                            <a href="#fitur" class="text-sm font-semibold text-gray-500 hover:text-gray-900 transition-colors flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M7 2v10M2 7l5 5 5-5"/></svg>
                                Pelajari lebih lanjut
                            </a>
                        </div>

                        <!-- Animated Stats Row -->
                        <div class="reveal flex items-center gap-8 mt-12 pt-8 border-t border-gray-100" style="transition-delay:0.55s">
                            <div>
                                <p class="stat-number text-3xl font-black" data-count="2400" data-suffix="+">0</p>
                                <p class="text-xs text-gray-400 mt-0.5">Koleksi Buku</p>
                            </div>
                            <div class="w-px h-8 bg-gray-100"></div>
                            <div>
                                <p class="stat-number text-3xl font-black" data-count="1200" data-suffix="+">0</p>
                                <p class="text-xs text-gray-400 mt-0.5">Pembaca Aktif</p>
                            </div>
                            <div class="w-px h-8 bg-gray-100"></div>
                            <div>
                                <p class="stat-number text-3xl font-black" data-count="98" data-suffix="%">0</p>
                                <p class="text-xs text-gray-400 mt-0.5">Kepuasan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Bookshelf Illustration -->
                    <div class="reveal-right hidden lg:block" style="transition-delay:0.2s">
                        <div class="animate-float relative">
                            <img
                                src="{{ asset('images/Bookshelf_landing.svg') }}"
                                alt="Rak buku Alinea"
                                class="w-full max-w-lg ml-autoobject-cover"
                                style="max-height: 500px; object-position: center top;"
                            >
                            <!-- Glow effect behind image -->
                            <div class="absolute -inset-4 bg-primary rounded-3xl opacity-50 blur-2xl -z-10"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scroll indicator -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-fade-down" style="animation-delay:1s; opacity:0">
                <span class="text-xs text-gray-400 font-medium tracking-widest uppercase">Scroll</span>
                <div class="w-5 h-8 border-2 border-gray-300 rounded-full flex items-start justify-center p-1">
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"></div>
                </div>
            </div>
        </section>

       

        <!-- =================== SECTION 2: BORROW, READ, GIVE BACK =================== -->
        <section id="fitur" class="bg-primary py-20 lg:py-28 relative overflow-hidden">
            <!-- Decorative circles with animation -->
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-secondary rounded-full opacity-40 pointer-events-none animate-float-slow"></div>
            <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-secondary rounded-full opacity-30 pointer-events-none animate-float-slow" style="animation-delay:2s"></div>

            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <!-- Left: Book Image -->
                    <div class="reveal-left relative order-2 lg:order-1">
                        <div class="relative group">
                            <img
                                src="{{ asset('img/books_stack.png') }}"
                                alt="Tumpukan buku"
                                class="w-full max-w-md rounded-2xl shadow-2xl object-cover transition-transform duration-700 group-hover:scale-[1.02]"
                                style="max-height: 420px;"
                            >
                            <!-- Image glow -->
                            <div class="absolute -inset-3 bg-accent rounded-3xl opacity-20 blur-xl -z-10 transition-opacity duration-300 group-hover:opacity-40"></div>
                        </div>
                    </div>

                    <!-- Right: Content -->
                    <div class="reveal-right order-1 lg:order-2" style="transition-delay:0.1s">
                        <!-- Badge -->
                        <div class="reveal inline-flex items-center gap-2 bg-[#fff176] border-2 border-text text-gray-800 text-xs font-bold px-5 py-1 shadow-pop rounded-full mb-5 tracking-wider">
                            Ayo Pinjam
                        </div>

                        <h2 class="reveal text-4xl lg:text-5xl font-black text-text leading-tight mb-5" style="transition-delay:0.1s">
                            Borrow, Read,<br>Give Back.
                        </h2>

                        <p class="reveal text-text !opacity-50 font-semibold text-sm leading-relaxed mb-8 max-w-md" style="transition-delay:0.2s">
                            Platform ini dibangun di atas nilai gotong royong. Pinjam buku dari anggota komunitas di sekitarmu dengan mudah, baca sepuasnya, lalu kembalikan dan bantu orang lain menikmatinya.
                        </p>

                        <!-- Steps with stagger -->
                        <div class="space-y-4 mb-8">
                            <div class="reveal step-card bg-white rounded-xl p-4 border border-gray-100 shadow-pop2 cursor-default flex items-start gap-4" style="transition-delay:0.3s">
                                <div class="w-10 h-10 bg-accent text-text rounded-xl flex items-center justify-center font-bold text-sm shrink-0 transition-transform duration-300 group-hover:scale-110">01</div>
                                <div>
                                    <p class="font-bold text-sm text-text mb-0.5">Cari Buku</p>
                                    <p class="text-xs text-text">Temukan buku yang tersedia di komunitas dekat kamu dengan pencarian cerdas.</p>
                                </div>
                            </div>
                            <div class="reveal step-card bg-white rounded-xl p-4 border border-gray-100 shadow-pop2 cursor-default flex items-start gap-4" style="transition-delay:0.4s">
                                <div class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center text-text font-bold text-sm shrink-0">02</div>
                                <div>
                                    <p class="font-bold text-sm text-text mb-0.5">Pinjam</p>
                                    <p class="text-xs text-text">Ajukan peminjaman langsung ke pemilik buku dan atur jadwal pengambilan.</p>
                                </div>
                            </div>
                            <div class="reveal step-card bg-white rounded-xl p-4 border border-gray-100 shadow-pop2 cursor-default flex items-start gap-4" style="transition-delay:0.5s">
                                <div class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center text-text font-bold text-sm shrink-0">03</div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900 mb-0.5">Kembalikan & Ulas</p>
                                    <p class="text-xs text-gray-500">Selesai baca? Kembalikan dan tinggalkan ulasan untuk membantu komunitas.</p>
                                </div>
                            </div>
                        </div>

                        <div class="reveal" style="transition-delay:0.6s">
                            <a href="#" id="pinjam-cta" class="inline-flex items-center gap-2 bg-accent hover:bg-white text-gray-900 font-bold text-sm px-6 py-3 rounded-full border-2 border-text shadow-pop2 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group">
                                <span>Mulai Meminjam</span>
                                <svg class="transition-transform duration-300 group-hover:translate-x-1" width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h10M8 3l4 4-4 4"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================== SECTION 3: TWITTER TAPI UNTUK BUKU =================== -->
        <section id="komunitas" class="bg-accent py-20 lg:py-28 relative overflow-hidden">
            <!-- Decorative blobs with animation -->
            <div class="absolute top-10 right-10 w-40 h-40 bg-amber-200 rounded-full opacity-50 blur-2xl pointer-events-none animate-float-slow"></div>
            <div class="absolute bottom-10 left-20 w-56 h-56 bg-orange-200 rounded-full opacity-40 blur-2xl pointer-events-none animate-float-slow" style="animation-delay:3s"></div>

            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <!-- Left: Content -->
                    <div class="reveal-left">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 bg-[#fff176] border-2 border-text text-text text-xs font-bold px-4 py-1.5 rounded-full mb-5 shadow-pop tracking-wider">
                            Apaan tuh?!
                        </div>

                        <h2 class="text-4xl lg:text-5xl font-black text-text leading-tight mb-5">
                            Twitter, Tapi<br>Untuk Buku.
                        </h2>

                        <p class="text-text font-semibold opacity-50 text-sm leading-relaxed mb-8 max-w-md">
                            Bagikan progres bacaan kamu, komentari kutipan favorit, ikuti pembaca lain yang punya selera senada. Alinea adalah timeline membacamu yang hidup dan interaktif.
                        </p>

                        <!-- Tag pills (interactive) -->
                        <div class="flex flex-wrap gap-2 mb-8" id="tag-pills">
                            <span class="tag-pill border-2 border-text text-text text-xs font-semibold px-3 py-1.5 rounded-full bg-secondary shadow-pop">Menurutku</span>
                            <span class="tag-pill border-2 border-text text-text text-xs font-semibold px-3 py-1.5 rounded-full bg-[#fff176] shadow-pop">Akhirnya!!</span>
                            <span class="tag-pill border-2 border-text text-text text-xs font-semibold px-3 py-1.5 rounded-full shadow-pop bg-[#ffb3c6]">"Kuotes"</span>
                            <span class="tag-pill border-2 border-text text-text text-xs font-semibold px-3 py-1.5 rounded-full shadow-pop bg-white">SUKASUKA!</span>
                        </div>

                        <a href="{{ route('timeline_home') }}" id="timeline-cta" class="inline-flex items-center gap-2 bg-white hover:bg-primary text-text font-bold text-sm px-6 py-3 shadow-pop2 border-text border-2 rounded-full transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group">
                            <span>Lihat Timeline</span>
                            <svg class="transition-transform duration-300 group-hover:translate-x-1" width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h10M8 3l4 4-4 4"/></svg>
                        </a>
                    </div>

                    <!-- Right: Phone Mockup / Timeline Preview -->
                    <div class="reveal-right relative" style="transition-delay:0.15s">
                        <div class="bg-white rounded-3xl shadow-2xl p-6 max-w-sm mx-auto border border-gray-100">
                            <!-- Phone-style header -->
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-gray-900">Alinea Timeline</span>
                                </div>
                                <div class="flex gap-1">
                                    <div class="w-2 h-2 bg-red-400 rounded-full"></div>
                                    <div class="w-2 h-2 bg-amber-400 rounded-full"></div>
                                    <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                </div>
                            </div>

                            <!-- Timeline posts -->
                            <div class="space-y-3">
                                <div class="flex gap-3 p-3 bg-amber-50 rounded-xl hover:bg-amber-100 transition-colors cursor-pointer">
                                    <div class="w-8 h-8 bg-sky-400 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">A</div>
                                    <div>
                                        <p class="font-bold text-xs text-gray-900 mb-0.5">Arya S. <span class="text-gray-400 font-normal">· 2m</span></p>
                                        <p class="text-xs text-gray-700 leading-relaxed">"Baru selesai baca Laskar Pelangi. Luar biasa 😭 Siapa mau pinjam?"</p>
                                        <div class="flex gap-3 mt-2 text-gray-400 text-xs">
                                            <span class="hover:text-red-500 cursor-pointer">❤️ 14</span>
                                            <span class="hover:text-sky-500 cursor-pointer">💬 3</span>
                                            <span class="hover:text-green-500 cursor-pointer">🔁 2</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                                    <div class="w-8 h-8 bg-purple-400 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">D</div>
                                    <div>
                                        <p class="font-bold text-xs text-gray-900 mb-0.5">Dina R. <span class="text-gray-400 font-normal">· 15m</span></p>
                                        <p class="text-xs text-gray-700 leading-relaxed">"Progres baca: 67% The Midnight Library. Plot twist-nya gila! 🤯"</p>
                                        <div class="flex gap-3 mt-2 text-gray-400 text-xs">
                                            <span class="hover:text-red-500 cursor-pointer">❤️ 28</span>
                                            <span class="hover:text-sky-500 cursor-pointer">💬 7</span>
                                            <span class="hover:text-green-500 cursor-pointer">🔁 5</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                                    <div class="w-8 h-8 bg-green-400 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">R</div>
                                    <div>
                                        <p class="font-bold text-xs text-gray-900 mb-0.5">Reza M. <span class="text-gray-400 font-normal">· 1j</span></p>
                                        <p class="text-xs text-gray-700 leading-relaxed">"Habis nonton Dune, langsung cari bukunya di Alinea 😂 Ada yang punya?"</p>
                                        <div class="flex gap-3 mt-2 text-gray-400 text-xs">
                                            <span class="hover:text-red-500 cursor-pointer">❤️ 9</span>
                                            <span class="hover:text-sky-500 cursor-pointer">💬 12</span>
                                            <span class="hover:text-green-500 cursor-pointer">🔁 1</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature grid below -->
                <div class="mt-16 grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="reveal feature-grid-card bg-white rounded-2xl p-5 shadow-pop2 border-2 border-text" style="transition-delay:0.1s">
                        <div class="text-2xl mb-2 transition-transform duration-300 hover:scale-125 inline-block">
                            <img src="images/book.svg" alt="" class="h-8 w-auto">
                        </div>
                        <p class="font-bold text-sm text-text mb-1">Bagikan Bacaan</p>
                        <p class="text-xs text-text opacity-70 leading-relaxed">Ceritakan perjalanan bacaanmu ke komunitas</p>
                    </div>
                    <div class="reveal feature-grid-card bg-white rounded-2xl p-5 shadow-pop2 border-2 border-text" style="transition-delay:0.2s">
                        <div class="text-2xl mb-2 transition-transform duration-300 hover:scale-125 inline-block">
                            <img src="images/quotes.svg" class="h-8 w-auto"/>
                        </div>
                        <p class="font-bold text-sm text-text mb-1">Catat Highlight</p>
                        <p class="text-xs text-text opacity-70 leading-relaxed">Simpan kutipan favorit dan bagikan inspirasi</p>
                    </div>
                    <div class="reveal feature-grid-card bg-white rounded-2xl p-5 shadow-pop2 border-2 border-text" style="transition-delay:0.3s">
                        <div class="text-2xl mb-2 transition-transform duration-300 hover:scale-125 inline-block">
                            <img src="images/confetti.svg" alt="" class="h-8 w-auto">
                        </div>
                        <p class="font-bold text-sm text-text mb-1">Ikuti Pembaca</p>
                        <p class="text-xs text-text opacity-70 leading-relaxed">Temukan teman baca dengan selera yang sama</p>
                    </div>
                    <div class="reveal feature-grid-card bg-white border-2 border-text rounded-2xl p-5 shadow-pop2" style="transition-delay:0.4s">
                        <div class="text-2xl mb-2 transition-transform duration-300 hover:scale-125 inline-block">
                            <img src="images/fire.svg" alt="" class="h-8 w-auto">
                        </div>
                        <p class="font-bold text-sm text-text mb-1">Buku Trending</p>
                        <p class="text-xs text-text opacity-70 leading-relaxed">Pantau buku yang lagi ramai dibicarakan</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================== SECTION 4: BACA. ULAS. PAMER! =================== -->
        <section id="ulasan" class="bg-white py-20 lg:py-28 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <!-- Header -->
                <div class="reveal text-center mb-16">
                    <h2 class="text-4xl lg:text-6xl font-black text-text mb-4">
                        Baca. Ulas. Pamer!
                    </h2>
                    <p class="text-text font-semibold opacity-50 text-base max-w-lg mx-auto leading-relaxed">
                        Gabungkan kecintaanmu pada buku dengan ekspresi diri. Buat ulasan yang berkesan dan pamer koleksi seleraanmu ke komunitas.
                    </p>
                </div>

                <!-- Review Cards with reveal-scale -->
                <div class="relative" style="min-height: 500px;">
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 px-4">

                        <div class="reveal-scale review-card bg-white rounded-2xl p-5 border border-text shadow-pop2 cursor-pointer" style="transform: rotate(-2deg); transition-delay:0.05s">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-linear-to-r from-peach-soft/80 to-blue-main rounded-full flex items-center justify-center text-text font-bold text-sm">P</div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Putri C.</p>
                                    <div class="flex gap-0.5 mt-0.5">
                                        <span class="text-amber-400 text-xs">★★★★★</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed italic">"Atomic Habits benar-benar mengubah rutinitas harianku. Rekomendasi banget!"</p>
                            <p class="text-xs text-gray-400 mt-3 font-medium">Atomic Habits</p>
                        </div>

                        <div class="reveal-scale review-card bg-white rounded-2xl p-5 shadow-pop2 border border-text cursor-pointer" style="transform: rotate(2.5deg) translateY(24px); transition-delay:0.1s">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-gradient-4 rounded-full flex items-center justify-center text-text font-bold text-sm">A</div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Alinea C.</p>
                                    <div class="flex gap-0.5 mt-0.5">
                                        <span class="text-amber-400 text-xs">★★★★☆</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed italic">"Bumi Manusia masterpiece! Andrea Hirata benar-benar jagoan cerita."</p>
                            <p class="text-xs text-gray-400 mt-3 font-medium">Bumi Manusia</p>
                        </div>

                        <div class="reveal-scale review-card bg-white rounded-2xl p-5 shadow-pop2 border border-text cursor-pointer col-span-2 lg:col-span-1" style="transform: rotate(-1.5deg) translateY(-12px); transition-delay:0.15s">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-gradient-3 rounded-full flex items-center justify-center text-white font-bold text-sm">R</div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Rasyid F.</p>
                                    <div class="flex gap-0.5 mt-0.5">
                                        <span class="text-amber-400 text-xs">★★★★★</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed italic">"Sapiens membuka pikiran tentang sejarah umat manusia. Wajib dibaca semua orang."</p>
                            <p class="text-xs text-gray-400 mt-3 font-medium">Sapiens</p>
                        </div>

                        <div class="reveal-scale review-card bg-white rounded-2xl p-5 shadow-pop2 border border-text cursor-pointer" style="transform: rotate(3deg) translateY(16px); transition-delay:0.2s">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-linear-to-br from-peach-soft/80 to-blue-main rounded-full flex items-center justify-center text-white font-bold text-sm">S</div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Sari N.</p>
                                    <div class="flex gap-0.5 mt-0.5">
                                        <span class="text-amber-400 text-xs">★★★★☆</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed italic">"The Alchemist singkat tapi dalam banget pesannya. Dibaca dalam satu duduk!"</p>
                            <p class="text-xs text-gray-400 mt-3 font-medium">The Alchemist</p>
                        </div>

                        <div class="reveal-scale review-card bg-white rounded-2xl p-5 shadow-pop2 border border-text cursor-pointer" style="transform: rotate(-2.5deg) translateY(8px); transition-delay:0.25s">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-rose-400 to-red-500 rounded-full flex items-center justify-center text-white font-bold text-sm">B</div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Bara K.</p>
                                    <div class="flex gap-0.5 mt-0.5">
                                        <span class="text-amber-400 text-xs">★★★★★</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed italic">"Educated karya Tara Westover bikin nangis dan terinspirasi. Luar biasa."</p>
                            <p class="text-xs text-gray-400 mt-3 font-medium">Educated</p>
                        </div>

                        <div class="reveal-scale review-card bg-white rounded-2xl p-5 shadow-pop2 border border-text cursor-pointer hidden lg:block" style="transform: rotate(1.5deg) translateY(28px); transition-delay:0.3s">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-violet-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-sm">M</div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900">Maya H.</p>
                                    <div class="flex gap-0.5 mt-0.5">
                                        <span class="text-amber-400 text-xs">★★★★★</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 leading-relaxed italic">"1984 masih relevan banget sampai sekarang. Classic yang nggak boleh dilewatin!"</p>
                            <p class="text-xs text-gray-400 mt-3 font-medium">1984</p>
                        </div>

                    </div>
                </div>

                <!-- CTA -->
                <div class="reveal text-center mt-16">
                    <a href="#" id="ulasan-cta" class="inline-flex items-center text-white gap-2 bg-gray-900 hover:bg-gradient-3 hover:text-text font-bold text-sm px-8 py-4 rounded-full transition-all duration-300 hover:shadow-xl hover:-translate-y-1 group">
                        <span>Tulis Ulasanmu Sekarang</span>
                        <svg class="transition-transform duration-300 group-hover:translate-x-1" width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h10M8 3l4 4-4 4"/></svg>
                    </a>
                </div>
            </div>
        </section>

        <!-- =================== FOOTER =================== -->
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

        <!-- =================== MOBILE NAV MENU =================== -->
        <div id="mobile-menu" class="hidden-menu fixed inset-0 z-40 bg-white/95 backdrop-blur-lg flex flex-col items-center justify-center text-center">
            <button id="close-mobile-menu" class="absolute top-5 right-5 p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Tutup Menu">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 4l12 12M16 4L4 16"/>
                </svg>
            </button>
            <nav class="flex flex-col gap-8 text-2xl font-black text-gray-900">
                <a href="#" class="hover:text-amber-500 transition-colors" onclick="closeMobileMenu()">Beranda</a>
                <a href="#fitur" class="hover:text-amber-500 transition-colors" onclick="closeMobileMenu()">Fitur</a>
                <a href="#komunitas" class="hover:text-amber-500 transition-colors" onclick="closeMobileMenu()">Komunitas</a>
                <a href="#ulasan" class="hover:text-amber-500 transition-colors" onclick="closeMobileMenu()">Ulasan</a>
                <a href="#tentang" class="hover:text-amber-500 transition-colors" onclick="closeMobileMenu()">Tentang</a>
            </nav>
        </div>

        <script>
            /* =========================================================
               SCROLL PROGRESS BAR
            ========================================================= */
            const progressBar = document.getElementById('scroll-progress');
            window.addEventListener('scroll', () => {
                const scrollTop = window.scrollY;
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
                if (progressBar) progressBar.style.width = progress + '%';
            }, { passive: true });

            /* =========================================================
               MOBILE MENU (animated)
            ========================================================= */
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const closeMobileMenuBtn = document.getElementById('close-mobile-menu');

            mobileMenuBtn?.addEventListener('click', () => {
                mobileMenu.classList.remove('hidden-menu');
                mobileMenu.classList.add('visible-menu');
            });

            closeMobileMenuBtn?.addEventListener('click', closeMobileMenu);

            function closeMobileMenu() {
                mobileMenu.classList.remove('visible-menu');
                mobileMenu.classList.add('hidden-menu');
            }

            /* =========================================================
               SMOOTH SCROLL
            ========================================================= */
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href === '#' || !href) return;
                    e.preventDefault();
                    const el = document.querySelector(href);
                    if (el) el.scrollIntoView({ behavior: 'smooth' });
                    closeMobileMenu();
                });
            });

            /* =========================================================
               SCROLL REVEAL (IntersectionObserver)
            ========================================================= */
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

            document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale').forEach(el => {
                revealObserver.observe(el);
            });

            /* =========================================================
               TYPEWRITER EFFECT
            ========================================================= */
            const phrases = [
                'Pinjam. Baca. Bagikan.',
                'Temukan Buku Favoritmu.',
                'Komunitas Pembaca Indonesia.',
                'Explore. Review. Repeat.'
            ];
            let phraseIndex = 0, charIndex = 0, isDeleting = false;
            const typeEl = document.getElementById('typewriter-text');

            function typeWriter() {
                if (!typeEl) return;
                const current = phrases[phraseIndex];
                if (isDeleting) {
                    typeEl.textContent = current.substring(0, charIndex--);
                    if (charIndex < 0) {
                        isDeleting = false;
                        phraseIndex = (phraseIndex + 1) % phrases.length;
                        setTimeout(typeWriter, 400);
                        return;
                    }
                    setTimeout(typeWriter, 50);
                } else {
                    typeEl.textContent = current.substring(0, charIndex++);
                    if (charIndex > current.length) {
                        isDeleting = true;
                        setTimeout(typeWriter, 1800);
                        return;
                    }
                    setTimeout(typeWriter, 80);
                }
            }
            setTimeout(typeWriter, 800);

            /* =========================================================
               ANIMATED COUNTERS
            ========================================================= */
            function animateCounter(el) {
                const target = parseInt(el.dataset.count);
                const suffix = el.dataset.suffix || '';
                const duration = 1800;
                const step = target / (duration / 16);
                let current = 0;
                const timer = setInterval(() => {
                    current = Math.min(current + step, target);
                    el.textContent = Math.floor(current).toLocaleString('id-ID') + suffix;
                    if (current >= target) clearInterval(timer);
                }, 16);
            }

            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        counterObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

            /* =========================================================
               TAG PILLS TOGGLE
            ========================================================= */
            document.querySelectorAll('.tag-pill').forEach(pill => {
                pill.addEventListener('click', () => {
                    document.querySelectorAll('.tag-pill').forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                });
            });

            /* =========================================================
               PARALLAX BLOBS
            ========================================================= */
            const blobs = document.querySelectorAll('.parallax-blob');
            window.addEventListener('mousemove', (e) => {
                const cx = window.innerWidth / 2, cy = window.innerHeight / 2;
                const dx = (e.clientX - cx) / cx;
                const dy = (e.clientY - cy) / cy;
                blobs.forEach(blob => {
                    const speed = parseFloat(blob.dataset.speed || 0.04);
                    blob.style.transform = `translate(${dx * speed * 80}px, ${dy * speed * 80}px)`;
                });
            }, { passive: true });

            /* =========================================================
               PARTICLE CANVAS
            ========================================================= */
            const canvas = document.getElementById('particle-canvas');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                let particles = [];

                function resizeCanvas() {
                    const hero = document.getElementById('hero-section');
                    if (!hero) return;
                    canvas.width = hero.offsetWidth;
                    canvas.height = hero.offsetHeight;
                }
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas, { passive: true });

                function createParticle() {
                    return {
                        x: Math.random() * canvas.width,
                        y: canvas.height + 10,
                        r: Math.random() * 3 + 1,
                        vy: -(Math.random() * 0.6 + 0.2),
                        vx: (Math.random() - 0.5) * 0.4,
                        life: 1,
                        decay: Math.random() * 0.004 + 0.003,
                        color: Math.random() > 0.5 ? '#fbbf24' : '#7dd3fc'
                    };
                }

                for (let i = 0; i < 30; i++) {
                    const p = createParticle();
                    p.y = Math.random() * canvas.height;
                    p.life = Math.random();
                    particles.push(p);
                }

                function drawParticles() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    particles.forEach((p, i) => {
                        p.x += p.vx;
                        p.y += p.vy;
                        p.life -= p.decay;
                        if (p.life <= 0) particles[i] = createParticle();
                        ctx.beginPath();
                        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                        ctx.fillStyle = p.color;
                        ctx.globalAlpha = p.life * 0.5;
                        ctx.fill();
                    });
                    ctx.globalAlpha = 1;
                    requestAnimationFrame(drawParticles);
                }
                drawParticles();
            }

            /* =========================================================
               REVIEW CARD TILT EFFECT (mouse parallax)
            ========================================================= */
            document.querySelectorAll('.review-card').forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const cx = rect.left + rect.width / 2;
                    const cy = rect.top + rect.height / 2;
                    const rx = ((e.clientY - cy) / (rect.height / 2)) * 4;
                    const ry = -((e.clientX - cx) / (rect.width / 2)) * 4;
                    card.style.transform = `rotate(0deg) translateY(-8px) scale(1.04) rotateX(${rx}deg) rotateY(${ry}deg)`;
                    card.style.zIndex = '10';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                    card.style.zIndex = '1';
                });
            });

            /* =========================================================
               NAVBAR SCROLL SHRINK
            ========================================================= */
            const navbar = document.querySelector('nav');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 60) {
                    navbar.classList.add('shadow-md');
                } else {
                    navbar.classList.remove('shadow-md');
                }
            }, { passive: true });
        </script>
    </body>
</html>
