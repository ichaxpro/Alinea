/* ═══════════════════════════════════════════
   detail_buku.js — Alinea Book Detail Page
   Data-driven: semua data dari JS, siap integrasi DB
   
   Untuk integrasi database nanti, cukup ganti BOOK_DATA, REVIEWS,
   dan SIMILAR_BOOKS dengan data dari controller via:
   window.__BOOK_DATA__ = {!! json_encode($book) !!};
═══════════════════════════════════════════ */

// ══════════════════════════════════════
// DUMMY DATA — ganti dengan data dari DB nanti
// Struktur ini sudah match dengan skema migration
// ══════════════════════════════════════

// const BOOK_DATA = {
//   id: 1,
//   judul: 'Pulang',
//   penulis: 'Tere Liye',
//   penerbit: 'Republika',
//   tahun_terbit: 2015,
//   jumlah_halaman: 406,
//   bahasa: 'Indonesia',
//   isbn: '978-602-0851-00-7',
//   kategori: 'Fiksi',
//   sinopsis: 'Bujang, si "Tukang Pukul" Keluarga Tong, besar di dunia ekonomi bayangan yang penuh intrik. Meski tangguh, ia harus hadapi pengkhianatan besar. Di balik aksi menegangkan, Bujang mencari makna "pulang" sejati: menaklukkan rasa takut, berdamai dengan masa lalu, dan kembali kepada jati diri serta Tuhan.',
//   foto_sampul: null, // null = pakai gradient placeholder
//   status: 'tersedia',
//   owners: [
//     { id: 1, name: 'Alinea Library', location: 'Jakarta Pusat' },
//     { id: 2, name: 'Ichachellow', location: 'Bandung' },
//     { id: 3, name: 'Budi Santoso', location: 'Surabaya' }
//   ],
//   genres: ['Horror', 'Thriller'],
//   // Statistik rating (dihitung dari reviews)
//   rating_avg: 4.6,
//   rating_count: 200,
//   rating_distribution: { 5: 156, 4: 32, 3: 8, 2: 3, 1: 1 },
// };



const REVIEWS = [
  { id:1, name:'Budi Ashcroft', initial:'B', rating:5, date:'6 Hari Lalu', text:'Novel ini paket lengkap! Tere Liye menggabungkan thriller aksi ekonomi bayangan dengan pesan filosofis mendalam. Perjalanan Bujang sangat ikonik, mengajarkan bahwa sejauh apa pun kaki melangkah, kita harus kembali ke akar jati diri. Alurnya cepat, penuh kejutan, dan emosional. Sangat layak dibaca!', helpful:30 },
  { id:2, name:'Siti Rahmawati', initial:'S', rating:5, date:'2 Minggu Lalu', text:'Buku ini luar biasa! Tere Liye berhasil membuat saya terpaku dari halaman pertama sampai terakhir. Karakter Bujang ditulis dengan sangat baik — penuh lapisan emosi dan kompleksitas. Ending-nya bikin nangis.', helpful:24 },
  { id:3, name:'Andi Wijaya', initial:'A', rating:4, date:'3 Minggu Lalu', text:'Ceritanya sangat menarik dengan plot twist yang tidak terduga. Satu-satunya kekurangan adalah beberapa bagian di tengah yang terasa agak lambat. Tapi secara keseluruhan, ini adalah salah satu karya terbaik Tere Liye.', helpful:18 },
  { id:4, name:'Dewi Lestari', initial:'D', rating:5, date:'1 Bulan Lalu', text:'Pulang adalah novel yang sempurna untuk siapa pun yang suka thriller dengan sentuhan emosional. World-building ekonomi bayangannya detail dan meyakinkan. Sangat recommended!', helpful:15 },
  { id:5, name:'Reza Pratama', initial:'R', rating:5, date:'1 Bulan Lalu', text:'Masterpiece dari Tere Liye. Buku ini mengajarkan banyak hal tentang keberanian, pengorbanan, dan arti pulang yang sesungguhnya. Wajib baca untuk semua pecinta sastra Indonesia.', helpful:12 },
  { id:6, name:'Fitri Handayani', initial:'F', rating:4, date:'2 Bulan Lalu', text:'Bagus banget! Alur ceritanya bikin penasaran dan gak bisa stop baca. Karakter-karakternya hidup dan relatable. Tere Liye memang penulis kelas wahid.', helpful:9 },
  { id:7, name:'Hendra Gunawan', initial:'H', rating:5, date:'2 Bulan Lalu', text:'Ini buku ke-10 Tere Liye yang saya baca dan tetap tidak mengecewakan. Pulang punya tempat spesial di hati saya. Ceritanya tentang pencarian jati diri sangat universal.', helpful:7 },
  { id:8, name:'Maya Putri', initial:'M', rating:3, date:'3 Bulan Lalu', text:'Ceritanya oke tapi menurut saya agak terlalu panjang di beberapa bagian. Mungkin bisa lebih ringkas. Tapi ending-nya sangat memuaskan.', helpful:5 },
];

