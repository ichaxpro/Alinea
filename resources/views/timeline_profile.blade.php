<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — Timeline Profile</title>
    <meta name="description" content="Ikuti timeline buku Alinea — bagikan progres bacaan, ulasan, dan kutipan favoritmu." />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/timeline.js'])
</head>

<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">

    {{-- ========== NAVBAR (fixed, hides when scrolled away from top) ========== --}}
    <nav id="main-navbar"
         class="fixed inset-x-0 top-0 z-50 h-14 bg-white flex items-center border-b-2 border-black px-6 lg:px-10 transition-transform duration-300">
        <div class="flex items-center justify-between w-full max-w-[1280px] mx-auto">

            {{-- Logo --}}
            <a href="/" class="flex-shrink-0" aria-label="Alinea — Halaman Utama">
                <img src="{{ asset('images/alinealogo.svg') }}" alt="Alinea" class="h-8 w-auto"/>
            </a>

            {{-- Desktop nav links --}}
            <ul class="hidden md:flex items-center gap-7 list-none">
                @foreach ([
                    ['/', 'Beranda'],
                    ['/pinjam', 'Pinjam'],
                    ['/komunitas', 'Komunitas'],
                    ['/klub', 'Klub'],
                    ['/ulasan', 'Ulasan'],
                ] as [$href, $label])
                <li>
                    <a href="{{ $href }}"
                       class="relative text-gray-600 text-sm font-medium hover:text-gray-900 transition-colors
                              after:absolute after:bottom-[-3px] after:left-0 after:w-0 after:h-[2px]
                              after:bg-accent after:transition-all hover:after:w-full">
                        {{ $label }}
                    </a>
                </li>
                @endforeach
            </ul>

            {{-- Action buttons --}}
            <div class="flex items-center gap-3">
                <button id="navbar-search-btn" aria-label="Cari"
                        class="w-9 h-9 rounded-full border-2 border-text flex items-center justify-center text-text hover:bg-white/10 transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>

                <a href="/login" id="masuk-btn"
                   class="bg-accent text-text font-bold text-sm px-5 py-2 rounded-full border-2 border-text hover:opacity-90 transition-opacity">
                    Masuk
                </a>
            </div>
        </div>
    </nav>

    {{-- ========== PAGE LAYOUT (3-column: left | center | right) ========== --}}
    <div class="min-h-screen pt-14">
        <div class="flex items-start gap-6 max-w-[1200px] mx-auto px-4 py-6">

            {{-- ===== LEFT SIDEBAR — floating sticky card ===== --}}
            <aside class="hidden lg:block w-[200px] flex-shrink-0 sticky top-6">
                <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-4 flex flex-col gap-1">
                    @php
                    $sideNav = [
                        ['id' => 'sidenav-beranda',    'label' => 'Beranda',    'active' => false,
                         'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
                        ['id' => 'sidenav-profil',     'label' => 'Profil',     'active' => true,
                         'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
                        ['id' => 'sidenav-notifikasi', 'label' => 'Notifikasi', 'active' => false,
                         'icon' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>'],
                        ['id' => 'sidenav-pesan',      'label' => 'Pesan',      'active' => false,
                         'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><circle cx="9" cy="10" r="1" fill="currentColor"/><circle cx="12" cy="10" r="1" fill="currentColor"/><circle cx="15" cy="10" r="1" fill="currentColor"/>'],
                    ];
                    @endphp

                    @foreach ($sideNav as $item)
                    <button id="{{ $item['id'] }}" data-sidenav aria-label="{{ $item['label'] }}"
                            class="flex items-center gap-3 w-full px-3 py-3 rounded-xl text-left transition-colors cursor-pointer
                                   {{ $item['active'] ? 'bg-[#FFDDAF] text-[#444] font-semibold' : 'text-gray-500 hover:bg-gray-100' }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0">
                            {!! $item['icon'] !!}
                        </svg>
                        <span class="text-sm">{{ $item['label'] }}</span>
                    </button>
                    @endforeach
                </div>
            </aside>

            {{-- ===== CENTER — FEED COLUMN ===== --}}
            <main class="flex-1 min-w-0 flex flex-col gap-4">

                

                {{-- Composer --}}
                <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6">
                    <div class="flex gap-6 items-start">
                        <div class="w-28 h-28 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0"></div>

                        <div class="flex-1">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <h2 class="text-2xl font-bold text-[#222]">Dewi Chalissa</h2>
                                        <p class="text-sm text-gray-500">@oioioi</p>
                                    </div>
                                </div>

                                <p class="mt-4 text-lg text-[#333]">Apaan Nih?!</p>
                                <p class="text-sm text-gray-500">
                                    <span class="font-bold text-[#222]">256</span> Following
                                    <span class="mx-2">|</span>
                                    <span class="font-bold text-[#222]">165</span> Followers
                                </p>

                                
                            </div>
                        </div>
                    </div>
                </article>
            </main>

            {{-- ===== RIGHT SIDEBAR — floating sticky card (mirrors left) ===== --}}
            <aside class="hidden xl:flex flex-col gap-4 w-[280px] flex-shrink-0 sticky top-6">

                {{-- Search --}}
                <div class="bg-white border-[1.5px] border-[#444] rounded-2xl px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="search" id="sidebar-search-input" placeholder="Cari buku atau pengguna..."
                               class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" />
                    </div>
                </div>

                {{-- What's Trending --}}
                <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5">
                    <h2 class="font-bold text-[15px] mb-4">What's Trending</h2>

                    @php
                    $trending = [
                        ['Harry Potter',          'J.K. Rowling'],
                        ['Toko Kelontong Namiya', 'Keigo Higashino'],
                        ['Crime & Punishment',    'Fyodor Dostoyevsky'],
                        ['The Silent Voice',      'Naoko Yamada'],
                        ['Your Name',             'Makoto Shinkai'],
                    ];
                    @endphp

                    <ol class="flex flex-col gap-3.5">
                        @foreach ($trending as $rank => $book)
                        <li class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                            <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">{{ $rank + 1 }}</span>
                            <div>
                                <span class="font-bold text-[13px] leading-tight block">{{ $book[0] }}</span>
                                <span class="text-[11px] text-gray-400">{{ $book[1] }}</span>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </aside>

        </div>
    </div>

    {{-- ========== BACK TO TOP ========== --}}
    <button id="back-to-top" aria-label="Kembali ke atas"
            class="fixed bottom-7 right-7 z-50 w-12 h-12 rounded-full bg-[#444] text-white
                   flex items-center justify-center border-2 border-[#FFDDAF]
                   opacity-0 pointer-events-none translate-y-4
                   transition-all duration-300
                   hover:bg-[#FFDDAF] hover:text-[#444] hover:border-[#444] cursor-pointer">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"/>
        </svg>
    </button>

</body>
</html>