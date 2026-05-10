<!-- =================== NAVBAR =================== -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <a href="{{ route('beranda') }}" class="flex items-center gap-2 group py-16">
                        <div class="flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <img fill="none" src="img/alinealogo.svg" class="h-7">
                        </div>
                    </a>

                    <!-- Nav Links (Desktop) -->
                    <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                        <a href="{{ route('beranda') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Beranda</a>
                        <a href="{{ route('pinjam') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Pinjam</a>
                        <a href="{{ route('timeline_home') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Komunitas</a>
                        <a href="{{ route('klub') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Klub</a>
                        <a href="{{ route('ulasan') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Ulasan</a>
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