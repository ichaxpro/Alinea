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

        <a href="{{ route('timeline_komunitas') }}"
           class="flex flex-col items-center gap-0.5 w-16 py-1 no-underline {{ $active === 'komunitas' ? 'text-[#444]' : 'text-gray-400' }}">
            <x-community class="w-5 h-5" />
            <span class="text-[11px] {{ $active === 'komunitas' ? 'font-semibold' : 'font-medium' }}">Komunitas</span>
        </a>

        <a href="{{ route('timeline_notifikasi') }}"
           class="flex flex-col items-center gap-0.5 w-16 py-1 no-underline {{ $active === 'notifikasi' ? 'text-[#444]' : 'text-gray-400' }}">
            <div class="relative">
                <x-notifikasi class="w-5 h-5" />
                @if(auth()->check())
                    @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                    @if($unreadCount > 0)
                        <span class="absolute -top-1 -right-2 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full border border-white leading-none">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    @endif
                @endif
            </div>
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
