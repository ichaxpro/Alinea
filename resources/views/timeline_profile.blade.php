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
    <x-navbar></x-navbar>

    {{-- ========== PAGE LAYOUT (3-column: left | center | right) ========== --}}
    <div class="min-h-screen pt-14">
        <div class="flex items-start gap-6 max-w-[1200px] mx-auto px-4 py-6">

            {{-- ===== LEFT SIDEBAR — floating sticky card ===== --}}
            <aside class="hidden lg:block w-[200px] flex-shrink-0 sticky top-6">
                <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-4 flex flex-col gap-1">
                    @php
                    $sideNav = [
                        ['id' => 'sidenav-beranda',    'label' => 'Beranda',    'active' => false,
                         'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>', 'url' => route('timeline_home')],
                        ['id' => 'sidenav-profil',     'label' => 'Profil',     'active' => true,
                         'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>', 'url' => route('timeline_profile')],
                        ['id' => 'sidenav-notifikasi', 'label' => 'Notifikasi', 'active' => false,
                         'icon' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>', 'url' => route('timeline_notifikasi')],
                        ['id' => 'sidenav-pesan',      'label' => 'Chat',      'active' => false,
                         'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><circle cx="9" cy="10" r="1" fill="currentColor"/><circle cx="12" cy="10" r="1" fill="currentColor"/><circle cx="15" cy="10" r="1" fill="currentColor"/>', 'url' => route('chat')],
                        ['id' => 'sidenav-komunitas', 'label' => 'Komunitas', 'active' => false, 
                         'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>', 'url' => route('timeline_komunitas')],
                    ];
                    @endphp

                    @foreach ($sideNav as $item)
                    @php $tag = isset($item['url']) ? 'a' : 'button'; @endphp
                    <{{ $tag }} id="{{ $item['id'] }}" {!! isset($item['url']) ? 'href="'.$item['url'].'"' : 'data-sidenav' !!} aria-label="{{ $item['label'] }}"
                            class="flex items-center gap-3 w-full px-3 py-3 rounded-xl text-left transition-colors cursor-pointer
                                   {{ $item['active'] ? 'bg-[#FFDDAF] text-[#444] font-semibold' : 'text-gray-500 hover:bg-gray-100' }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0">
                            {!! $item['icon'] !!}
                        </svg>
                        <span class="text-sm">{{ $item['label'] }}</span>
                    </{{ $tag }}>
                    @endforeach
                </div>
            </aside>

            {{-- ===== CENTER — FEED COLUMN ===== --}}
            <main class="flex-1 min-w-0 flex flex-col gap-4">

                

                {{-- Composer --}}
                <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6">
                    <div class="flex gap-6 items-start">
                        <div class="w-28 h-28 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0 overflow-hidden flex items-center justify-center">
                            @if($user->foto_profil ?? false)
                            <img src="{{ Storage::disk('public')->url($user->foto_profil) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                            <span class="text-4xl font-black text-text/60">D</span>
                            @endif
                        </div>

                        <div class="flex-1">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <h2 class="text-2xl font-bold text-[#222]">Dewi Chalissa</h2>
                                        <p class="text-sm text-gray-500">@oioioi</p>
                                    </div>
                                </div>

                                <p class="mt-4 text-base text-[#333]">Apaan Nih?!</p>
                                <p class="text-sm text-gray-500">
                                    <span class="font-bold text-[#222]">256</span> Following
                                    <span class="mx-2">|</span>
                                    <span class="font-bold text-[#222]">165</span> Followers
                                </p>

                            </div>
                        </div>
                    </div>

                    <div class="mt-8 border-b border-gray-200">
                        <div class="overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                            <div class="flex min-w-[560px] w-full">
                                @foreach ([
                                    ['label' => 'Unggahan', 'active' => true],
                                    ['label' => 'Penghargaan', 'active' => false],
                                    ['label' => 'Riwayat', 'active' => false],
                                    ['label' => 'Media', 'active' => false],
                                ] as $tab)
                                <button type="button"
                                        data-profile-tab
                                        data-profile-tab-target="{{ strtolower($tab['label']) }}"
                                        class="relative flex-1 px-1 pb-4 text-sm font-semibold transition-colors cursor-pointer text-center {{ $tab['active'] ? 'text-[#111]' : 'text-gray-400 hover:text-gray-600' }}"
                                        aria-selected="{{ $tab['active'] ? 'true' : 'false' }}">
                                    {{ $tab['label'] }}
                                    <span data-profile-tab-indicator
                                          class="absolute left-1/2 -translate-x-1/2 -bottom-[1px] h-1 w-24 rounded-full bg-[#5DA9FF] {{ $tab['active'] ? '' : 'hidden' }}"></span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div id="profile-feed-panel" data-profile-panel="unggahan" class="mt-5 flex flex-col gap-5" role="tabpanel" aria-labelledby="tab-for-you">
                        @php
                        $profilePosts = [
                            [
                                'id' => 1, 'name' => 'Dewi Chalissa', 'handle' => '@oioioi',
                                'location' => 'Malang', 'time' => '12 Menit Lalu', 'book' => 'Harry Potter',
                                'tag' => 'Dibaca',
                                'body' => 'Harry Potter Adalah Kisah Tentang Seorang Anak Penyihir Yang Menemukan Jati Dirinya Di Sekolah Sihir Hogwarts. Ia Belajar Tentang Persahabatan, Keberanian, Dan Pengorbanan Bersama Teman-Temannya Seperti Ron Dan Hermione. Cerita Ini Juga Menampilkan Pertarungan Antara Kebaikan Dan Kejahatan Melalui Sosok Voldemort, Dengan Dunia Magis Yang Kaya Dan Penuh Imajimasi.',
                                'comments' => '1.2K', 'likes_base' => 50000, 'likes_label' => '50K',
                                'liked' => true, 'avatar_from' => '#FFDDAF', 'avatar_to' => '#C7E7FF',
                            ],
                            [
                                'id' => 2, 'name' => 'Dewi Chalissa', 'handle' => '@oioioi',
                                'location' => 'Surabaya', 'time' => '35 Menit Lalu', 'book' => 'The Midnight Library',
                                'tag' => 'Selesai',
                                'body' => 'Baru sampai di halaman 67% dan plot twist-nya benar-benar di luar ekspektasi. Matt Haig dengan apiknya menggambarkan bagaimana setiap pilihan hidup membawa kita ke jalur yang berbeda. Sangat direkomendasikan untuk yang sedang merasa stuck dalam hidup!',
                                'comments' => '843', 'likes_base' => 28000, 'likes_label' => '28K',
                                'liked' => false, 'avatar_from' => '#C7E7FF', 'avatar_to' => '#FFDDAF',
                            ],
                            [
                                'id' => 3, 'name' => 'Dewi Chalissa', 'handle' => '@oioioi',
                                'location' => 'Bandung', 'time' => '2 Jam Lalu', 'book' => 'Atomic Habits',
                                'tag' => 'Kutipan',
                                'body' => '"Setiap tindakan yang kamu ambil adalah suara untuk tipe orang yang ingin kamu jadi." — James Clear. Kutipan ini benar-benar mengubah cara pandangku tentang kebiasaan kecil. Sangat recommended untuk yang ingin membangun rutinitas produktif!',
                                'comments' => '2.1K', 'likes_base' => 41000, 'likes_label' => '41K',
                                'liked' => false, 'avatar_from' => '#D4F6FF', 'avatar_to' => '#FFDDAF',
                            ],
                            [
                                'id' => 4, 'name' => 'Dewi Chalissa', 'handle' => '@oioioi',
                                'location' => 'Jakarta', 'time' => '4 Jam Lalu', 'book' => 'Sapiens',
                                'tag' => 'Dibaca',
                                'body' => 'Habis nonton dokumenter sejarah langsung lari ke buku Sapiens. Yuval Noah Harari benar-benar jago merangkum sejarah manusia dalam narasi yang segar dan mudah dicerna. Ini buku ketiga kalinya saya baca ulang!',
                                'comments' => '512', 'likes_base' => 19000, 'likes_label' => '19K',
                                'liked' => false, 'avatar_from' => '#FFDDAF', 'avatar_to' => '#D4F6FF',
                            ],
                        ];
                        @endphp

                        @foreach ($profilePosts as $post)
                        <article class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0"
                                     style="background: linear-gradient(135deg, {{ $post['avatar_from'] }}, {{ $post['avatar_to'] }})"></div>
                                <div class="flex-1 min-w-0">
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
                                <div class="bg-[#fff176] border-2 border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold flex-shrink-0">
                                    {{ $post['tag'] }}
                                </div>
                            </div>

                            <div class="inline-flex items-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold mb-3">
                                {{ $post['book'] }}
                            </div>

                            <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $post['body'] }}</p>

                            <div class="flex items-center gap-5 pt-3 border-t border-gray-100">
                                <button id="comment-btn-profile-{{ $post['id'] }}" aria-label="Komentar"
                                        class="flex items-center gap-1.5 text-gray-400 text-[13px] font-medium hover:text-[#444] transition-colors cursor-pointer">
                                    <x-icon-comment fill="none" />
                                    <span>{{ $post['comments'] }}</span>
                                </button>

                                <button id="like-btn-profile-{{ $post['id'] }}" data-like-btn
                                        data-base="{{ $post['likes_base'] }}" data-liked="{{ $post['liked'] ? 'true' : 'false' }}"
                                        aria-pressed="{{ $post['liked'] ? 'true' : 'false' }}" aria-label="Suka"
                                        class="flex items-center gap-1.5 text-[13px] font-medium transition-colors cursor-pointer
                                               {{ $post['liked'] ? 'text-red-500' : 'text-gray-400 hover:text-red-400' }}">
                                    <x-icon-like fill="{{ $post['liked'] ? 'currentColor' : 'none' }}" />
                                    <span data-like-count>{{ $post['likes_label'] }}</span>
                                </button>

                                <div class="ml-auto flex items-center gap-2">
                                    <button id="bookmark-btn-profile-{{ $post['id'] }}" data-bookmark-btn aria-pressed="false" aria-label="Simpan"
                                            class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">
                                        <x-icon-bookmark fill="none" />
                                    </button>
                                    <button id="share-btn-profile-{{ $post['id'] }}" data-share-btn aria-label="Bagikan"
                                            class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">
                                        <x-icon-share fill="none" />
                                    </button>
                                </div>
                            </div>
                        </article>
                        @endforeach
                    </div>

                    <div data-profile-panel="penghargaan" class="hidden mt-5 flex flex-col gap-5">
                        @php
                        $achievements = [
                            [
                                'id' => 1, 'title' => 'Pionir Literasi', 
                                'desc' => 'Berhasil Melakukan Peminjaman Buku Pertama Kamu Di Alinea!',
                                'image' => 'badge_(2).png',
                            ],
                            [
                                'id' => 2, 'title' => 'Kritikus Andal',
                                'desc' => 'Menyelesaikan ulasan buku pertama!',
                                'image' => 'badge_(2).png',
                            ],
                            [
                                'id' => 3, 'title' => 'Sang Kolektor',
                                'desc' => 'Berhasil menambahkan 5 buku pribadi ke dalam katalog koleksi publik.',
                                'image' => 'badge_(2).png',
                            ],
                        ];
                        @endphp
                        
                        @foreach ($achievements as $achievement)
                        <div class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0">
                            <div class="flex items-center gap-5">
                                <div class="w-24 h-24 "
                                     style="background: url('{{ asset('images/' . $achievement['image']) }}') no-repeat center center; background-size: cover;"></div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-[15px] text-[#444]">{{ $achievement['title'] }}</h3>
                                    <p class="text-sm text-gray-500">{{ $achievement['desc'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div data-profile-panel="riwayat" class="hidden mt-5 flex flex-col gap-8">
                        @php
                        $readingNow = [
                            ['title' => 'The Midnight Library', 'author' => 'Matt Haig', 'image' => 'midnight_library.jpg'],
                            ['title' => 'Atomic Habits', 'author' => 'James Clear', 'image' => 'atomic_habits.jpg'],
                            ['title' => 'Sapiens', 'author' => 'Yuval Noah Harari', 'image' => 'sapiens.jpg'],
                        ];

                        $finishedBooks = [
                            ['title' => 'Harry Potter', 'author' => 'J.K. Rowling', 'image' => 'book_cover_4.jpg'],
                            ['title' => 'Educated', 'author' => 'Tara Westover', 'image' => 'book_cover_5.jpg'],
                            ['title' => 'Laut Bercerita', 'author' => 'Leila S. Chudori', 'image' => 'laut_bercerita.jpg'],
                            ['title' => 'The Alchemist', 'author' => 'Paulo Coelho', 'image' => 'book_cover_7.jpg'],
                            ['title' => 'Pachinko', 'author' => 'Min Jin Lee', 'image' => 'book_cover_8.jpg'],
                            ['title' => 'Bumi Manusia', 'author' => 'Pramoedya A.T.', 'image' => 'book_cover_9.jpg'],
                            ['title' => 'Dune', 'author' => 'Frank Herbert', 'image' => 'book_cover_10.jpg'],
                            ['title' => 'Norwegian Wood', 'author' => 'Haruki Murakami', 'image' => 'book_cover_11.jpg'],
                        ];
                        @endphp

                        <section>
                            <div class="flex items-end justify-between mb-3">
                                <h5 class="text-sm font-bold text-[#222]">Sedang Dibaca</h5>
                                <span class="text-xs text-gray-400">Reading shelf</span>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach ($readingNow as $book)
                                <article class="group">
                                    <div class="relative aspect-[2/3] rounded-2xl border-2 border-[#444] overflow-hidden shadow-sm bg-gray-100">
                                        <img src="{{ asset('images/' . $book['image']) }}" alt="Sampul {{ $book['title'] }}"
                                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]">
                                        <span class="absolute top-2 left-2 text-[10px] font-bold tracking-wide px-2 py-0.5 rounded-full border border-[#444] bg-white/90 text-[#333]">
                                            Reading now...
                                        </span>
                                    </div>
                                    <div class="pt-2 px-0.5">
                                        <h4 class="text-[13px] leading-tight font-bold text-[#2a2a2a] line-clamp-1">{{ $book['title'] }}</h4>
                                        <p class="text-[11px] mt-0.5 text-gray-500 line-clamp-1">{{ $book['author'] }}</p>
                                    </div>
                                </article>
                                @endforeach
                            </div>
                        </section>

                        <section>
                            <div class="flex items-end justify-between mb-3">
                                <h3 class="text-sm font-bold text-[#222]">Sudah Dibaca</h3>
                                <span class="text-xs text-gray-400">Finished shelf</span>
                            </div>

                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                @foreach ($finishedBooks as $book)
                                <article class="group">
                                    <div class="aspect-[2/3] rounded-xl border-[1.5px] border-[#444] overflow-hidden bg-gray-100 transition-transform duration-200 group-hover:translate-y-[-2px]">
                                        <img src="{{ asset('images/' . $book['image']) }}" alt="Sampul {{ $book['title'] }}"
                                             class="w-full h-full object-cover [filter:sepia(0.38)_saturate(0.8)_brightness(0.9)]">
                                    </div>
                                    <p class="pt-1 text-[11px] font-medium text-gray-500 leading-tight line-clamp-1">{{ $book['title'] }}</p>
                                </article>
                                @endforeach
                            </div>
                        </section>
                    </div>

                    <div data-profile-panel="media" class="hidden mt-5 flex flex-col gap-5">
                        @php
                        $mediaPosts = [
                            [
                                'id' => 1,
                                'name' => 'Dewi Chalissa', 'handle' => '@oioioi', 'time' => '1 Jam Lalu','location' => 'Bandung',
                                'tag' => 'Dibaca',
                                'caption' => 'Mengabadikan momen baca di kafe — suasana cocok banget untuk tenggelam di cerita.',
                                'attachments' => [
                                    ['type' => 'image', 'src' => 'cafe.jpg'],
                                ],
                                'comments' => '124', 'likes_label' => '3.2K', 'liked' => false,
                            ],
                            [
                                'id' => 2,
                                'name' => 'Dewi Chalissa', 'handle' => '@oioioi', 'time' => '2 Jam Lalu','location' => 'Jakarta',
                                'tag' => 'Selesai',
                                'caption' => 'Sudut rak buku favoritku — rekomendasi buku bagus di sana!',
                                'attachments' => [
                                    ['type' => 'image', 'src' => 'bookshelve.jpg'],
                                    ['type' => 'image', 'src' => 'bookshelve.jpg'],
                                    ['type' => 'image', 'src' => 'bookshelve.jpg'],
                                    ['type' => 'video', 'src' => 'reading_clip.mp4'],
                                ],
                                'comments' => '88', 'likes_label' => '1.1K', 'liked' => true,
                            ],
                            [
                                'id' => 3,
                                'name' => 'Dewi Chalissa', 'handle' => '@oioioi', 'time' => 'Kemarin','location' => 'Surabaya',
                                'tag' => 'Kutipan',
                                'caption' => 'Cuplikan acara: presentasi buku terbaru.',
                                'attachments' => [
                                    ['type' => 'video', 'src' => 'reading_clip.mp4'],
                                    ['type' => 'file', 'src' => 'transcript.pdf', 'label' => 'Transkrip presentasi.pdf'],
                                ],
                                'comments' => '64', 'likes_label' => '940', 'liked' => false,
                            ],
                        ];
                        @endphp

                        @foreach ($mediaPosts as $media)
                        <article class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <span class="font-bold text-[15px] leading-tight">{{ $media['name'] }}</span>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                        <span>{{ $media['handle'] }}</span>
                                        <span class="text-gray-200">•</span>
                                        <span>{{ $media['time'] }}</span>
                                        <span class="text-gray-200">•</span>
                                        <span>{{ $media['location'] }}</span>
                                    </div>
                                </div>
                                <div class="bg-[#fff176] border-2 border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold flex-shrink-0">
                                    {{ $media['tag'] }}
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 leading-relaxed mb-3">{{ $media['caption'] }}</p>

                            @if (!empty($media['attachments']))
                                @php $imgs = array_filter($media['attachments'], fn($a) => $a['type']==='image'); @endphp
                                @if (count($imgs) === 1)
                                    @php $img = reset($imgs); @endphp
                                    <div class="mb-4">
                                        <img src="{{ asset('images/' . $img['src']) }}" alt="media-{{ $media['id'] }}"
                                             class="w-full max-w-[420px] h-auto rounded-2xl border-[1.5px] border-[#444] mx-auto">
                                    </div>
                                @elseif (count($imgs) === 2)
                                    <div class="grid grid-cols-2 gap-2 mb-4">
                                        @foreach ($imgs as $img)
                                        <img src="{{ asset('images/' . $img['src']) }}" alt="media-{{ $media['id'] }}"
                                             class="w-full h-auto rounded-xl border-[1.5px] border-[#444] object-cover">
                                        @endforeach
                                    </div>
                                @elseif (count($imgs) > 2)
                                    <div class="mb-4 relative">
                                        <div class="overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
                                             data-carousel-scroll-{{ $media['id'] }}>
                                            <div class="flex gap-3 w-max px-1">
                                                @foreach ($imgs as $img)
                                                <img src="{{ asset('images/' . $img['src']) }}" alt="media-{{ $media['id'] }}"
                                                     class="rounded-xl border-[1.5px] border-[#444] object-cover w-[280px] h-auto flex-shrink-0">
                                                @endforeach
                                            </div>
                                        </div>
                                        <button class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-10 w-9 h-9 rounded-full bg-white border-2 border-[#444] flex items-center justify-center text-[#444] hover:bg-[#FFDDAF] hover:text-white transition-colors"
                                                data-carousel-prev="{{ $media['id'] }}" aria-label="Gambar sebelumnya">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="15 18 9 12 15 6"/>
                                            </svg>
                                        </button>
                                        <button class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-10 w-9 h-9 rounded-full bg-white border-2 border-[#444] flex items-center justify-center text-[#444] hover:bg-[#FFDDAF] hover:text-white transition-colors"
                                                data-carousel-next="{{ $media['id'] }}" aria-label="Gambar berikutnya">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="9 18 15 12 9 6"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endif

                                @foreach ($media['attachments'] as $att)
                                    @if ($att['type'] === 'video')
                                        <div class="mb-4">
                                            <video controls class="w-full max-w-[720px] mx-auto rounded-2xl border-[1.5px] border-[#444]">
                                                <source src="{{ asset('images/' . $att['src']) }}" type="video/mp4">
                                                Browser Anda tidak mendukung tag video.
                                            </video>
                                        </div>
                                    @elseif ($att['type'] === 'file')
                                        <div class="mb-3">
                                            <a href="{{ asset('images/' . $att['src']) }}" download class="inline-flex items-center gap-3 px-4 py-2 bg-white border-[1px] border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                <span>{{ $att['label'] ?? $att['src'] }}</span>
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <div class="flex items-center gap-5 pt-2">
                                <button aria-label="Komentar" class="flex items-center gap-1.5 text-gray-400 text-[13px] font-medium hover:text-[#444] transition-colors cursor-pointer">
                                    <x-icon-comment fill="none" />
                                    <span>{{ $media['comments'] }}</span>
                                </button>

                                <button data-like-btn aria-pressed="{{ $media['liked'] ? 'true' : 'false' }}" aria-label="Suka"
                                        class="flex items-center gap-1.5 text-[13px] font-medium transition-colors cursor-pointer {{ $media['liked'] ? 'text-red-500' : 'text-gray-400 hover:text-red-400' }}">
                                    <x-icon-like fill="{{ $media['liked'] ? 'currentColor' : 'none' }}" />
                                    <span>{{ $media['likes_label'] }}</span>
                                </button>

                                <div class="ml-auto flex items-center gap-2">
                                    <button aria-pressed="false" aria-label="Simpan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">
                                        <x-icon-bookmark fill="none" />
                                    </button>
                                    <button aria-label="Bagikan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">
                                        <x-icon-share fill="none" />
                                    </button>
                                </div>
                            </div>
                        </article>
                        @endforeach
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