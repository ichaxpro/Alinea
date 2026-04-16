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
                font-family: 'Inter', sans-serif;
            }
            .font-display {
                font-family: 'Playfair Display', serif;
            }
            /* Scrollbar styling */
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: #f1f1f1; }
            ::-webkit-scrollbar-thumb { background: #c5a96b; border-radius: 3px; }

            /* Animations */
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
            }
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes slideInLeft {
                from { opacity: 0; transform: translateX(-40px); }
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes slideInRight {
                from { opacity: 0; transform: translateX(40px); }
                to { opacity: 1; transform: translateX(0); }
            }
            .animate-float { animation: float 4s ease-in-out infinite; }
            .animate-fade-up { animation: fadeInUp 0.8s ease forwards; }
            .animate-slide-left { animation: slideInLeft 0.8s ease forwards; }
            .animate-slide-right { animation: slideInRight 0.8s ease forwards; }

            /* Review card tilt effects */
            .review-card:nth-child(1) { transform: rotate(-3deg); }
            .review-card:nth-child(2) { transform: rotate(2deg) translateY(20px); }
            .review-card:nth-child(3) { transform: rotate(-1.5deg) translateY(-10px); }
            .review-card:nth-child(4) { transform: rotate(3.5deg) translateY(30px); }
            .review-card:nth-child(5) { transform: rotate(-2deg) translateY(10px); }
            .review-card:hover { transform: rotate(0) translateY(-5px) scale(1.02); z-index: 10; }

            /* Tag pill hover */
            .tag-pill { transition: all 0.2s ease; }
            .tag-pill:hover { transform: translateY(-2px); }

            /* Step card hover */
            .step-card { transition: all 0.3s ease; }
            .step-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }

            /* Nav link hover underline */
            .nav-link::after {
                content: '';
                position: absolute;
                width: 0;
                height: 2px;
                background: #1a1a1a;
                bottom: -2px;
                left: 0;
                transition: width 0.3s ease;
            }
            .nav-link:hover::after { width: 100%; }
        </style>
    </head>
    <body class="bg-white text-gray-900 overflow-x-hidden">

        <!-- =================== NAVBAR =================== -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <a href="#" class="flex items-center gap-2 group">
                        <div class="w-7 h-7 bg-amber-400 rounded-full flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300">
                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2 12V3C2 2.45 2.45 2 3 2H11C11.55 2 12 2.45 12 3V12L7 10L2 12Z" fill="white"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold tracking-tight text-gray-900">Alinea</span>
                    </a>

                    <!-- Nav Links (Desktop) -->
                    <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                        <a href="#" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Beranda</a>
                        <a href="#fitur" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Fitur</a>
                        <a href="#komunitas" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Komunitas</a>
                        <a href="#ulasan" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Ulasan</a>
                        <a href="#tentang" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Tentang</a>
                    </div>

                    <!-- CTA Button -->
                    <div class="flex items-center gap-3">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">Masuk</a>
                            @endauth
                        @endif
                        <a href="#" id="cta-nav" class="bg-gray-900 text-white text-sm font-semibold px-5 py-2 rounded-full hover:bg-gray-700 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                            Mulai →
                        </a>
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
        <section class="min-h-screen bg-white pt-16 relative overflow-hidden">
            <!-- Subtle background decoration -->
            <div class="absolute top-20 right-0 w-96 h-96 bg-amber-50 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
            <div class="absolute bottom-20 left-0 w-64 h-64 bg-sky-50 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16 lg:py-24">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left: Text Content -->
                    <div class="animate-fade-up">
                        <!-- Small decorative element -->
                        <div class="flex items-center gap-2 mb-6">
                            <div class="w-2 h-2 bg-sky-400 rounded-full"></div>
                            <div class="w-2 h-2 bg-amber-400 rounded-full"></div>
                        </div>

                        <h1 class="text-7xl lg:text-8xl font-black tracking-tighter text-gray-900 leading-none mb-6">
                            Alinea
                        </h1>

                        <p class="text-gray-500 text-base leading-relaxed max-w-sm mb-8">
                            Alinea menghubungkan para pembaca. Pinjam buku dari sesama, bagikan ulasan jujur, dan temukan buku favorit berikutnya bersama komunitas di sekitarmu.
                        </p>

                        <div class="flex items-center gap-4">
                            <a href="#" id="hero-cta" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-500 text-gray-900 font-bold text-sm px-6 py-3 rounded-full transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 border-2 border-amber-500">
                                <span>GET STARTED</span>
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 7h10M8 3l4 4-4 4"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Right: Bookshelf Illustration -->
                    <div class="relative animate-slide-right hidden lg:block">
                        <div class="animate-float">
                            <img
                                src="{{ asset('img/bookshelf.png') }}"
                                alt="Rak buku Alinea"
                                class="w-full max-w-lg ml-auto rounded-2xl shadow-2xl object-cover"
                                style="max-height: 500px; object-position: center top;"
                            >
                        </div>
                        <!-- Floating decorative badge -->
                        <div class="absolute -bottom-4 -left-8 bg-white rounded-2xl px-4 py-3 shadow-xl border border-gray-100 animate-float" style="animation-delay: 1s;">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-lg">📚</div>
                                <div>
                                    <p class="text-xs font-bold text-gray-900">2,400+ Buku</p>
                                    <p class="text-xs text-gray-500">Tersedia di kotamu</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================== SECTION 2: BORROW, READ, GIVE BACK =================== -->
        <section id="fitur" class="bg-sky-100 py-20 lg:py-28 relative overflow-hidden">
            <!-- Decorative circles -->
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-sky-200 rounded-full opacity-40 pointer-events-none"></div>
            <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-sky-200 rounded-full opacity-30 pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <!-- Left: Book Image -->
                    <div class="relative order-2 lg:order-1">
                        <div class="relative">
                            <img
                                src="{{ asset('img/books_stack.png') }}"
                                alt="Tumpukan buku"
                                class="w-full max-w-md rounded-2xl shadow-2xl object-cover"
                                style="max-height: 420px;"
                            >
                            <!-- Label overlay -->
                            <div class="absolute top-4 left-4 bg-amber-400 text-gray-900 text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">
                                📖 Perpustakaan Komunitas
                            </div>
                        </div>
                    </div>

                    <!-- Right: Content -->
                    <div class="order-1 lg:order-2">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 bg-amber-300 border-2 border-amber-400 text-gray-800 text-xs font-bold px-3 py-1.5 rounded-full mb-5 uppercase tracking-wider">
                            ✨ Fitur Utama
                        </div>

                        <h2 class="text-4xl lg:text-5xl font-black text-gray-900 leading-tight mb-5">
                            Borrow, Read,<br>Give Back.
                        </h2>

                        <p class="text-gray-600 text-sm leading-relaxed mb-8 max-w-md">
                            Platform ini dibangun di atas nilai gotong royong. Pinjam buku dari anggota komunitas di sekitarmu dengan mudah, baca sepuasnya, lalu kembalikan dan bantu orang lain menikmatinya.
                        </p>

                        <!-- Steps -->
                        <div class="space-y-4 mb-8">
                            <div class="step-card bg-white rounded-xl p-4 border border-gray-100 shadow-sm cursor-default flex items-start gap-4">
                                <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center text-sky-600 font-bold text-sm shrink-0">01</div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900 mb-0.5">Cari Buku</p>
                                    <p class="text-xs text-gray-500">Temukan buku yang tersedia di komunitas dekat kamu dengan pencarian cerdas.</p>
                                </div>
                            </div>
                            <div class="step-card bg-white rounded-xl p-4 border border-gray-100 shadow-sm cursor-default flex items-start gap-4">
                                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 font-bold text-sm shrink-0">02</div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900 mb-0.5">Pinjam</p>
                                    <p class="text-xs text-gray-500">Ajukan peminjaman langsung ke pemilik buku dan atur jadwal pengambilan.</p>
                                </div>
                            </div>
                            <div class="step-card bg-white rounded-xl p-4 border border-gray-100 shadow-sm cursor-default flex items-start gap-4">
                                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center text-green-600 font-bold text-sm shrink-0">03</div>
                                <div>
                                    <p class="font-bold text-sm text-gray-900 mb-0.5">Kembalikan & Ulas</p>
                                    <p class="text-xs text-gray-500">Selesai baca? Kembalikan dan tinggalkan ulasan untuk membantu komunitas.</p>
                                </div>
                            </div>
                        </div>

                        <a href="#" id="pinjam-cta" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-500 text-gray-900 font-bold text-sm px-6 py-3 rounded-full border-2 border-amber-500 transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                            Mulai Meminjam →
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================== SECTION 3: TWITTER TAPI UNTUK BUKU =================== -->
        <section id="komunitas" class="bg-amber-100 py-20 lg:py-28 relative overflow-hidden">
            <!-- Decorative blobs -->
            <div class="absolute top-10 right-10 w-40 h-40 bg-amber-200 rounded-full opacity-50 blur-2xl pointer-events-none"></div>
            <div class="absolute bottom-10 left-20 w-56 h-56 bg-orange-200 rounded-full opacity-40 blur-2xl pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <!-- Left: Content -->
                    <div>
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 bg-white border-2 border-gray-200 text-gray-700 text-xs font-bold px-3 py-1.5 rounded-full mb-5 uppercase tracking-wider shadow-sm">
                            🐦 Fitur Komunitas
                        </div>

                        <h2 class="text-4xl lg:text-5xl font-black text-gray-900 leading-tight mb-5">
                            Twitter, Tapi<br>Untuk Buku.
                        </h2>

                        <p class="text-gray-700 text-sm leading-relaxed mb-8 max-w-md">
                            Bagikan progres bacaan kamu, komentari kutipan favorit, ikuti pembaca lain yang punya selera senada. Alinea adalah timeline membacamu yang hidup dan interaktif.
                        </p>

                        <!-- Tag pills -->
                        <div class="flex flex-wrap gap-2 mb-8" id="tag-pills">
                            <span class="tag-pill bg-white border border-gray-200 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-full cursor-pointer hover:bg-gray-900 hover:text-white hover:border-gray-900 shadow-sm">#FiksiIlmiah</span>
                            <span class="tag-pill bg-gray-900 text-white text-xs font-semibold px-3 py-1.5 rounded-full cursor-pointer hover:bg-gray-700 shadow-sm">#SastraIndonesia</span>
                            <span class="tag-pill bg-white border border-gray-200 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-full cursor-pointer hover:bg-gray-900 hover:text-white hover:border-gray-900 shadow-sm">#NonFiksi</span>
                            <span class="tag-pill bg-white border border-gray-200 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-full cursor-pointer hover:bg-gray-900 hover:text-white hover:border-gray-900 shadow-sm">#Sejarah</span>
                            <br>
                            <span class="tag-pill bg-white border border-gray-200 text-gray-700 text-xs font-semibold px-3 py-1.5 rounded-full cursor-pointer hover:bg-gray-900 hover:text-white hover:border-gray-900 shadow-sm">#Gabung</span>
                        </div>

                        <a href="#" id="timeline-cta" class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-700 text-white font-bold text-sm px-6 py-3 rounded-full transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                            Lihat Timeline →
                        </a>
                    </div>

                    <!-- Right: Phone Mockup / Timeline Preview -->
                    <div class="relative">
                        <div class="bg-white rounded-3xl shadow-2xl p-6 max-w-sm mx-auto border border-gray-100">
                            <!-- Phone-style header -->
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-amber-400 rounded-full flex items-center justify-center text-xs">📚</div>
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

                        <!-- Decorative floating cards -->
                        <div class="absolute -top-6 -right-6 bg-white rounded-xl p-3 shadow-xl border border-gray-100 text-center animate-float hidden lg:block" style="animation-delay: 0.5s;">
                            <p class="text-2xl font-black text-gray-900">1.2K</p>
                            <p class="text-xs text-gray-500">Pembaca Aktif</p>
                        </div>
                    </div>
                </div>

                <!-- Feature grid below -->
                <div class="mt-16 grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-100 hover:shadow-md transition-shadow cursor-default">
                        <div class="text-2xl mb-2">📖</div>
                        <p class="font-bold text-sm text-gray-900 mb-1">Bagikan Bacaan</p>
                        <p class="text-xs text-gray-500 leading-relaxed">Ceritakan perjalanan bacaanmu ke komunitas</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-100 hover:shadow-md transition-shadow cursor-default">
                        <div class="text-2xl mb-2">✍️</div>
                        <p class="font-bold text-sm text-gray-900 mb-1">Catat Highlight</p>
                        <p class="text-xs text-gray-500 leading-relaxed">Simpan kutipan favorit dan bagikan inspirasi</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-100 hover:shadow-md transition-shadow cursor-default">
                        <div class="text-2xl mb-2">🎯</div>
                        <p class="font-bold text-sm text-gray-900 mb-1">Ikuti Pembaca</p>
                        <p class="text-xs text-gray-500 leading-relaxed">Temukan teman baca dengan selera yang sama</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-amber-100 hover:shadow-md transition-shadow cursor-default">
                        <div class="text-2xl mb-2">🔥</div>
                        <p class="font-bold text-sm text-gray-900 mb-1">Buku Trending</p>
                        <p class="text-xs text-gray-500 leading-relaxed">Pantau buku yang lagi ramai dibicarakan</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- =================== SECTION 4: BACA. ULAS. PAMER! =================== -->
        <section id="ulasan" class="bg-white py-20 lg:py-28 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-16">
                    <h2 class="text-4xl lg:text-6xl font-black text-gray-900 mb-4">
                        Baca. Ulas. Pamer!
                    </h2>
                    <p class="text-gray-500 text-base max-w-lg mx-auto leading-relaxed">
                        Gabungkan kecintaanmu pada buku dengan ekspresi diri. Buat ulasan yang berkesan dan pamer koleksi seleraanmu ke komunitas.
                    </p>
                </div>

                <!-- Review Cards (scattered layout) -->
                <div class="relative" style="min-height: 500px;">
                    <!-- Grid for desktop scattered look -->
                    <div class="grid grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 px-4">

                        <div class="review-card bg-white rounded-2xl p-5 shadow-lg border border-gray-100 cursor-pointer transition-all duration-300" style="transform: rotate(-2deg);">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm">P</div>
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

                        <div class="review-card bg-amber-50 rounded-2xl p-5 shadow-lg border border-amber-100 cursor-pointer transition-all duration-300" style="transform: rotate(2.5deg) translateY(24px);">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center text-white font-bold text-sm">A</div>
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

                        <div class="review-card bg-sky-50 rounded-2xl p-5 shadow-lg border border-sky-100 cursor-pointer transition-all duration-300 col-span-2 lg:col-span-1" style="transform: rotate(-1.5deg) translateY(-12px);">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-sm">R</div>
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

                        <div class="review-card bg-green-50 rounded-2xl p-5 shadow-lg border border-green-100 cursor-pointer transition-all duration-300" style="transform: rotate(3deg) translateY(16px);">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-teal-500 rounded-full flex items-center justify-center text-white font-bold text-sm">S</div>
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

                        <div class="review-card bg-rose-50 rounded-2xl p-5 shadow-lg border border-rose-100 cursor-pointer transition-all duration-300" style="transform: rotate(-2.5deg) translateY(8px);">
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

                        <div class="review-card bg-violet-50 rounded-2xl p-5 shadow-lg border border-violet-100 cursor-pointer transition-all duration-300 hidden lg:block" style="transform: rotate(1.5deg) translateY(28px);">
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
                <div class="text-center mt-16">
                    <a href="#" id="ulasan-cta" class="inline-flex items-center gap-2 bg-gray-900 hover:bg-gray-700 text-white font-bold text-sm px-8 py-4 rounded-full transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                        Tulis Ulasanmu Sekarang →
                    </a>
                </div>
            </div>
        </section>

        <!-- =================== FOOTER =================== -->
        <footer id="tentang" class="bg-gray-950 text-gray-400 py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                    <!-- Logo & Brand -->
                    <div class="col-span-2 lg:col-span-1">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center">
                                <svg width="16" height="16" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 12V3C2 2.45 2.45 2 3 2H11C11.55 2 12 2.45 12 3V12L7 10L2 12Z" fill="white"/>
                                </svg>
                            </div>
                            <span class="text-white text-xl font-bold">Alinea</span>
                        </div>
                        <p class="text-sm text-gray-500 leading-relaxed mb-5 max-w-xs">
                            Platform komunitas buku pertama dari dan untuk pembaca Indonesia. Pinjam, Baca, Bagikan.
                        </p>
                        <div class="flex gap-3">
                            <a href="#" class="w-9 h-9 bg-gray-800 hover:bg-amber-400 rounded-full flex items-center justify-center transition-colors duration-300" aria-label="Twitter">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                            <a href="#" class="w-9 h-9 bg-gray-800 hover:bg-amber-400 rounded-full flex items-center justify-center transition-colors duration-300" aria-label="Instagram">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1.5" fill="currentColor" stroke="none"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Fitur -->
                    <div>
                        <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Fitur</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Pinjam Buku</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Timeline</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Ulasan Buku</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Book Club</a></li>
                        </ul>
                    </div>

                    <!-- Informasi -->
                    <div>
                        <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Informasi</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Tentang Kami</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Blog</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Karir</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Bantuan</a></li>
                        </ul>
                    </div>

                    <!-- Quick Contact -->
                    <div>
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
                    <p class="text-xs text-gray-600">© {{ date('Y') }} Alinea. All rights reserved.</p>
                    <div class="flex gap-6 text-xs">
                        <a href="#" class="hover:text-white transition-colors duration-200">Syarat & Ketentuan</a>
                        <a href="#" class="hover:text-white transition-colors duration-200">Privasi</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- =================== MOBILE NAV MENU =================== -->
        <div id="mobile-menu" class="hidden fixed inset-0 z-40 bg-white/95 backdrop-blur-lg flex-col items-center justify-center text-center">
            <button id="close-mobile-menu" class="absolute top-5 right-5 p-2 rounded-lg hover:bg-gray-100" aria-label="Tutup Menu">
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
            // Mobile menu
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const closeMobileMenuBtn = document.getElementById('close-mobile-menu');

            mobileMenuBtn?.addEventListener('click', () => {
                mobileMenu.classList.remove('hidden');
                mobileMenu.classList.add('flex');
            });

            closeMobileMenuBtn?.addEventListener('click', closeMobileMenu);

            function closeMobileMenu() {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('flex');
            }

            // Smooth scroll for nav links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href === '#' || !href) return;
                    e.preventDefault();
                    const el = document.querySelector(href);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            // Scroll reveal with IntersectionObserver
            const observerOptions = {
                threshold: 0.15,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = entry.target.dataset.transform || 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe sections
            document.querySelectorAll('section').forEach(section => {
                observer.observe(section);
            });

            // Review cards interactive tilt on hover
            document.querySelectorAll('.review-card').forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transition = 'all 0.3s ease';
                    card.style.zIndex = '10';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.zIndex = '1';
                });
            });
        </script>
    </body>
</html>
