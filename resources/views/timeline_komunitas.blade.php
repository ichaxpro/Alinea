<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — Timeline Komunitas</title>
    <meta name="description" content="Ikuti timeline komunitas Alinea — lihat diskusi dari klub buku yang kamu ikuti." />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

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
                         'icon' => 'notifikasi', 'url' => route('timeline_notifikasi')], 
                        ['id' => 'sidenav-pesan',      'label' => 'Pesan',      'active' => false,
                         'icon' => 'pesan', 'url' => route('chat')],
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
                        @auth
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0 overflow-hidden flex items-center justify-center">
                                @if (Auth::user()->avatar_url)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="Avatar {{ Auth::user()->name }}" class="w-full h-full object-cover" />
                                @else
                                    <span class="text-sm font-bold text-[#444]">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                @endif
                            </div>
                        @else
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0 flex items-center justify-center">
                                <span class="text-sm font-bold text-[#444]">U</span>
                            </div>
                        @endauth

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

                            <input type="text" id="composer-title" placeholder="Judul Buku" maxlength="120"
                                   class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm placeholder-gray-300 outline-none focus:border-[#444] transition-colors" />

                            <textarea id="composer-body" data-autogrow placeholder="Apa yang ingin kamu diskusikan dengan member klub?" rows="3"
                                      class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-[#444] resize-none transition-colors overflow-hidden"></textarea>

                            {{-- Pilih Klub Dropdown untuk composer --}}
                            <div class="w-full">
                                <select id="composer-klub" class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-500 outline-none focus:border-[#444] transition-colors appearance-none bg-white cursor-pointer">
                                    <option value="" disabled selected>Pilih Klub Tujuan...</option>
                                    @forelse ($joinedClubs as $club)
                                    <option value="{{ $club->id }}">{{ $club->nama_klub }}</option>
                                    @empty
                                    <option value="" disabled>Belum ada klub yang diikuti</option>
                                    @endforelse
                                </select>
                            </div>

                            {{-- Hidden file input for media attachments --}}
                            <input type="file" id="composer-media" class="hidden" accept="image/*,video/*,*/*" />



                            {{-- Footer: media icons | char counter | submit --}}
                            <div class="flex items-center justify-between mt-1">
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

                {{-- Club Filters (Multi-select) --}}
                <div class="flex flex-wrap gap-2 pt-1 pb-2" id="club-filters">
                    @forelse ($joinedClubs as $klub)
                    <button data-klub-filter="{{ $klub->nama_klub }}"
                            class="text-[13px] font-semibold px-5 py-2 rounded-full border-[1.5px] border-[#444] text-[#444] hover:bg-gray-50 transition-colors cursor-pointer bg-white shadow-sm">
                        {{ $klub->nama_klub }}
                    </button>
                    @empty
                    <span class="text-sm text-gray-400">Belum ada klub yang diikuti.</span>
                    @endforelse
                </div>

                {{-- Post feed --}}
                <div id="feed-panel" data-post-store-url="{{ route('timeline_posts.store') }}" class="flex flex-col gap-4" role="tabpanel" aria-labelledby="tab-my-clubs">
                    @forelse ($posts as $post)
                    <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5 hover:bg-gray-50 transition-colors" data-post-klub="{{ $post['klub'] }}" data-post-id="{{ $post['id'] }}">

                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-3 justify-between">
                            <div class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0 overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center">
                                @if (!empty($post['avatar_url']))
                                    <img src="{{ $post['avatar_url'] }}" alt="Avatar {{ $post['name'] }}" class="w-full h-full object-cover" />
                                @else
                                    <span class="text-xs font-bold text-[#444]">{{ strtoupper(substr($post['name'] ?? 'U', 0, 1)) }}</span>
                                @endif
                            </div>
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
                            @if (!empty($post['book']))
                            <div class="inline-flex items-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold">
                                📖 {{ $post['book'] }}
                            </div>
                            @endif
                            <div class="inline-flex items-center bg-[#C7E7FF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold text-[#444]">
                                👥 {{ $post['klub'] }}
                            </div>
                        </div>
                        
                        {{-- Media (server-rendered) --}}
                        @if (!empty($post['media_url']))
                            @if (($post['media_type'] ?? '') === 'image')
                                <div class="mb-3"><img src="{{ $post['media_url'] }}" alt="media" class="w-full h-auto max-h-96 object-contain rounded-lg"/></div>
                            @elseif (($post['media_type'] ?? '') === 'video')
                                <div class="mb-3"><video src="{{ $post['media_url'] }}" controls class="w-full h-auto rounded-lg"></video></div>
                            @else
                                <div class="mb-3 text-sm"><a href="{{ $post['media_url'] }}" class="underline">{{ $post['media_original_name'] ?? 'Unduh file' }}</a></div>
                            @endif
                        @endif

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
                    @empty
                    <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5 text-sm text-gray-500">
                        Belum ada post dari klub yang kamu ikuti.
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

                    <ol class="flex flex-col gap-3.5">
                        @forelse ($popularClubs as $rank => $club)
                        <li class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                            <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">{{ $rank + 1 }}</span>
                            <div>
                                <span class="font-bold text-[13px] leading-tight block" title="{{ $club->nama_klub }}">{{ \Illuminate\Support\Str::limit($club->nama_klub, 22) }}</span>
                                <span class="text-[11px] text-gray-400">{{ $club->member_count }} Member</span>
                            </div>
                        </li>
                        @empty
                        <li class="text-sm text-gray-400">Belum ada klub yang bisa ditampilkan.</li>
                        @endforelse
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
