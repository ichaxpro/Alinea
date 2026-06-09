<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — {{ $user->name }}</title>
    <meta name="description" content="Profil {{ $user->name }} di Alinea" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-auth" content="{{ Auth::check() ? 'true' : 'false' }}">
    <meta name="user-name" content="{{ Auth::user()?->name ?? '' }}">
    <meta name="user-avatar-url" content="{{ Auth::user()?->avatar_url ?? '' }}" />
    <meta name="user-id" content="{{ Auth::id() ?? '' }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/timeline.js'])
</head>

<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">

    {{-- ========== NAVBAR (fixed, hides when scrolled away from top) ========== --}}
    <x-navbar></x-navbar>

    {{-- ========== PAGE LAYOUT (3-column: left | center | right) ========== --}}
    <div class="min-h-screen pt-16">
        <div class="flex items-start gap-6 max-w-[1200px] mx-auto px-4 py-6 max-md:pb-24">

            {{-- ===== LEFT SIDEBAR — floating sticky card ===== --}}
            <x-timeline-sidebar />

            {{-- ===== CENTER — FEED COLUMN ===== --}}
            <main class="flex-1 min-w-0 flex flex-col gap-4">

                

                {{-- Profile Header --}}
                <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6">
                    <div class="flex flex-col sm:flex-row gap-6 items-center sm:items-start text-center sm:text-left">
                        <div class="w-28 h-28 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0 overflow-hidden flex items-center justify-center">
                            @if($user->foto_profil)
                            <img src="{{ Storage::disk('public')->url($user->foto_profil) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                            <span class="text-4xl font-black text-text/60">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>

                        <div class="flex-1 w-full">
                            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                                <div class="text-center sm:text-left">
                                    <h2 class="text-2xl font-bold text-[#222]">{{ $user->name }}</h2>
                                    <p class="text-sm text-gray-500">{{ $user->username ? '@' . $user->username : 'tanpa_username' }}</p>
                                </div>

                                @auth
                                    @if($isOwnProfile)
                                        <a href="{{ route('profile.edit') }}"
                                           class="sm:ml-auto px-4 py-2 bg-[#FFDDAF] border-2 border-[#444] rounded-full text-sm font-bold hover:bg-[#ffcf90] transition-colors whitespace-nowrap">
                                            Edit Profil
                                        </a>
                                    @else
                                        @if($hasBlockedMe)
                                            <div class="sm:ml-auto px-4 py-2 bg-red-50 text-red-600 rounded-full text-sm font-bold border border-red-200">
                                                Anda diblokir oleh pengguna ini
                                            </div>
                                        @elseif($isBlockedByMe)
                                            <div class="sm:ml-auto flex items-center gap-2">
                                                <button type="button" onclick="handleBlockUserProfile({{ $user->id }}, '{{ addslashes($user->name) }}')" class="px-5 py-2 rounded-full text-sm font-bold border-2 border-red-500 bg-red-500 text-white hover:bg-red-600 transition-colors whitespace-nowrap">
                                                    Buka Blokir
                                                </button>
                                            </div>
                                        @else
                                            <div class="sm:ml-auto flex items-center gap-2">
                                                {{-- DM Button --}}
                                                <a href="{{ route('chat') }}?user_id={{ $user->id }}" class="w-10 h-10 flex items-center justify-center rounded-full border-2 border-[#444] text-[#444] hover:bg-gray-100 transition-colors cursor-pointer bg-white" title="Kirim Pesan">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                                    </svg>
                                                </a>
                                                
                                                {{-- Follow Button --}}
                                                <button id="follow-btn"
                                                        data-follow-url="{{ route('profile.follow', $user) }}"
                                                        data-following="{{ $isFollowing ? 'true' : 'false' }}"
                                                        data-following-count="{{ $followingCount }}"
                                                        class="px-5 py-2 rounded-full text-sm font-bold border-2 border-[#444] transition-colors cursor-pointer whitespace-nowrap
                                                               {{ $isFollowing ? 'bg-[#444] text-white' : 'bg-[#FFDDAF] hover:bg-[#ffcf90]' }}">
                                                    {{ $isFollowing ? 'Mengikuti' : 'Ikuti' }}
                                                </button>
                                                
                                                {{-- More Menu (Report & Block) --}}
                                                <div class="relative" data-post-menu>
                                                    <button type="button" class="w-10 h-10 flex items-center justify-center rounded-full border-2 border-[#444] text-[#444] hover:bg-gray-100 transition-colors cursor-pointer bg-white" data-post-menu-trigger title="Lainnya">
                                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                                    </button>
                                                    <div class="absolute right-0 mt-2 min-w-max bg-white rounded-xl border-2 border-[#444] py-1 hidden z-50 transform origin-top-right transition-all" data-post-menu-dropdown>
                                                        <button type="button" onclick="handleReportUserProfile({{ $user->id }}, '{{ addslashes($user->name) }}')" class="w-full text-left px-4 py-2.5 text-sm text-red-500 font-bold hover:bg-red-50 transition-colors flex items-center gap-2 whitespace-nowrap">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                                                            Laporkan Pengguna
                                                        </button>
                                                        <button type="button" onclick="handleBlockUserProfile({{ $user->id }}, '{{ addslashes($user->name) }}')" class="w-full text-left px-4 py-2.5 text-sm text-[#444] font-bold hover:bg-gray-100 transition-colors flex items-center gap-2 whitespace-nowrap">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                                            Blokir Pengguna
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                @endauth
                            </div>

                            @if($user->deskripsi)
                                <p class="mt-4 text-base text-[#333] text-center sm:text-left">{{ $user->deskripsi }}</p>
                            @endif

                            <p class="text-sm text-gray-500 mt-2 text-center sm:text-left">
                                <button type="button" id="profile-following-trigger" data-user-id="{{ $user->id }}" class="hover:underline cursor-pointer">
                                    <span class="font-bold text-[#222]">{{ $followingCount }}</span> Mengikuti
                                </button>
                                <span class="mx-2">|</span>
                                <button type="button" id="profile-followers-trigger" data-user-id="{{ $user->id }}" class="hover:underline cursor-pointer">
                                    <span class="font-bold text-[#222]">{{ $followersCount }}</span> Pengikut
                                </button>
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 border-b border-gray-200">
                        <div class="overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                            <div class="flex w-full">
                                @foreach ([
                                    ['label' => 'Unggahan', 'active' => true],
                                    ['label' => 'Penghargaan', 'active' => false],
                                    ['label' => 'Riwayat', 'active' => false],
                                    ['label' => 'Media', 'active' => false],
                                ] as $tab)
                                <button type="button"
                                        data-profile-tab
                                        data-profile-tab-target="{{ strtolower($tab['label']) }}"
                                        class="flex-shrink-0 relative flex-1 px-3 pb-4 text-sm font-semibold transition-colors cursor-pointer text-center {{ $tab['active'] ? 'text-[#111]' : 'text-gray-400 hover:text-gray-600' }}"
                                        aria-selected="{{ $tab['active'] ? 'true' : 'false' }}">
                                    {{ $tab['label'] }}
                                    <span data-profile-tab-indicator
                                          class="absolute left-1/2 -translate-x-1/2 -bottom-[1px] h-1 w-24 rounded-full bg-[#5DA9FF] {{ $tab['active'] ? '' : 'hidden' }}"></span>
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Tab: Unggahan --}}
                    <div id="profile-feed-panel" data-profile-panel="unggahan" class="mt-5 flex flex-col gap-5" role="tabpanel" aria-labelledby="tab-for-you">
                        @forelse ($posts as $post)
                        <article class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0" data-post-id="{{ $post['id'] }}">
                            <div class="flex items-start gap-3 mb-3">
                                <a href="{{ $post['profile_url'] ?? '#' }}" class="w-11 h-11 max-sm:w-9 max-sm:h-9 rounded-full border-2 border-[#444] flex-shrink-0 overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity">
                                    @if($post['avatar_url'])
                                        <img src="{{ $post['avatar_url'] }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs font-bold text-[#444]">{{ strtoupper(substr($post['name'], 0, 1)) }}</span>
                                    @endif
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ $post['profile_url'] ?? '#' }}" class="font-bold text-[15px] leading-tight hover:underline cursor-pointer">{{ $post['name'] }}</a>
                                    <div class="flex flex-wrap items-center gap-1.5 max-sm:gap-1 text-xs text-gray-400">
                                        <a href="{{ $post['profile_url'] ?? '#' }}" class="whitespace-nowrap hover:underline cursor-pointer">{{ $post['handle'] }}</a>
                                        <span class="text-gray-200 whitespace-nowrap">•</span>
                                        <span class="flex items-center gap-1 whitespace-nowrap">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                            </svg>
                                            {{ $post['location'] }}
                                        </span>
                                        <span class="text-gray-200 whitespace-nowrap">•</span>
                                        <span class="whitespace-nowrap" title="{{ $post['absolute_time'] }}">{{ $post['time'] }}</span>
                                    </div>
                                </div>
                                <div class="bg-[#fff176] border-2 border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold flex-shrink-0">
                                    {{ $post['tag'] }}
                                </div>
                                @if($isOwnProfile)
                                <div class="relative" data-post-menu>
                                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors" data-post-menu-trigger>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                    </button>
                                    <div class="absolute right-0 mt-1 w-48 bg-white rounded-xl border border-gray-100 py-1 hidden z-50 transform origin-top-right transition-all" data-post-menu-dropdown>
                                        <button type="button" class="w-full text-left px-4 py-2.5 text-sm text-red-500 font-semibold hover:bg-red-50 transition-colors flex items-center gap-2" data-post-delete>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                            Hapus Unggahan
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            @if($post['book'])
                            <div class="inline-flex items-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold mb-3">
                                {{ $post['book'] }}
                            </div>
                            @endif

                            <p class="text-sm max-sm:text-xs text-gray-600 leading-relaxed mb-4">{{ $post['body'] }}</p>

                            {{-- Media --}}
                            @if(!empty($post['attachments']))
                            <div class="grid grid-cols-2 max-sm:grid-cols-1 gap-2 mb-4">
                                @foreach($post['attachments'] as $attachment)
                                    @php $attachmentUrl = $attachment['url'] ?? ($attachment['src'] ?? null); @endphp
                                    @if(($attachment['type'] ?? '') === 'image')
                                    <img src="{{ $attachmentUrl }}" data-media-url="{{ $attachmentUrl }}" data-media-type="image"
                                         class="w-full h-40 object-cover rounded-xl border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity" alt="Attachment" />
                                    @elseif(($attachment['type'] ?? '') === 'video')
                                    <video src="{{ $attachmentUrl }}" data-media-url="{{ $attachmentUrl }}" data-media-type="video"
                                           class="w-full h-40 object-cover rounded-xl border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity" controls></video>
                                    @endif
                                @endforeach
                            </div>
                            @elseif($post['media_url'] && $post['media_type'] === 'image')
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    <img src="{{ $post['media_url'] }}" data-media-url="{{ $post['media_url'] }}" data-media-type="image"
                                         class="w-full h-40 object-cover rounded-xl border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity" alt="Attachment" />
                                </div>
                            @elseif($post['media_url'] && $post['media_type'] === 'video')
                                <div class="mb-4">
                                    <video src="{{ $post['media_url'] }}" data-media-url="{{ $post['media_url'] }}" data-media-type="video" controls class="w-full max-w-[720px] mx-auto rounded-2xl border-[1.5px] border-[#444] bg-black cursor-pointer hover:opacity-90 transition-opacity"></video>
                                </div>
                            @elseif($post['media_url'])
                                <div class="mb-3">
                                    <a href="{{ $post['media_url'] }}" download
                                       class="inline-flex items-center gap-3 px-4 py-2 bg-white border-[1px] border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                                        <span>{{ $post['media_original_name'] ?? 'Unduh file' }}</span>
                                    </a>
                                </div>
                            @endif

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
                                    <button id="bookmark-btn-{{ $post['id'] }}" data-bookmark-btn aria-pressed="{{ !empty($post['bookmarked']) && $post['bookmarked'] ? 'true' : 'false' }}" aria-label="Simpan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-yellow-500 transition-colors cursor-pointer {{ !empty($post['bookmarked']) && $post['bookmarked'] ? 'text-yellow-500' : '' }}"><x-icon-bookmark fill="{{ !empty($post['bookmarked']) && $post['bookmarked'] ? 'currentColor' : 'none' }}" /></button>
                                <div class="relative">
                                    <button id="share-btn-{{ $post['id'] }}" data-share-btn aria-label="Bagikan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-50 transition-colors cursor-pointer"><x-icon-share fill="none" /></button>
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
                            <div data-comments-panel class="comments-section hidden mt-4 border-t border-gray-100 pt-4"
                                 id="comments-section-{{ $post['id'] }}"
                                 data-comments-loaded="false"
                                 data-comments-url="{{ route('timeline_home.comments', $post['id']) }}"
                                 data-comments-store-url="{{ route('timeline_home.comments.store', $post['id']) }}">
                                <div class="flex flex-col gap-3 comments-list" data-comment-list id="comments-list-{{ $post['id'] }}">
                                    {{-- Loaded via AJAX --}}
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <form data-comment-form class="flex gap-3 max-sm:gap-1.5 items-start w-full">
                                        <input type="text" data-comment-input
                                               class="flex-1 border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#444] comment-input"
                                               placeholder="Tulis komentar..."
                                               data-post-id="{{ $post['id'] }}">
                                        <button type="submit" data-comment-submit
                                                class="bg-[#FFDDAF] text-[#444] px-4 max-sm:px-2.5 py-2 rounded-lg font-bold text-sm max-sm:text-xs border-[1.5px] border-[#444] hover:bg-[#ffcf90] submit-comment-btn whitespace-nowrap"
                                                data-post-id="{{ $post['id'] }}">Kirim</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                        @empty
                        @if($hasBlockedMe)
                            <p class="text-center text-gray-400 py-8">{{ $user->name }} telah memblokir Anda.</p>
                        @elseif($isBlockedByMe)
                            <p class="text-center text-gray-400 py-8">Anda memblokir pengguna ini.</p>
                        @else
                            <p class="text-center text-gray-400 py-8">Belum ada unggahan.</p>
                        @endif
                        @endforelse
                    </div>

                    {{-- Tab: Penghargaan --}}
                    <div data-profile-panel="penghargaan" class="hidden mt-5 flex flex-col gap-5">
                        @forelse ($achievements as $achievement)
                        <div class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0">
                            <div class="flex items-center gap-5">
                                <div class="w-24 h-24" style="background: url('{{ asset('images/' . ($achievement->icon ?? 'badge_(2).png')) }}') no-repeat center center; background-size: cover;"></div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-[15px] text-[#444]">{{ $achievement->title }}</h3>
                                    <p class="text-sm text-gray-500">{{ $achievement->description }}</p>
                                    @if($achievement->pivot?->earned_at)
                                        <p class="text-xs text-gray-400 mt-1">Diperoleh {{ \Carbon\Carbon::parse($achievement->pivot->earned_at)->locale('id')->diffForHumans() }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <p class="text-center text-gray-400 py-8">Belum ada penghargaan.</p>
                        @endforelse
                    </div>

                    {{-- Tab: Riwayat --}}
                    <div data-profile-panel="riwayat" class="hidden mt-5 flex flex-col gap-8">

                        {{-- ── Sedang Dibaca ── --}}
                        <section>
                            <div class="flex items-end justify-between mb-3">
                                <h3 class="text-sm font-bold text-[#222]">Sedang Dibaca</h3>
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-[#C7E7FF] text-[#444]">
                                    {{ $readingNow->count() }} buku
                                </span>
                            </div>
                            @if($readingNow->isNotEmpty())
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach ($readingNow as $book)
                                <article class="group">
                                    <div class="relative aspect-[2/3] rounded-2xl border-2 border-[#444] overflow-hidden shadow-sm bg-gray-100">
                                        @if($book->cover_url)
                                        @php
                                            $cover = str_starts_with($book->cover_url, 'http') ? $book->cover_url : (str_starts_with($book->cover_url, '/') ? asset(ltrim($book->cover_url, '/')) : asset('storage/' . $book->cover_url));
                                        @endphp
                                        <img src="{{ $cover }}" alt="Sampul {{ $book->judul }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                        <div style="display: none;" class="w-full h-full items-center justify-center bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF] text-[#444] text-sm font-bold p-4 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                                        @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF] text-[#444] text-sm font-bold p-4 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                                        @endif
                                        <span class="absolute top-2 left-2 text-[10px] font-bold tracking-wide px-2 py-0.5 rounded-full border border-[#444] bg-[#C7E7FF]/90 text-[#333]">Sedang Dibaca</span>
                                    </div>
                                    <div class="pt-2 px-0.5">
                                        <h4 class="text-[13px] leading-tight font-bold text-[#2a2a2a] line-clamp-1">{{ $book->judul }}</h4>
                                        <p class="text-[11px] mt-0.5 text-gray-500 line-clamp-1">{{ $book->penulis }}</p>
                                    </div>
                                </article>
                                @endforeach
                            </div>
                            @else
                            <p class="text-sm text-gray-400">Belum ada buku yang sedang dibaca.</p>
                            @endif
                        </section>

                        {{-- ── Sudah Dibaca ── --}}
                        <section>
                            <div class="flex items-end justify-between mb-3">
                                <h3 class="text-sm font-bold text-[#222]">Sudah Dibaca</h3>
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-[#D4F6FF] text-[#444]">
                                    {{ $finishedBooks->count() }} buku
                                </span>
                            </div>
                            @if($finishedBooks->isNotEmpty())
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                @foreach ($finishedBooks as $book)
                                <article class="group">
                                    <div class="relative aspect-[2/3] rounded-xl border-[1.5px] border-[#444] overflow-hidden bg-gray-100 transition-transform duration-200 group-hover:translate-y-[-2px]">
                                        @if($book->cover_url)
                                        @php
                                            $cover = str_starts_with($book->cover_url, 'http') ? $book->cover_url : (str_starts_with($book->cover_url, '/') ? asset(ltrim($book->cover_url, '/')) : asset('storage/' . $book->cover_url));
                                        @endphp
                                        <img src="{{ $cover }}" alt="Sampul {{ $book->judul }}" class="w-full h-full object-cover [filter:sepia(0.38)_saturate(0.8)_brightness(0.9)]" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                        <div style="display: none;" class="w-full h-full items-center justify-center bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF] text-[#444] text-xs font-bold p-2 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                                        @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF] text-[#444] text-xs font-bold p-2 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                                        @endif
                                        <span class="absolute top-1.5 left-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-[#D4F6FF]/90 border border-[#444] text-[#333]">Selesai</span>
                                    </div>
                                    <p class="pt-1 text-[11px] font-medium text-gray-500 leading-tight line-clamp-1">{{ $book->judul }}</p>
                                </article>
                                @endforeach
                            </div>
                            @else
                            <p class="text-sm text-gray-400">Belum ada buku yang selesai dibaca.</p>
                            @endif
                        </section>

                        {{-- ── Ingin Dibaca ── --}}
                        <section>
                            <div class="flex items-end justify-between mb-3">
                                <h3 class="text-sm font-bold text-[#222]">Ingin Dibaca</h3>
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-gray-200 text-gray-500">
                                    {{ $wantToRead->count() }} buku
                                </span>
                            </div>
                            @if($wantToRead->isNotEmpty())
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                @foreach ($wantToRead as $book)
                                <article class="group">
                                    <div class="relative aspect-[2/3] rounded-xl border-[1.5px] border-dashed border-gray-400 overflow-hidden bg-gray-50 transition-transform duration-200 group-hover:translate-y-[-2px]">
                                        @if($book->cover_url)
                                        @php
                                            $cover = str_starts_with($book->cover_url, 'http') ? $book->cover_url : (str_starts_with($book->cover_url, '/') ? asset(ltrim($book->cover_url, '/')) : asset('storage/' . $book->cover_url));
                                        @endphp
                                        <img src="{{ $cover }}" alt="Sampul {{ $book->judul }}" class="w-full h-full object-cover opacity-60" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                        <div style="display: none;" class="w-full h-full items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400 text-xs font-bold p-2 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                                        @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 text-gray-400 text-xs font-bold p-2 text-center">{{ strtoupper(substr($book->judul, 0, 1)) }}</div>
                                        @endif
                                        <span class="absolute top-1.5 left-1.5 text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-white/90 border border-gray-400 text-gray-500">Ingin Dibaca</span>
                                    </div>
                                    <p class="pt-1 text-[11px] font-medium text-gray-500 leading-tight line-clamp-1">{{ $book->judul }}</p>
                                </article>
                                @endforeach
                            </div>
                            @else
                            <p class="text-sm text-gray-400">Belum ada buku yang ingin dibaca.</p>
                            @endif
                        </section>

                    </div>

                    {{-- Tab: Media --}}
                    <div data-profile-panel="media" class="hidden mt-5 flex flex-col gap-5">
                        @forelse ($mediaPosts as $media)
                        <article class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0" data-post-id="{{ $media['id'] }}">
                            <div class="flex items-start gap-3 mb-3">
                                <a href="{{ $media['profile_url'] ?? '#' }}" class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0 overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity">
                                    @if($media['avatar_url'])
                                        <img src="{{ $media['avatar_url'] }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs font-bold text-[#444]">{{ strtoupper(substr($media['name'], 0, 1)) }}</span>
                                    @endif
                                </a>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ $media['profile_url'] ?? '#' }}" class="font-bold text-[15px] leading-tight hover:underline cursor-pointer">{{ $media['name'] }}</a>
                                    <div class="flex flex-wrap items-center gap-1.5 max-sm:gap-1 text-xs text-gray-400">
                                        <a href="{{ $media['profile_url'] ?? '#' }}" class="whitespace-nowrap hover:underline cursor-pointer">{{ $media['handle'] }}</a>
                                        <span class="text-gray-200">•</span>
                                        @if($media['location'])
                                        <span class="flex items-center gap-1 whitespace-nowrap">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                            </svg>
                                            {{ $media['location'] }}
                                        </span>
                                        <span class="text-gray-200">•</span>
                                        @endif
                                        <span class="whitespace-nowrap" title="{{ $media['absolute_time'] }}">{{ $media['time'] }}</span>
                                    </div>
                                </div>
                                <div class="bg-[#fff176] border-2 border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold flex-shrink-0">{{ $media['tag'] }}</div>
                                @if($isOwnProfile)
                                <div class="relative" data-post-menu>
                                    <button type="button" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors" data-post-menu-trigger>
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                    </button>
                                    <div class="absolute right-0 mt-1 w-48 bg-white rounded-xl border border-gray-100 py-1 hidden z-50 transform origin-top-right transition-all" data-post-menu-dropdown>
                                        <button type="button" class="w-full text-left px-4 py-2.5 text-sm text-red-500 font-semibold hover:bg-red-50 transition-colors flex items-center gap-2" data-post-delete>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                            Hapus Unggahan
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600 leading-relaxed mb-3">{{ $media['caption'] }}</p>

                            @if(!empty($media['attachments']))
                                @php $imgs = array_filter($media['attachments'], fn($a) => $a['type'] === 'image'); @endphp
                                @if(count($imgs) === 1)
                                    @php $img = reset($imgs); @endphp
                                    <div class="mb-4">
                                        <img src="{{ asset('storage/' . $img['src']) }}" data-media-url="{{ asset('storage/' . $img['src']) }}" data-media-type="image" alt="media" class="w-full max-w-[420px] h-auto rounded-2xl border-[1.5px] border-[#444] mx-auto cursor-pointer hover:opacity-90 transition-opacity">
                                    </div>
                                @elseif(count($imgs) > 1)
                                    <div class="grid grid-cols-2 gap-2 mb-4">
                                        @foreach ($imgs as $img)
                                        <img src="{{ asset('storage/' . $img['src']) }}" data-media-url="{{ asset('storage/' . $img['src']) }}" data-media-type="image" alt="media" class="w-full h-auto rounded-xl border-[1.5px] border-[#444] object-cover cursor-pointer hover:opacity-90 transition-opacity">
                                        @endforeach
                                    </div>
                                @endif
                                @foreach ($media['attachments'] as $att)
                                    @if($att['type'] === 'video')
                                        <div class="mb-4">
                                            <video src="{{ asset('storage/' . $att['src']) }}" data-media-url="{{ asset('storage/' . $att['src']) }}" data-media-type="video" controls class="w-full max-w-[720px] mx-auto rounded-2xl border-[1.5px] border-[#444] cursor-pointer hover:opacity-90 transition-opacity"></video>
                                        </div>
                                    @elseif($att['type'] === 'file')
                                        <div class="mb-3">
                                            <a href="{{ asset('storage/' . $att['src']) }}" download class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                                                <span>{{ $att['label'] ?? $att['src'] }}</span>
                                            </a>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <div class="flex items-center gap-5 max-sm:gap-3 pt-2">
                                {{-- Comment --}}
                                <button id="comment-btn-{{ $media['id'] }}" aria-label="Komentar" data-comment-toggle
                                        class="flex items-center gap-1.5 text-gray-400 text-[13px] font-medium hover:text-[#444] transition-colors cursor-pointer">
                                    <x-icon-comment fill="none" />
                                    <span data-comment-count>{{ $media['comments'] }}</span>
                                </button>

                                {{-- Like --}}
                                <button id="like-btn-{{ $media['id'] }}" data-like-btn
                                        data-base="{{ $media['likes_base'] }}" data-liked="{{ $media['liked'] ? 'true' : 'false' }}"
                                        aria-pressed="{{ $media['liked'] ? 'true' : 'false' }}" aria-label="Suka"
                                        class="flex items-center gap-1.5 text-[13px] font-medium transition-colors cursor-pointer
                                               {{ $media['liked'] ? 'text-red-500' : 'text-gray-400 hover:text-red-400' }}">
                                    <x-icon-like fill="{{ $media['liked'] ? 'currentColor' : 'none' }}" />
                                    <span data-like-count>{{ $media['likes_label'] }}</span>
                                </button>

                                {{-- Bookmark & Share --}}
                                <div class="ml-auto flex items-center gap-2">
                                    <button id="bookmark-media-btn-{{ $media['id'] }}" data-bookmark-btn aria-pressed="{{ !empty($media['bookmarked']) && $media['bookmarked'] ? 'true' : 'false' }}" aria-label="Simpan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-yellow-500 transition-colors cursor-pointer {{ !empty($media['bookmarked']) && $media['bookmarked'] ? 'text-yellow-500' : '' }}"><x-icon-bookmark fill="{{ !empty($media['bookmarked']) && $media['bookmarked'] ? 'currentColor' : 'none' }}" /></button>
                                <div class="relative">
                                    <button id="share-media-btn-{{ $media['id'] }}" data-share-btn aria-label="Bagikan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-50 transition-colors cursor-pointer"><x-icon-share fill="none" /></button>
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
                            <div data-comments-panel class="comments-section hidden mt-4 border-t border-gray-100 pt-4"
                                 id="comments-section-{{ $media['id'] }}"
                                 data-comments-loaded="false"
                                 data-comments-url="{{ route('timeline_home.comments', $media['id']) }}"
                                 data-comments-store-url="{{ route('timeline_home.comments.store', $media['id']) }}">
                                <div class="flex flex-col gap-3 comments-list" data-comment-list id="comments-list-{{ $media['id'] }}">
                                    {{-- Loaded via AJAX --}}
                                </div>
                                <div class="mt-3 flex gap-2">
                                    <form data-comment-form class="flex gap-3 max-sm:gap-1.5 items-start w-full">
                                        <input type="text" data-comment-input
                                               class="flex-1 border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#444] comment-input"
                                               placeholder="Tulis komentar..."
                                               data-post-id="{{ $media['id'] }}">
                                        <button type="submit" data-comment-submit
                                                class="bg-[#FFDDAF] text-[#444] px-4 max-sm:px-2.5 py-2 rounded-lg font-bold text-sm max-sm:text-xs border-[1.5px] border-[#444] hover:bg-[#ffcf90] submit-comment-btn whitespace-nowrap"
                                                data-post-id="{{ $media['id'] }}">Kirim</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                        @empty
                        @if($hasBlockedMe)
                            <p class="text-center text-gray-400 py-8">{{ $user->name }} telah memblokir Anda.</p>
                        @elseif($isBlockedByMe)
                            <p class="text-center text-gray-400 py-8">Anda memblokir pengguna ini.</p>
                        @else
                            <p class="text-center text-gray-400 py-8">Belum ada media.</p>
                        @endif
                        @endforelse
                    </div>
                </article>
            </main>

            {{-- ===== RIGHT SIDEBAR — floating sticky card ===== --}}
            <x-timeline-sidebar-right
                searchPlaceholder="Cari buku atau pengguna..."
                trendingTitle="Populer Minggu Ini"
                :trendingItems="$trendingItems"
            />

        </div>
    </div>

    <div id="follow-modal-overlay" class="fixed inset-0 z-999 bg-black/50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200">
        <div id="follow-modal" class="bg-white rounded-2xl border-[1.5px] border-text w-full max-w-md mx-4 max-h-[80vh] flex flex-col shadow-xl opacity-0 translate-y-4 transition-all duration-200">
            <div class="flex items-center justify-between px-5 pt-5 pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-[#222]">Mengikuti &amp; Pengikut</h3>
                <button type="button" id="follow-modal-close" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-text hover:bg-gray-100 transition-colors cursor-pointer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <div class="flex border-b border-gray-100">
                <button type="button" id="follow-tab-following" data-follow-tab="following" class="flex-1 pb-3 pt-3 text-sm font-semibold text-center transition-colors cursor-pointer text-[#111]">
                    Mengikuti
                    <span class="block mx-auto mt-1 h-1 w-16 rounded-full bg-[#5DA9FF]"></span>
                </button>
                <button type="button" id="follow-tab-followers" data-follow-tab="followers" class="flex-1 pb-3 pt-3 text-sm font-semibold text-center transition-colors cursor-pointer text-gray-400 hover:text-gray-600">
                    Pengikut
                    <span class="block mx-auto mt-1 h-1 w-16 rounded-full bg-transparent"></span>
                </button>
            </div>

            <div id="follow-modal-body" class="flex-1 overflow-y-auto p-5 min-h-50">
                <div class="flex items-center justify-center h-32">
                    <div class="w-6 h-6 border-2 border-text border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>
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
                <h3 class="font-bold text-[13px] text-gray-400 uppercase tracking-wider mb-3">What's Trending</h3>
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
    <x-timeline-bottom-nav active="profil" />

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

    <script>
        async function handleReportUserProfile(userId, name) {
            const reason = prompt(`Laporkan ${name}?\nTuliskan alasanmu (opsional):`);
            if (reason === null) return;

            try {
                const res = await fetch(`/api/users/${userId}/report`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason })
                });
                alert(res.ok ? 'Laporan telah dikirim. Terima kasih.' : 'Gagal mengirim laporan. Coba lagi.');
            } catch {
                alert('Terjadi kesalahan. Coba lagi.');
            }
        }

        async function handleBlockUserProfile(userId, name) {
            if (!confirm(`Yakin ingin mengubah status blokir untuk ${name}?`)) return;

            try {
                const res = await fetch(`/api/users/${userId}/block`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.action === 'blocked') {
                        alert(`${name} telah diblokir.`);
                    } else {
                        alert(`Blokir untuk ${name} telah dibuka.`);
                    }
                    window.location.reload(); // Reload to reflect changes (hide/show posts and buttons)
                } else {
                    alert('Gagal memproses permintaan.');
                }
            } catch {
                alert('Terjadi kesalahan. Coba lagi.');
            }
        }
    </script>
</body>
</html>