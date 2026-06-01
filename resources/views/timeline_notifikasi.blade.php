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

    {{-- ========== NAVBAR ========== --}}
    <x-navbar></x-navbar>

    {{-- ========== PAGE LAYOUT ========== --}}
    <div class="min-h-screen pt-16">
        <div class="flex items-start gap-6 max-w-300 mx-auto px-4 py-6 max-md:pb-24">

            {{-- ===== LEFT SIDEBAR — floating sticky card ===== --}}
            <x-timeline-sidebar />

            {{-- ===== MAIN CONTENT ===== --}}
            <main class="bg-white border-[1.5px] border-[#444] rounded-2xl overflow-hidden flex flex-col flex-1">
                
                {{-- Header Halaman Notifikasi --}}
                <div class="border-b-[1.5px] border-[#444] bg-[#FFDDAF] px-5 py-4 flex items-center justify-between">
                    <h1 class="text-lg font-bold text-[#444]">Notifikasi</h1>
                </div>

                {{-- LIST NOTIFIKASI CONTAINER --}}
                <div class="divide-y-[1.5px] divide-gray-200">
                    
                    @php
                    // Data simulasi interaksi general (Like, Comment, Follow, System/Community)
                    $notifications = [
                        [
                            'type' => 'like',
                            'avatar_from' => '#FFD2D2', 'avatar_to' => '#FFA3A3',
                            'user' => 'Dina Rahmawati',
                            'meta' => 'Menyukai Catatan Anda',
                            'body' => 'menyukai catatan kutipan buku Anda di "The Midnight Library".',
                            'time' => 'Baru saja'
                        ],
                        [
                            'type' => 'comment',
                            'avatar_from' => '#D4F6FF', 'avatar_to' => '#C7E7FF',
                            'user' => 'Budi Ashcroft',
                            'meta' => 'Mengomentari Postingan',
                            'body' => 'membalas ulasan Anda: "Setuju banget! Bab ke-3 benar-benar bikin plot twist yang gak disangka-sangka."',
                            'time' => '12 Menit Lalu'
                        ],
                        [
                            'type' => 'follow',
                            'avatar_from' => '#E2FFE2', 'avatar_to' => '#A8FFA8',
                            'user' => 'Ahmad Fauzan',
                            'meta' => 'Pengikut Baru',
                            'body' => 'mulai mengikuti Anda. Ikuti balik untuk mulai bertukar pesan dan berbagi rekomendasi buku!',
                            'time' => '2 Jam Lalu'
                        ],
                        [
                            'type' => 'community',
                            'avatar_from' => '#FFE8CC', 'avatar_to' => '#FFD4A3',
                            'user' => 'Klub Buku Horor Malang',
                            'meta' => 'Rekomendasi Komunitas',
                            'body' => 'baru saja dibuat! Berdasarkan preferensi membaca Anda, Anda mungkin tertarik untuk bergabung di komunitas ini.',
                            'time' => '30 Apr'
                        ]
                    ];
                    @endphp

                    @foreach ($notifications as $notif)
                    <div class="p-4 hover:bg-gray-50 transition-colors flex gap-4 items-start">
                        
                        {{-- Indikator Tipe Visual Kiri (Opsional untuk Icon/Warna) --}}
                        <div class="flex-shrink-0 pt-0.5">
                            @if($notif['type'] === 'like')
                                <div class="w-2 h-2 rounded-full bg-red-500 mt-2"></div>
                            @elseif($notif['type'] === 'comment')
                                <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                            @elseif($notif['type'] === 'follow')
                                <div class="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                            @else
                                <div class="w-2 h-2 rounded-full bg-amber-500 mt-2"></div>
                            @endif
                        </div>

                        {{-- Isi Konten --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                {{-- Avatar User / Sistem --}}
                                <div class="w-7 h-7 rounded-full border border-[#444] flex-shrink-0"
                                     style="background: linear-gradient(135deg, {{ $notif['avatar_from'] }}, {{ $notif['avatar_to'] }})">
                                </div>
                                {{-- Info Pengirim --}}
                                <span class="font-bold text-sm text-[#444]">{{ $notif['user'] }}</span>
                                @if($notif['meta'])
                                    <span class="text-xs text-gray-400 truncate">• {{ $notif['meta'] }}</span>
                                @endif
                                <span class="text-xs text-gray-400 ml-auto flex-shrink-0">{{ $notif['time'] }}</span>
                            </div>
                            
                            {{-- Teks Detail Notifikasi (Ditata presisi tanpa margin kiri yang terlalu menjorok) --}}
                            <p class="text-xs text-gray-600 leading-relaxed pl-9 break-words">
                                <strong>{{ $notif['user'] }}</strong> {{ $notif['body'] }}
                            </p>
                        </div>
                    </div>
                    @endforeach

                </div>
            </main>

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

    {{-- ===== MOBILE BOTTOM NAV ===== --}}
    <x-timeline-bottom-nav active="notifikasi" />

</body>
</html>