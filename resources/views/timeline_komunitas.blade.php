<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — Timeline Komunitas</title>
    <meta name="description" content="Ikuti timeline komunitas Alinea — lihat diskusi dari klub buku yang kamu ikuti." />

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
        <div class="flex items-start gap-6 max-w-300 mx-auto px-4 py-6">

            {{-- ===== LEFT SIDEBAR — floating sticky card ===== --}}
            <aside class="hidden lg:block w-50 shrink-0 sticky top-6">
                <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-4 flex flex-col gap-1">
                    @php
                    $sideNav = [
                        ['id' => 'sidenav-beranda',    'label' => 'Beranda',    'active' => false,
                         'icon' => 'beranda', 'url' => route('timeline_home')],
                        ['id' => 'sidenav-profil',     'label' => 'Profil',     'active' => false,
                         'icon' => 'profil', 'url' => route('timeline_profile')],
                        ['id' => 'sidenav-notifikasi', 'label' => 'Notifikasi', 'active' => false,
                         'icon' => 'notifikasi'],
                        ['id' => 'sidenav-pesan',      'label' => 'Pesan',      'active' => false,
                         'icon' => 'pesan'],
                        ['id' => 'sidenav-komunitas', 'label' => 'Komunitas', 'active' => true, 'icon' => 'community', 'url' => route('timeline_komunitas')]
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
                    </{{ $tag }}>
                    @endforeach
                </div>
            </aside>

            {{-- ===== CENTER — FEED COLUMN ===== --}}
            <main class="flex-1 min-w-0 flex flex-col gap-4">

                {{-- Section Title (sticky with bg mask so posts slide behind it) --}}
                <div class="sticky top-0 z-30 -mt-6 pt-6 pb-2 mb-1 bg-gray-100 flex flex-col gap-3">
                    <div class="flex bg-white border-[1.5px] border-[#444] rounded-full overflow-hidden">
                        <div class="flex-1 py-2.5 text-sm font-bold text-[#444] bg-[#FFDDAF] rounded-full text-center">
                            Klub Saya
                        </div>
                    </div>


                </div>

                {{-- Composer --}}
                <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5">
                    <div class="flex gap-3">
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0"></div>

                        <div class="flex-1 flex flex-col gap-3">
                            {{-- Category pills --}}
                            <div class="flex flex-wrap gap-2">
                                @foreach (['Diskusi', 'Tanya Jawab', 'Rekomendasi', 'Pengumuman'] as $i => $tag)
                                <button data-composer-tag id="tag-{{ Str::slug($tag) }}"
                                        class="text-xs font-medium px-4 py-1 rounded-full border-[1.5px] transition-colors cursor-pointer
                                               {{ $i === 0
                                                   ? 'border-[#444] bg-[#FFDDAF] text-[#444]'
                                                   : 'border-gray-300 text-gray-500 hover:border-[#444] hover:text-[#444]' }}">
                                    {{ $tag }}
                                </button>
                                @endforeach
                            </div>

                            <div class="relative w-full">
                                <input type="text" id="composer-title" placeholder="Judul Buku" maxlength="120"
                                       class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm placeholder-gray-300 outline-none focus:border-[#444] transition-colors" autocomplete="off" />
                                
                                {{-- Autocomplete Dropdown --}}
                                <div id="composer-autocomplete-dropdown" class="hidden absolute top-full mt-1 w-full bg-white border-[1.5px] border-[#444] rounded-xl shadow-lg z-50 max-h-60 overflow-y-auto">
                                    <ul id="composer-autocomplete-list" class="flex flex-col">
                                        {{-- Populated by JS --}}
                                    </ul>
                                </div>
                            </div>

                            <textarea id="composer-body" data-autogrow placeholder="Apa yang ingin kamu diskusikan dengan member klub?" rows="3"
                                      class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-[#444] resize-none transition-colors overflow-hidden"></textarea>

                            {{-- Pilih Klub Dropdown untuk composer --}}
                            <div class="w-full">
                                <select id="composer-klub" class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-500 outline-none focus:border-[#444] transition-colors appearance-none bg-white cursor-pointer">
                                    <option value="" disabled selected>Pilih Klub Tujuan...</option>
                                    @foreach($joinedClubs as $c)
                                    <option value="{{ $c->id }}">{{ $c->nama_klub }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Footer: media icons | char counter | submit --}}
                            <div class="flex items-center justify-between mt-1">
                                {{-- Media upload icons --}}
                                <div class="flex items-center gap-2">
                                    <button type="button" aria-label="Unggah gambar" title="Unggah gambar" id="btn-upload-image"
                                            class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                        </svg>
                                    </button>
                                    <button type="button" aria-label="Unggah video" title="Unggah video" id="btn-upload-video"
                                            class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                                        </svg>
                                    </button>
                                    <input type="file" id="composer-media" accept="image/*,video/*" multiple class="hidden" />
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
                            <div id="composer-media-preview" class="flex flex-wrap gap-2 mt-2 hidden"></div>
                        </div>
                    </div>
                </article>

                {{-- Club Filters (Multi-select) --}}
                <div class="flex flex-wrap gap-2 pb-1" id="club-filters">
                    @foreach ($joinedClubs as $c)
                    <button data-klub-filter="{{ $c->nama_klub }}"
                            class="text-medium font-medium px-4 py-1.5 rounded-full border-[1.5px] border-black text-black hover:border-[#444] hover:text-[#444] transition-colors cursor-pointer bg-white">
                        {{ $c->nama_klub }}
                    </button>
                    @endforeach
                </div>

                {{-- Post feed --}}
                <div id="feed-panel" class="flex flex-col gap-4" role="tabpanel" aria-labelledby="tab-my-clubs" data-post-store-url="{{ route('timeline_posts.store') }}">
                    @forelse ($posts as $post)
                    <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5 hover:bg-gray-50 transition-colors post-item" data-post-klub="{{ $post['klub'] }}" data-post-id="{{ $post['id'] }}">

                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-3 justify-between">
                            @if(!empty($post['avatar_url']))
                            <img src="{{ $post['avatar_url'] }}" alt="avatar" class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0 object-cover" />
                            @else
                            <div class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0"
                                 style="background: linear-gradient(135deg, {{ $post['avatar_from'] }}, {{ $post['avatar_to'] }})"></div>
                            @endif
                            <div class="flex-1">
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
                            <div class="bg-[#fff176] border-2 inline-flex items-center rounded-full border-text px-3.5 py-0.5 text-xs font-bold">{{ $post['tag'] }}</div>
                        </div>

                        {{-- Tags: Book and Club --}}
                        <div class="flex flex-wrap gap-2 mb-3">
                            @if(!empty($post['book']))
                            <div class="inline-flex items-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold">
                                📖 {{ $post['book'] }}
                            </div>
                            @endif
                            @if(!empty($post['klub']))
                            <div class="inline-flex items-center bg-[#C7E7FF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold text-[#444]">
                                👥 {{ $post['klub'] }}
                            </div>
                            @endif
                        </div>
                        
                        {{-- Body --}}
                        <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $post['body'] }}</p>

                        {{-- Attachments --}}
                        @if(!empty($post['attachments']))
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            @foreach($post['attachments'] as $attachment)
                                @if($attachment['type'] === 'image')
                                <img src="{{ $attachment['url'] }}" class="w-full h-40 object-cover rounded-xl border border-gray-200" alt="Attachment" />
                                @elseif($attachment['type'] === 'video')
                                <video src="{{ $attachment['url'] }}" class="w-full h-40 object-cover rounded-xl border border-gray-200" controls></video>
                                @endif
                            @endforeach
                        </div>
                        @endif

                        {{-- Actions --}}
                        <div class="flex items-center gap-5 pt-3 border-t border-gray-100">
                            {{-- Comment --}}
                            <button id="comment-btn-{{ $post['id'] }}" aria-label="Komentar" data-comment-toggle
                                    class="flex items-center gap-1.5 text-gray-400 text-[13px] font-medium hover:text-[#444] transition-colors cursor-pointer">
                                <x-icon-comment fill="none" />
                                <span data-comment-count>{{ $post['comments'] }}</span>
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

                        {{-- Comments Section (Hidden by default) --}}
                        <div data-comments-panel class="comments-section hidden mt-4 border-t border-gray-100 pt-4" id="comments-section-{{ $post['id'] }}" data-comments-loaded="false" data-comments-url="{{ route('timeline_home.comments', $post['id']) }}" data-comments-store-url="{{ route('timeline_home.comments.store', $post['id']) }}">
                            <div class="flex flex-col gap-3 comments-list" data-comment-list id="comments-list-{{ $post['id'] }}">
                                {{-- Loaded via AJAX --}}
                            </div>
                            <div class="mt-3 flex gap-2">
                                <form data-comment-form class="flex gap-3 items-start w-full">
                                    <input type="text" data-comment-input class="flex-1 border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#444] comment-input" placeholder="Tulis komentar..." data-post-id="{{ $post['id'] }}">
                                    <button type="submit" data-comment-submit class="bg-[#FFDDAF] text-[#444] px-4 py-2 rounded-lg font-bold text-sm border-[1.5px] border-[#444] hover:bg-[#ffcf90] submit-comment-btn" data-post-id="{{ $post['id'] }}">Kirim</button>
                                </form>
                            </div>
                        </div>
                    </article>
                    @empty
                    <div class="text-center py-10 text-gray-400">
                        <p>Belum ada postingan.</p>
                    </div>
                    @endforelse
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
                        <input type="search" id="sidebar-search-input" placeholder="Cari diskusi klub..."
                               class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" />
                    </div>
                </div>

                {{-- What's Trending --}}
                <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5">
                    <h2 class="font-bold text-[15px] mb-4">Klub Terpopuler</h2>

                    @php
                    $trending = [
                        ['Romance Readers',          '30 Member'],
                        ['Dunia Fantasi',            '24 Member'],
                        ['Buku Anak Muda',           '22 Member'],
                        ['Sastra Nusantara',         '18 Member'],
                        ['Filsafat Kopi',            '15 Member'],
                    ];
                    @endphp

                    <ol class="flex flex-col gap-3.5">
                        @foreach ($trending as $rank => $club)
                        <li class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                            <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">{{ $rank + 1 }}</span>
                            <div>
                                <span class="font-bold text-[13px] leading-tight block">{{ $club[0] }}</span>
                                <span class="text-[11px] text-gray-400">{{ $club[1] }}</span>
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
