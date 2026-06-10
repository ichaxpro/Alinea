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
                            Bagikan progres bacaan kamu, komentari kutipan favorit, ikuti pembaca lain yang punya selera senada. Alinea adalah lini masa membacamu yang hidup dan interaktif.
                        </p>

                        <!-- Tag pills (interactive) -->
                        <div class="flex flex-wrap gap-2 mb-8" id="tag-pills">
                            <span class="tag-pill border-2 border-text text-text text-xs font-semibold px-3 py-1.5 rounded-full bg-secondary shadow-pop">Menurutku</span>
                            <span class="tag-pill border-2 border-text text-text text-xs font-semibold px-3 py-1.5 rounded-full bg-[#fff176] shadow-pop">Akhirnya!!</span>
                            <span class="tag-pill border-2 border-text text-text text-xs font-semibold px-3 py-1.5 rounded-full shadow-pop bg-[#ffb3c6]">"Kuotes"</span>
                            <span class="tag-pill border-2 border-text text-text text-xs font-semibold px-3 py-1.5 rounded-full shadow-pop bg-white">SUKASUKA!</span>
                        </div>

                        <a href="{{ route('timeline_home') }}" id="timeline-cta" class="inline-flex items-center gap-2 bg-white hover:bg-primary text-text font-bold text-sm px-6 py-3 shadow-pop2 border-text border-2 rounded-full transition-all duration-300 hover:shadow-lg hover:-translate-y-1 group">
                            <span>Lihat lini masa</span>
                            <svg class="transition-transform duration-300 group-hover:translate-x-1" width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 7h10M8 3l4 4-4 4"/></svg>
                        </a>
                    </div>

                    <!-- Right: Timeline Preview (mirrors actual UI) -->
                    <div class="reveal-right relative" style="transition-delay:0.15s">
                        <div class="bg-[#f3f4f6] rounded-3xl shadow-2xl p-4 max-w-sm mx-auto border-2 border-[#444] overflow-hidden" style="max-height:420px; overflow:hidden;">

                            <!-- Tab bar -->
                            <div class="flex bg-white border-[1.5px] border-[#444] rounded-full overflow-hidden mb-3">
                                <div class="flex-1 py-2 text-xs font-bold text-[#444] bg-[#FFDDAF] rounded-full text-center">For You</div>
                                <div class="flex-1 py-2 text-xs text-gray-400 rounded-full text-center">Following</div>
                            </div>

                            <!-- Composer hint -->
                            <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-3 mb-3">
                                <div class="flex gap-2 items-center">
                                    <div class="w-8 h-8 rounded-full border-2 border-[#444] shrink-0" style="background: linear-gradient(135deg, #FFDDAF, #C7E7FF)"></div>
                                    <div class="flex-1">
                                        <div class="flex gap-1 mb-1.5">
                                            <span class="text-[9px] font-semibold px-2 py-0.5 rounded-full border border-[#444] bg-[#FFDDAF] text-[#444]">Dibaca</span>
                                            <span class="text-[9px] px-2 py-0.5 rounded-full border border-gray-300 text-gray-400">Selesai</span>
                                            <span class="text-[9px] px-2 py-0.5 rounded-full border border-gray-300 text-gray-400">Kutipan</span>
                                        </div>
                                        <div class="text-[10px] text-gray-300 bg-gray-50 rounded-lg px-2 py-1.5">Apa yang sedang kamu baca?...</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Post 1 -->
                            <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-3 mb-2">
                                <div class="flex items-center gap-2 mb-2 justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full border-2 border-[#444] shrink-0" style="background: linear-gradient(135deg,#FFDDAF,#C7E7FF)"></div>
                                        <div>
                                            <p class="font-bold text-[11px] leading-tight">Budi Ashcroft</p>
                                            <p class="text-[9px] text-gray-400">@isoba__ · Malang · 12 Menit Lalu</p>
                                        </div>
                                    </div>
                                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border border-[#444] bg-[#fff176]">Dibaca</span>
                                </div>
                                <span class="inline-flex text-[9px] font-bold px-2.5 py-0.5 rounded-full border border-[#444] bg-[#FFDDAF] mb-1.5">Harry Potter</span>
                                <p class="text-[10px] text-gray-600 leading-relaxed mb-2 line-clamp-2">Harry Potter adalah kisah tentang seorang anak penyihir yang menemukan jati dirinya di Sekolah Hogwarts...</p>
                                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                                    <button class="flex items-center gap-1 text-gray-400 text-[10px]">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        1.2K
                                    </button>
                                    <button class="flex items-center gap-1 text-red-500 text-[10px]">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                        50K
                                    </button>
                                    <div class="ml-auto flex gap-1">
                                        <button class="w-6 h-6 flex items-center justify-center rounded-full text-gray-400">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                        </button>
                                        <button class="w-6 h-6 flex items-center justify-center rounded-full text-gray-400">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Post 2 -->
                            <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-3">
                                <div class="flex items-center gap-2 mb-2 justify-between">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full border-2 border-[#444] shrink-0" style="background: linear-gradient(135deg,#C7E7FF,#FFDDAF)"></div>
                                        <div>
                                            <p class="font-bold text-[11px] leading-tight">Dina Rahmawati</p>
                                            <p class="text-[9px] text-gray-400">@dina_r · Surabaya · 35 Menit Lalu</p>
                                        </div>
                                    </div>
                                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-full border border-[#444] bg-[#fff176]">Selesai</span>
                                </div>
                                <span class="inline-flex text-[9px] font-bold px-2.5 py-0.5 rounded-full border border-[#444] bg-[#FFDDAF] mb-1.5">The Midnight Library</span>
                                <p class="text-[10px] text-gray-600 leading-relaxed mb-2 line-clamp-2">Baru sampai di halaman 67% dan plot twist-nya benar-benar di luar ekspektasi...</p>
                                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                                    <button class="flex items-center gap-1 text-gray-400 text-[10px]">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                        843
                                    </button>
                                    <button class="flex items-center gap-1 text-gray-400 text-[10px]">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                                        28K
                                    </button>
                                    <div class="ml-auto flex gap-1">
                                        <button class="w-6 h-6 flex items-center justify-center rounded-full text-gray-400">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                        </button>
                                        <button class="w-6 h-6 flex items-center justify-center rounded-full text-gray-400">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                        </button>
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