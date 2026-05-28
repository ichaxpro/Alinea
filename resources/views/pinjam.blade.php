<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Alinea - Pinjam</title>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('css/pinjam.css') }}">
</head>
<body>
	@php
		$genres = [
			'Sci-Fi', 'Fantasy', 'Horror', 'Thriller', 'Romance', 'Adventure',
			'Comedy', 'Self-Improvement', 'Education', 'Economy', 'Comic / Manga', 'Action'
		];
	@endphp

	<header class="topbar">
		<div class="brand">
			<img src="{{ asset('images/alinealogo.svg') }}" alt="Logo Alinea" class="brand-logo">
		</div>

		<nav class="menu">
			<a href="#">Beranda</a>
			<a href="#" class="active">Pinjam</a>
			<a href="#">Komunitas</a>
			<a href="#">Klub</a>
			<a href="#">Ulasan</a>
		</nav>

		<div class="topbar-actions">
			<button class="search-btn" type="button" aria-label="Cari">
				<span class="search-icon"></span>
			</button>
			<button class="login-btn" type="button">Masuk</button>
		</div>
	</header>

	<main class="pinjam-layout">
		<aside class="filter-panel">
			<h2>Penelusuran</h2>
			<input type="text" aria-label="Pencarian buku">

			<h3>Genre</h3>
			<div class="chips">
				@foreach ($genres as $genre)
					<button class="chip" type="button">{{ $genre }}</button>
				@endforeach
			</div>

			<button class="apply-btn" type="button">Terapkan</button>
		</aside>

		<section class="content-panel">
			<div class="content-heading">
				<h1>9 Buku Ditemukan</h1>
				<p>Menampilkan Semua Buku Tersedia</p>
			</div>

			<div class="book-grid">
				@forelse ($books as $book)
					<article class="book-card">
						<img src="{{ $book->cover_url ?: asset('images/default_cover.png') }}" alt="Sampul {{ $book->judul }}">
						<div class="book-info">
							<h4>{{ $book->judul }}</h4>
							<p class="author">{{ $book->penulis }}</p>
							<p class="category">{{ $book->kategori }}</p>
							<p class="owner text-xs text-gray-500 mt-2">Pemilik: <strong>{{ $book->user->name ?? 'Anonim' }}</strong></p>
						</div>
						@if(Auth::check() && Auth::id() === $book->user_id)
							<button class="pinjam-btn" disabled style="background:#ccc;">Buku Milikmu</button>
						@else
							<button class="pinjam-btn" onclick="requestLoan({{ $book->id }})">Ajukan Pinjam</button>
						@endif
					</article>
				@empty
					<p>Belum ada buku yang tersedia untuk dipinjam.</p>
				@endforelse
			</div>
		</section>
	</main>

	<script>
	async function requestLoan(bookId) {
		const titikTemu = prompt("Dimana Anda ingin bertemu untuk mengambil buku ini?");
		if(!titikTemu) return;

		const durasi = prompt("Berapa hari Anda ingin meminjam buku ini?", "7");
		if(!durasi) return;

		try {
			const response = await fetch('/transactions', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
					'Accept': 'application/json'
				},
				body: JSON.stringify({ 
					book_id: bookId, 
					titik_temu: titikTemu, 
					durasi_hari: parseInt(durasi) 
				})
			});

			if (response.ok) {
				alert('Pengajuan berhasil dikirim! Menunggu konfirmasi pemilik.');
			} else {
                let errorMsg = 'Gagal mengirim pengajuan.';
                try {
                    const errorData = await response.json();
                    if (errorData.message) errorMsg += '\\n' + errorData.message;
                } catch(e) {}
				alert(errorMsg);
			}
		} catch (error) {
			console.error(error);
            alert('Terjadi kesalahan jaringan.');
		}
	}
	</script>
</body>
</html>
