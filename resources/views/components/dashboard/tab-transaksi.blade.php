<div data-tab-panel="transaksi" class="hidden">
    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 md:p-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#FFDDAF]/30 flex items-center justify-center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-lg">Riwayat Peminjaman</h2>
                    <p class="text-xs text-gray-400">Riwayat peminjaman bukumu</p>
                </div>
            </div>
        </div>

        {{-- Filter pills --}}
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach([
                ['key'=>'all','label'=>'Semua'],
                ['key'=>'pending','label'=>'Pengajuan'],
                ['key'=>'on_loan','label'=>'Dipinjam'],
                ['key'=>'overdue','label'=>'Terlambat'],
                ['key'=>'returned','label'=>'Dikembalikan'],
                ['key'=>'rejected','label'=>'Ditolak'],
            ] as $i => $f)
            <button data-tx-filter="{{ $f['key'] }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs border-[1.5px] transition-all duration-200 cursor-pointer
                           {{ $i===0 ? 'bg-[#FFDDAF] border-[#444] text-[#444] font-bold' : 'bg-white border-gray-200 text-gray-400 font-medium hover:border-gray-400' }}">
                {{ $f['label'] }}
                <span data-tx-count="{{ $f['key'] }}" class="bg-white/60 px-1.5 py-0.5 rounded-full text-[10px] font-bold">0</span>
            </button>
            @endforeach
        </div>

        <div id="tx-list" class="space-y-3"></div>
    </div>
</div>