const SIMILAR_BOOKS = [
  { title:'Bumi', author:'Tere Liye', color:'from-[#C7E7FF] to-[#D4F6FF]', rating:4.8, initial:'B' },
  { title:'Bulan', author:'Tere Liye', color:'from-[#FFDDAF] to-[#D4F6FF]', rating:4.7, initial:'B' },
  { title:'Matahari', author:'Tere Liye', color:'from-[#FFDDAF] to-[#C7E7FF]', rating:4.8, initial:'M' },
  { title:'Bintang', author:'Tere Liye', color:'from-[#D4F6FF] to-[#FFDDAF]', rating:4.6, initial:'B' },
  { title:'Ceros dan Batozar', author:'Tere Liye', color:'from-[#C7E7FF] to-[#FFDDAF]', rating:4.5, initial:'C' },
];

// ══════════════════════════════════════
// HELPERS
// ══════════════════════════════════════

function starsHtml(rating, size = 'text-base') {
  return [1,2,3,4,5].map(s =>
    `<span class="${size} ${s <= Math.round(rating) ? 'text-[#F5C518]' : 'text-[#ddd]'}">★</span>`
  ).join('');
}

function setTextById(id, text) {
  const el = document.getElementById(id);
  if (el) el.textContent = text;
}

function setHtmlById(id, html) {
  const el = document.getElementById(id);
  if (el) el.innerHTML = html;
}

// ══════════════════════════════════════
// RENDER BOOK DETAIL
// ══════════════════════════════════════

function renderBookDetail(book) {
  // Page title & meta
  document.title = `${book.judul} — ${book.penulis} | Alinea`;
  const metaDesc = document.querySelector('meta[name="description"]');
  if (metaDesc) metaDesc.content = `Detail buku ${book.judul} karya ${book.penulis}. Baca ulasan, lihat rating, dan pinjam buku di Alinea.`;

  // Cover
  const cover = document.getElementById('bookCover');
  if (cover && book.foto_sampul) {
    cover.innerHTML = `<img src="${book.foto_sampul}" alt="Sampul ${book.judul}" class="w-full h-full object-cover" />`;
  }

  // Basic info
  setTextById('bookCategory', book.kategori);
  setTextById('bookTitle', book.judul);
  setTextById('bookMeta', `${book.penulis} • ${book.tahun_terbit} • ${book.jumlah_halaman} Halaman`);
  setTextById('bookSynopsis', book.sinopsis);

  // Modal title
  setTextById('modalBookTitle', book.judul);

  // Pinjam Modal Details
  setTextById('pinjamBookTitle', book.judul);
  setTextById('pinjamBookWriter', book.penulis);
  // Owner is dynamically set upon selection
  const pinjamCover = document.getElementById('pinjamBookCover');
  if (pinjamCover && book.foto_sampul) {
    pinjamCover.innerHTML = `<img src="${book.foto_sampul}" alt="Sampul ${book.judul}" class="w-full h-full object-cover rounded-xl" />`;
  } else if (pinjamCover) {
    pinjamCover.innerHTML = `<div class="absolute inset-0 rounded-xl shadow-[inset_0_0_0_1px_rgba(0,0,0,0.06)] pointer-events-none"></div>`;
  }

  // Rating
  setHtmlById('bookRating', `
    <div class="flex gap-0.5">${starsHtml(book.rating_avg)}</div>
    <span class="text-[0.85rem] text-text/60">
      <strong class="text-text font-bold">${book.rating_avg}</strong> (${book.rating_count} Ulasan)
    </span>
  `);

  // Genres
  setHtmlById('bookGenres', book.genres.map(g =>
    `<span class="px-6 py-1.5 text-[0.8rem] font-medium text-text border-[1.5px] border-[#ddd] rounded-full transition-all duration-200 hover:border-text hover:bg-[#FBFBFB]">${g}</span>`
  ).join(''));

  // Info grid
  const statusColor = book.status === 'tersedia' ? 'text-[#22c55e]' : 'text-red-500';
  const statusText = book.status === 'tersedia' ? 'Tersedia' : 'Dipinjam';
  const infoItems = [
    ['Penerbit', book.penerbit],
    ['ISBN', book.isbn],
    ['Bahasa', book.bahasa],
    ['Ketersediaan', `<span class="font-semibold ${statusColor}">${statusText}</span>`],
  ];
  setHtmlById('bookInfoGrid', infoItems.map(([label, value]) => `
    <div>
      <span class="block text-[0.72rem] font-semibold text-[#444444]/45 uppercase tracking-[0.06em] mb-0.5">${label}</span>
      <span class="text-[0.85rem] font-medium text-[#444444]">${value}</span>
    </div>
  `).join(''));
}

