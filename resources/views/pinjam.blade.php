<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Alinea - Pinjam</title>
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

		$books = array_fill(0, 9, [
			'title' => 'Pulang',
			'author' => 'Tere Liye',
			'genres' => ['Action', 'Adventure', 'Thriller'],
			'rating' => 4.5,
			'cover' => asset('images/117225_f 1.png'),
		]);
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
				@foreach ($books as $book)
					<article class="book-card">
						<img src="{{ $book['cover'] }}" alt="Sampul {{ $book['title'] }}">
						<div class="book-info">
							<h4>{{ $book['title'] }}</h4>
							<p class="author">{{ $book['author'] }}</p>
							<p class="category">{{ implode(' • ', $book['genres']) }}</p>
							<p class="rating">★ {{ number_format($book['rating'], 1) }}</p>
						</div>
					</article>
				@endforeach
			</div>
		</section>
	</main>
</body>
</html>
