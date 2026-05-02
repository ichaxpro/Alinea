<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — Klub Buku</title>
    <meta name="description" content="Temukan dan bergabung dengan klub buku di Alinea — komunitas pembaca yang seru dan interaktif." />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/klub.js'])
</head>

<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">

    {{-- ========== NAVBAR ========== --}}
    <nav id="main-navbar"
         class="fixed inset-x-0 top-0 z-50 h-14 bg-white flex items-center border-b-2 border-black px-6 lg:px-10 transition-transform duration-300">
        <div class="flex items-center justify-between w-full max-w-[1280px] mx-auto">

            {{-- Logo --}}
            <a href="/" class="flex-shrink-0" aria-label="Alinea — Halaman Utama">
                <img src="{{ asset('images/alinealogo.svg') }}" alt="Alinea" class="h-8 w-auto" />
            </a>

            {{-- Desktop nav links --}}
            <ul class="hidden md:flex items-center gap-7 list-none">
                @foreach ([
                    ['/', 'Beranda'],
                    ['/pinjam', 'Pinjam'],
                    ['/komunitas', 'Komunitas'],
                    ['/klub', 'Klub'],
                    ['/ulasan', 'Ulasan'],
                ] as [$href, $label])
                <li>
                    <a href="{{ $href }}"
                       class="relative text-sm font-medium transition-colors
                              after:absolute after:bottom-[-3px] after:left-0 after:w-0 after:h-[2px]
                              after:bg-accent after:transition-all hover:after:w-full
                              {{ request()->is(trim($href, '/') ?: '/') ? 'text-gray-900 font-bold after:w-full' : 'text-gray-600 hover:text-gray-900' }}">
                        {{ $label }}
                    </a>
                </li>
                @endforeach
            </ul>

            {{-- Action buttons --}}
            <div class="flex items-center gap-3">
                <button id="navbar-search-btn" aria-label="Cari"
                        class="w-9 h-9 rounded-full border-2 border-text flex items-center justify-center text-text hover:bg-white/10 transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>

                <a href="/login" id="masuk-btn"
                   class="bg-accent text-text font-bold text-sm px-5 py-2 rounded-full border-2 border-text hover:opacity-90 transition-opacity">
                    Masuk
                </a>
            </div>
        </div>
    </nav>

    {{-- ========== MAIN CONTENT ========== --}}
    <main class="pt-14">
        <div class="max-w-275 mx-auto px-4 sm:px-6 py-8">

            {{-- Toolbar: Search + Filters + Create --}}
            <div class="flex flex-wrap items-center gap-3 mb-8">
                {{-- Search --}}
                <div class="flex items-center gap-2 bg-white border-[1.5px] border-text rounded-lg px-4 py-2.5 w-full sm:w-64">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input type="search" id="klub-search-input" placeholder="Cari klub buku..."
                           class="border-none outline-none bg-transparent text-sm placeholder-gray-300 w-full" />
                </div>

                {{-- Category filter --}}
                <div class="relative">
                    <select id="klub-filter-category"
                            class="appearance-none bg-white border-[1.5px] border-[#444] rounded-lg pl-4 pr-9 py-2.5 text-sm font-medium text-[#444] outline-none cursor-pointer hover:bg-gray-50 transition-colors">
                        <option value="">Semua Kategori</option>
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[#444]" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>

                {{-- Sort --}}
                <div class="relative">
                    <select id="klub-sort"
                            class="appearance-none bg-white border-[1.5px] border-[#444] rounded-lg pl-4 pr-9 py-2.5 text-sm font-medium text-[#444] outline-none cursor-pointer hover:bg-gray-50 transition-colors">
                        <option value="name-asc">Nama A–Z</option>
                        <option value="name-desc">Nama Z–A</option>
                        <option value="members-desc">Member Terbanyak</option>
                        <option value="members-asc">Member Tersedikit</option>
                        <option value="newest">Terbaru</option>
                    </select>
                    <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[#444]" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Create button --}}
                <button id="buat-klub-btn"
                        class="bg-accent text-[#444] font-bold text-sm px-5 py-2.5 rounded-full border-[1.5px] border-text hover:bg-amber-500 transition-colors whitespace-nowrap">
                    + Buat Klub
                </button>
            </div>

            {{-- Club cards grid — max ~336px per card to match Figma --}}
            <div id="klub-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 justify-items-center"
                 style="grid-template-columns: repeat(auto-fill, minmax(280px, 336px)); justify-content: center;">
            </div>

            {{-- Pagination --}}
            <nav id="klub-pagination" class="flex items-center justify-center gap-2 mt-8" aria-label="Navigasi halaman">
            </nav>
        </div>
    </main>

    {{-- ========== CLUB DATA (DB-ready) ========== --}}
    @php
        // Replace this array with data from your controller/database later:
        // e.g. $clubs = Club::with('members')->get();
        $clubs = [
            [
                'id' => 1, 'name' => 'Pengulik Kebenaran', 'category' => 'Mystery',
                'members' => 10, 'founded' => '12 Januari 2025',
                'description' => 'Punya Insting Detektif? Yuk, Bedah Kasus Di Balik Novel Misteri. Di Pengulik Kebenaran, Tak Ada Rahasia Yang Aman Dari Diskusi Tajam Kami!',
                'full_description' => 'Klub ini didirikan untuk para pecinta novel misteri dan thriller. Kami membedah alur cerita, menganalisis karakter antagonis, dan berdiskusi tentang teknik plot twist terbaik dari berbagai penulis dunia.',
                'admin' => 'Rina Maharani',
                'members_list' => ['Rina Maharani', 'Budi Santoso', 'Dewi Anggraini', 'Fajar Nugroho', 'Gita Puspita', 'Hendra Wijaya', 'Indah Sari', 'Joko Prasetyo', 'Kartika Sari', 'Luthfi Rahman'],
                'recent_books' => ['The Girl on the Train', 'Gone Girl', 'And Then There Were None'],
                'schedule' => 'Setiap Sabtu, 19:00 WIB',
                'gradient_from' => '#FFDDAF', 'gradient_to' => '#C7E7FF',
            ],
            [
                'id' => 2, 'name' => 'Dunia Fantasi', 'category' => 'Fantasy',
                'members' => 24, 'founded' => '5 Maret 2024',
                'description' => 'Jelajahi dunia sihir, naga, dan petualangan epik bersama para pembaca fantasi sejati.',
                'full_description' => 'Dunia Fantasi adalah rumah bagi para penggemar genre fantasy dari high fantasy hingga urban fantasy. Kami membahas world-building, sistem magic, dan karakter-karakter legendaris.',
                'admin' => 'Arya Pratama',
                'members_list' => ['Arya Pratama', 'Bella Safitri', 'Candra Wibowo', 'Diana Putri', 'Eko Saputra'],
                'recent_books' => ['The Name of the Wind', 'Mistborn', 'The Hobbit'],
                'schedule' => 'Setiap Minggu, 14:00 WIB',
                'gradient_from' => '#C7E7FF', 'gradient_to' => '#D4F6FF',
            ],
            [
                'id' => 3, 'name' => 'Filsafat Kopi', 'category' => 'Philosophy',
                'members' => 15, 'founded' => '20 Juni 2024',
                'description' => 'Ngopi sambil ngobrolin eksistensialisme? Klub ini tempatnya! Dari Nietzsche sampai Camus.',
                'full_description' => 'Filsafat Kopi menggabungkan kecintaan pada kopi dan pemikiran filosofis. Setiap pertemuan kami membedah satu karya filsafat sambil menikmati kopi bersama.',
                'admin' => 'Maya Hernanda',
                'members_list' => ['Maya Hernanda', 'Naufal Rizki', 'Olivia Darmawan', 'Putra Aditya', 'Qori Amelia'],
                'recent_books' => ['The Stranger', 'Sophie\'s World', 'Meditations'],
                'schedule' => 'Setiap Jumat, 20:00 WIB',
                'gradient_from' => '#D4F6FF', 'gradient_to' => '#FFDDAF',
            ],
            [
                'id' => 4, 'name' => 'Sastra Nusantara', 'category' => 'Sastra',
                'members' => 18, 'founded' => '1 Agustus 2024',
                'description' => 'Menyelami keindahan sastra Indonesia dari Pramoedya hingga Dee Lestari.',
                'full_description' => 'Klub yang didedikasikan untuk mengapresiasi dan melestarikan sastra Indonesia. Kami membaca karya-karya klasik dan kontemporer dari penulis-penulis terbaik tanah air.',
                'admin' => 'Sari Dewi',
                'members_list' => ['Sari Dewi', 'Taufik Hidayat', 'Ulfa Nur', 'Vino Bastian', 'Wulan Sari'],
                'recent_books' => ['Bumi Manusia', 'Laut Bercerita', 'Supernova'],
                'schedule' => 'Setiap Rabu, 19:30 WIB',
                'gradient_from' => '#FFDDAF', 'gradient_to' => '#D4F6FF',
            ],
            [
                'id' => 5, 'name' => 'Sci-Fi Society', 'category' => 'Sci-Fi',
                'members' => 12, 'founded' => '15 Oktober 2024',
                'description' => 'Dari Asimov sampai Liu Cixin, diskusikan masa depan yang mungkin terjadi.',
                'full_description' => 'Sci-Fi Society adalah komunitas untuk para penggemar fiksi ilmiah. Kami mendiskusikan konsep-konsep sains, teknologi masa depan, dan bagaimana penulis sci-fi memvisualisasikan dunia yang akan datang.',
                'admin' => 'Reza Mahendra',
                'members_list' => ['Reza Mahendra', 'Anisa Fitri', 'Bayu Krisna', 'Citra Dewi', 'Dimas Arya'],
                'recent_books' => ['Dune', 'The Three-Body Problem', 'Neuromancer'],
                'schedule' => 'Setiap Sabtu, 16:00 WIB',
                'gradient_from' => '#C7E7FF', 'gradient_to' => '#FFDDAF',
            ],
            [
                'id' => 6, 'name' => 'Romance Readers', 'category' => 'Romance',
                'members' => 30, 'founded' => '14 Februari 2024',
                'description' => 'Baper bersama! Klub untuk para pecinta novel romantis dari seluruh penjuru.',
                'full_description' => 'Romance Readers adalah klub terbesar di Alinea untuk para pecinta cerita cinta. Dari slow burn hingga enemies-to-lovers, kami membahas semua trope favorit.',
                'admin' => 'Laras Sekar',
                'members_list' => ['Laras Sekar', 'Mira Aulia', 'Nadia Cahya', 'Omar Fadhil', 'Patricia Tan'],
                'recent_books' => ['The Notebook', 'Beach Read', 'People We Meet on Vacation'],
                'schedule' => 'Setiap Minggu, 10:00 WIB',
                'gradient_from' => '#FFDDAF', 'gradient_to' => '#C7E7FF',
            ],
            [
                'id' => 7, 'name' => 'Non-Fiksi Faktual', 'category' => 'Non-Fiksi',
                'members' => 8, 'founded' => '3 November 2024',
                'description' => 'Pelajari dunia nyata lewat buku-buku non-fiksi terbaik bersama klub ini.',
                'full_description' => 'Non-Fiksi Faktual fokus pada buku-buku yang memperluas wawasan — dari sejarah, sains, ekonomi, hingga psikologi.',
                'admin' => 'Ahmad Fauzan',
                'members_list' => ['Ahmad Fauzan', 'Bella Putri', 'Cahyo Wibisono', 'Dian Pertiwi', 'Eka Saputri'],
                'recent_books' => ['Sapiens', 'Atomic Habits', 'Thinking, Fast and Slow'],
                'schedule' => 'Setiap Kamis, 19:00 WIB',
                'gradient_from' => '#D4F6FF', 'gradient_to' => '#C7E7FF',
            ],
            [
                'id' => 8, 'name' => 'Horror Corner', 'category' => 'Horror',
                'members' => 14, 'founded' => '31 Oktober 2024',
                'description' => 'Berani baca buku horor sendirian? Yuk diskusi bareng biar nggak sendirian!',
                'full_description' => 'Horror Corner adalah tempat aman untuk mendiskusikan buku-buku yang bikin merinding. Dari Stephen King hingga horor Jepang dan lokal.',
                'admin' => 'Kevin Darma',
                'members_list' => ['Kevin Darma', 'Lisa Andriani', 'Muhamad Ilham', 'Nina Kurnia', 'Oscar Putra'],
                'recent_books' => ['It', 'Mexican Gothic', 'The Haunting of Hill House'],
                'schedule' => 'Setiap Jumat, 21:00 WIB',
                'gradient_from' => '#C7E7FF', 'gradient_to' => '#D4F6FF',
            ],
            [
                'id' => 9, 'name' => 'Buku Anak Muda', 'category' => 'Young Adult',
                'members' => 22, 'founded' => '7 Januari 2025',
                'description' => 'Coming-of-age, first love, dan petualangan remaja — semua ada di sini!',
                'full_description' => 'Klub ini khusus untuk pembaca genre Young Adult. Kami membahas buku-buku yang menceritakan perjalanan tumbuh dewasa, persahabatan, dan menemukan jati diri.',
                'admin' => 'Zahra Amelia',
                'members_list' => ['Zahra Amelia', 'Adit Nugraha', 'Bunga Citra', 'Deni Setiawan', 'Eva Mustika'],
                'recent_books' => ['The Fault in Our Stars', 'Percy Jackson', 'Divergent'],
                'schedule' => 'Setiap Sabtu, 13:00 WIB',
                'gradient_from' => '#FFDDAF', 'gradient_to' => '#D4F6FF',
            ],
            [
                'id' => 9, 'name' => 'Buku Anak Muda', 'category' => 'Young Adult',
                'members' => 22, 'founded' => '7 Januari 2025',
                'description' => 'Coming-of-age, first love, dan petualangan remaja — semua ada di sini!',
                'full_description' => 'Klub ini khusus untuk pembaca genre Young Adult. Kami membahas buku-buku yang menceritakan perjalanan tumbuh dewasa, persahabatan, dan menemukan jati diri.',
                'admin' => 'Zahra Amelia',
                'members_list' => ['Zahra Amelia', 'Adit Nugraha', 'Bunga Citra', 'Deni Setiawan', 'Eva Mustika'],
                'recent_books' => ['The Fault in Our Stars', 'Percy Jackson', 'Divergent'],
                'schedule' => 'Setiap Sabtu, 13:00 WIB',
                'gradient_from' => '#FFDDAF', 'gradient_to' => '#D4F6FF',
            ],
            [
                'id' => 9, 'name' => 'Buku Anak Muda', 'category' => 'Young Adult',
                'members' => 22, 'founded' => '7 Januari 2025',
                'description' => 'Coming-of-age, first love, dan petualangan remaja — semua ada di sini!',
                'full_description' => 'Klub ini khusus untuk pembaca genre Young Adult. Kami membahas buku-buku yang menceritakan perjalanan tumbuh dewasa, persahabatan, dan menemukan jati diri.',
                'admin' => 'Zahra Amelia',
                'members_list' => ['Zahra Amelia', 'Adit Nugraha', 'Bunga Citra', 'Deni Setiawan', 'Eva Mustika'],
                'recent_books' => ['The Fault in Our Stars', 'Percy Jackson', 'Divergent'],
                'schedule' => 'Setiap Sabtu, 13:00 WIB',
                'gradient_from' => '#FFDDAF', 'gradient_to' => '#D4F6FF',
            ],
            [
                'id' => 9, 'name' => 'Buku Anak Muda', 'category' => 'Young Adult',
                'members' => 22, 'founded' => '7 Januari 2025',
                'description' => 'Coming-of-age, first love, dan petualangan remaja — semua ada di sini!',
                'full_description' => 'Klub ini khusus untuk pembaca genre Young Adult. Kami membahas buku-buku yang menceritakan perjalanan tumbuh dewasa, persahabatan, dan menemukan jati diri.',
                'admin' => 'Zahra Amelia',
                'members_list' => ['Zahra Amelia', 'Adit Nugraha', 'Bunga Citra', 'Deni Setiawan', 'Eva Mustika'],
                'recent_books' => ['The Fault in Our Stars', 'Percy Jackson', 'Divergent'],
                'schedule' => 'Setiap Sabtu, 13:00 WIB',
                'gradient_from' => '#FFDDAF', 'gradient_to' => '#D4F6FF',
            ],
            [
                'id' => 9, 'name' => 'Buku Anak Muda', 'category' => 'Young Adult',
                'members' => 22, 'founded' => '7 Januari 2025',
                'description' => 'Coming-of-age, first love, dan petualangan remaja — semua ada di sini!',
                'full_description' => 'Klub ini khusus untuk pembaca genre Young Adult. Kami membahas buku-buku yang menceritakan perjalanan tumbuh dewasa, persahabatan, dan menemukan jati diri.',
                'admin' => 'Zahra Amelia',
                'members_list' => ['Zahra Amelia', 'Adit Nugraha', 'Bunga Citra', 'Deni Setiawan', 'Eva Mustika'],
                'recent_books' => ['The Fault in Our Stars', 'Percy Jackson', 'Divergent'],
                'schedule' => 'Setiap Sabtu, 13:00 WIB',
                'gradient_from' => '#FFDDAF', 'gradient_to' => '#D4F6FF',
            ],
            [
                'id' => 9, 'name' => 'Buku Anak Muda', 'category' => 'Young Adult',
                'members' => 22, 'founded' => '7 Januari 2025',
                'description' => 'Coming-of-age, first love, dan petualangan remaja — semua ada di sini!',
                'full_description' => 'Klub ini khusus untuk pembaca genre Young Adult. Kami membahas buku-buku yang menceritakan perjalanan tumbuh dewasa, persahabatan, dan menemukan jati diri.',
                'admin' => 'Zahra Amelia',
                'members_list' => ['Zahra Amelia', 'Adit Nugraha', 'Bunga Citra', 'Deni Setiawan', 'Eva Mustika'],
                'recent_books' => ['The Fault in Our Stars', 'Percy Jackson', 'Divergent'],
                'schedule' => 'Setiap Sabtu, 13:00 WIB',
                'gradient_from' => '#FFDDAF', 'gradient_to' => '#D4F6FF',
            ],
            [
                'id' => 9, 'name' => 'Buku Anak Muda', 'category' => 'Young Adult',
                'members' => 22, 'founded' => '7 Januari 2025',
                'description' => 'Coming-of-age, first love, dan petualangan remaja — semua ada di sini!',
                'full_description' => 'Klub ini khusus untuk pembaca genre Young Adult. Kami membahas buku-buku yang menceritakan perjalanan tumbuh dewasa, persahabatan, dan menemukan jati diri.',
                'admin' => 'Zahra Amelia',
                'members_list' => ['Zahra Amelia', 'Adit Nugraha', 'Bunga Citra', 'Deni Setiawan', 'Eva Mustika'],
                'recent_books' => ['The Fault in Our Stars', 'Percy Jackson', 'Divergent'],
                'schedule' => 'Setiap Sabtu, 13:00 WIB',
                'gradient_from' => '#FFDDAF', 'gradient_to' => '#D4F6FF',
            ],
        ];
    @endphp

    {{-- Pass data to JS — when you connect a DB, just change $clubs source above --}}
    <script>
        window.__KLUB_DATA__ = @json($clubs);
    </script>

    {{-- ========== CLUB DETAIL MODAL ========== --}}
    <div id="klub-modal" class="fixed inset-0 z-[100] hidden">
        {{-- Backdrop --}}
        <div id="klub-modal-backdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        {{-- Modal panel --}}
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div id="klub-modal-panel"
                 class="relative bg-white border-2 border-[#444] rounded-2xl w-full max-w-2xl max-h-[85vh] overflow-y-auto
                        transform scale-95 opacity-0 transition-all duration-300">

                {{-- Close button --}}
                <button id="klub-modal-close" aria-label="Tutup"
                        class="absolute top-4 right-4 z-10 w-8 h-8 rounded-full border-[1.5px] border-[#444] flex items-center justify-center
                               text-[#444] hover:bg-[#FFDDAF] transition-colors cursor-pointer bg-white">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path d="M4 4l12 12M16 4L4 16"/>
                    </svg>
                </button>

                {{-- Modal content (populated by JS) --}}
                <div id="klub-modal-content"></div>
            </div>
        </div>
    </div>

</body>
</html>