// ══════════════════════════════════════
// RENDER RATING BREAKDOWN
// ══════════════════════════════════════

function renderRatingBreakdown(book) {
  const total = book.rating_count;

  setHtmlById('ratingAvgBlock', `
    <span class="block text-5xl font-black text-[#444444] leading-none">${book.rating_avg.toFixed(1)}</span>
    <div class="my-2 md:mt-2 md:mb-1 flex text-[1.1rem]">${starsHtml(book.rating_avg, 'text-[1.1rem]')}</div>
    <span class="text-[0.78rem] text-[#444444]/50">${total} ulasan</span>
  `);

  setHtmlById('ratingBarsBlock', [5,4,3,2,1].map(star => {
    const count = book.rating_distribution[star] || 0;
    const pct = total > 0 ? ((count / total) * 100).toFixed(1) : 0;
    return `
      <div class="flex items-center gap-2.5">
        <span class="text-[0.78rem] font-semibold text-[#444444] w-3.5 text-right">${star}</span>
        <div class="flex-1 h-2 bg-[#e8e8e8] rounded-full overflow-hidden">
          <div class="h-full bg-[#F5C518] rounded-full transition-all duration-1000 ease-in-out" style="width: ${pct}%"></div>
        </div>
        <span class="text-[0.72rem] text-[#444444]/45 w-7">${count}</span>
      </div>`;
  }).join(''));
}

// ══════════════════════════════════════
// RENDER REVIEWS (paginated)
// ══════════════════════════════════════

let currentReviewPage = 0;
const REVIEWS_PER_PAGE = 4;
let pickedRating = 0;
let helpfulVotes = new Set();

function renderReviews(reset = false) {
  const list = document.getElementById('reviewsList');
  if (reset) { list.innerHTML = ''; currentReviewPage = 0; }

  const start = currentReviewPage * REVIEWS_PER_PAGE;
  const end = Math.min(start + REVIEWS_PER_PAGE, REVIEWS.length);

  REVIEWS.slice(start, end).forEach((r, i) => {
    const voted = helpfulVotes.has(r.id);
    const card = document.createElement('div');
    card.className = 'bg-white rounded-[20px] p-7 md:p-8 mb-4 animate-fade-in-up';
    card.style.animationDelay = `${i * 0.08}s`;

    card.innerHTML = `
      <div class="flex items-center justify-between mb-3.5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#D4F6FF] flex items-center justify-center text-[0.85rem] font-bold text-[#444444]">${r.initial}</div>
          <div>
            <span class="text-[0.9rem] font-bold text-[#444444]">${r.name}</span>
            <span class="text-[0.75rem] text-[#444444]/40 ml-2">${r.date}</span>
          </div>
        </div>
        <div class="flex gap-0.5 text-[0.85rem]">${starsHtml(r.rating, 'text-[0.85rem]')}</div>
      </div>
      <p class="text-[0.88rem] leading-[1.75] text-[#444444]/80 mb-4">${r.text}</p>
      <div class="flex items-center justify-between">
        <span class="text-[0.78rem] text-[#444444]/35 italic">Apakah ulasan ini membantu?</span>
        <button class="btn-helpful inline-flex items-center gap-1.5 px-4 py-1.5 text-[0.78rem] font-semibold text-[#444444] border ${voted ? 'border-[#FFDDAF] bg-[#FFDDAF]' : 'border-[#ddd] bg-white'} rounded-full transition-all duration-200 hover:border-[#444444] hover:bg-[#FBFBFB]" data-id="${r.id}">
          Membantu (${r.helpful})
        </button>
      </div>`;
    list.appendChild(card);
  });

  currentReviewPage++;
  const btn = document.getElementById('loadMoreReviews');
  btn.style.display = end >= REVIEWS.length ? 'none' : 'inline-block';
}

// ══════════════════════════════════════
// RENDER SIMILAR BOOKS
// ══════════════════════════════════════

