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
                            Pinjam buku dari sesama pengguna, bagikan ulasanmu, dan jadilah bagian dari gerakan literasi kotamu. Gratis, lokal, dan mudah.                      
                        </p>
                        <div class="reveal flex items-center gap-4" style="transition-delay:0.45s">
                            <a href="{{ route('mulai') }}" id="hero-cta" class="relative inline-flex items-center gap-2 bg-accent hover:bg-amber-500 text-text shadow-pop2 font-bold text-sm px-6 py-3 rounded-full transition-all duration-300 hover:shadow-xl hover:-translate-y-1 border-2 border-text overflow-hidden group">
                                <span class="relative z-10">Mulai</span>
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