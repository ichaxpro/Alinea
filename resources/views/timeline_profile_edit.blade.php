<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — Edit Profil</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(config('services.google_books.key'))
    <meta name="google-books-key" content="{{ config('services.google_books.key') }}" />
    @endif
    @vite(['resources/css/app.css', 'resources/js/timeline.js', 'resources/js/profile-edit.js'])
    <style>
        /* ── Book search dropdown ── */
        #book-search-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0; right: 0;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.10);
            z-index: 50;
            max-height: 300px;
            overflow-y: auto;
        }
        .book-result-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .book-result-item:hover { background: #f9fafb; }
        .book-result-item + .book-result-item { border-top: 1px solid #f3f4f6; }

        /* ── Selected book preview ── */
        #selected-book-preview {
            display: none;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 12px;
        }
        #selected-book-preview.show { display: flex; }

        /* ── Spinner ── */
        @keyframes spin { to { transform: rotate(360deg); } }
        .animate-spin { animation: spin 0.8s linear infinite; }
    </style>
</head>
<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">
<x-navbar></x-navbar>

<div class="min-h-screen pt-16">
    <div class="max-w-2xl mx-auto px-4 py-8 max-md:pb-24">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-700 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- ═══ FORM PROFIL ═══ --}}
        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6">
            <h1 class="text-2xl font-bold text-[#222] mb-6">Edit Profil</h1>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-[#444] mb-2">Foto Profil</label>
                    <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                        <div class="w-20 h-20 rounded-full border-2 border-[#444] overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex-shrink-0 flex items-center justify-center">
                            @if($user->foto_profil)
                                <img src="{{ Storage::disk('public')->url($user->foto_profil) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-2xl font-bold text-gray-400">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-col gap-1.5 items-center sm:items-start w-full sm:w-auto">
                            <input type="file" name="foto_profil" accept="image/*" 
                                   class="text-xs file:mr-3 file:py-1.5 file:px-3.5 file:rounded-full file:border-2 file:border-[#444] file:text-xs file:font-bold file:bg-[#C7E7FF] file:text-[#444] hover:file:bg-[#b0dcff] file:cursor-pointer max-w-full">
                            <p class="text-xs text-gray-400">Klik "Simpan" setelah memilih file.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="name" class="block text-sm font-semibold text-[#444] mb-1">Nama</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                           class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#444]">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="mb-4">
                    <label for="deskripsi" class="block text-sm font-semibold text-[#444] mb-1">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3"
                              class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-[#444] resize-none">{{ old('deskripsi', $user->deskripsi) }}</textarea>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="px-6 py-2.5 bg-[#FFDDAF] border-2 border-[#444] rounded-full text-sm font-bold hover:bg-[#ffcf90] transition-colors cursor-pointer">
                        Simpan
                    </button>
                    <a href="{{ route('timeline_profile') }}" class="px-6 py-2.5 text-sm text-gray-500 hover:text-[#444] transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        {{-- ═══ RIWAYAT BACA ═══ --}}
        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 mt-6">
            <h2 class="text-lg font-bold text-[#222] mb-4">Riwayat Baca</h2>

            {{-- Filter bar (untuk daftar yang sudah ada) --}}
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 mb-1">
                <div class="flex items-center gap-2 flex-1 bg-gray-50 border-[1.5px] border-gray-200 rounded-lg px-3 py-2
                            focus-within:border-[#444] transition-colors duration-200">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa"
                         stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="search" id="riwayat-search-input"
                           placeholder="Filter riwayat baca..."
                           class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full">
                    <button id="riwayat-search-clear"
                            class="hidden text-gray-300 hover:text-[#444] transition-colors"
                            aria-label="Hapus filter">
                        <svg width="12" height="12" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round">
                            <path d="M4 4l12 12M16 4L4 16"/>
                        </svg>
                    </button>
                </div>
                <x-custom-select 
                    id="riwayat-filter-status" 
                    title="Filter Status"
                    placeholder="Semua Status" 
                    :options="[
                        'sedang_dibaca' => 'Sedang Dibaca',
                        'selesai' => 'Selesai',
                        'diinginkan' => 'Ingin Dibaca'
                    ]" 
                />
            </div>
            <p class="text-xs text-gray-400 mb-5">
                Menampilkan <strong id="riwayat-result-count">0</strong> buku
            </p>

            {{-- ── TAMBAH BUKU via Google Books Search ── --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                <h3 class="text-sm font-semibold text-[#444] mb-3">Tambah Buku ke Riwayat</h3>

                {{-- Search input --}}
                <div class="relative mb-3" id="book-search-wrapper">
                    <div class="flex items-center gap-2 bg-white border-[1.5px] border-gray-200 rounded-lg px-3 py-2.5
                                focus-within:border-[#444] transition-colors duration-200">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa"
                             stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        <input type="text" id="book-api-search"
                               placeholder="Ketik judul buku untuk mencari..."
                               autocomplete="off"
                               class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" />
                        <span id="book-search-spinner" class="hidden">
                            <svg class="animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="#bbb" stroke-width="2.5" stroke-linecap="round">
                                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                            </svg>
                        </span>
                    </div>
                    {{-- Dropdown --}}
                    <div id="book-search-dropdown" class="hidden"></div>
                </div>

                {{-- Selected book preview --}}
                <div id="selected-book-preview">
                    <div id="selected-cover"
                         class="w-10 h-14 rounded-md overflow-hidden bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF]
                                flex-shrink-0 flex items-center justify-center text-lg font-bold text-white/50">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p id="selected-title" class="text-sm font-semibold text-[#333] truncate"></p>
                        <p id="selected-author" class="text-xs text-gray-500 truncate"></p>
                    </div>
                    <button id="selected-clear"
                            class="text-gray-300 hover:text-red-400 transition-colors flex-shrink-0"
                            title="Batalkan pilihan">
                        <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                             stroke-width="2.5" stroke-linecap="round">
                            <path d="M4 4l12 12M16 4L4 16"/>
                        </svg>
                    </button>
                </div>

                {{-- Status + Tambah button --}}
                <div class="flex items-center gap-3">
                    <select id="add-reading-status"
                            class="border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm outline-none
                                   focus:border-[#444] bg-white cursor-pointer">
                        <option value="sedang_dibaca">Sedang Dibaca</option>
                        <option value="selesai">Selesai</option>
                        <option value="diinginkan">Ingin Dibaca</option>
                    </select>
                    <button id="add-reading-btn" disabled
                            class="px-4 py-2 bg-gray-200 border-2 border-gray-300 rounded-full text-sm font-bold
                                   text-gray-400 cursor-not-allowed transition-all duration-200">
                        Tambah
                    </button>
                </div>
            </div>

            {{-- ── DAFTAR BUKU ── --}}
            <div id="reading-books-list" class="space-y-3">
                @foreach($user->personalBooks()->whereNotNull('reading_status')->orderByDesc('updated_at')->get() as $book)
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-200"
                         data-book-id="{{ $book->id }}"
                         data-judul="{{ strtolower($book->judul) }}"
                         data-penulis="{{ strtolower($book->penulis) }}"
                         data-status="{{ $book->reading_status }}">

                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            {{-- Cover --}}
                            @if($book->cover_url)
                                @php
                                    $cover = str_starts_with($book->cover_url, 'http') ? $book->cover_url : (str_starts_with($book->cover_url, '/') ? asset(ltrim($book->cover_url, '/')) : asset('storage/' . $book->cover_url));
                                @endphp
                                <img src="{{ $cover }}" alt="Sampul {{ $book->judul }}"
                                     class="w-9 h-12 object-cover rounded-md flex-shrink-0"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                                <div style="display: none;" class="w-9 h-12 rounded-md bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF] flex-shrink-0 items-center justify-center text-sm font-bold text-white/70">
                                    {{ strtoupper(substr($book->judul, 0, 1)) }}
                                </div>
                            @else
                                <div class="w-9 h-12 rounded-md bg-gradient-to-br from-[#C7E7FF] to-[#FFDDAF]
                                            flex-shrink-0 flex items-center justify-center text-sm font-bold text-white/70">
                                    {{ strtoupper(substr($book->judul, 0, 1)) }}
                                </div>
                            @endif

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-[#333] truncate">{{ $book->judul }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $book->penulis }}</p>
                                <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-full mt-1
                                    {{ $book->reading_status === 'sedang_dibaca' ? 'bg-[#C7E7FF] text-[#004e89]'
                                     : ($book->reading_status === 'selesai'       ? 'bg-[#D4F6FF] text-[#006070]' : 'bg-gray-200 text-gray-600') }}">
                                    {{ $book->reading_status === 'sedang_dibaca' ? 'Sedang Dibaca'
                                     : ($book->reading_status === 'selesai'       ? 'Selesai' : 'Diinginkan') }}
                                </span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-between sm:justify-end gap-3 flex-shrink-0 pt-2.5 border-t border-gray-100 sm:border-t-0 sm:pt-0">
                            <select data-change-status
                                    class="text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 outline-none cursor-pointer bg-white focus:border-[#444] max-sm:flex-1">
                                <option value="sedang_dibaca" {{ $book->reading_status === 'sedang_dibaca' ? 'selected' : '' }}>Sedang Dibaca</option>
                                <option value="selesai"       {{ $book->reading_status === 'selesai'       ? 'selected' : '' }}>Selesai</option>
                                <option value="diinginkan"    {{ $book->reading_status === 'diinginkan'    ? 'selected' : '' }}>Diinginkan</option>
                            </select>
                            <button data-delete-book
                                    class="text-red-500 hover:text-red-700 text-xs font-semibold cursor-pointer transition-colors px-3 py-1.5 rounded-lg hover:bg-red-50">
                                Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Empty state --}}
            <div id="riwayat-empty" class="hidden text-center py-10">
                <div class="text-3xl mb-2">📖</div>
                <p class="text-sm font-semibold text-gray-500">Tidak ada buku ditemukan</p>
                <p class="text-xs text-gray-400 mt-1">Coba ubah kata kunci atau filter.</p>
            </div>
        </div>

    </div>
</div>

</body>
</html>
