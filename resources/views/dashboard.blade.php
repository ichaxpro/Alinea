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

                    {{-- ━━━ PANEL: Personal Information ━━━ --}}
                    <div data-tab-panel="personal">
                        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 md:p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-xl bg-[#FFDDAF]/30 flex items-center justify-center">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div>
                                    <h2 class="font-bold text-lg">Informasi Pribadi</h2>
                                    <p class="text-xs text-gray-400">Perbarui data profilmu</p>
                                </div>
                            </div>

                            <form id="profile-form" class="space-y-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label for="prof-nama" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Nama Lengkap</label>
                                        <input type="text" id="prof-nama" name="nama"
                                               class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" />
                                    </div>
                                    <div>
                                        <label for="prof-email" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Email</label>
                                        <input type="email" id="prof-email" disabled
                                               class="w-full border-[1.5px] border-gray-100 rounded-xl px-4 py-3 text-sm bg-gray-50 text-gray-400 cursor-not-allowed" />
                                    </div>
                                    <div>
                                        <label for="prof-kota" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Kota</label>
                                        <input type="text" id="prof-kota" name="kota"
                                               class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" />
                                    </div>
                                    <div>
                                        <label for="prof-telp" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">No. Telepon</label>
                                        <input type="tel" id="prof-telp" name="no_telp"
                                               class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Genre Favorit <span class="font-normal text-gray-300">(maks. 5)</span></label>
                                    <div id="genre-picker" class="flex flex-wrap gap-2"></div>
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="bg-[#FFDDAF] text-[#444] font-bold text-sm px-8 py-3 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors cursor-pointer">
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ━━━ PANEL: Security ━━━ --}}
                    <div data-tab-panel="security" class="hidden">
                        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 md:p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-xl bg-[#FFDDAF]/30 flex items-center justify-center">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                </div>
                                <div>
                                    <h2 class="font-bold text-lg">Keamanan</h2>
                                    <p class="text-xs text-gray-400">Ubah kata sandi akunmu</p>
                                </div>
                            </div>

                            <form id="security-form" class="max-w-md space-y-5">
                                @foreach([
                                    ['id'=>'pw-current','label'=>'Kata Sandi Saat Ini','placeholder'=>'Masukkan kata sandi saat ini'],
                                    ['id'=>'pw-new','label'=>'Kata Sandi Baru','placeholder'=>'Minimal 8 karakter'],
                                    ['id'=>'pw-confirm','label'=>'Konfirmasi Kata Sandi Baru','placeholder'=>'Ulangi kata sandi baru'],
                                ] as $field)
                                <div>
                                    <label for="{{ $field['id'] }}" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">{{ $field['label'] }}</label>
                                    <div class="relative">
                                        <input type="password" id="{{ $field['id'] }}" placeholder="{{ $field['placeholder'] }}"
                                               class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 pr-12 text-sm outline-none focus:border-[#444] transition-colors" />
                                        <button type="button" data-toggle-pw="{{ $field['id'] }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 hover:text-gray-500 transition-colors">
                                            <span class="eye-open"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></span>
                                            <span class="eye-closed hidden"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg></span>
                                        </button>
                                    </div>
                                </div>
                                @endforeach

                                <div class="bg-[#D4F6FF]/40 border-[1.5px] border-[#C7E7FF] rounded-xl p-4">
                                    <p class="text-xs font-bold text-[#444] mb-2">Syarat Kata Sandi:</p>
                                    <ul class="text-xs text-gray-500 space-y-1">
                                        <li class="flex items-center gap-2"><span class="text-gray-300">○</span> Minimal 8 karakter</li>
                                        <li class="flex items-center gap-2"><span class="text-gray-300">○</span> Kombinasi huruf dan angka direkomendasikan</li>
                                    </ul>
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="bg-[#FFDDAF] text-[#444] font-bold text-sm px-8 py-3 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors cursor-pointer">
                                        Ubah Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ━━━ PANEL: Pengajuan Pinjam ━━━ --}}
                    <div data-tab-panel="pengajuan" class="hidden">
                        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 md:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#FFDDAF]/30 flex items-center justify-center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                                    </div>
                                    <div>
                                        <h2 class="font-bold text-lg">Pengajuan Pinjam</h2>
                                        <p class="text-xs text-gray-400">Permintaan peminjaman bukumu dari pengguna lain</p>
                                    </div>
                                </div>
                            </div>
                            <div id="pengajuan-list" class="space-y-3"></div>
                        </div>
                    </div>

                    {{-- ━━━ PANEL: Riwayat Peminjaman ━━━ --}}
                    <div data-tab-panel="transaksi" class="hidden">
                        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 md:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#FFDDAF]/30 flex items-center justify-center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    </div>
                                    <div>
                                        <h2 class="font-bold text-lg">Riwayat Peminjaman</h2>
                                        <p class="text-xs text-gray-400">Riwayat peminjaman bukumu</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Filter pills --}}
                            <div class="flex flex-wrap gap-2 mb-6">
                                @foreach([
                                    ['key'=>'all','label'=>'Semua'],
                                    ['key'=>'pending','label'=>'Pengajuan'],
                                    ['key'=>'on_loan','label'=>'Dipinjam'],
                                    ['key'=>'overdue','label'=>'Terlambat'],
                                    ['key'=>'returned','label'=>'Dikembalikan'],
                                ] as $i => $f)
                                <button data-tx-filter="{{ $f['key'] }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs border-[1.5px] transition-all duration-200 cursor-pointer
                                               {{ $i===0 ? 'bg-[#FFDDAF] border-[#444] text-[#444] font-bold' : 'bg-white border-gray-200 text-gray-400 font-medium hover:border-gray-400' }}">
                                    {{ $f['label'] }}
                                    <span data-tx-count="{{ $f['key'] }}" class="bg-white/60 px-1.5 py-0.5 rounded-full text-[10px] font-bold">0</span>
                                </button>
                                @endforeach
                            </div>

                            <div id="tx-list" class="space-y-3"></div>
                        </div>
                    </div>

                    {{-- ━━━ PANEL: Katalog ━━━ --}}
                    <div data-tab-panel="katalog" class="hidden">
                        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 md:p-8">
                            <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-[#FFDDAF]/30 flex items-center justify-center">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                    </div>
                                    <div>
                                        <h2 class="font-bold text-lg">Katalog Buku</h2>
                                        <p class="text-xs text-gray-400">Kelola koleksi pribadi — <span id="catalog-count" class="font-medium">0 buku</span></p>
                                    </div>
                                </div>
                                <button id="btn-add-book" class="bg-[#FFDDAF] text-[#444] font-bold text-sm px-5 py-2.5 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors cursor-pointer flex items-center gap-2">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Tambah Buku
                                </button>
                            </div>

                            {{-- Search --}}
                            <div class="flex items-center gap-2 bg-gray-50 border-[1.5px] border-gray-200 rounded-xl px-4 py-2.5 mb-5 focus-within:border-[#444] transition-colors">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="search" id="catalog-search" placeholder="Cari buku di koleksimu..." class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" />
                            </div>

                            <div id="catalog-list"></div>
                        </div>
                    </div>

                    <div data-tab-panel="tersimpan" class="hidden">
                        <div class="bg-white border-[1.5px] border-text rounded-2xl p-6 md:p-8">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-xl bg-accent/30 flex items-center justify-center">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" /></svg>
                                </div>
                                <div>
                                    <h2 class="font-bold text-lg">Buku Tersimpan</h2>
                                    <p class="text-xs text-gray-400">Koleksi buku yang tersimpan</p>
                                </div>
                            </div>
                            <div id="bookmarks-list" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            </div>
                            <div id="bookmarks-empty" class="hidden text-center py-16">
                                <svg class="mx-auto mb-3 text-gray-200" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                <p class="text-sm text-gray-400 font-medium">Belum ada buku tersimpan</p>
                                <p class="text-xs text-gray-300 mt-1">Jelajahi katalog dan simpan buku favoritmu!</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    {{-- ═══ ADD BOOK MODAL ═══ --}}
    <div id="add-book-modal" class="hidden fixed inset-0 z-[200] bg-black/40 backdrop-blur-sm items-center justify-center p-4">
        <div class="bg-white rounded-2xl border-[1.5px] border-[#444] w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 md:p-8 relative animate-[fadeInUp_0.2s_ease]">
            <button id="close-add-book" class="absolute top-4 right-4 w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors cursor-pointer z-10">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="#444" stroke-width="2.5" stroke-linecap="round"><path d="M4 4l12 12M16 4L4 16"/></svg>
            </button>

            <h3 class="font-bold text-lg mb-1">Tambah Buku Baru</h3>
            <p class="text-xs text-gray-400 mb-5">Cari judul buku untuk mengisi otomatis, atau isi manual</p>

            {{-- Book Search Section --}}
            <div class="mb-5 relative" id="book-search-wrapper">
                <label for="book-search-input" class="block text-xs font-bold text-gray-500 mb-1.5 uppercase tracking-wider">Cari Buku</label>
                <div class="relative">
                    <div class="flex items-center gap-2 border-[1.5px] border-gray-200 rounded-xl px-4 py-3 focus-within:border-[#444] transition-colors bg-white">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" class="flex-shrink-0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="book-search-input" autocomplete="off" class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" placeholder="Ketik judul buku, penulis, atau ISBN..." />
                        <svg id="book-search-spinner" class="hidden animate-spin flex-shrink-0 text-gray-400" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    </div>

                    {{-- Search Results Dropdown --}}
                    <div id="book-search-results" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border-[1.5px] border-[#444] rounded-2xl shadow-xl z-50 max-h-[320px] overflow-y-auto">
                        {{-- Results will be injected by JS --}}
                    </div>
                </div>
                <p class="text-[11px] text-gray-300 mt-1.5">Minimal 3 karakter untuk mulai mencari</p>
            </div>

            {{-- Selected Book Preview (shown after picking from dropdown) --}}
            <div id="book-preview" class="hidden mb-5 bg-gradient-to-r from-[#D4F6FF]/30 to-[#FFDDAF]/20 border-[1.5px] border-[#444]/15 rounded-2xl p-4 transition-all duration-300">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold text-green-600 uppercase tracking-wider flex items-center gap-1">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        Buku Dipilih
                    </span>
                    <button type="button" id="btn-clear-selection" class="text-[11px] text-gray-400 hover:text-red-500 transition-colors cursor-pointer font-medium flex items-center gap-1">
                        <svg width="10" height="10" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M4 4l12 12M16 4L4 16"/></svg>
                        Hapus
                    </button>
                </div>
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <img id="preview-cover" src="" alt="Cover" class="w-[72px] h-[108px] rounded-lg border-[1.5px] border-[#444] object-cover bg-gray-100 shadow-sm" />
                        <div id="preview-cover-placeholder" class="hidden w-[72px] h-[108px] rounded-lg border-[1.5px] border-[#444] bg-gradient-to-br from-[#C7E7FF] to-[#D4F6FF] items-center justify-center">
                            <span class="text-xl font-black text-[#444]/40" id="preview-cover-initial"></span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p id="preview-title" class="font-bold text-[15px] text-[#444] leading-tight mb-1"></p>
                        <p id="preview-author" class="text-xs text-gray-500 mb-2"></p>
                        <div class="flex flex-wrap gap-1.5">
                            <span id="preview-year" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#FFDDAF]/50 text-[#444] border border-[#444]/15"></span>
                            <span id="preview-category" class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#C7E7FF]/50 text-[#444] border border-[#444]/15"></span>
                            <span id="preview-pages" class="hidden inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#D4F6FF]/50 text-[#444] border border-[#444]/15"></span>
                            <span id="preview-isbn" class="hidden inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500 border border-gray-200 font-mono"></span>
                        </div>
                        <p id="preview-desc" class="text-[11px] text-gray-400 mt-2 line-clamp-2 leading-relaxed"></p>
                    </div>
                </div>
            </div>

            {{-- Divider --}}
            <div class="flex items-center gap-3 mb-5">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">Detail Buku</span>
                <div class="flex-1 h-px bg-gray-200"></div>
            </div>

            <form id="add-book-form" class="space-y-4">
                <div>
                    <label for="add-book-judul" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Judul Buku <span class="text-red-400">*</span></label>
                    <input type="text" id="add-book-judul" name="judul" required class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" placeholder="Masukkan judul buku" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="add-book-penulis" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Penulis <span class="text-red-400">*</span></label>
                        <input type="text" id="add-book-penulis" name="penulis" required class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" placeholder="Nama penulis" />
                    </div>
                    <div>
                        <label for="add-book-isbn-manual" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">ISBN</label>
                        <input type="text" id="add-book-isbn-manual" name="isbn" class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors font-mono" placeholder="978-xxx-xxx" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="add-book-tahun" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Tahun Terbit</label>
                        <input type="number" id="add-book-tahun" name="tahun_terbit" min="1900" max="2030" class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" placeholder="2024" />
                    </div>
                    <div>
                        <label for="add-book-kategori" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Kategori</label>
                        <select id="add-book-kategori" name="kategori" class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors bg-white cursor-pointer">
                            <option value="Fiksi">Fiksi</option>
                            <option value="Non-Fiksi">Non-Fiksi</option>
                            <option value="Thriller">Thriller</option>
                            <option value="Misteri">Misteri</option>
                            <option value="Romansa">Romansa</option>
                            <option value="Sci-Fi">Sci-Fi</option>
                            <option value="Fantasi">Fantasi</option>
                            <option value="Horror">Horror</option>
                            <option value="Biografi">Biografi</option>
                            <option value="Sejarah">Sejarah</option>
                            <option value="Pengembangan Diri">Pengembangan Diri</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="add-book-halaman" class="block text-xs font-bold text-gray-500 mb-1 uppercase tracking-wider">Jumlah Halaman</label>
                        <input type="number" id="add-book-halaman" name="halaman" min="1" class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#444] transition-colors" placeholder="320" />
                    </div>
                </div>
                {{-- Hidden fields --}}
                <input type="hidden" id="add-book-cover-url" name="foto_sampul" />

                <div class="pt-2 flex gap-3">
                    <button type="submit" class="flex-1 bg-[#FFDDAF] text-[#444] font-bold text-sm py-3 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors cursor-pointer">
                        Tambahkan
                    </button>
                    <button type="button" onclick="closeAddBookModal()" class="px-6 py-3 text-sm font-medium text-gray-400 hover:text-[#444] transition-colors cursor-pointer">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

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
        window.CURRENT_USER = {!! json_encode($user->only(['id', 'name', 'username', 'email', 'kota', 'no_telp', 'foto_profil', 'created_at'])) !!};
        window.CURRENT_USER.nama = window.CURRENT_USER.name;
        window.CURRENT_USER.preferred_genres = {!! json_encode($user->preferred_genres ?? []) !!};
        window.CURRENT_USER.member_since = window.CURRENT_USER.created_at ? window.CURRENT_USER.created_at.substring(0, 10) : '';
        delete window.CURRENT_USER.created_at;
    </script>
</body>
</html>
