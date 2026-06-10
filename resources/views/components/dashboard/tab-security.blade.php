<div data-tab-panel="security" class="hidden">
    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 md:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-[#FFDDAF]/30 flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <div>
                <h2 class="font-bold text-lg">Keamanan</h2>
                <p class="text-xs text-gray-400">Ubah kata sandi akunmu</p>
            </div>
        </div>

        <form id="security-form" class="max-w-md space-y-5">
            @foreach([
                ['id'=>'pw-current','label'=>'Kata Sandi Saat Ini','placeholder'=>'Masukkan kata sandi saat ini'],
                ['id'=>'pw-new','label'=>'Kata Sandi Baru','placeholder'=>'Minimal 8 karakter'],
                ['id'=>'pw-confirm','label'=>'Konfirmasi Kata Sandi Baru','placeholder'=>'Ulangi kata sandi baru'],
            ] as $field)
            <div>
                <label for="{{ $field['id'] }}" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">{{ $field['label'] }}</label>
                <div class="relative">
                    <input type="password" id="{{ $field['id'] }}" placeholder="{{ $field['placeholder'] }}"
                           class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 pr-12 text-sm outline-none focus:border-[#444] transition-colors" />
                    <button type="button" data-toggle-pw="{{ $field['id'] }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 transition-colors">
                        <span class="eye-open"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></span>
                        <span class="eye-closed hidden"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg></span>
                    </button>
                </div>
            </div>
            @endforeach

            <div class="bg-[#D4F6FF]/40 border-[1.5px] border-[#C7E7FF] rounded-xl p-4">
                <p class="text-xs font-bold text-[#444] mb-2">Syarat Kata Sandi:</p>
                <ul class="text-xs text-gray-500 space-y-1">
                    <li class="flex items-center gap-2"><span class="text-gray-300">○</span> Minimal 8 karakter</li>
                    <li class="flex items-center gap-2"><span class="text-gray-300">○</span> Kombinasi huruf dan angka direkomendasikan</li>
                </ul>
            </div>

            <div class="pt-2">
                <button type="submit" class="bg-[#FFDDAF] text-[#444] font-bold text-sm px-8 py-3 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors cursor-pointer">
                    Ubah Password
                </button>
            </div>
        </form>
    </div>
</div>
