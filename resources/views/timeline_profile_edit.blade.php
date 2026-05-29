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
    @vite(['resources/css/app.css', 'resources/js/timeline.js'])
</head>
<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">
<x-navbar></x-navbar>

<div class="min-h-screen pt-14">
    <div class="max-w-2xl mx-auto px-4 py-8">
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 border border-green-300 text-green-700 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Profile form --}}
        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6">
            <h1 class="text-2xl font-bold text-[#222] mb-6">Edit Profil</h1>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-[#444] mb-2">Foto Profil</label>
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-full border-2 border-[#444] overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF]">
                            @if($user->foto_profil)
                                <img src="{{ Storage::disk('public')->url($user->foto_profil) }}" class="w-full h-full object-cover">
                            @else
                                <span class="flex items-center justify-center w-full h-full text-2xl font-bold text-gray-400">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <input type="file" name="foto_profil" accept="image/*" class="text-sm">
                        <p class="text-xs text-gray-400">Klik "Simpan" setelah memilih file.</p>
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
                    <a href="{{ route('timeline_profile') }}" class="px-6 py-2.5 text-sm text-gray-500 hover:text-[#444] transition-colors">Batal</a>
                </div>
            </form>
        </div>

        {{-- Riwayat Baca --}}
        <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6 mt-6">
            <h2 class="text-lg font-bold text-[#222] mb-4">Riwayat Baca</h2>

            <form id="add-reading-book-form" class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                @csrf
                <h3 class="text-sm font-semibold text-[#444] mb-3">Tambah Buku</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500">Judul</label>
                        <input type="text" name="judul" required
                               class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#444]">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500">Penulis</label>
                        <input type="text" name="penulis" required
                               class="w-full border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#444]">
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <select name="reading_status" required class="border-[1.5px] border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[#444]">
                        <option value="sedang_dibaca">Sedang Dibaca</option>
                        <option value="selesai">Selesai</option>
                        <option value="diinginkan">Ingin Dibaca</option>
                    </select>
                    <button type="submit"
                            class="px-4 py-2 bg-[#FFDDAF] border-2 border-[#444] rounded-full text-sm font-bold hover:bg-[#ffcf90] transition-colors cursor-pointer">
                        Tambah
                    </button>
                </div>
            </form>

            <div id="reading-books-list" class="space-y-3">
                @foreach($user->personalBooks()->whereNotNull('reading_status')->get() as $book)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-200" data-book-id="{{ $book->id }}">
                        <div>
                            <p class="text-sm font-semibold text-[#333]">{{ $book->judul }}</p>
                            <p class="text-xs text-gray-500">{{ $book->penulis }}</p>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                {{ $book->reading_status === 'sedang_dibaca' ? 'bg-[#C7E7FF]' : ($book->reading_status === 'selesai' ? 'bg-[#D4F6FF]' : 'bg-gray-200') }}">
                                {{ $book->reading_status === 'sedang_dibaca' ? 'Sedang Dibaca' : ($book->reading_status === 'selesai' ? 'Selesai' : 'Diinginkan') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <select data-change-status data-book-id="{{ $book->id }}"
                                    class="text-xs border border-gray-200 rounded-lg px-2 py-1 outline-none">
                                <option value="sedang_dibaca" {{ $book->reading_status === 'sedang_dibaca' ? 'selected' : '' }}>Sedang Dibaca</option>
                                <option value="selesai" {{ $book->reading_status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                <option value="diinginkan" {{ $book->reading_status === 'diinginkan' ? 'selected' : '' }}>Diinginkan</option>
                            </select>
                            <button data-delete-book data-book-id="{{ $book->id }}"
                                    class="text-red-400 hover:text-red-600 text-xs font-medium cursor-pointer">Hapus</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function showToast(msg) {
        let el = document.getElementById('toast-msg');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toast-msg';
            el.className = 'fixed bottom-24 left-1/2 -translate-x-1/2 z-[9999] bg-[#444] text-white text-sm font-medium px-5 py-3 rounded-full transition-all duration-300 opacity-0 translate-y-2';
            document.body.appendChild(el);
        }
        el.textContent = msg;
        requestAnimationFrame(() => {
            el.classList.remove('opacity-0', 'translate-y-2');
            el.classList.add('opacity-100', 'translate-y-0');
        });
        setTimeout(() => {
            el.classList.add('opacity-0', 'translate-y-2');
            el.classList.remove('opacity-100', 'translate-y-0');
        }, 2500);
    }

    // Add reading book
    const form = document.getElementById('add-reading-book-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(form);
            try {
                const resp = await fetch('/profile/reading-books', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: fd,
                });
                const result = await resp.json();
                if (!resp.ok) throw new Error(result.message || 'Gagal');
                location.reload();
            } catch (err) { showToast(err.message); }
        });
    }

    // Change status
    document.querySelectorAll('[data-change-status]').forEach(sel => {
        sel.addEventListener('change', async () => {
            try {
                const resp = await fetch('/profile/reading-books/' + sel.dataset.bookId, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ reading_status: sel.value }),
                });
                const result = await resp.json();
                if (!resp.ok) throw new Error(result.message || 'Gagal');
                location.reload();
            } catch (err) { showToast(err.message); }
        });
    });

    // Delete book
    document.querySelectorAll('[data-delete-book]').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Hapus buku dari riwayat baca?')) return;
            try {
                const resp = await fetch('/profile/reading-books/' + btn.dataset.bookId, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                });
                const result = await resp.json();
                if (!resp.ok) throw new Error(result.message || 'Gagal');
                btn.closest('[data-book-id]').remove();
                showToast('Buku berhasil dihapus.');
            } catch (err) { showToast(err.message); }
        });
    });
});
</script>
</body>
</html>
