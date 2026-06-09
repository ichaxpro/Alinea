<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unggahan | Alinea</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    @vite(['resources/css/app.css', 'resources/js/timeline.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8F9FA; color: #333; margin: 0; padding: 0; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">
    <x-navbar />

    <div class="min-h-screen pt-16">
        <div class="flex items-start gap-6 max-w-300 mx-auto px-4 py-6 max-md:pb-24">
            <x-timeline-sidebar />

            <!-- MAIN FEED -->
            <main class="flex-1 min-w-0 flex flex-col gap-4">
            <div class="flex items-center gap-3 mb-6">
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('timeline_home') }}" class="w-10 h-10 bg-white border-[1.5px] border-[#444] rounded-full flex items-center justify-center hover:bg-gray-50 transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-black text-[#222]">Unggahan</h1>
            </div>

            <!-- Single Post -->
            <article class="bg-white border-[1.5px] border-[#444] rounded-3xl p-5 mb-4" data-post-id="{{ $post['id'] }}">
                <div class="flex items-start gap-4">
                    <a href="{{ url('/u/' . ltrim($post['handle'], '@')) }}" class="block shrink-0">
                        <div class="w-12 h-12 rounded-full border-[1.5px] border-[#444] bg-center bg-cover bg-no-repeat flex items-center justify-center text-[#444] font-bold text-lg overflow-hidden relative group" style="{{ $post['avatar_url'] ? 'background-image: url('.$post['avatar_url'].')' : 'background: linear-gradient(135deg, '.$post['avatar_from'].', '.$post['avatar_to'].')' }}">
                            @if(!$post['avatar_url']) {{ substr($post['name'], 0, 1) }} @endif
                            <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </div>
                    </a>

                    <div class="flex-1 min-w-0 pt-1">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div class="leading-tight truncate">
                                <a href="{{ url('/u/' . ltrim($post['handle'], '@')) }}" class="font-bold text-[#222] hover:underline text-[0.95rem] mr-1">{{ $post['name'] }}</a>
                                @if($post['klub'])
                                    <span class="text-[0.8rem] text-gray-500 font-medium">di <span class="font-semibold text-[#222]">{{ $post['klub'] }}</span></span>
                                @endif
                                <div class="flex items-center gap-1.5 text-[0.8rem] text-gray-500 mt-0.5">
                                    <span class="truncate max-w-[120px]">{{ $post['handle'] }}</span>
                                    <span>·</span>
                                    <span title="{{ $post['time'] }}">{{ $post['absolute_time'] }}</span>
                                </div>
                            </div>
                            
                            <!-- Post Actions Dropdown -->
                            <div class="relative">
                                <button class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors" data-post-menu-trigger>
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/><circle cx="12" cy="5" r="1"/></svg>
                                </button>
                                <div class="absolute right-0 top-full mt-1 w-48 bg-white border-[1.5px] border-[#444] rounded-xl overflow-hidden hidden z-[60]" data-post-menu-dropdown>
                                    @if(auth()->check() && auth()->id() === $post['user_id'])
                                        <button class="w-full px-4 py-2.5 text-left text-sm text-red-500 hover:bg-red-50 flex items-center gap-2 transition-colors" data-post-delete>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            Hapus Unggahan
                                        </button>
                                    @elseif(auth()->check() && auth()->id() !== $post['user_id'])
                                        <button class="w-full px-4 py-2.5 text-left text-sm text-red-500 hover:bg-red-50 transition-colors flex items-center gap-2" data-report-post-btn data-post-id="{{ $post['id'] }}">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                            Laporkan unggahan
                                        </button>
                                        <button class="w-full px-4 py-2.5 text-left text-sm text-[#222] hover:bg-gray-100 transition-colors flex items-center gap-2 border-t border-gray-100" data-unfollow-btn data-user-id="{{ $post['user_id'] }}">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line></svg>
                                            Berhenti mengikuti
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($post['book'])
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#FFDDAF] text-[#444] rounded-full text-[0.75rem] font-bold border-[1.5px] border-[#444] mb-3">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            {{ $post['book'] }}
                        </div>
                        @endif

                        <p class="text-[#222] text-[0.95rem] leading-relaxed mb-3 whitespace-pre-wrap">{{ $post['body'] }}</p>

                        @if(!empty($post['attachments']))
                            <div class="grid {{ count($post['attachments']) === 1 ? 'grid-cols-1' : (count($post['attachments']) === 2 ? 'grid-cols-2' : (count($post['attachments']) === 3 ? 'grid-cols-2' : 'grid-cols-2')) }} gap-2 mb-4">
                                @foreach($post['attachments'] as $index => $attachment)
                                    @php
                                        $isImage = $attachment['type'] === 'image' || str_starts_with($attachment['type'], 'image/');
                                        $isVideo = $attachment['type'] === 'video' || str_starts_with($attachment['type'], 'video/');
                                        $isThreeItemsAndFirst = (count($post['attachments']) === 3 && $index === 0);
                                    @endphp
                                    <div class="relative w-full overflow-hidden rounded-2xl border-[1.5px] border-[#444] cursor-pointer group {{ $isThreeItemsAndFirst ? 'col-span-2 aspect-[21/9]' : (count($post['attachments']) === 1 ? 'aspect-video' : 'aspect-square') }}" 
                                         data-media-url="{{ $attachment['url'] }}" 
                                         data-media-type="{{ $attachment['type'] }}">
                                        @if($isImage)
                                            <img src="{{ $attachment['url'] }}" alt="Attached Media" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                        @elseif($isVideo)
                                            <video src="{{ $attachment['url'] }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" preload="metadata"></video>
                                            <div class="absolute inset-0 bg-black/20 flex items-center justify-center group-hover:bg-black/30 transition-colors pointer-events-none">
                                                <div class="w-12 h-12 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center border border-white/20 shadow-lg group-hover:scale-110 transition-transform">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="#444" stroke="#444" stroke-width="2" stroke-linejoin="round" class="ml-1"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                                </div>
                                            </div>
                                        @else
                                            <div class="w-full h-full bg-gray-100 flex items-center justify-center p-4">
                                                <div class="text-center">
                                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mx-auto text-gray-400 mb-2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                                                    <span class="text-xs text-gray-500 font-medium truncate block max-w-full">{{ $attachment['original_name'] }}</span>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($post['media'])
                            <div class="relative w-full aspect-video overflow-hidden rounded-2xl border-[1.5px] border-[#444] mb-4 cursor-pointer group"
                                 data-media-url="{{ $post['media_url'] }}"
                                 data-media-type="{{ $post['media_type'] }}">
                                @if($post['media_type'] === 'video' || str_starts_with($post['media_type'], 'video/'))
                                    <video src="{{ $post['media_url'] }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"></video>
                                    <div class="absolute inset-0 bg-black/20 flex items-center justify-center group-hover:bg-black/30 transition-colors pointer-events-none">
                                        <div class="w-12 h-12 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center border border-white/20 shadow-lg group-hover:scale-110 transition-transform">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="#444" stroke="#444" stroke-width="2" stroke-linejoin="round" class="ml-1"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                        </div>
                                    </div>
                                @else
                                    <img src="{{ $post['media_url'] }}" alt="Post Media" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                @endif
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                            </div>
                        @endif

                        <div class="flex items-center gap-5 max-sm:gap-3 pt-3 mt-4 border-t border-gray-100">
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
                    </div>
                </div>

                <!-- Comments Panel (timeline.js hook) -->
                <div data-comments-panel class="hidden mt-4 pt-4 border-t border-gray-100" data-comments-loaded="false" data-comments-url="/timeline_home/posts/{{ $post['id'] }}/comments" data-comments-store-url="/timeline_home/posts/{{ $post['id'] }}/comments">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-bold text-[#444]">Balasan</h4>
                        <span class="text-xs text-gray-400"></span>
                    </div>
                    <div data-comment-list class="space-y-3 mb-4"></div>
                    @auth
                    <form data-comment-form class="flex gap-3 items-start">
                        <div class="w-9 h-9 rounded-full border border-[#444] overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center flex-shrink-0">
                            @if(Auth::user()->foto_profil || Auth::user()->avatar_url)
                                <img src="{{ Auth::user()->foto_profil ? asset('storage/' . Auth::user()->foto_profil) : Auth::user()->avatar_url }}" class="w-full h-full object-cover">
                            @else
                                <span class="font-bold text-sm text-[#444]">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <textarea data-comment-input rows="1" maxlength="500" placeholder="Tulis balasan..." class="w-full border-[1.5px] border-gray-200 rounded-xl px-3 py-2 text-sm placeholder-gray-300 outline-none focus:border-[#444] resize-none transition-colors overflow-hidden"></textarea>
                            <input type="file" data-comment-media-input class="hidden" accept="image/*,video/*,*/*" multiple />
                            <div class="flex flex-wrap items-center justify-between gap-2 mt-2">
                                <div class="flex items-center gap-2">
                                    <button type="button" data-comment-media-trigger="image" aria-label="Unggah gambar komentar" title="Unggah gambar" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </button>
                                    <button type="button" data-comment-media-trigger="video" aria-label="Unggah video komentar" title="Unggah video" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                                    </button>
                                    <span data-comment-media-label class="text-xs text-gray-500 font-medium truncate max-w-[150px]"></span>
                                </div>
                                <button type="submit" data-comment-submit class="px-4 py-1.5 bg-[#FFDDAF] border-[1.5px] border-[#444] text-[#444] text-xs font-bold rounded-full hover:bg-[#ffcf90] transition-colors shadow-[2px_2px_0_0_rgba(68,68,68,1)] disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                                    Kirim
                                </button>
                            </div>
                        </div>
                    </form>
                    @endauth
                </div>
            </article>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // Simulasikan klik pada tombol komentar agar timeline.js memuat balasan.
                    setTimeout(() => {
                        const toggleBtn = document.querySelector('[data-comment-toggle]');
                        if(toggleBtn) {
                            toggleBtn.click();
                        }
                    }, 50);
                });
            </script>
        </main>
        </div>
    </div>
    
    <x-timeline-bottom-nav active="" />
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