function renderSimilarBooks() {
  const grid = document.getElementById('similarGrid');
  grid.innerHTML = SIMILAR_BOOKS.map(b => `
    <div class="cursor-pointer transition-transform duration-200 hover:-translate-y-1" role="button" tabindex="0">
      <div class="w-full aspect-[2/3] rounded-xl flex items-center justify-center text-2xl font-black text-[#444444]/20 mb-2.5 overflow-hidden bg-gradient-to-br ${b.color}">${b.initial}</div>
      <h4 class="text-[0.82rem] font-bold text-[#444444] mb-0.5 whitespace-nowrap overflow-hidden text-ellipsis">${b.title}</h4>
      <p class="text-[0.72rem] text-[#444444]/50">${b.author}</p>
      <p class="text-[0.72rem] text-[#F5C518] mt-1">★ ${b.rating}</p>
    </div>
  `).join('');
}

// ══════════════════════════════════════
// RENDER OWNERS TABLE
// ══════════════════════════════════════

function renderOwnersTable(book) {
  const tbody = document.getElementById('ownersTableBody');
  if (!tbody || !book.owners) return;
  
  tbody.innerHTML = book.owners.map(owner => `
    <tr class="border-b-[1.5px] border-[#eee] last:border-0 hover:bg-[#FBFBFB] transition-colors">
      <td class="py-4 px-4">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#D4F6FF] flex items-center justify-center text-[0.7rem] font-bold text-[#444444]">${owner.name[0].toUpperCase()}</div>
          <span class="text-[0.9rem] font-semibold text-[#444444]">${owner.name}</span>
        </div>
      </td>
      <td class="py-4 px-4 text-[0.85rem] text-[#444444]/70">${owner.location}</td>
      <td class="py-4 px-4 text-center">
        <button class="btn-pilih-owner px-4 py-1.5 text-[0.8rem] font-bold text-[#444444] bg-white border-[1.5px] border-[#ddd] rounded-full transition-all duration-200 hover:border-[#444444] hover:bg-[#FBFBFB]" data-name="${owner.name}">Pilih</button>
      </td>
    </tr>
  `).join('');
}

// ══════════════════════════════════════
// EVENT HANDLERS
// ══════════════════════════════════════

// Helpful vote toggle
document.getElementById('reviewsList').addEventListener('click', e => {
  const btn = e.target.closest('.btn-helpful');
  if (!btn) return;
  const id = Number(btn.dataset.id);
  const review = REVIEWS.find(r => r.id === id);
  if (!review) return;

  if (helpfulVotes.has(id)) {
    helpfulVotes.delete(id); review.helpful--;
    btn.classList.remove('border-[#FFDDAF]', 'bg-[#FFDDAF]');
    btn.classList.add('border-[#ddd]', 'bg-white');
  } else {
    helpfulVotes.add(id); review.helpful++;
    btn.classList.remove('border-[#ddd]', 'bg-white');
    btn.classList.add('border-[#FFDDAF]', 'bg-[#FFDDAF]');
  }
  btn.textContent = `Membantu (${review.helpful})`;
});

// Load more
document.getElementById('loadMoreReviews').addEventListener('click', () => renderReviews());

