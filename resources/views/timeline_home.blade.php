<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — Timeline</title>
    <meta name="description" content="Ikuti timeline buku Alinea — bagikan progres bacaan, ulasan, dan kutipan favoritmu." />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-auth" content="{{ Auth::check() ? 'true' : 'false' }}">
    <meta name="user-name" content="{{ Auth::user()?->name ?? '' }}">
    <meta name="user-avatar-url" content="{{ Auth::user()?->foto_profil ? asset('storage/' . Auth::user()->foto_profil) : (Auth::user()?->avatar_url ?? '') }}" />
    <meta name="user-id" content="{{ auth()->id() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/timeline.js'])
    <script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.1/dist/browser-image-compression.js"></script>
</head>

<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">

    {{-- ========== NAVBAR (fixed, hides when scrolled away from top) ========== --}}
    <x-navbar></x-navbar>

    {{-- ========== PAGE LAYOUT (3-column: left | center | right) ========== --}}
    <div class="min-h-screen pt-16">
        <div class="flex items-start gap-6 max-w-300 mx-auto px-4 py-6 max-md:pb-24">

            {{-- ===== LEFT SIDEBAR — floating sticky card ===== --}}
            <x-timeline-sidebar />

            {{-- ===== CENTER — FEED COLUMN ===== --}}
            <main class="flex-1 min-w-0 flex flex-col gap-4">

                {{-- Tab switcher (sticky with bg mask so posts slide behind it) --}}
                <div class="sticky top-0 z-30 -mt-6 pt-6 pb-2 mb-1 bg-gray-100 flex items-center gap-2">
                    <div class="flex-1 flex bg-white border-[1.5px] border-[#444] rounded-full overflow-hidden"
                         role="tablist" aria-label="Pilih umpan">
                        <button data-tab-btn role="tab" id="tab-for-you" aria-selected="{{ request('tab', 'untukmu') === 'untukmu' ? 'true' : 'false' }}" aria-controls="feed-panel"
                                class="flex-1 py-2.5 text-sm max-sm:py-2 max-sm:text-xs max-sm:px-2 {{ request('tab', 'untukmu') === 'untukmu' ? 'font-bold text-[#444] bg-[#FFDDAF]' : 'text-gray-400 hover:bg-gray-50' }} rounded-full transition-colors cursor-pointer">
                            Untukmu
                        </button>
                        <button data-tab-btn role="tab" id="tab-following" aria-selected="{{ request('tab') === 'mengikuti' ? 'true' : 'false' }}" aria-controls="feed-panel"
                                class="flex-1 py-2.5 text-sm max-sm:py-2 max-sm:text-xs max-sm:px-2 {{ request('tab') === 'mengikuti' ? 'font-bold text-[#444] bg-[#FFDDAF]' : 'text-gray-400 hover:bg-gray-50' }} rounded-full transition-colors cursor-pointer">
                            Mengikuti
                        </button>
                    </div>

                    {{-- Filter Dropdown --}}
                    <div class="relative">
                        <button type="button" onclick="document.getElementById('filter-dropdown-menu-home').classList.toggle('hidden')" class="flex-shrink-0 w-[42px] h-[42px] max-sm:w-[34px] max-sm:h-[34px] flex items-center justify-center bg-white border-[1.5px] border-[#444] rounded-full hover:bg-gray-50 transition-colors {{ request('tag_filter') ? 'bg-[#FFDDAF]' : '' }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="max-sm:w-4 max-sm:h-4">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                        </button>
                        
                        <div id="filter-dropdown-menu-home" class="hidden absolute right-0 mt-2 w-48 bg-white border-[1.5px] border-[#444] rounded-xl z-50 overflow-hidden">
                            <div class="px-4 py-2 border-b border-gray-100 font-bold text-[10px] text-gray-400 uppercase tracking-wider bg-gray-50">
                                Filter Status
                            </div>
                            <div class="flex flex-col py-1">
                                <a href="{{ request()->fullUrlWithQuery(['tag_filter' => null]) }}" class="px-4 py-2 text-sm hover:bg-gray-50 transition-colors {{ !request('tag_filter') ? 'font-bold text-[#444]' : 'text-gray-600' }}">Semua Status</a>
                                @foreach (['Dibaca', 'Selesai', 'Kutipan'] as $tag)
                                <a href="{{ request()->fullUrlWithQuery(['tag_filter' => $tag]) }}" class="px-4 py-2 text-sm hover:bg-gray-50 transition-colors {{ request('tag_filter') === $tag ? 'font-bold text-[#444]' : 'text-gray-600' }}">{{ $tag }}</a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Composer --}}
                <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5 max-sm:p-4">
                    <div class="flex gap-3">
                        {{-- Composer avatar: foto profil asli atau inisial --}}
                        @auth
                            @if(Auth::user()->foto_profil)
                                <img src="{{ asset('storage/' . Auth::user()->foto_profil) }}"
                                     alt="{{ Auth::user()->name }}"
                                     class="w-11 h-11 max-sm:w-9 max-sm:h-9 rounded-full border-2 border-[#444] flex-shrink-0 object-cover" />
                            @else
                                <div class="w-11 h-11 max-sm:w-9 max-sm:h-9 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0 flex items-center justify-center">
                                    <span class="text-sm font-bold text-[#444]">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        @else
                            <div class="w-11 h-11 max-sm:w-9 max-sm:h-9 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0"></div>
                        @endauth

                        <div class="flex-1 flex flex-col gap-3">
                            {{-- Category pills --}}
                            <div class="flex flex-nowrap max-sm:overflow-x-auto max-sm:gap-1.5 gap-2 max-sm:pb-1">
                                @foreach (['Dibaca', 'Selesai', 'Kutipan'] as $i => $tag)
                                <button data-composer-tag id="tag-{{ Str::lower($tag) }}"
                                        class="text-xs font-medium px-4 py-1 rounded-full border-[1.5px] transition-colors cursor-pointer
                                               {{ $i === 0
                                                   ? 'border-[#444] bg-[#FFDDAF] text-[#444]'
                                                   : 'border-gray-300 text-gray-500 hover:border-[#444] hover:text-[#444]' }}">
                                    {{ $tag }}
                                </button>
                                @endforeach
                            </div>

                            <div class="relative w-full">
                                <input type="text" id="composer-title" placeholder="Judul buku (opsional)..." maxlength="120"
                                       class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm placeholder-gray-300 outline-none focus:border-[#444] transition-colors" autocomplete="off" />
                                
                                {{-- Autocomplete Dropdown --}}
                                <div id="composer-autocomplete-dropdown" class="hidden absolute top-full mt-1 w-full bg-white border-[1.5px] border-[#444] rounded-xl z-50 max-h-60 overflow-y-auto">
                                    <ul id="composer-autocomplete-list" class="flex flex-col">
                                        {{-- Populated by JS --}}
                                    </ul>
                                </div>
                            </div>

                            <textarea id="composer-body" data-autogrow placeholder="Apa yang sedang kamu baca? Bagikan pikiranmu..." rows="3"
                                      class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-[#444] resize-none transition-colors overflow-hidden"></textarea>

                            {{-- Footer: media icons | char counter | submit --}}
                            <div class="flex items-center justify-between">
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
                                    <span id="char-counter" data-char-counter class="text-xs text-gray-300">0/500</span>
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

                {{-- Active book filter indicator --}}
                @if ($activeBook)
                <div class="bg-[#C7E7FF] border-[1.5px] border-[#444] rounded-2xl px-5 py-3 flex items-center justify-between">
                    <p class="text-sm">
                        <span class="text-gray-500">Menampilkan postingan tentang</span>
                        <span class="font-bold">"{{ $activeBook }}"</span>
                    </p>
                    <a href="{{ route('timeline_home') }}" class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full hover:bg-black/10 transition-colors" aria-label="Hapus filter">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </a>
                </div>
                @endif

                {{-- Post feed --}}
                <div id="feed-panel" class="flex flex-col gap-4" role="tabpanel" aria-labelledby="tab-for-you" data-post-store-url="{{ route('timeline_home.store') }}">
                    @forelse ($posts as $post)
                    <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5 max-sm:p-3 hover:bg-gray-50 transition-colors post-item cursor-pointer" data-post-id="{{ $post['id'] }}" data-href="{{ route('timeline.post', $post['id']) }}">

                        {{-- Header --}}
                        <div class="flex items-center gap-3 mb-3 justify-between">
                            <a href="{{ $post['profile_url'] ?? '#' }}" class="flex-shrink-0 cursor-pointer hover:opacity-80 transition-opacity">
                                @if(!empty($post['avatar_url']))
                                <img src="{{ $post['avatar_url'] }}" alt="avatar" class="w-11 h-11 max-sm:w-9 max-sm:h-9 rounded-full border-2 border-[#444] object-cover" />
                                @else
                                <div class="w-11 h-11 max-sm:w-9 max-sm:h-9 rounded-full border-2 border-[#444]"
                                     style="background: linear-gradient(135deg, {{ $post['avatar_from'] }}, {{ $post['avatar_to'] }})"></div>
                                @endif
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ $post['profile_url'] ?? '#' }}" class="font-bold text-[15px] leading-tight hover:underline cursor-pointer block truncate">{{ $post['name'] }}</a>
                                <div class="flex items-center text-xs text-gray-400 mt-0.5 min-w-0">
                                    <a href="{{ $post['profile_url'] ?? '#' }}" class="hover:underline cursor-pointer shrink truncate">{{ $post['handle'] }}</a>
                                    <span class="text-gray-200 mx-1 shrink-0 max-sm:hidden">•</span>
                                    <span class="inline-flex items-center gap-0.5 shrink truncate max-sm:hidden">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        <span class="truncate">{{ $post['location'] }}</span>
                                    </span>
                                    <span class="text-gray-200 mx-1 shrink-0">•</span>
                                    <span title="{{ $post['absolute_time'] }}" class="whitespace-nowrap shrink-0">{{ $post['time'] }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="bg-[#fff176] border-2 inline-flex items-center rounded-full border-text px-3.5 py-0.5 text-xs font-bold">{{ $post['tag'] }}</div>
                                
                                {{-- Post Actions Dropdown --}}
                                <div class="relative">
                                    <button class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors" data-post-menu-trigger>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/><circle cx="12" cy="5" r="1"/></svg>
                                    </button>
                                    <div class="absolute right-0 top-full mt-1 w-48 bg-white border-[1.5px] border-[#444] rounded-xl overflow-hidden hidden z-[60]" data-post-menu-dropdown>
                                        @if(auth()->check() && auth()->id() !== $post['user_id'])
                                            <button class="w-full px-4 py-2.5 text-left text-sm text-red-500 hover:bg-red-50 transition-colors flex items-center gap-2" data-report-post-btn data-post-id="{{ $post['id'] }}">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                Laporkan unggahan
                                            </button>
                                            <button class="w-full px-4 py-2.5 text-left text-sm text-[#222] hover:bg-gray-100 transition-colors flex items-center gap-2 border-t border-gray-100" data-unfollow-btn data-user-id="{{ $post['user_id'] }}">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line></svg>
                                                Berhenti mengikuti
                                            </button>
                                        @elseif(auth()->check() && auth()->id() === $post['user_id'])
                                            <button class="w-full px-4 py-2.5 text-left text-sm text-red-500 font-semibold hover:bg-red-50 flex items-center gap-2 transition-colors" data-post-delete>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                                Hapus Unggahan
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(!empty($post['book']))
                        <div class="inline-flex items-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold mb-3">
                            {{ $post['book'] }}
                        </div>
                        @endif
                        
                        {{-- Body --}}
                        <p class="text-sm max-sm:text-xs text-gray-600 leading-relaxed mb-4">{{ $post['body'] }}</p>

                        {{-- Attachments --}}
                        @if(!empty($post['attachments']))
                        <div class="grid grid-cols-2 max-sm:grid-cols-1 gap-2 mb-4">
                            @foreach($post['attachments'] as $attachment)
                                @if($attachment['type'] === 'image')
                                <img src="{{ $attachment['url'] }}" data-media-url="{{ $attachment['url'] }}" data-media-type="image" class="w-full h-40 object-cover rounded-xl border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity" alt="Attachment" />
                                @elseif($attachment['type'] === 'video')
                                <video src="{{ $attachment['url'] }}" data-media-url="{{ $attachment['url'] }}" data-media-type="video" class="w-full h-40 object-cover rounded-xl border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity" controls></video>
                                @endif
                            @endforeach
                        </div>
                        @endif

                        {{-- Actions --}}
                        <div class="flex items-center gap-5 max-sm:gap-3 pt-3 border-t border-gray-100">
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
                                <button id="bookmark-btn-{{ $post['id'] }}" data-bookmark-btn aria-pressed="{{ !empty($post['bookmarked']) && $post['bookmarked'] ? 'true' : 'false' }}" aria-label="Simpan"
                                        class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-yellow-500 transition-colors cursor-pointer {{ !empty($post['bookmarked']) && $post['bookmarked'] ? 'text-yellow-500' : '' }}">
                                    <x-icon-bookmark fill="{{ !empty($post['bookmarked']) && $post['bookmarked'] ? 'currentColor' : 'none' }}" />
                                </button>
                                <div class="relative">
                                    <button id="share-btn-{{ $post['id'] }}" data-share-btn aria-label="Bagikan"
                                            class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-50 transition-colors cursor-pointer">
                                        <x-icon-share fill="none" />
                                    </button>
                                    <div class="absolute right-0 bottom-full mb-2 w-48 bg-white border-[1.5px] border-[#444] rounded-xl overflow-hidden hidden z-[60]" data-share-dropdown>
                                        <button class="w-full px-4 py-2.5 text-left text-sm font-bold text-[#222] hover:bg-[#FFDDAF] transition-colors" data-share-chat-btn>
                                            Bagikan ke-
                                        </button>
                                        <button class="w-full px-4 py-2.5 text-left text-sm font-bold text-[#222] hover:bg-gray-100 transition-colors border-t border-gray-100" data-share-copy-btn>
                                            Salin Tautan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Comments Section (Hidden by default) --}}
                        <div data-comments-panel class="comments-section hidden mt-4 border-t border-gray-100 pt-4" id="comments-section-{{ $post['id'] }}" data-comments-loaded="false" data-comments-limit="5" data-comments-url="{{ route('timeline_home.comments', $post['id']) }}" data-comments-store-url="{{ route('timeline_home.comments.store', $post['id']) }}">
                            <div class="flex flex-col gap-3 comments-list" data-comment-list id="comments-list-{{ $post['id'] }}">
                                {{-- Loaded via AJAX --}}
                            </div>
                            <div class="mt-3 flex gap-2">
                                <form data-comment-form class="flex gap-3 max-sm:gap-1.5 items-start w-full">
                                    <input type="text" data-comment-input class="flex-1 min-w-0 border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#444] comment-input" placeholder="Tulis komentar..." data-post-id="{{ $post['id'] }}">
                                    <button type="submit" data-comment-submit class="w-9 h-9 flex items-center justify-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full hover:bg-[#ffcf90] transition-colors shrink-0 submit-comment-btn" data-post-id="{{ $post['id'] }}" aria-label="Kirim">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12L2 22l3-10L2 2z"></path><line x1="5" y1="12" x2="14" y2="12"></line></svg>
                                    </button>
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

            {{-- ===== RIGHT SIDEBAR — floating sticky card ===== --}}
            <x-timeline-sidebar-right
                searchPlaceholder="Cari buku atau pengguna..."
                trendingTitle="Populer Minggu Ini"
                :trendingItems="$trendingItems"
            />

        </div>
    </div>

    {{-- ===== MOBILE SEARCH (full-page, like Twitter) ===== --}}
    <div id="mobile-search-overlay" class="hidden fixed inset-0 z-50 bg-white flex-col md:hidden">
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 flex-shrink-0">
            <button id="mobile-search-back" class="w-8 h-8 flex items-center justify-center -ml-1 cursor-pointer text-[#444] hover:bg-gray-100 rounded-full transition-colors flex-shrink-0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="search" id="mobile-search-input" placeholder="Cari postingan atau pengguna..."
                       class="w-full border-[1.5px] border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm outline-none focus:border-[#444] transition-colors"
                       autocomplete="off" />
            </div>
            <button id="mobile-search-close" class="text-sm font-semibold text-gray-500 hover:text-[#444] cursor-pointer flex-shrink-0">Batal</button>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-4">
            <div id="mobile-search-dropdown" class="hidden"></div>
            <div id="mobile-search-trending">
                <h3 class="font-bold text-[13px] text-gray-400 uppercase tracking-wider mb-3">Populer Minggu ini</h3>
                <div class="flex flex-col gap-3">
                    @forelse ($trendingItems as $rank => $item)
                    <a href="{{ $item[2] ?? route('timeline_home', ['book' => $item[0]]) }}"
                       class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                        <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">{{ $rank + 1 }}</span>
                        <div>
                            <span class="font-bold text-[13px] leading-tight block">{{ $item[0] }}</span>
                            <span class="text-[11px] text-gray-400">{{ $item[1] }}</span>
                        </div>
                    </a>
                    @empty
                    <p class="text-[13px] text-gray-400">Belum ada trending minggu ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MOBILE BOTTOM NAV ===== --}}
    <x-timeline-bottom-nav active="beranda" />

    {{-- ========== BACK TO TOP ========== --}}
    <button id="back-to-top" aria-label="Kembali ke atas"
            class="fixed bottom-7 max-sm:bottom-20 right-7 z-50 w-12 h-12 rounded-full bg-[#444] text-white
                   flex items-center justify-center border-2 border-[#FFDDAF]
                   opacity-0 pointer-events-none translate-y-4
                   transition-all duration-300
                   hover:bg-[#FFDDAF] hover:text-[#444] hover:border-[#444] cursor-pointer">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"/>
        </svg>
    </button>

    {{-- ========== REPORT POST MODAL ========== --}}
    <div id="report-post-modal" class="fixed inset-0 z-[100] hidden">
        {{-- Backdrop --}}
        <div id="report-post-backdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        {{-- Modal panel --}}
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div id="report-post-panel"
                 class="relative bg-white border-2 border-[#444] rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto
                        transform scale-95 opacity-0 transition-all duration-300">

                {{-- Close button --}}
                <button id="report-post-close" aria-label="Tutup"
                        class="absolute top-2 right-2 z-10 w-9 h-9 rounded-full flex items-center justify-center
                               text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer bg-white">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path d="M4 4l12 12M16 4L4 16"/>
                    </svg>
                </button>

                {{-- Form Content --}}
                <div class="px-6 pb-6 pt-5">
                    <h2 class="font-bold text-xl mb-1 flex items-center gap-2 text-[#222]">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-red-500"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        Laporkan Unggahan
                    </h2>
                    <p class="text-xs text-gray-500 mb-6">Mohon beritahu kami mengapa unggahan ini melanggar pedoman komunitas Alinea.</p>

                    <form id="report-post-form">
                        <input type="hidden" id="report-post-id" name="post_id" value="">
                        
                        <div class="mb-5">
                            <label for="report-reason" class="block text-xs font-bold text-[#444] mb-1.5 uppercase tracking-wider">
                                Alasan <span class="text-red-400">*</span>
                            </label>
                            <textarea id="report-reason" name="reason" required rows="4" minlength="8"
                                      placeholder="Tuliskan alasan minimal 8 karakter..."
                                      class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-[#444] transition-colors bg-[#FBFBFB] resize-y min-h-[80px]"></textarea>
                            <span id="report-reason-counter" class="block text-right text-[10px] text-gray-400 mt-1">0 karakter (min. 8)</span>
                        </div>

                        <div class="flex gap-3 mt-6">
                            <button type="button" id="report-post-cancel" class="flex-1 py-2.5 text-sm font-bold text-[#444] bg-white rounded-full border-[1.5px] border-gray-200 hover:border-[#444] hover:bg-gray-50 transition-all cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" id="btn-submit-report" disabled
                                    class="flex-1 py-2.5 text-sm font-bold text-[#444] bg-[#FFDDAF] rounded-full border-[1.5px] border-[#444]
                                           hover:-translate-y-[1px] hover:bg-[#ffcf90] transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:bg-[#FFDDAF]">
                                Kirim
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
