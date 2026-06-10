<div id="add-book-modal" class="hidden fixed inset-0 z-[200] bg-black/40 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl border-[1.5px] border-[#444] w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 md:p-8 relative animate-[fadeInUp_0.2s_ease]">
        <button id="close-add-book" class="absolute top-4 right-4 w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors cursor-pointer z-10">
            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="#444" stroke-width="2.5" stroke-linecap="round"><path d="M4 4l12 12M16 4L4 16"/></svg>
        </button>

        <h3 class="font-bold text-lg mb-1">Tambah Buku Baru</h3>
        <p class="text-xs text-gray-400 mb-5">Cari judul buku untuk mengisi otomatis, atau isi manual</p>

        {{-- Book Search Section --}}
        <div class="mb-5 relative" id="book-search-wrapper">
            <label for="book-search-input" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Cari Buku</label>
            <div class="relative">
                <div class="flex items-center gap-2 border-[1.5px] border-gray-200 rounded-xl px-4 py-3 focus-within:border-[#444] transition-colors bg-white">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" class="flex-shrink-0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" id="book-search-input" autocomplete="off" class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" placeholder="Ketik judul buku, penulis, atau ISBN..." />
                    <svg id="book-search-spinner" class="hidden animate-spin flex-shrink-0 text-gray-400" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                </div>

                {{-- Search Results Dropdown --}}
                <div id="book-search-results" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border-[1.5px] border-[#444] rounded-2xl shadow-xl z-50 max-h-[320px] overflow-y-auto">
                    {{-- Results will be injected by JS --}}
                </div>
            </div>
            <p class="text-[11px] text-gray-300 mt-1.5">Minimal 3 karakter untuk mulai mencari</p>
        </div>

        {{-- Selected Book Preview (shown after picking from dropdown) --}}
        <div id="book-preview" class="hidden mb-5 bg-gradient-to-r from-[#D4F6FF]/30 to-[#FFDDAF]/20 border-[1.5px] border-[#444]/15 rounded-2xl p-4 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-bold text-green-600 uppercase tracking-wider flex items-center gap-1">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    Buku Dipilih
                </span>
                <button type="button" id="btn-clear-selection" class="text-[11px] text-gray-400 hover:text-red-500 transition-colors cursor-pointer font-medium flex items-center gap-1">
                    <svg width="10" height="10" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M4 4l12 12M16 4L4 16"/></svg>
                    Hapus
                </button>
            </div>
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <img id="preview-cover" src="" alt="Cover" class="w-[72px] h-[108px] rounded-lg border-[1.5px] border-[#444] object-cover bg-gray-100 shadow-sm" />
                    <div id="preview-cover-placeholder" class="hidden w-[72px] h-[108px] rounded-lg border-[1.5px] border-[#444] bg-gradient-to-br from-[#C7E7FF] to-[#D4F6FF] items-center justify-center">
                        <span class="text-xl font-black text-[#444]/40" id="preview-cover-initial"></span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p id="preview-title" class="font-bold text-[15px] text-[#444] leading-tight mb-1"></p>
                    <p id="preview-author" class="text-xs text-gray-500 mb-2"></p>
                    <div class="flex flex-wrap gap-1.5">
                        <span id="preview-year" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FFDDAF]/50 text-[#444] border border-[#444]/15"></span>
                        <span id="preview-category" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#C7E7FF]/50 text-[#444] border border-[#444]/15"></span>
                        <span id="preview-pages" class="hidden inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#D4F6FF]/50 text-[#444] border border-[#444]/15"></span>
                        <span id="preview-isbn" class="hidden inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200 font-mono"></span>
                    </div>
                    <p id="preview-desc" class="text-[11px] text-gray-400 mt-2 line-clamp-2 leading-relaxed"></p>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="flex items-center gap-3 mb-5">
            <div class="flex-1 h-px bg-gray-200"></div>
            <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">Detail Buku</span>
            <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <form id="add-book-form" class="space-y-4">
            <div>
                <label for="add-book-judul" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Judul Buku <span class="text-red-400">*</span></label>
                <input type="text" id="add-book-judul" name="judul" required class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" placeholder="Masukkan judul buku" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="add-book-penulis" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Penulis <span class="text-red-400">*</span></label>
                    <input type="text" id="add-book-penulis" name="penulis" required class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" placeholder="Nama penulis" />
                </div>
                <div>
                    <label for="add-book-isbn-manual" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">ISBN</label>
                    <input type="text" id="add-book-isbn-manual" name="isbn" class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors font-mono" placeholder="978-xxx-xxx" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="add-book-tahun" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Tahun Terbit</label>
                    <input type="number" id="add-book-tahun" name="tahun_terbit" min="1900" max="2030" class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" placeholder="2024" />
                </div>
                <div>
                    <label for="add-book-kategori" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Kategori</label>
                    <select id="add-book-kategori" name="kategori" class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors bg-white cursor-pointer">
                        <option value="Fiksi">Fiksi</option>
                        <option value="Non-Fiksi">Non-Fiksi</option>
                        <option value="Thriller">Thriller</option>
                        <option value="Misteri">Misteri</option>
                        <option value="Romansa">Romansa</option>
                        <option value="Sci-Fi">Sci-Fi</option>
                        <option value="Fantasi">Fantasi</option>
                        <option value="Horror">Horror</option>
                        <option value="Biografi">Biografi</option>
                        <option value="Sejarah">Sejarah</option>
                        <option value="Pengembangan Diri">Pengembangan Diri</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="add-book-halaman" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Jumlah Halaman</label>
                    <input type="number" id="add-book-halaman" name="halaman" min="1" class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" placeholder="320" />
                </div>
            </div>
            {{-- Hidden fields --}}
            <input type="hidden" id="add-book-cover-url" name="foto_sampul" />

            <div class="pt-2 flex gap-3">
                <button type="submit" class="flex-1 bg-[#FFDDAF] text-[#444] font-bold text-sm py-3 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors cursor-pointer">
                    Tambahkan
                </button>
                <button type="button" data-action="close-add-book-modal" class="px-6 py-3 text-sm font-medium text-gray-400 hover:text-[#444] transition-colors cursor-pointer">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
