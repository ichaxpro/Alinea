@props(['active' => 'beranda'])

<nav id="mobile-bottom-nav"
     class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 md:hidden"
     aria-label="Navigasi mobile">
    <div class="flex items-center justify-around h-16 max-w-lg mx-auto px-2">
        <a href="{{ route('timeline_home') }}"
           class="flex flex-col items-center gap-0.5 w-16 py-1 no-underline {{ $active === 'beranda' ? 'text-[#444]' : 'text-gray-400' }}">
            <x-beranda class="w-5 h-5" />
            <span class="text-[11px] {{ $active === 'beranda' ? 'font-semibold' : 'font-medium' }}">Beranda</span>
        </a>

        <button id="mobile-search-trigger"
                class="flex flex-col items-center gap-0.5 w-16 py-1 text-gray-400 cursor-pointer"
                aria-label="Cari">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <span class="text-[11px] font-medium">Cari</span>
        </button>

        <a href="{{ route('timeline_notifikasi') }}"
           class="flex flex-col items-center gap-0.5 w-16 py-1 no-underline {{ $active === 'notifikasi' ? 'text-[#444]' : 'text-gray-400' }}">
            <x-notifikasi class="w-5 h-5" />
            <span class="text-[11px] {{ $active === 'notifikasi' ? 'font-semibold' : 'font-medium' }}">Notifikasi</span>
        </a>

        <a href="{{ route('chat') }}"
           class="flex flex-col items-center gap-0.5 w-16 py-1 no-underline {{ $active === 'pesan' ? 'text-[#444]' : 'text-gray-400' }}">
            <x-pesan class="w-5 h-5" />
            <span class="text-[11px] {{ $active === 'pesan' ? 'font-semibold' : 'font-medium' }}">Pesan</span>
        </a>

        <a href="{{ route('timeline_profile') }}"
           class="flex flex-col items-center gap-0.5 w-16 py-1 no-underline {{ $active === 'profil' ? 'text-[#444]' : 'text-gray-400' }}">
            <x-profil class="w-5 h-5" />
            <span class="text-[11px] {{ $active === 'profil' ? 'font-semibold' : 'font-medium' }}">Profil</span>
        </a>
    </div>
</nav>
