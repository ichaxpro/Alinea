<div data-tab-panel="pengajuan" class="hidden">
    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#FFDDAF]/30 flex items-center justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-lg">Pengajuan Pinjam</h2>
                    <p class="text-xs text-gray-400">Permintaan peminjaman bukumu dari pengguna lain</p>
                </div>
            </div>
        </div>
        
        <div class="flex flex-wrap items-center gap-2 mb-4 bg-gray-50/50 p-1.5 rounded-xl border-[1.5px] border-gray-100">
            <button data-pengajuan-filter="all" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all bg-[#FFDDAF] text-[#444] border-[1.5px] border-[#444] shadow-sm">Semua <span class="ml-1 opacity-60">0</span></button>
            <button data-pengajuan-filter="incoming" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-gray-500 hover:bg-gray-100">Menunggu <span class="ml-1 opacity-60">0</span></button>
            <button data-pengajuan-filter="ongoing" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-gray-500 hover:bg-gray-100">Aktif <span class="ml-1 opacity-60">0</span></button>
            <button data-pengajuan-filter="history" class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all text-gray-500 hover:bg-gray-100">Riwayat <span class="ml-1 opacity-60">0</span></button>
        </div>

        <div id="pengajuan-list" class="space-y-3"></div>
    </div>
</div>