// Review modal
const modalOverlay = document.getElementById('reviewModalOverlay');
function openModal() { modalOverlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeModal() { modalOverlay.classList.remove('active'); document.body.style.overflow = ''; }

document.getElementById('tulisUlasanBtn').addEventListener('click', openModal);
document.getElementById('reviewModalClose').addEventListener('click', closeModal);
modalOverlay.addEventListener('click', e => { if (e.target === modalOverlay) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// Star picker
const starPicker = document.getElementById('starPicker');
starPicker.querySelectorAll('.pick-star').forEach(star => {
  star.addEventListener('click', () => {
    pickedRating = Number(star.dataset.val);
    starPicker.querySelectorAll('.pick-star').forEach(s =>
      s.classList.toggle('active', Number(s.dataset.val) <= pickedRating)
    );
  });
  star.addEventListener('mouseenter', () => {
    const val = Number(star.dataset.val);
    starPicker.querySelectorAll('.pick-star').forEach(s =>
      s.style.color = Number(s.dataset.val) <= val ? '#F5C518' : '#ddd'
    );
  });
});
starPicker.addEventListener('mouseleave', () => {
  starPicker.querySelectorAll('.pick-star').forEach(s =>
    s.style.color = s.classList.contains('active') ? '#F5C518' : '#ddd'
  );
});

// Submit review
document.getElementById('submitReviewBtn').addEventListener('click', () => {
  const name = document.getElementById('reviewName').value.trim();
  const text = document.getElementById('reviewText').value.trim();
  if (!name || !text || pickedRating === 0) { showToast('⚠️ Lengkapi semua field dan pilih rating'); return; }

  REVIEWS.unshift({ id: Date.now(), name, initial: name[0].toUpperCase(), rating: pickedRating, date: 'Baru saja', text, helpful: 0 });
  renderReviews(true);
  closeModal();
  document.getElementById('reviewName').value = '';
  document.getElementById('reviewText').value = '';
  pickedRating = 0;
  starPicker.querySelectorAll('.pick-star').forEach(s => s.classList.remove('active'));
  showToast('✅ Ulasan berhasil dikirim!');
  document.getElementById('reviewsSection').scrollIntoView({ behavior: 'smooth' });
});

// Save button
document.getElementById('simpanBtn').addEventListener('click', function() {
  this.classList.toggle('saved');
  showToast(this.classList.contains('saved') ? '🔖 Buku disimpan!' : '🔖 Buku dihapus dari simpanan');
});

// Owners modal
const ownersModalOverlay = document.getElementById('ownersModalOverlay');
function openOwnersModal() { ownersModalOverlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
function closeOwnersModal() { ownersModalOverlay.classList.remove('active'); document.body.style.overflow = ''; }

// Pinjam button modal
const pinjamModalOverlay = document.getElementById('pinjamModalOverlay');
function openPinjamModal() { pinjamModalOverlay.classList.add('active'); document.body.style.overflow = 'hidden'; }
function closePinjamModal() { pinjamModalOverlay.classList.remove('active'); document.body.style.overflow = ''; }

// Handle Pinjam button click: show owners first
document.getElementById('pinjamBtn').addEventListener('click', () => {
  renderOwnersTable(window.__BOOK_DATA__);
  openOwnersModal();
});

// Close listeners for owners modal
document.getElementById('ownersModalClose').addEventListener('click', closeOwnersModal);
if (ownersModalOverlay) {
  ownersModalOverlay.addEventListener('click', e => { if (e.target === ownersModalOverlay) closeOwnersModal(); });
}

// Close listeners for pinjam modal
document.getElementById('pinjamModalClose').addEventListener('click', closePinjamModal);
if (pinjamModalOverlay) {
  pinjamModalOverlay.addEventListener('click', e => { if (e.target === pinjamModalOverlay) closePinjamModal(); });
}

document.addEventListener('keydown', e => { 
  if (e.key === 'Escape') {
    if (ownersModalOverlay && ownersModalOverlay.classList.contains('active')) closeOwnersModal();
    if (pinjamModalOverlay && pinjamModalOverlay.classList.contains('active')) closePinjamModal();
  }
});

// Handle owner selection
document.getElementById('ownersTableBody').addEventListener('click', (e) => {
  const btn = e.target.closest('.btn-pilih-owner');
  if (!btn) return;
  
  const selectedOwnerName = btn.dataset.name;
  
  // Update pinjam modal with selected owner
  setTextById('pinjamBookOwner', selectedOwnerName);
  
  closeOwnersModal();
  openPinjamModal();
});

// Submit pinjam request
document.getElementById('submitPinjamBtn').addEventListener('click', () => {
  const durasi = document.getElementById('durasiPeminjaman').value.trim();
  const titik = document.getElementById('titikTemu').value.trim();
  if (!durasi || !titik) { showToast('⚠️ Lengkapi durasi dan titik temu'); return; }
  
  closePinjamModal();
  document.getElementById('durasiPeminjaman').value = '';
  document.getElementById('titikTemu').value = '';
  showToast('📚 Permintaan peminjaman diajukan! Cek notifikasi secara berkala.');
});

// Sort reviews
document.getElementById('sortSelect').addEventListener('change', function() {
  const sorters = {
    newest: (a,b) => b.id - a.id, oldest: (a,b) => a.id - b.id,
    highest: (a,b) => b.rating - a.rating, lowest: (a,b) => a.rating - b.rating,
    helpful: (a,b) => b.helpful - a.helpful,
  };
  REVIEWS.sort(sorters[this.value] || sorters.newest);
  renderReviews(true);
});

// Toast
function showToast(msg) {
  const t = document.createElement('div');
  t.className = 'toast bg-[#444444] text-white px-6 py-3.5 rounded-xl text-[0.85rem] font-semibold shadow-[0_8px_24px_rgba(0,0,0,0.15)]';
  t.textContent = msg;
  document.getElementById('toastContainer').appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3200);
}

// ══════════════════════════════════════
// INIT — render semua dari data
// ══════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
  // Kalau nanti data dari DB, cukup ganti: const book = window.__BOOK_DATA__ || BOOK_DATA;
  const book = window.__BOOK_DATA__;

  renderBookDetail(book);
  renderRatingBreakdown(book);
  renderReviews();
  renderSimilarBooks();
});