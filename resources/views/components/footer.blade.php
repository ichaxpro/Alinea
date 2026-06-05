<footer id="tentang" class="bg-text text-gray-400 py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12 mb-12">
            <!-- Logo & Brand -->
            <div class="col-span-1 sm:col-span-2 lg:col-span-2 pr-0 lg:pr-10">
                <div class="flex items-center gap-2 mb-4">
                    <img src="{{ asset('images/Alinea_footer.svg') }}" alt="Alinea">
                </div>
                <p class="text-sm text-white opacity-50 leading-relaxed mb-5 max-w-xs">
                    Platform komunitas buku pertama dari dan untuk pembaca Indonesia. Pinjam, Baca, Bagikan.
                </p>
            </div>

            <!-- Fitur -->
            <div class="pt-2 lg:pt-5 lg:pl-8">
                <h3 class="text-white font-bold text-sm mb-6 uppercase tracking-wider">Tautan Cepat</h3>
                <ul class="space-y-4 text-sm">
                    <li><a href="{{ route('beranda') }}" class="hover:text-white transition-colors duration-200">Beranda</a></li>
                    <li><a href="{{ route('timeline_home') }}" class="hover:text-white transition-colors duration-200">Lini Masa</a></li>
                    <li><a href="{{ route('klub') }}" class="hover:text-white transition-colors duration-200">Klub</a></li>
                    <li><a href="{{ route('katalog') }}" class="hover:text-white transition-colors duration-200">Katalog Buku</a></li>
                </ul>
            </div>

            <!-- Quick Contact -->
            <div class="pt-2 lg:pt-5">
                <h3 class="text-white font-bold text-sm mb-6 uppercase tracking-wider">Hubungi Kami</h3>
                <ul class="space-y-4 text-sm">
                    <li><a href="mailto:halo@alinea.id" class="hover:text-white transition-colors duration-200">halo@alinea.id</a></li>
                    <li><a href="tel:+62212345678" class="hover:text-white transition-colors duration-200">+62 21 2345 6789</a></li>
                    <li><span class="text-gray-500">Malang, Indonesia</span></li>
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
