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
    <div class="min-h-screen pt-14">
        <div class="flex items-start gap-6 max-w-[1200px] mx-auto px-4 py-6">

            {{-- ===== LEFT SIDEBAR — floating sticky card ===== --}}
            <x-timeline-sidebar />

            {{-- ===== CENTER — FEED COLUMN ===== --}}
            <main class="flex-1 min-w-0 flex flex-col gap-4">

                

                {{-- Profile Header --}}
                <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6">
                    <div class="flex gap-6 items-start">
                        <div class="w-28 h-28 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0 overflow-hidden flex items-center justify-center">
                            @if($user->foto_profil)
                            <img src="{{ Storage::disk('public')->url($user->foto_profil) }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                            <span class="text-4xl font-black text-text/60">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>

                        <div class="flex-1">
                            <div class="flex items-center gap-4">
                                <div>
                                    <h2 class="text-2xl font-bold text-[#222]">{{ $user->name }}</h2>
                                    <p class="text-sm text-gray-500">{{ $user->username ? '@' . $user->username : 'tanpa_username' }}</p>
                                </div>

                                @auth
                                    @if($isOwnProfile)
                                        <a href="{{ route('profile.edit') }}"
                                           class="ml-auto px-4 py-2 bg-[#FFDDAF] border-2 border-[#444] rounded-full text-sm font-bold hover:bg-[#ffcf90] transition-colors">
                                            Edit Profil
                                        </a>
                                    @else
                                        <button id="follow-btn"
                                                data-follow-url="{{ route('profile.follow', $user) }}"
                                                data-following="{{ $isFollowing ? 'true' : 'false' }}"
                                                data-following-count="{{ $followingCount }}"
                                                class="ml-auto px-5 py-2 rounded-full text-sm font-bold border-2 border-[#444] transition-colors cursor-pointer
                                                       {{ $isFollowing ? 'bg-[#444] text-white' : 'bg-[#FFDDAF] hover:bg-[#ffcf90]' }}">
                                            {{ $isFollowing ? 'Mengikuti' : 'Pengikut' }}
                                        </button>
                                    @endif
                                @endauth
                            </div>

                            @if($user->deskripsi)
                                <p class="mt-4 text-base text-[#333]">{{ $user->deskripsi }}</p>
                            @endif

                            <p class="text-sm text-gray-500 mt-2">
                                <button type="button" id="profile-following-trigger" data-user-id="{{ $user->id }}" class="hover:underline cursor-pointer text-left">
                                    <span class="font-bold text-[#222]">{{ $followingCount }}</span> Mengikuti
                                </button>
                                <span class="mx-2">|</span>
                                <button type="button" id="profile-followers-trigger" data-user-id="{{ $user->id }}" class="hover:underline cursor-pointer text-left">
                                    <span class="font-bold text-[#222]">{{ $followersCount }}</span> Pengikut
                                </button>
                            </p>
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

                    {{-- Tab: Unggahan --}}
                    <div id="profile-feed-panel" data-profile-panel="unggahan" class="mt-5 flex flex-col gap-5" role="tabpanel" aria-labelledby="tab-for-you">
                        @forelse ($posts as $post)
                        <article class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0" data-post-id="{{ $post['id'] }}">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0 overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center">
                                    @if($post['avatar_url'])
                                        <img src="{{ $post['avatar_url'] }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs font-bold text-[#444]">{{ strtoupper(substr($post['name'], 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="font-bold text-[15px] leading-tight">{{ $post['name'] }}</span>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                        <span>{{ $post['handle'] }}</span>
                                        <span class="text-gray-200">•</span>
                                        <span>{{ $post['location'] }}</span>
                                        <span class="text-gray-200">•</span>
                                        <span>{{ $post['time'] }}</span>
                                    </div>
                                </div>
                                <div class="bg-[#fff176] border-2 border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold flex-shrink-0">
                                    {{ $post['tag'] }}
                                </div>
                            </div>

                            @if($post['book'])
                            <div class="inline-flex items-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold mb-3">
                                {{ $post['book'] }}
                            </div>
                            @endif

                            <p class="text-sm text-gray-600 leading-relaxed mb-4">{{ $post['body'] }}</p>

                            {{-- Media --}}
                            @if(!empty($post['attachments']))
                                @php
                                    $galleryImages = collect($post['attachments'])->filter(fn ($attachment) => ($attachment['type'] ?? '') === 'image')->values();
                                    $otherAttachments = collect($post['attachments'])->reject(fn ($attachment) => ($attachment['type'] ?? '') === 'image')->values();
                                @endphp

                                @if($galleryImages->count() > 1)
                                    <div class="mb-4" data-media-gallery data-media-gallery-count="{{ $galleryImages->count() }}">
                                        <div class="relative">
                                            <button type="button" data-gallery-prev aria-label="Sebelumnya" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/90 border border-gray-200 shadow flex items-center justify-center text-[#444] hover:bg-white">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"></path></svg>
                                            </button>
                                            <button type="button" data-gallery-next aria-label="Berikutnya" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/90 border border-gray-200 shadow flex items-center justify-center text-[#444] hover:bg-white">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"></path></svg>
                                            </button>
                                            <div data-media-track class="flex gap-3 overflow-x-auto snap-x snap-mandatory scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                                @foreach($galleryImages as $index => $attachment)
                                                    @php $attachmentUrl = $attachment['url'] ?? ($attachment['src'] ?? null); @endphp
                                                    <div class="w-full shrink-0 snap-center flex items-center justify-center rounded-2xl overflow-hidden">
                                                        <img src="{{ $attachmentUrl }}" alt="media {{ $index + 1 }}" class="w-full h-auto max-h-[560px] object-contain rounded-2xl shadow-sm mx-auto">
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div data-media-counter class="absolute bottom-2 right-2 rounded-full bg-black/60 px-2.5 py-1 text-[11px] font-semibold text-white">1/{{ $galleryImages->count() }}</div>
                                        </div>

                                        @foreach($otherAttachments as $attachment)
                                            @php $attachmentUrl = $attachment['url'] ?? ($attachment['src'] ?? null); @endphp
                                            @if(($attachment['type'] ?? '') === 'video')
                                                <div class="mt-4">
                                                    <video controls class="w-full max-w-[720px] mx-auto rounded-2xl">
                                                        <source src="{{ $attachmentUrl }}" type="video/mp4">
                                                    </video>
                                                </div>
                                            @elseif($attachmentUrl)
                                                <div class="mt-4">
                                                    <a href="{{ $attachmentUrl }}" download
                                                       class="inline-flex items-center gap-3 px-4 py-2 bg-white border-[1px] border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                                                        <span>{{ $attachment['original_name'] ?? $attachment['label'] ?? 'Unduh file' }}</span>
                                                    </a>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="space-y-4 mb-4">
                                        @foreach($post['attachments'] as $attachment)
                                            @php $attachmentUrl = $attachment['url'] ?? ($attachment['src'] ?? null); @endphp
                                            @if(($attachment['type'] ?? '') === 'image')
                                                <div>
                                                    <img src="{{ $attachmentUrl }}" alt="media" class="w-full max-w-[560px] h-auto rounded-2xl shadow-sm mx-auto object-contain">
                                                </div>
                                            @elseif(($attachment['type'] ?? '') === 'video')
                                                <div>
                                                    <video controls class="w-full max-w-[720px] mx-auto rounded-2xl border-[1.5px] border-[#444] bg-black">
                                                        <source src="{{ $attachmentUrl }}" type="video/mp4">
                                                    </video>
                                                </div>
                                            @elseif($attachmentUrl)
                                                <div class="mb-3">
                                                    <a href="{{ $attachmentUrl }}" download
                                                       class="inline-flex items-center gap-3 px-4 py-2 bg-white border-[1px] border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                                                        <span>{{ $attachment['original_name'] ?? $attachment['label'] ?? 'Unduh file' }}</span>
                                                    </a>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @elseif($post['media_url'] && $post['media_type'] === 'image')
                                <div class="mb-4">
                                    <img src="{{ $post['media_url'] }}" alt="media" class="w-full max-w-[560px] h-auto rounded-2xl border-[1.5px] border-[#444] mx-auto object-contain bg-white">
                                </div>
                            @elseif($post['media_url'] && $post['media_type'] === 'video')
                                <div class="mb-4">
                                    <video controls class="w-full max-w-[720px] mx-auto rounded-2xl border-[1.5px] border-[#444] bg-black">
                                        <source src="{{ $post['media_url'] }}" type="video/mp4">
                                    </video>
                                </div>
                            @elseif($post['media_url'])
                                <div class="mb-3">
                                    <a href="{{ $post['media_url'] }}" download
                                       class="inline-flex items-center gap-3 px-4 py-2 bg-white border-[1px] border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                                        <span>{{ $post['media_original_name'] ?? 'Unduh file' }}</span>
                                    </a>
                                </div>
                            @endif

                            <div class="flex items-center gap-5 pt-3 border-t border-gray-100">
                                <button aria-label="Komentar" class="flex items-center gap-1.5 text-gray-400 text-[13px] font-medium hover:text-[#444] transition-colors cursor-pointer">
                                    <x-icon-comment fill="none" /><span>{{ $post['comments'] }}</span>
                                </button>
                                <button data-like-btn data-base="{{ $post['likes_base'] }}" data-liked="{{ $post['liked'] ? 'true' : 'false' }}"
                                        aria-pressed="{{ $post['liked'] ? 'true' : 'false' }}" aria-label="Suka"
                                        class="flex items-center gap-1.5 text-[13px] font-medium transition-colors cursor-pointer {{ $post['liked'] ? 'text-red-500' : 'text-gray-400 hover:text-red-400' }}">
                                    <x-icon-like fill="{{ $post['liked'] ? 'currentColor' : 'none' }}" /><span data-like-count>{{ $post['likes_label'] }}</span>
                                </button>
                                <div class="ml-auto flex items-center gap-2">
                                    <button id="bookmark-btn-{{ $post['id'] }}" data-bookmark-btn aria-pressed="{{ !empty($post['bookmarked']) && $post['bookmarked'] ? 'true' : 'false' }}" aria-label="Simpan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-yellow-500 transition-colors cursor-pointer {{ !empty($post['bookmarked']) && $post['bookmarked'] ? 'text-yellow-500' : '' }}"><x-icon-bookmark fill="{{ !empty($post['bookmarked']) && $post['bookmarked'] ? 'currentColor' : 'none' }}" /></button>
                                    <button id="share-btn-{{ $post['id'] }}" data-share-btn aria-label="Bagikan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer"><x-icon-share fill="none" /></button>
                                </div>
                            </div>
                        </article>
                        @empty
                        <p class="text-center text-gray-400 py-8">Belum ada unggahan.</p>
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
                                        <p class="text-xs text-gray-400 mt-1">Diperoleh {{ \Carbon\Carbon::parse($achievement->pivot->earned_at)->diffForHumans() }}</p>
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
                        {{-- Sedang Dibaca --}}
                        <section>
                            <div class="flex items-end justify-between mb-3">
                                <h5 class="text-sm font-bold text-[#222]">Sedang Dibaca</h5>
                                <span class="text-xs text-gray-400">Reading shelf</span>
                            </div>
                            @if($readingNow->isNotEmpty())
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach ($readingNow as $book)
                                <article class="group">
                                    <div class="relative aspect-[2/3] rounded-2xl border-2 border-[#444] overflow-hidden shadow-sm bg-gray-100">
                                        @if($book->cover_url)
                                        <img src="{{ $book->cover_url }}" alt="Sampul {{ $book->judul }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]">
                                        @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-400 text-sm p-4 text-center">{{ $book->judul }}</div>
                                        @endif
                                        <span class="absolute top-2 left-2 text-[10px] font-bold tracking-wide px-2 py-0.5 rounded-full border border-[#444] bg-white/90 text-[#333]">Reading now...</span>
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

                        {{-- Sudah Dibaca --}}
                        <section>
                            <div class="flex items-end justify-between mb-3">
                                <h3 class="text-sm font-bold text-[#222]">Sudah Dibaca</h3>
                                <span class="text-xs text-gray-400">Finished shelf</span>
                            </div>
                            @if($finishedBooks->isNotEmpty())
                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                @foreach ($finishedBooks as $book)
                                <article class="group">
                                    <div class="aspect-[2/3] rounded-xl border-[1.5px] border-[#444] overflow-hidden bg-gray-100 transition-transform duration-200 group-hover:translate-y-[-2px]">
                                        @if($book->cover_url)
                                        <img src="{{ $book->cover_url }}" alt="Sampul {{ $book->judul }}" class="w-full h-full object-cover [filter:sepia(0.38)_saturate(0.8)_brightness(0.9)]">
                                        @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-400 text-xs p-2 text-center">{{ $book->judul }}</div>
                                        @endif
                                    </div>
                                    <p class="pt-1 text-[11px] font-medium text-gray-500 leading-tight line-clamp-1">{{ $book->judul }}</p>
                                </article>
                                @endforeach
                            </div>
                            @else
                            <p class="text-sm text-gray-400">Belum ada buku yang selesai dibaca.</p>
                            @endif
                        </section>
                    </div>

                    {{-- Tab: Media --}}
                    <div data-profile-panel="media" class="hidden mt-5 flex flex-col gap-5">
                        @forelse ($mediaPosts as $media)
                        <article class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0" data-post-id="{{ $media['id'] }}">
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0 overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center">
                                    @if($media['avatar_url'])
                                        <img src="{{ $media['avatar_url'] }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs font-bold text-[#444]">{{ strtoupper(substr($media['name'], 0, 1)) }}</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="font-bold text-[15px] leading-tight">{{ $media['name'] }}</span>
                                    <div class="flex items-center gap-1.5 text-xs text-gray-400">
                                        <span>{{ $media['handle'] }}</span>
                                        <span class="text-gray-200">•</span>
                                        <span>{{ $media['time'] }}</span>
                                        @if($media['location'])<span class="text-gray-200">•</span><span>{{ $media['location'] }}</span>@endif
                                    </div>
                                </div>
                                <div class="bg-[#fff176] border-2 border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold flex-shrink-0">{{ $media['tag'] }}</div>
                            </div>

                            <p class="text-sm text-gray-600 leading-relaxed mb-3">{{ $media['caption'] }}</p>

                            @if(!empty($media['attachments']))
                                @php $imgs = array_filter($media['attachments'], fn($a) => $a['type'] === 'image'); @endphp
                                @if(count($imgs) === 1)
                                    @php $img = reset($imgs); @endphp
                                    <div class="mb-4">
                                        <img src="{{ asset('storage/' . $img['src']) }}" alt="media" class="w-full max-w-[420px] h-auto rounded-2xl border-[1.5px] border-[#444] mx-auto">
                                    </div>
                                @elseif(count($imgs) > 1)
                                    <div class="grid grid-cols-2 gap-2 mb-4">
                                        @foreach ($imgs as $img)
                                        <img src="{{ asset('storage/' . $img['src']) }}" alt="media" class="w-full h-auto rounded-xl border-[1.5px] border-[#444] object-cover">
                                        @endforeach
                                    </div>
                                @endif
                                @foreach ($media['attachments'] as $att)
                                    @if($att['type'] === 'video')
                                        <div class="mb-4">
                                            <video controls class="w-full max-w-[720px] mx-auto rounded-2xl border-[1.5px] border-[#444]">
                                                <source src="{{ asset('storage/' . $att['src']) }}" type="video/mp4">
                                            </video>
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

                            <div class="flex items-center gap-5 pt-2">
                                <button aria-label="Komentar" class="flex items-center gap-1.5 text-gray-400 text-[13px] font-medium hover:text-[#444] transition-colors cursor-pointer">
                                    <x-icon-comment fill="none" /><span>{{ $media['comments'] }}</span>
                                </button>
                                <button data-like-btn aria-pressed="{{ $media['liked'] ? 'true' : 'false' }}" aria-label="Suka"
                                        class="flex items-center gap-1.5 text-[13px] font-medium transition-colors cursor-pointer {{ $media['liked'] ? 'text-red-500' : 'text-gray-400 hover:text-red-400' }}">
                                    <x-icon-like fill="{{ $media['liked'] ? 'currentColor' : 'none' }}" /><span>{{ $media['likes_label'] }}</span>
                                </button>
                                <div class="ml-auto flex items-center gap-2">
                                    <button id="bookmark-media-btn-{{ $media['id'] }}" data-bookmark-btn aria-pressed="{{ !empty($media['bookmarked']) && $media['bookmarked'] ? 'true' : 'false' }}" aria-label="Simpan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-yellow-500 transition-colors cursor-pointer {{ !empty($media['bookmarked']) && $media['bookmarked'] ? 'text-yellow-500' : '' }}"><x-icon-bookmark fill="{{ !empty($media['bookmarked']) && $media['bookmarked'] ? 'currentColor' : 'none' }}" /></button>
                                    <button id="share-media-btn-{{ $media['id'] }}" data-share-btn aria-label="Bagikan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer"><x-icon-share fill="none" /></button>
                                </div>
                            </div>
                        </article>
                        @empty
                        <p class="text-center text-gray-400 py-8">Belum ada media.</p>
                        @endforelse
                    </div>
                </article>
            </main>

            {{-- ===== RIGHT SIDEBAR — floating sticky card ===== --}}
            <x-timeline-sidebar-right
                searchPlaceholder="Cari buku atau pengguna..."
                trendingTitle="What's Trending"
                :trendingItems="[
                    ['Harry Potter',          'J.K. Rowling'],
                    ['Toko Kelontong Namiya', 'Keigo Higashino'],
                    ['Crime & Punishment',    'Fyodor Dostoyevsky'],
                    ['The Silent Voice',      'Naoko Yamada'],
                    ['Your Name',             'Makoto Shinkai'],
                ]"
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