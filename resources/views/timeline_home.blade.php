<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — Timeline</title>
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
                        ['id' => 'sidenav-beranda',    'label' => 'Beranda',    'active' => true,
                         'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
                        ['id' => 'sidenav-profil',     'label' => 'Profil',     'active' => false,
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

                {{-- Tab switcher (sticky with bg mask so posts slide behind it) --}}
                <div class="sticky top-0 z-30 -mt-6 pt-6 pb-2 mb-1 bg-gray-100">
                    <div class="flex bg-white border-[1.5px] border-[#444] rounded-full overflow-hidden"
                         role="tablist" aria-label="Pilih umpan">
                        <button data-tab-btn role="tab" id="tab-for-you" aria-selected="true" aria-controls="feed-panel"
                                class="flex-1 py-2.5 text-sm font-bold text-[#444] bg-[#FFDDAF] rounded-full transition-colors cursor-pointer">
                            For You
                        </button>
                        <button data-tab-btn role="tab" id="tab-following" aria-selected="false" aria-controls="feed-panel"
                                class="flex-1 py-2.5 text-sm text-gray-400 rounded-full transition-colors cursor-pointer hover:bg-gray-50">
                            Following
                        </button>
                    </div>
                </div>

                {{-- Composer --}}
                <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5">
                    <div class="flex gap-3">
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0"></div>

                        <div class="flex-1 flex flex-col gap-3">
                            {{-- Category pills --}}
                            <div class="flex flex-wrap gap-2">
                                @foreach (['Dibaca', 'Selesai', 'Kutipan', 'Ulasan', 'Dll'] as $i => $tag)
                                <button data-composer-tag id="tag-{{ Str::lower($tag) }}"
                                        class="text-xs font-medium px-4 py-1 rounded-full border-[1.5px] transition-colors cursor-pointer
                                               {{ $i === 0
                                                   ? 'border-[#444] bg-[#FFDDAF] text-[#444]'
                                                   : 'border-gray-300 text-gray-500 hover:border-[#444] hover:text-[#444]' }}">
                                    {{ $tag }}
                                </button>
                                @endforeach
                            </div>

                            <input type="text" id="composer-title" placeholder="Judul buku (opsional)..." maxlength="120"
                                   class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm placeholder-gray-300 outline-none focus:border-[#444] transition-colors" />

                            <textarea id="composer-body" data-autogrow placeholder="Apa yang sedang kamu baca? Bagikan pikiranmu..." rows="3"
                                      class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-[#444] resize-none transition-colors overflow-hidden"></textarea>

                            {{-- Footer: media icons | char counter | submit --}}
                            <div class="flex items-center justify-between">
                                {{-- Media upload icons --}}
                                <div class="flex items-center gap-2">
                                    <button type="button" aria-label="Unggah gambar" title="Unggah gambar"
                                            class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    </button>
                                    <button type="button" aria-label="Unggah video" title="Unggah video"
                                            class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                                        </svg>
                                    </button>
                                    <button type="button" aria-label="Lampirkan file" title="Lampirkan file"
                                            class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Char counter + submit --}}
                                <div class="flex items-center gap-3">
                                    <span id="char-counter" data-char-counter class="text-xs text-gray-300">0/250</span>
                                    <button id="kirim-btn"
                                            class="bg-[#FFDDAF] text-[#444] font-bold text-sm px-6 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors cursor-pointer">
                                        Kirim
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                {{-- Post feed --}}
                <div id="feed-panel" class="flex flex-col gap-4" role="tabpanel" aria-labelledby="tab-for-you">
                    @php
                    $posts = [
                        [
                            'id' => 1, 'name' => 'Budi Ashcroft', 'handle' => '@isoba__',
                            'location' => 'Malang', 'time' => '12 Menit Lalu', 'book' => 'Harry Potter',
                            'body' => 'Harry Potter Adalah Kisah Tentang Seorang Anak Penyihir Yang Menemukan Jati Dirinya Di Sekolah Sihir Hogwarts. Ia Belajar Tentang Persahabatan, Keberanian, Dan Pengorbanan Bersama Teman-Temannya Seperti Ron Dan Hermione. Cerita Ini Juga Menampilkan Pertarungan Antara Kebaikan Dan Kejahatan Melalui Sosok Voldemort, Dengan Dunia Magis Yang Kaya Dan Penuh Imajinasi.',
                            'comments' => '1.2K', 'likes_base' => 50000, 'likes_label' => '50K',
                            'liked' => true, 'avatar_from' => '#FFDDAF', 'avatar_to' => '#C7E7FF',
                        ],
                        [
                            'id' => 2, 'name' => 'Dina Rahmawati', 'handle' => '@dina_r',
                            'location' => 'Surabaya', 'time' => '35 Menit Lalu', 'book' => 'The Midnight Library',
                            'body' => 'Baru sampai di halaman 67% dan plot twist-nya benar-benar di luar ekspektasi. Matt Haig dengan apiknya menggambarkan bagaimana setiap pilihan hidup membawa kita ke jalur yang berbeda. Sangat direkomendasikan untuk yang sedang merasa stuck dalam hidup!',
                            'comments' => '843', 'likes_base' => 28000, 'likes_label' => '28K',
                            'liked' => false, 'avatar_from' => '#C7E7FF', 'avatar_to' => '#FFDDAF',
                        ],
                        [
                            'id' => 3, 'name' => 'Ahmad Fauzan', 'handle' => '@afauzan_',
                            'location' => 'Bandung', 'time' => '2 Jam Lalu', 'book' => 'Atomic Habits',
                            'body' => '"Setiap tindakan yang kamu ambil adalah suara untuk tipe orang yang ingin kamu jadi." — James Clear. Kutipan ini benar-benar mengubah cara pandangku tentang kebiasaan kecil. Sangat recommended untuk yang ingin membangun rutinitas produktif!',
                            'comments' => '2.1K', 'likes_base' => 41000, 'likes_label' => '41K',
                            'liked' => false, 'avatar_from' => '#D4F6FF', 'avatar_to' => '#FFDDAF',
                        ],
                        [
                            'id' => 4, 'name' => 'Reza Mahendra', 'handle' => '@reza_m',
                            'location' => 'Jakarta', 'time' => '4 Jam Lalu', 'book' => 'Sapiens',
                            'body' => 'Habis nonton dokumenter sejarah langsung lari ke buku Sapiens. Yuval Noah Harari benar-benar jago merangkum sejarah manusia dalam narasi yang segar dan mudah dicerna. Ini buku ketiga kalinya saya baca ulang!',
                            'comments' => '512', 'likes_base' => 19000, 'likes_label' => '19K',
                            'liked' => false, 'avatar_from' => '#FFDDAF', 'avatar_to' => '#D4F6FF',
                        ],
                    ];
                    @endphp

                    @foreach ($posts as $post)
                    <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5 hover:bg-gray-50 transition-colors">

                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0"
                                 style="background: linear-gradient(135deg, {{ $post['avatar_from'] }}, {{ $post['avatar_to'] }})"></div>
                            <div>
                                <span class="font-bold text-[15px] leading-tight">{{ $post['name'] }}</span>
                                <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                    <span>{{ $post['handle'] }}</span>
                                    <span class="text-gray-200">•</span>
                                    <span class="flex items-center gap-1">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        {{ $post['location'] }}
                                    </span>
                                    <span class="text-gray-200">•</span>
                                    <span>{{ $post['time'] }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Book tag --}}
                        <div class="inline-flex items-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold mb-3">
                            {{ $post['book'] }}
                        </div>

                        {{-- Body --}}
                        <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $post['body'] }}</p>

                        {{-- Actions --}}
                        <div class="flex items-center gap-5 pt-3 border-t border-gray-100">
                            {{-- Comment --}}
                            <button id="comment-btn-{{ $post['id'] }}" aria-label="Komentar"
                                    class="flex items-center gap-1.5 text-gray-400 text-[13px] font-medium hover:text-[#444] transition-colors cursor-pointer">
                                <x-icon-comment fill="none" />
                                <span>{{ $post['comments'] }}</span>
                            </button>

                            {{-- Like --}}
                            <button id="like-btn-{{ $post['id'] }}" data-like-btn
                                    data-base="{{ $post['likes_base'] }}" data-liked="{{ $post['liked'] ? 'true' : 'false' }}"
                                    aria-pressed="{{ $post['liked'] ? 'true' : 'false' }}" aria-label="Suka"
                                    class="flex items-center gap-1.5 text-[13px] font-medium transition-colors cursor-pointer
                                           {{ $post['liked'] ? 'text-red-500' : 'text-gray-400 hover:text-red-400' }}">
                                <x-icon-like fill="{{ $post['liked'] ? 'currentColor' : 'none' }}" />
                                <span data-like-count>{{ $post['likes_label'] }}</span>
                            </button>

                            {{-- Bookmark & Share --}}
                            <div class="ml-auto flex items-center gap-2">
                                <button id="bookmark-btn-{{ $post['id'] }}" data-bookmark-btn aria-pressed="false" aria-label="Simpan"
                                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">
                                    <x-icon-bookmark fill="none" />
                                </button>
                                <button id="share-btn-{{ $post['id'] }}" data-share-btn aria-label="Bagikan"
                                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">
                                    <x-icon-share fill="none" />
                                </button>
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>
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
