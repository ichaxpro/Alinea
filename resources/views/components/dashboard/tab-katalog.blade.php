<div data-tab-panel="katalog" class="hidden">
    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 md:p-8">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#FFDDAF]/30 flex items-center justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-lg">Katalog Buku</h2>
                    <p class="text-xs text-gray-400">Kelola koleksi pribadi — <span id="catalog-count" class="font-medium">0 buku</span></p>
                </div>
            </div>
            <button id="btn-add-book" class="bg-[#FFDDAF] text-[#444] font-bold text-sm px-5 py-2.5 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors cursor-pointer flex items-center gap-2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Buku
            </button>
        </div>

        {{-- Search --}}
        <div class="flex items-center gap-2 bg-gray-50 border-[1.5px] border-gray-200 rounded-xl px-4 py-2.5 mb-5 focus-within:border-[#444] transition-colors">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" id="catalog-search" placeholder="Cari buku di koleksimu..." class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" />
        </div>

        <div id="catalog-list"></div>
    </div>
</div>
