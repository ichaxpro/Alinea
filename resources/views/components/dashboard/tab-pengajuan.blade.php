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
        
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach([
                ['key'=>'all','label'=>'Semua'],
                ['key'=>'incoming','label'=>'Menunggu'],
                ['key'=>'ongoing','label'=>'Aktif'],
                ['key'=>'history','label'=>'Riwayat'],
            ] as $i => $f)
            <button data-pengajuan-filter="{{ $f['key'] }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs border-[1.5px] transition-all duration-200 cursor-pointer
                           {{ $i===0 ? 'bg-[#FFDDAF] border-[#444] text-[#444] font-bold' : 'bg-white border-gray-200 text-gray-400 font-medium hover:border-gray-400' }}">
                {{ $f['label'] }}
                <span class="bg-white/60 px-1.5 py-0.5 rounded-full text-[10px] font-bold">0</span>
            </button>
            @endforeach
        </div>

        <div id="pengajuan-list" class="space-y-3"></div>
    </div>
</div>
