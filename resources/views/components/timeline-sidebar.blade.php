{{-- ===== LEFT SIDEBAR — floating sticky card ===== --}}
<aside class="hidden lg:block w-50 shrink-0 sticky top-6">
    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-4 flex flex-col gap-1">
        @php
        $sideNav = [
            ['id' => 'sidenav-beranda',    'label' => 'Beranda',    'active' => request()->routeIs('timeline_home'),
             'icon' => 'beranda', 'url' => route('timeline_home')],
            ['id' => 'sidenav-profil',     'label' => 'Profil',     'active' => request()->routeIs('timeline_profile'),
             'icon' => 'profil', 'url' => route('timeline_profile')],
            ['id' => 'sidenav-notifikasi', 'label' => 'Notifikasi', 'active' => request()->routeIs('timeline_notifikasi'),
             'icon' => 'notifikasi', 'url' => route('timeline_notifikasi')],
            ['id' => 'sidenav-pesan',      'label' => 'Pesan',      'active' => request()->routeIs('chat'),
             'icon' => 'pesan', 'url' => route('chat')],
            ['id' => 'sidenav-komunitas', 'label' => 'Komunitas', 'active' => request()->routeIs('timeline_komunitas'), 'icon' => 'community', 'url' => route('timeline_komunitas')],
            ['id' => 'sidenav-simpanan', 'label' => 'Simpanan', 'active' => request()->routeIs('timeline_simpanan'), 'icon' => 'simpanan', 'url' => route('timeline_simpanan')]
        ];
        @endphp

        @foreach ($sideNav as $item)
        @php $tag = isset($item['url']) ? 'a' : 'button'; @endphp
        <{{ $tag }} id="{{ $item['id'] }}" {!! isset($item['url']) ? 'href="'.$item['url'].'"' : 'data-sidenav' !!} aria-label="{{ $item['label'] }}"
                class="flex items-center gap-3 w-full px-3 py-3 rounded-xl text-left transition-colors cursor-pointer
                       {{ $item['active'] ? 'bg-[#FFDDAF] text-[#444] font-semibold' : 'text-gray-500 hover:bg-gray-100' }}">
            <div class="w-5 h-5 shrink-0 flex items-center justify-center">
                <x-dynamic-component :component="$item['icon']" class="w-full h-full" />
            </div>

            <span class="text-sm">{{ $item['label'] }}</span>
            @if($item['id'] === 'sidenav-notifikasi' && auth()->check())
                @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                @if($unreadCount > 0)
                    <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @endif
            @endif
        </{{ $tag }}>
        @endforeach
    </div>
</aside>
