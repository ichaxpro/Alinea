<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title id="pageTitle">Detail Buku | Alinea</title>
  <meta name="description" id="pageDescription" content="Detail buku dan ulasan di Alinea — platform baca buku komunitas." />
  
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
  
  @vite(['resources/css/app.css', 'resources/js/ulasan.js'])

  <style>
    /* CSS State khusus yang diatur oleh JavaScript (jangan dihapus) */
    .review-modal-overlay.active { opacity: 1; visibility: visible; }
    .review-modal-overlay.active .review-modal { transform: translateY(0); }
    .pinjam-modal-overlay.active { opacity: 1; visibility: visible; }
    .pinjam-modal-overlay.active .pinjam-modal { transform: translateY(0); }
    .pick-star.active { color: #F5C518; transform: scale(1.15); }
    .btn-simpan.saved { color: #F5C518; border-color: #F5C518; }
    .btn-simpan.saved svg { fill: #F5C518; }
    .toast { opacity: 0; transform: translateY(10px); transition: all 0.3s ease; }
    .toast.show { opacity: 1; transform: translateY(0); }
    
    /* Animasi Keyframes untuk Review Card JS */
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(16px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fadeInUp 0.4s ease both; }
  </style>
</head>
<body class="font-['Poppins'] bg-white text-text leading-relaxed overflow-x-hidden">

  <x-navbar></x-navbar>

  <main>
    {{-- ========== BOOK DETAIL SECTION ========== --}}
    <section class="pt-40 pb-10 bg-white" id="bookDetail">
      <div class="max-w-270 mx-auto px-5 lg:px-10">
        <div class="grid grid-cols-1 sm:grid-cols-[140px_1fr] lg:grid-cols-[200px_1fr] gap-6 lg:gap-12 items-start">
          
          {{-- Cover buku — diisi oleh JS --}}
          <div class="max-w-45 sm:max-w-none mx-auto sm:mx-0">
            <div id="bookCover" class="relative rounded-xl overflow-hidden aspect-2/3 shadow-[0_8px_32px_rgba(0,0,0,0.1)] bg-gradient-3">
              <div class="absolute inset-0 rounded-xl shadow-[inset_0_0_0_1px_rgba(0,0,0,0.06)] pointer-events-none"></div>
            </div>
          </div>

          <div>
            {{-- Kategori --}}
            <span class="inline-block px-5 py-1 text-[0.78rem] font-semibold text-text border-[1.5px] border-[#ddd] rounded-full mb-3" id="bookCategory"></span>

            {{-- Judul --}}
            <h1 class="text-3xl md:text-[2.2rem] font-black text-text leading-tight tracking-[-0.02em] mb-2" id="bookTitle"></h1>

            {{-- Meta: penulis • tahun • halaman --}}
            <p class="text-[0.9rem] font-normal text-text/60 mb-3" id="bookMeta"></p>

            {{-- Rating bintang + jumlah ulasan --}}
            <div class="flex items-center gap-2.5 mb-4" id="bookRating"></div>

            {{-- Sinopsis --}}
            <p class="text-[0.85rem] leading-relaxed text-text/75 mb-5 max-w-140" id="bookSynopsis"></p>

            {{-- Genre pills --}}
            <div class="flex gap-2.5 mb-6 flex-wrap" id="bookGenres"></div>

            {{-- Action buttons --}}
            <div class="flex flex-col sm:flex-row gap-2.5 items-start sm:items-center mb-8 flex-wrap">
              <button class="inline-flex items-center gap-2 px-7 py-2.5 text-[0.85rem] font-bold text-text bg-accent rounded-full border-[1.5px] border-text transition-all duration-200 hover:-translate-y-px hover:shadow-[0_4px_12px_rgba(0,0,0,0.1)]" id="tulisUlasanBtn">
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M14.5 2.5a2.121 2.121 0 0 1 3 3L6 17l-4 1 1-4L14.5 2.5z"/></svg>
                Tulis Ulasan
              </button>
              <button class="inline-flex items-center gap-2 px-7 py-2.5 text-[0.85rem] font-bold text-white bg-text rounded-full border-[1.5px] border-text transition-all duration-200 hover:-translate-y-px hover:shadow-[0_4px_12px_rgba(68,68,68,0.3)]" id="pinjamBtn">
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M4 19.5A2.5 2.5 0 0 1 1.5 17V3A2.5 2.5 0 0 1 4 .5h11A2.5 2.5 0 0 1 17.5 3v14a2.5 2.5 0 0 1-2.5 2.5H4z"/><path d="M1.5 15H16"/></svg>
                Pinjam Buku
              </button>
              <button class="btn-simpan flex items-center justify-center w-10 h-10 rounded-full border-[1.5px] border-[#ddd] bg-white text-[#444444] transition-all duration-200 hover:border-[#444444]" id="simpanBtn" aria-label="Simpan buku">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
              </button>
            </div>

            {{-- Info grid: penerbit, ISBN, bahasa, ketersediaan --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-8 pt-5 border-t border-[#eee]" id="bookInfoGrid"></div>
          </div>
        </div>
      </div>
    </section>

    {{-- ========== RATING BREAKDOWN ========== --}}
    <section class="pb-10" id="ratingBreakdown">
      <div class="max-w-[1080px] mx-auto px-5 lg:px-10">
        <div class="bg-[#FBFBFB] rounded-[20px] p-8">
          <div class="grid grid-cols-1 md:grid-cols-[auto_1fr] gap-5 md:gap-10 items-center">
            {{-- Rata-rata rating --}}
            <div class="text-left md:text-center flex md:block items-center gap-4 md:gap-0" id="ratingAvgBlock"></div>
            
            {{-- Bar distribusi per bintang --}}
            <div class="flex flex-col gap-2" id="ratingBarsBlock"></div>
          </div>
        </div>
      </div>
    </section>

    {{-- ========== REVIEWS LIST ========== --}}
    <section class="py-10 lg:py-[60px] bg-[#FBFBFB]" id="reviewsSection">
      <div class="max-w-[1080px] mx-auto px-5 lg:px-10">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7">
          <h2 class="text-xl font-extrabold text-[#444444]">Ulasan Pembaca</h2>
          <div class="flex items-center gap-2">
            <label for="sortSelect" class="text-[0.78rem] text-[#444444]/55">Urutkan:</label>
            <select id="sortSelect" class="font-['Poppins'] text-[0.8rem] font-semibold text-[#444444] bg-white border border-[#ddd] rounded-md px-3 py-1.5 outline-none cursor-pointer">
              <option value="newest">Terbaru</option>
              <option value="oldest">Terlama</option>
              <option value="highest">Rating Tertinggi</option>
              <option value="lowest">Rating Terendah</option>
              <option value="helpful">Paling Membantu</option>
            </select>
          </div>
        </div>

        <div id="reviewsList"></div>

        <div class="text-center mt-6">
          <button class="px-9 py-3 text-[0.85rem] font-bold text-[#444444] bg-white border-[1.5px] border-[#ddd] rounded-full transition-all duration-200 hover:border-[#444444]" id="loadMoreReviews">Lihat Ulasan Lainnya</button>
        </div>
      </div>
    </section>

    {{-- ========== REVIEW MODAL ========== --}}
    <div class="review-modal-overlay fixed inset-0 bg-black/40 backdrop-blur-sm z-[200] flex items-center justify-center opacity-0 invisible transition-all duration-300" id="reviewModalOverlay">
      <div class="review-modal bg-white rounded-[20px] p-8 md:p-10 w-full max-w-[520px] relative translate-y-5 transition-transform duration-300 mx-4" id="reviewModal">
        <button class="absolute top-4 right-5 text-2xl text-[#444444]/40 leading-none transition-colors duration-200 hover:text-[#444444]" id="reviewModalClose" aria-label="Tutup">&times;</button>
        <h3 class="text-xl font-extrabold mb-1">Tulis Ulasan</h3>
        <p class="text-[0.85rem] text-[#444444]/50 mb-7">Bagikan pendapatmu tentang <strong class="text-[#444444]" id="modalBookTitle"></strong></p>

        <div>
          <div class="mb-5">
            <label class="block text-[0.78rem] font-bold text-[#444444] mb-2 uppercase tracking-[0.04em]">Rating</label>
            <div class="flex gap-1.5" id="starPicker">
              <span class="pick-star text-[1.8rem] text-[#ddd] cursor-pointer transition-all duration-150 ease-in-out hover:text-[#F5C518] hover:scale-110" data-val="1">★</span>
              <span class="pick-star text-[1.8rem] text-[#ddd] cursor-pointer transition-all duration-150 ease-in-out hover:text-[#F5C518] hover:scale-110" data-val="2">★</span>
              <span class="pick-star text-[1.8rem] text-[#ddd] cursor-pointer transition-all duration-150 ease-in-out hover:text-[#F5C518] hover:scale-110" data-val="3">★</span>
              <span class="pick-star text-[1.8rem] text-[#ddd] cursor-pointer transition-all duration-150 ease-in-out hover:text-[#F5C518] hover:scale-110" data-val="4">★</span>
              <span class="pick-star text-[1.8rem] text-[#ddd] cursor-pointer transition-all duration-150 ease-in-out hover:text-[#F5C518] hover:scale-110" data-val="5">★</span>
            </div>
          </div>
          <div class="mb-5">
            <label for="reviewName" class="block text-[0.78rem] font-bold text-[#444444] mb-2 uppercase tracking-[0.04em]">Nama</label>
            <input type="text" id="reviewName" placeholder="Nama kamu" class="w-full font-['Poppins'] text-[0.88rem] text-[#444444] border-[1.5px] border-[#e0e0e0] rounded-xl px-4 py-3 outline-none transition-colors duration-200 bg-[#FBFBFB] focus:border-[#FFDDAF]" />
          </div>
          <div class="mb-5">
            <label for="reviewText" class="block text-[0.78rem] font-bold text-[#444444] mb-2 uppercase tracking-[0.04em]">Ulasan</label>
            <textarea id="reviewText" rows="5" placeholder="Ceritakan pengalamanmu membaca buku ini..." class="w-full font-['Poppins'] text-[0.88rem] text-[#444444] border-[1.5px] border-[#e0e0e0] rounded-xl px-4 py-3 outline-none transition-colors duration-200 bg-[#FBFBFB] focus:border-[#FFDDAF] resize-y min-h-[100px]"></textarea>
          </div>
          <button class="w-full p-3.5 text-[0.9rem] font-bold text-[#444444] bg-[#FFDDAF] rounded-full border-[1.5px] border-[#444444] transition-all duration-200 hover:-translate-y-[1px] hover:shadow-[0_4px_12px_rgba(0,0,0,0.1)]" id="submitReviewBtn">Kirim Ulasan</button>
        </div>
      </div>
    </div>

    {{-- ========== PINJAM MODAL ========== --}}
    <div class="pinjam-modal-overlay fixed inset-0 bg-black/40 backdrop-blur-sm z-[200] flex items-center justify-center opacity-0 invisible transition-all duration-300" id="pinjamModalOverlay">
      <div class="pinjam-modal bg-white rounded-[20px] p-8 md:p-10 w-full max-w-[800px] relative translate-y-5 transition-transform duration-300 mx-4" id="pinjamModal">
        <button class="absolute top-4 right-5 text-2xl text-[#444444]/40 leading-none transition-colors duration-200 hover:text-[#444444]" id="pinjamModalClose" aria-label="Tutup">&times;</button>
        
        <div class="grid grid-cols-1 md:grid-cols-[260px_1fr] gap-8 md:gap-14">
          <!-- Left Part -->
          <div class="flex flex-col items-center">
            <div id="pinjamBookCover" class="w-[200px] aspect-[2/3] rounded-2xl overflow-hidden mb-4 shadow-sm border border-[#e0e0e0]">
              <!-- image will be inserted here -->
            </div>
            <h4 class="text-[1.2rem] font-semibold text-[#444444] text-center leading-snug" id="pinjamBookTitle"></h4>
            <p class="text-[0.8rem] text-[#444444]/50 text-center mb-1" id="pinjamBookWriter"></p>
            
            <div class="inline-block bg-[#D4F6FF] text-[#444444] text-[0.6rem] font-medium px-2 py-1 rounded-full text-center">
              Pemilik : <span id="pinjamBookOwner">Ichachellow</span>
            </div>
          </div>

          <!-- Right Part -->
          <div class="pt-2 md:pt-4">
            <h3 class="text-[1.2rem] font-bold mb-1 text-[#444444]">Pinjam Buku Ini</h3>
            <p class="text-[0.8rem] text-[#949494] font-medium mb-6">Lengkapi Formulir untuk Mengajukan Peminjaman</p>

            <div class="mb-6">
              <label for="durasiPeminjaman" class="block text-[0.8rem] font-medium text-[#444444] mb-2">Durasi Peminjaman (Hari)</label>
              <input type="number" id="durasiPeminjaman" min="1" class="w-full font-['Poppins'] text-[0.8rem] text-[#444444] border-[1.5px] border-[#444444] rounded-full px-3 py-1.5 outline-none transition-colors duration-200 bg-white focus:border-[#3FA9F5]" />
            </div>
            
            <div class="mb-10">
              <label for="titikTemu" class="block text-[0.8rem] font-medium text-[#444444] mb-2">Titik Temu</label>
              <input type="text" id="titikTemu" class="w-full font-['Poppins'] text-[0.8rem] text-[#444444] border-[1.5px] border-[#444444] rounded-full px-3 py-1.5 outline-none transition-colors duration-200 bg-white focus:border-[#3FA9F5]" />
            </div>
            
            <button class="w-full p-2 text-[0.9rem] font-bold text-[#444444] bg-[#FFDDAF] rounded-full border-[2px] border-[#444444] transition-all duration-200 hover:-translate-y-[1px] hover:shadow-[0_4px_0_rgba(68,68,68,1)]" id="submitPinjamBtn">Ajukan Peminjaman</button>
          </div>
        </div>

      </div>
    </div>

    {{-- ========== SIMILAR BOOKS ========== --}}
    <section class="py-[60px] bg-white" id="similarBooks">
      <div class="max-w-[1080px] mx-auto px-5 lg:px-10">
        <h2 class="text-xl font-extrabold mb-7">Buku Serupa</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-5" id="similarGrid"></div>
      </div>
    </section>
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

  <div class="fixed bottom-6 right-6 z-[300] flex flex-col gap-2" id="toastContainer"></div>

</body>
</html>