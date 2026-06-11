<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — Notifikasi</title>
    <meta name="description" content="Lihat semua aktivitas, suka, komentar, dan interaksi akun Anda di Alinea." />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/timeline.js'])
</head>

<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">

    <x-navbar></x-navbar>

    <div class="min-h-screen pt-16">
        <div class="flex items-start gap-6 max-w-300 mx-auto px-4 py-6 max-md:pb-24">

            <x-timeline-sidebar />

            <main class="bg-white border-[1.5px] border-[#444] rounded-2xl overflow-hidden flex flex-col flex-1">
                
                {{-- Header Halaman Notifikasi --}}
                <div class="border-b-[1.5px] border-[#444] bg-[#FFDDAF] px-5 py-4 flex items-center justify-between">
                    <h1 class="text-lg font-bold text-[#444]">Notifikasi</h1>
                </div>

                {{-- LIST NOTIFIKASI CONTAINER --}}
                <div class="divide-y-[1.5px] divide-gray-200">
                    
                    @foreach ($notifications as $notification)
                    @php
                        $notif = $notification->data;
                        $time = \Carbon\Carbon::parse($notification->created_at)->locale('id')->translatedFormat('d F Y, H:i');
                        $dynamicUser = isset($notif['user_id']) ? ($notificationUsers[$notif['user_id']] ?? null) : null;
                        $dynamicAvatar = $dynamicUser ? $dynamicUser->foto_profil : null;
                        $finalAvatar = $dynamicAvatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($dynamicAvatar) ? $dynamicAvatar : null;
                        $finalName = $dynamicUser ? $dynamicUser->name : ($notif['user_name'] ?? 'Sistem');
                    @endphp
                    <div class="p-4 hover:bg-gray-50 transition-colors flex gap-4 items-start {{ (isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended'])) ? 'bg-red-50' : ($notification->read_at ? '' : 'bg-blue-50/30') }}">
                        
                        {{-- Indikator Tipe Visual Kiri (Opsional untuk Icon/Warna) --}}
                        <div class="flex-shrink-0 pt-0.5">
                            @if(isset($notif['type']) && $notif['type'] === 'like')
                                <div class="w-2 h-2 rounded-full bg-red-500 mt-2"></div>
                            @elseif(isset($notif['type']) && $notif['type'] === 'comment')
                                <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                            @elseif(isset($notif['type']) && $notif['type'] === 'follow')
                                <div class="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                            @elseif(isset($notif['type']) && $notif['type'] === 'borrow')
                                <div class="w-2 h-2 rounded-full bg-purple-500 mt-2"></div>
                            @elseif(isset($notif['type']) && $notif['type'] === 'return')
                                <div class="w-2 h-2 rounded-full bg-teal-500 mt-2"></div>
                            @elseif(isset($notif['type']) && $notif['type'] === 'content_restored')
                                <div class="w-2 h-2 rounded-full bg-emerald-500 mt-2"></div>
                            @elseif(isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended']))
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-red-600 mt-1"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            @else
                                <div class="w-2 h-2 rounded-full bg-amber-500 mt-2"></div>
                            @endif
                        </div>

                        {{-- Isi Konten --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                {{-- Avatar User / Sistem --}}
                                @if(isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended']))
                                <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center bg-red-100 text-red-600">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                                </div>
                                @elseif($finalAvatar)
                                <div class="w-7 h-7 rounded-full border border-[#444] flex-shrink-0 overflow-hidden">
                                    <img src="{{ asset('storage/' . $finalAvatar) }}" alt="Avatar" class="w-full h-full object-cover">
                                </div>
                                @else
                                <div class="w-7 h-7 rounded-full border border-[#444] flex-shrink-0"
                                     style="background: linear-gradient(135deg, #FFDDAF, #C7E7FF)">
                                </div>
                                @endif
                                {{-- Info Pengirim --}}
                                <span class="font-bold text-sm {{ (isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended'])) ? 'text-red-700' : 'text-[#444]' }}">{{ (isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended'])) ? 'Peringatan Admin' : $finalName }}</span>
                                <span class="text-xs text-gray-400 ml-auto flex-shrink-0">{{ $time }}</span>
                            </div>
                            
                            {{-- Teks Detail Notifikasi --}}
                            <p class="text-xs leading-relaxed pl-9 break-words {{ (isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended'])) ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                @if(isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended']))
                                    {{ $notif['message'] }}
                                @else
                                    <strong>{{ $finalName }}</strong> {{ $notif['body'] ?? '' }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @endforeach

                    @if($notifications->isEmpty())
                        <div class="p-8 text-center text-gray-500">
                            <p>Belum ada notifikasi.</p>
                        </div>
                    @endif

                </div>
            </main>

        </div>
    </div>

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
                    <div class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                        <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">1</span>
                        <div>
                            <span class="font-bold text-[13px] leading-tight block">Harry Potter</span>
                            <span class="text-[11px] text-gray-400">J.K. Rowling</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                        <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">2</span>
                        <div>
                            <span class="font-bold text-[13px] leading-tight block">Toko Kelontong Namiya</span>
                            <span class="text-[11px] text-gray-400">Keigo Higashino</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                        <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">3</span>
                        <div>
                            <span class="font-bold text-[13px] leading-tight block">Crime &amp; Punishment</span>
                            <span class="text-[11px] text-gray-400">Fyodor Dostoyevsky</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                        <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">4</span>
                        <div>
                            <span class="font-bold text-[13px] leading-tight block">The Silent Voice</span>
                            <span class="text-[11px] text-gray-400">Naoko Yamada</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                        <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">5</span>
                        <div>
                            <span class="font-bold text-[13px] leading-tight block">Your Name</span>
                            <span class="text-[11px] text-gray-400">Makoto Shinkai</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-timeline-bottom-nav active="notifikasi" />

</body>
</html>