<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard — Alinea</title>
    <meta name="description" content="Kelola profil, koleksi buku, dan transaksi peminjamanmu di Alinea." />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @if(config('services.google_books.key'))
    <meta name="google-books-key" content="{{ config('services.google_books.key') }}" />
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/dashboard.js'])
</head>
<body class="font-['Poppins'] bg-gray-100 text-[#444] leading-relaxed overflow-x-hidden">

    <x-navbar></x-navbar>

    <main class="pt-20 pb-16">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6">

            {{-- ═══ PAGE HEADER ═══ --}}
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-black text-[#444] tracking-[-0.02em]">Akun Alinea</h1>
            </div>

            <div class="flex flex-col lg:flex-row gap-6">

                {{-- ═══ LEFT SIDEBAR ═══ --}}
                <aside class="w-full lg:w-[260px] flex-shrink-0">
                    <div class="lg:sticky lg:top-24 flex flex-col gap-4">

                        {{-- Profile Card (populated by JS from CURRENT_USER) --}}
                        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5 text-center">
                            <div id="profile-avatar-wrapper" class="w-20 h-20 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] mx-auto flex items-center justify-center mb-3 overflow-hidden cursor-pointer">
                                <span id="profile-initial" class="text-2xl font-black text-[#444]/70"></span>
                                <img id="profile-avatar-img" class="hidden w-full h-full object-cover" src="" alt="Avatar" />
                            </div>
                            <h2 id="sidebar-name" class="font-bold text-[15px]"></h2>
                            <p id="sidebar-username" class="text-[11px] text-gray-300 font-mono"></p>
                            <p class="text-xs text-gray-400 mt-0.5 flex items-center justify-center gap-1">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span id="sidebar-location"></span>
                            </p>
                            <div class="border-t border-gray-100 mt-4 pt-3 grid grid-cols-3 gap-1 text-center">
                                <div><p id="stat-koleksi" class="font-black text-lg">0</p><p class="text-[10px] text-gray-400 uppercase tracking-wider">Koleksi</p></div>
                                <div><p id="stat-pengajuan" class="font-black text-lg">0</p><p class="text-[10px] text-gray-400 uppercase tracking-wider">Pengajuan</p></div>
                                <div><p id="stat-transaksi" class="font-black text-lg">0</p><p class="text-[10px] text-gray-400 uppercase tracking-wider">Riwayat</p></div>
                            </div>
                        </div>

                        {{-- Navigation --}}
                        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-3 flex flex-row lg:flex-col gap-1 overflow-x-auto">
                            {{-- Mobile toggle (hidden on desktop) --}}
                            @php
                            $tabs = [
                                ['key'=>'personal','icon'=>'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>','label'=>'Informasi Pribadi'],
                                ['key'=>'security','icon'=>'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>','label'=>'Keamanan'],
                                ['key'=>'pengajuan','icon'=>'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>','label'=>'Pengajuan Pinjam'],
                                ['key'=>'transaksi','icon'=>'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>','label'=>'Riwayat Peminjaman'],
                                ['key'=>'katalog','icon'=>'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>','label'=>'Katalog Buku'],
                                ['key'=>'tersimpan','icon'=>'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>','label'=>'Buku Tersimpan'],
                            ];
                            @endphp
                            @foreach($tabs as $i => $tab)
                            <button data-tab-btn="{{ $tab['key'] }}"
                                    class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-left transition-all duration-200 cursor-pointer whitespace-nowrap text-sm
                                           {{ $i===0 ? 'bg-[#FFDDAF] text-[#444] font-bold' : 'text-gray-400 font-medium hover:bg-gray-50' }}">
                                <span class="w-5 h-5 flex items-center justify-center flex-shrink-0">{!! $tab['icon'] !!}</span>
                                {{ $tab['label'] }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </aside>

                {{-- ═══ MAIN CONTENT ═══ --}}
                <div class="flex-1 min-w-0">

                    {{-- ━━━ TABS ━━━ --}}
                    <x-dashboard.tab-personal />
                    <x-dashboard.tab-security />
                    <x-dashboard.tab-pengajuan />
                    <x-dashboard.tab-transaksi />
                    <x-dashboard.tab-katalog />
                    <x-dashboard.tab-tersimpan />

                </div>
            </div>
        </div>
    </main>

    <x-dashboard.add-book-modal />

    {{-- Toast --}}
    <x-avatar-crop-modal />
    <div class="fixed bottom-6 left-6 z-[300] flex flex-col gap-2" id="toastContainer"></div>

    <style>
        @keyframes fadeInUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
        @keyframes spin { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }
    </style>

    <script>
        window.__FEATURED_BOOKS__ = {!! json_encode($featuredBooks->map(fn($b) => [
            'id' => $b->id,
            'judul' => $b->judul,
            'penulis' => $b->penulis,
            'tahun' => $b->tahun,
            'sinopsis' => $b->sinopsis,
            'genres' => $b->genres ?? [],
            'cover' => $b->cover_url,
            'isbn' => $b->isbn,
            'jumlah_halaman' => $b->jumlah_halaman,
            'kategori' => $b->kategori ?? 'Fiksi',
            'gradient_from' => $b->gradient_from,
            'gradient_to' => $b->gradient_to,
        ])->values()) !!};
    </script>

    <script>
        window.CURRENT_USER = {!! json_encode(array_merge($user->only(['id', 'name', 'username', 'email', 'kota', 'no_telp', 'created_at']), ['foto_profil' => $user->avatar_url])) !!};
        window.CURRENT_USER.nama = window.CURRENT_USER.name;
        window.CURRENT_USER.preferred_genres = {!! json_encode($user->preferred_genres ?? []) !!};
        window.CURRENT_USER.member_since = window.CURRENT_USER.created_at ? window.CURRENT_USER.created_at.substring(0, 10) : '';
        delete window.CURRENT_USER.created_at;
    </script>
</body>
</html>
