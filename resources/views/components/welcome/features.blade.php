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
                            Pinjam, Baca,<br>Kembalikan.
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
                            <a href="{{ route('mulai') }}" id="pinjam-cta" class="inline-flex items-center gap-2 bg-accent hover:bg-white text-gray-900 font-bold text-sm px-6 py-3 rounded-full border-2 border-text shadow-pop2 transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group">
                                <span>Mulai Meminjam</span>
                                <svg class="transition-transform duration-300 group-hover:translate-x-1" width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h10M8 3l4 4-4 4"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>