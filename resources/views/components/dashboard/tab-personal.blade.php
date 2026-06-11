<div data-tab-panel="personal">
    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 md:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-[#FFDDAF]/30 flex items-center justify-center">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <h2 class="font-bold text-lg">Informasi Pribadi</h2>
                <p class="text-xs text-gray-400">Perbarui data profilmu</p>
            </div>
        </div>

        <form id="profile-form" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="prof-nama" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Nama Lengkap</label>
                    <input type="text" id="prof-nama" name="nama"
                           class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" />
                </div>
                <div>
                    <label for="prof-email" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Surel</label>
                    <input type="email" id="prof-email" disabled
                           class="w-full border-[1.5px] border-gray-100 rounded-xl px-4 py-3 text-sm bg-gray-50 text-gray-400 cursor-not-allowed" />
                </div>
                <div>
                    <label for="prof-kota" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Kota</label>
                    <input type="text" id="prof-kota" name="kota"
                           class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" />
                </div>
                <div>
                    <label for="prof-telp" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">No. Telepon</label>
                    <input type="tel" id="prof-telp" name="no_telp"
                           class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Genre Favorit <span class="font-normal text-gray-300">(maks. 5)</span></label>
                <div id="genre-picker" class="flex flex-wrap gap-2"></div>
            </div>

            <div class="pt-2">
                <button type="submit" class="bg-[#FFDDAF] text-[#444] font-bold text-sm px-8 py-3 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
