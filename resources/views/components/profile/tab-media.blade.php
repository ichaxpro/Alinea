@props(['mediaPosts', 'user', 'hasBlockedMe', 'isBlockedByMe', 'isOwnProfile'])

<div data-profile-panel="media" class="hidden mt-5 flex flex-col gap-5">
    @forelse ($mediaPosts as $media)
    <article class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0 cursor-pointer" data-post-id="{{ $media['id'] }}" data-href="{{ route('timeline.post', $media['id']) }}">
        <div class="flex items-start gap-3 mb-3">
            <a href="{{ $media['profile_url'] ?? '#' }}" class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0 overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity">
                @if($media['avatar_url'])
                    <img src="{{ $media['avatar_url'] }}" alt="" class="w-full h-full object-cover">
                @else
                    <span class="text-xs font-bold text-[#444]">{{ strtoupper(substr($media['name'], 0, 1)) }}</span>
                @endif
            </a>
            <div class="flex-1 min-w-0">
                <a href="{{ $media['profile_url'] ?? '#' }}" class="font-bold text-[15px] leading-tight hover:underline cursor-pointer block truncate">{{ $media['name'] }}</a>
                <div class="flex items-center text-xs text-gray-400 mt-0.5 min-w-0">
                    <a href="{{ $media['profile_url'] ?? '#' }}" class="hover:underline cursor-pointer shrink truncate">{{ $media['handle'] }}</a>
                    <span class="text-gray-200 mx-1 shrink-0">•</span>
                    @if($media['location'])
                    <span class="inline-flex items-center gap-0.5 shrink truncate max-sm:hidden">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span class="truncate">{{ $media['location'] }}</span>
                    </span>
                    <span class="text-gray-200 mx-1 shrink-0 max-sm:hidden">•</span>
                    @endif
                    <span title="{{ $media['absolute_time'] }}" class="whitespace-nowrap shrink-0">{{ $media['time'] }}</span>
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
              data-comments-limit="5"
              data-comments-url="{{ route('timeline_home.comments', $media['id']) }}"
              data-comments-store-url="{{ route('timeline_home.comments.store', $media['id']) }}">
            <div class="flex flex-col gap-3 comments-list" data-comment-list id="comments-list-{{ $media['id'] }}">
                {{-- Loaded via AJAX --}}
            </div>
            <div class="mt-3 flex gap-2">
                <form data-comment-form class="flex gap-3 max-sm:gap-1.5 items-start w-full">
                    <input type="text" data-comment-input
                           class="flex-1 min-w-0 border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#444] comment-input"
                           placeholder="Tulis komentar..."
                           data-post-id="{{ $media['id'] }}">
                    <button type="submit" data-comment-submit
                            class="w-9 h-9 flex items-center justify-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full hover:bg-[#ffcf90] transition-colors shrink-0 submit-comment-btn"
                            data-post-id="{{ $media['id'] }}" aria-label="Kirim">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12L2 22l3-10L2 2z"></path><line x1="5" y1="12" x2="14" y2="12"></line></svg>
                    </button>
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
