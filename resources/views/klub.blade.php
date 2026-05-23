<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Alinea — Klub Buku</title>
    <meta name="description" content="Temukan dan bergabung dengan klub buku di Alinea — komunitas pembaca yang seru dan interaktif." />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/klub.js'])
</head>

<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">

    {{-- ========== NAVBAR ========== --}}
    <x-navbar></x-navbar>

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

    <footer id="tentang" class="bg-text text-gray-400 py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                    <!-- Logo & Brand -->
                    <div class="col-span-2 lg:col-span-1">
                        <div class="flex items-center gap-2 mb-4">
                            <img src="images/Alinea_footer.svg" alt="">
                        </div>
                        <p class="text-sm text-white opacity-50 leading-relaxed mb-5 max-w-xs">
                            Platform komunitas buku pertama dari dan untuk pembaca Indonesia. Pinjam, Baca, Bagikan.
                        </p>
                    </div>

                    <!-- Fitur -->
                    <div class="pl-15 pt-5">
                        <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Fitur</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Pinjam Buku</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Timeline</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Ulasan Buku</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Book Club</a></li>
                        </ul>
                    </div>

                    <!-- Informasi -->
                    <div class="pt-5 pl-8">
                        <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Informasi</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Tentang Kami</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Blog</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Karir</a></li>
                            <li><a href="#" class="hover:text-white transition-colors duration-200">Bantuan</a></li>
                        </ul>
                    </div>

                    <!-- Quick Contact -->
                    <div class="pt-5">
                        <h3 class="text-white font-bold text-sm mb-5 uppercase tracking-wider">Quick Contact</h3>
                        <ul class="space-y-3 text-sm">
                            <li><a href="mailto:halo@alinea.id" class="hover:text-white transition-colors duration-200">halo@alinea.id</a></li>
                            <li><a href="tel:+62212345678" class="hover:text-white transition-colors duration-200">+62 21 2345 6789</a></li>
                            <li><span class="text-gray-500">Jakarta, Indonesia</span></li>
                        </ul>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-800 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-white opacity-50">© {{ date('Y') }} Alinea. All rights reserved.</p>
                    <div class="flex gap-6 text-xs">
                        <a href="#" class="hover:text-white transition-colors duration-200">Syarat & Ketentuan</a>
                        <a href="#" class="hover:text-white transition-colors duration-200">Privasi</a>
                    </div>
                </div>
            </div>
        </footer>

    {{--
    ============================================================
    DB-READY DATA
    ============================================================
    Variabel yang harus dikirim dari Controller:
      • $clubs       → Collection dari model BookClub (with members, owner, recentBooks)
      • $categories  → ['Mystery','Fantasy',...] — bisa dari DB atau config
      • $currentUser → Auth::user() (nullable, untuk cek membership / ownership)

    Contoh di Controller nanti:
      $clubs = BookClub::with(['owner','members.user','recentBooks'])
                ->withCount('members')
                ->get()
                ->map(fn($c) => [
                    'id'               => $c->id,
                    'name'             => $c->nama_klub,
                    'category'         => $c->kategori,
                    'members'          => $c->members_count,
                    'founded'          => $c->created_at->translatedFormat('d F Y'),
                    'description'      => Str::limit($c->deskripsi, 160),
                    'full_description' => $c->deskripsi,
                    'admin'            => $c->owner->nama,
                    'admin_avatar'     => $c->owner->avatar,
                    'members_list'     => $c->members->pluck('user.nama')->toArray(),
                    'recent_books'     => $c->recentBooks->pluck('judul')->toArray(),
                    'schedule'         => $c->jadwal,
                    'gradient_from'    => $c->gradient_from ?? '#FFDDAF',
                    'gradient_to'      => $c->gradient_to   ?? '#C7E7FF',
                ]);

      $categories = BookClub::distinct()->pluck('kategori')->sort()->values();

      return view('klub', compact('clubs','categories','currentUser'));
    ============================================================
    --}}

    @php
        // ── Fallback dummy data — HAPUS blok ini setelah controller siap ──
        $clubs = $clubs ?? collect([
            ['id'=>1,'name'=>'Pengulik Kebenaran','category'=>'Mystery','members'=>10,'founded'=>'12 Januari 2025','description'=>'Punya Insting Detektif? Yuk, Bedah Kasus Di Balik Novel Misteri.','full_description'=>'Klub ini didirikan untuk para pecinta novel misteri dan thriller. Kami membedah alur cerita, menganalisis karakter antagonis, dan berdiskusi tentang teknik plot twist.','admin'=>'Rina Maharani','admin_avatar'=>null,'members_list'=>['Rina Maharani','Budi Santoso','Dewi Anggraini','Fajar Nugroho','Gita Puspita','Hendra Wijaya','Indah Sari','Joko Prasetyo','Kartika Sari','Luthfi Rahman'],'recent_books'=>['The Girl on the Train','Gone Girl','And Then There Were None'],'schedule'=>'Setiap Sabtu, 19:00 WIB','gradient_from'=>'#FFDDAF','gradient_to'=>'#C7E7FF'],
            ['id'=>2,'name'=>'Dunia Fantasi','category'=>'Fantasy','members'=>24,'founded'=>'5 Maret 2024','description'=>'Jelajahi dunia sihir, naga, dan petualangan epik bersama para pembaca fantasi sejati.','full_description'=>'Dunia Fantasi adalah rumah bagi para penggemar genre fantasy dari high fantasy hingga urban fantasy.','admin'=>'Arya Pratama','admin_avatar'=>null,'members_list'=>['Arya Pratama','Bella Safitri','Candra Wibowo','Diana Putri','Eko Saputra'],'recent_books'=>['The Name of the Wind','Mistborn','The Hobbit'],'schedule'=>'Setiap Minggu, 14:00 WIB','gradient_from'=>'#C7E7FF','gradient_to'=>'#D4F6FF'],
            ['id'=>3,'name'=>'Filsafat Kopi','category'=>'Philosophy','members'=>15,'founded'=>'20 Juni 2024','description'=>'Ngopi sambil ngobrolin eksistensialisme? Klub ini tempatnya!','full_description'=>'Filsafat Kopi menggabungkan kecintaan pada kopi dan pemikiran filosofis.','admin'=>'Maya Hernanda','admin_avatar'=>null,'members_list'=>['Maya Hernanda','Naufal Rizki','Olivia Darmawan','Putra Aditya','Qori Amelia'],'recent_books'=>['The Stranger','Sophie\'s World','Meditations'],'schedule'=>'Setiap Jumat, 20:00 WIB','gradient_from'=>'#D4F6FF','gradient_to'=>'#FFDDAF'],
            ['id'=>4,'name'=>'Sastra Nusantara','category'=>'Sastra','members'=>18,'founded'=>'1 Agustus 2024','description'=>'Menyelami keindahan sastra Indonesia dari Pramoedya hingga Dee Lestari.','full_description'=>'Klub yang didedikasikan untuk mengapresiasi dan melestarikan sastra Indonesia.','admin'=>'Sari Dewi','admin_avatar'=>null,'members_list'=>['Sari Dewi','Taufik Hidayat','Ulfa Nur','Vino Bastian','Wulan Sari'],'recent_books'=>['Bumi Manusia','Laut Bercerita','Supernova'],'schedule'=>'Setiap Rabu, 19:30 WIB','gradient_from'=>'#FFDDAF','gradient_to'=>'#D4F6FF'],
            ['id'=>5,'name'=>'Sci-Fi Society','category'=>'Sci-Fi','members'=>12,'founded'=>'15 Oktober 2024','description'=>'Dari Asimov sampai Liu Cixin, diskusikan masa depan yang mungkin terjadi.','full_description'=>'Sci-Fi Society adalah komunitas untuk para penggemar fiksi ilmiah.','admin'=>'Reza Mahendra','admin_avatar'=>null,'members_list'=>['Reza Mahendra','Anisa Fitri','Bayu Krisna','Citra Dewi','Dimas Arya'],'recent_books'=>['Dune','The Three-Body Problem','Neuromancer'],'schedule'=>'Setiap Sabtu, 16:00 WIB','gradient_from'=>'#C7E7FF','gradient_to'=>'#FFDDAF'],
            ['id'=>6,'name'=>'Romance Readers','category'=>'Romance','members'=>30,'founded'=>'14 Februari 2024','description'=>'Baper bersama! Klub untuk para pecinta novel romantis.','full_description'=>'Romance Readers adalah klub terbesar di Alinea untuk para pecinta cerita cinta.','admin'=>'Laras Sekar','admin_avatar'=>null,'members_list'=>['Laras Sekar','Mira Aulia','Nadia Cahya','Omar Fadhil','Patricia Tan'],'recent_books'=>['The Notebook','Beach Read','People We Meet on Vacation'],'schedule'=>'Setiap Minggu, 10:00 WIB','gradient_from'=>'#FFDDAF','gradient_to'=>'#C7E7FF'],
            ['id'=>7,'name'=>'Non-Fiksi Faktual','category'=>'Non-Fiksi','members'=>8,'founded'=>'3 November 2024','description'=>'Pelajari dunia nyata lewat buku-buku non-fiksi terbaik.','full_description'=>'Non-Fiksi Faktual fokus pada buku-buku yang memperluas wawasan.','admin'=>'Ahmad Fauzan','admin_avatar'=>null,'members_list'=>['Ahmad Fauzan','Bella Putri','Cahyo Wibisono','Dian Pertiwi','Eka Saputri'],'recent_books'=>['Sapiens','Atomic Habits','Thinking, Fast and Slow'],'schedule'=>'Setiap Kamis, 19:00 WIB','gradient_from'=>'#D4F6FF','gradient_to'=>'#C7E7FF'],
            ['id'=>8,'name'=>'Horror Corner','category'=>'Horror','members'=>14,'founded'=>'31 Oktober 2024','description'=>'Berani baca buku horor sendirian? Yuk diskusi bareng!','full_description'=>'Horror Corner adalah tempat aman untuk mendiskusikan buku-buku yang bikin merinding.','admin'=>'Kevin Darma','admin_avatar'=>null,'members_list'=>['Kevin Darma','Lisa Andriani','Muhamad Ilham','Nina Kurnia','Oscar Putra'],'recent_books'=>['It','Mexican Gothic','The Haunting of Hill House'],'schedule'=>'Setiap Jumat, 21:00 WIB','gradient_from'=>'#C7E7FF','gradient_to'=>'#D4F6FF'],
            ['id'=>9,'name'=>'Buku Anak Muda','category'=>'Young Adult','members'=>22,'founded'=>'7 Januari 2025','description'=>'Coming-of-age, first love, dan petualangan remaja — semua ada di sini!','full_description'=>'Klub ini khusus untuk pembaca genre Young Adult.','admin'=>'Zahra Amelia','admin_avatar'=>null,'members_list'=>['Zahra Amelia','Adit Nugraha','Bunga Citra','Deni Setiawan','Eva Mustika'],'recent_books'=>['The Fault in Our Stars','Percy Jackson','Divergent'],'schedule'=>'Setiap Sabtu, 15:00 WIB','gradient_from'=>'#FFDDAF','gradient_to'=>'#D4F6FF'],
        ]);

        // Kategori — nanti ambil dari DB: Genre::pluck('nama_genre') atau BookClub::distinct('kategori')
        $categories = $categories ?? collect($clubs)->pluck('category')->unique()->sort()->values();

        // User saat ini — nanti dari Auth::user()
        $currentUser = $currentUser ?? null;
    @endphp

    {{-- Kirim data ke JS --}}
    <script>
        window.__KLUB_DATA__       = @json($clubs);
        window.__KLUB_CATEGORIES__ = @json($categories);
        window.__CURRENT_USER__    = @json($currentUser);
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

    {{-- ========== BUAT KLUB MODAL (Create Club Form) ========== --}}
    {{--
    DB-READY: Form ini akan POST ke route 'klub.store' nanti.
    Fields sesuai tabel book_clubs:
      - nama_klub  (string)
      - kategori   (string)
      - deskripsi  (text)
      - jadwal     (string, nullable)
      - foto_klub  (file, nullable — untuk sekarang pakai gradient picker)
    --}}
    <div id="buat-klub-modal" class="fixed inset-0 z-[100] hidden">
        {{-- Backdrop --}}
        <div id="buat-klub-backdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

        {{-- Modal panel --}}
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div id="buat-klub-panel"
                 class="relative bg-white border-2 border-[#444] rounded-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto
                        transform scale-95 opacity-0 transition-all duration-300">

                {{-- Close button --}}
                <button id="buat-klub-close" aria-label="Tutup"
                        class="absolute top-4 right-4 z-10 w-8 h-8 rounded-full border-[1.5px] border-[#444] flex items-center justify-center
                               text-[#444] hover:bg-[#FFDDAF] transition-colors cursor-pointer bg-white">
                    <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path d="M4 4l12 12M16 4L4 16"/>
                    </svg>
                </button>

                {{-- Gradient Preview Banner --}}
                <div id="buat-klub-preview-banner" class="h-28 rounded-t-2xl transition-all duration-500"
                     style="background: linear-gradient(135deg, #FFDDAF, #C7E7FF)"></div>

                {{-- Form Content --}}
                <div class="px-6 pb-6 pt-5">
                    <h2 class="font-bold text-xl mb-1">Buat Klub Baru</h2>
                    <p class="text-xs text-gray-400 mb-6">Mulai komunitas membacamu sendiri di Alinea!</p>

                    {{-- Form — action akan diisi nanti ke route('klub.store') --}}
                    <form id="buat-klub-form" method="POST" action="{{ url('/klub') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- Nama Klub --}}
                        <div class="mb-4">
                            <label for="input-nama-klub" class="block text-xs font-bold text-[#444] mb-1.5 uppercase tracking-wider">
                                Nama Klub <span class="text-red-400">*</span>
                            </label>
                            <input type="text" id="input-nama-klub" name="nama_klub" required maxlength="100"
                                   placeholder="Contoh: Pengulik Kebenaran"
                                   class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-[#444] transition-colors bg-[#FBFBFB]" />
                            <span id="nama-klub-counter" class="block text-right text-[10px] text-gray-300 mt-1">0/100</span>
                        </div>

                        {{-- Kategori --}}
                        <div class="mb-4">
                            <label for="input-kategori" class="block text-xs font-bold text-[#444] mb-1.5 uppercase tracking-wider">
                                Genre <span class="text-red-400">*</span>
                            </label>
                            <div class="relative">
                                <select id="input-kategori" name="kategori" required
                                        class="w-full appearance-none border-[1.5px] border-gray-200 rounded-xl px-4 py-2.5 pr-9 text-sm outline-none focus:border-[#444] transition-colors bg-[#FBFBFB] cursor-pointer">
                                    <option value="" disabled selected>Pilih kategori...</option>
                                    {{-- Kategori dari DB / fallback --}}
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                    <option value="__custom__">+ Kategori Lain...</option>
                                </select>
                                <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </div>
                            {{-- Custom category input (hidden by default) --}}
                            <input type="text" id="input-kategori-custom" name="kategori_custom" maxlength="50"
                                   placeholder="Ketik kategori baru..."
                                   class="hidden w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-[#444] transition-colors bg-[#FBFBFB] mt-2" />
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-4">
                            <label for="input-deskripsi" class="block text-xs font-bold text-[#444] mb-1.5 uppercase tracking-wider">
                                Deskripsi <span class="text-red-400">*</span>
                            </label>
                            <textarea id="input-deskripsi" name="deskripsi" required rows="4" maxlength="500"
                                      placeholder="Ceritakan tentang klub buku ini — visi, misi, dan siapa yang cocok bergabung..."
                                      class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-[#444] transition-colors bg-[#FBFBFB] resize-y min-h-[80px]"></textarea>
                            <span id="deskripsi-counter" class="block text-right text-[10px] text-gray-300 mt-1">0/500</span>
                        </div>

                        {{-- Gradient Color Picker --}}
                        <div class="mb-5">
                            <label class="block text-xs font-bold text-[#444] mb-2 uppercase tracking-wider">
                                Warna Klub
                            </label>
                            <div class="flex flex-wrap gap-2" id="gradient-picker">
                                @php
                                    $gradients = [
                                        ['#FFDDAF','#C7E7FF'],
                                        ['#C7E7FF','#D4F6FF'],
                                        ['#D4F6FF','#FFDDAF'],
                                        ['#FFDDAF','#D4F6FF'],
                                        ['#C7E7FF','#FFDDAF'],
                                        ['#D4F6FF','#C7E7FF'],
                                    ];
                                @endphp
                                @foreach ($gradients as $i => [$from, $to])
                                <button type="button" data-gradient-from="{{ $from }}" data-gradient-to="{{ $to }}"
                                        class="w-10 h-10 rounded-xl border-[1.5px] transition-all duration-200 cursor-pointer hover:scale-110
                                               {{ $i === 0 ? 'border-[#444] ring-2 ring-[#444] ring-offset-2' : 'border-gray-200' }}"
                                        style="background: linear-gradient(135deg, {{ $from }}, {{ $to }})"></button>
                                @endforeach
                            </div>
                            <input type="hidden" id="input-gradient-from" name="gradient_from" value="#FFDDAF" />
                            <input type="hidden" id="input-gradient-to" name="gradient_to" value="#C7E7FF" />
                        </div>

                        {{-- Foto Klub (opsional) --}}
                        <div class="mb-6">
                            <label for="input-foto-klub" class="block text-xs font-bold text-[#444] mb-1.5 uppercase tracking-wider">
                                Foto/Cover Klub <span class="text-gray-300 font-normal normal-case">(opsional)</span>
                            </label>
                            <div id="foto-klub-dropzone"
                                 class="border-[1.5px] border-dashed border-gray-200 rounded-xl p-5 text-center cursor-pointer hover:border-[#444] hover:bg-gray-50 transition-all">
                                <svg class="mx-auto mb-2 text-gray-300" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                                </svg>
                                <p class="text-xs text-gray-300" id="foto-klub-label">Klik atau seret gambar ke sini</p>
                                <input type="file" id="input-foto-klub" name="foto_klub" accept="image/*" class="hidden" />
                            </div>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" id="btn-submit-klub"
                                class="w-full py-3 text-sm font-bold text-[#444] bg-[#FFDDAF] rounded-full border-[1.5px] border-[#444]
                                       hover:-translate-y-[1px] hover:bg-[#ffcf90] transition-all duration-200 cursor-pointer">
                            Buat Klub Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
