<footer id="tentang" class="bg-text text-gray-400 py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-10 mb-12">
            <!-- Logo & Brand -->
            <div class="col-span-2 lg:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <img src="{{ asset('images/Alinea_footer.svg') }}" alt="Alinea">
                </div>
                <p class="text-sm text-white opacity-50 leading-relaxed mb-5 max-w-xs">
                    Platform komunitas buku pertama dari dan untuk pembaca Indonesia. Pinjam, Baca, Bagikan.
                </p>
            </div>

            <!-- Fitur -->
            <div class="lg:pl-[60px] pt-5">
                <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Fitur</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Pinjam Buku</a></li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Timeline</a></li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Ulasan Buku</a></li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Book Club</a></li>
                </ul>
            </div>

            <!-- Informasi -->
            <div class="pt-5 lg:pl-8">
                <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Informasi</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Tentang Kami</a></li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Blog</a></li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Karir</a></li>
                    <li><a href="#" class="hover:text-white transition-colors duration-200">Bantuan</a></li>
                </ul>
            </div>

            <!-- Quick Contact -->
            <div class="pt-5">
                <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Quick Contact</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="mailto:halo@alinea.id" class="hover:text-white transition-colors duration-200">halo@alinea.id</a></li>
                    <li><a href="tel:+62212345678" class="hover:text-white transition-colors duration-200">+62 21 2345 6789</a></li>
                    <li><span class="text-gray-500">Jakarta, Indonesia</span></li>
                </ul>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-800 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p class="text-xs text-white opacity-50">© {{ date('Y') }} Alinea. All rights reserved.</p>
            <div class="flex gap-6 text-xs">
                <a href="#" class="hover:text-white transition-colors duration-200">Syarat & Ketentuan</a>
                <a href="#" class="hover:text-white transition-colors duration-200">Privasi</a>
            </div>
        </div>
    </div>
</footer>
