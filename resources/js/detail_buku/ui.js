import { state } from './state';
import { starsHtml, setTextById, setHtmlById } from './utils';

export function refreshRatingStats() {
  const book  = window.__BOOK_DATA__;
  const total = state.REVIEWS.length;

  const avg = total > 0
    ? state.REVIEWS.reduce((sum, r) => sum + r.rating, 0) / total
    : 0;

  const dist = {};
  state.REVIEWS.forEach(r => { dist[r.rating] = (dist[r.rating] || 0) + 1; });

  book.rating_avg          = Math.round(avg * 10) / 10;
  book.rating_count        = total;
  book.rating_distribution = dist;

  // Update header rating badge
  setHtmlById('bookRating', `
    <div class="flex gap-0.5">${starsHtml(book.rating_avg)}</div>
    <span class="text-[0.85rem] text-text/60">
      <strong class="text-text font-bold">${book.rating_avg}</strong> (${book.rating_count} Ulasan)
    </span>
  `);

  // Update rating breakdown bars
  renderRatingBreakdown(book);
}

export function updateUlasanButton(hasReviewed, myReviewId) {
  const btn = document.getElementById('tulisUlasanBtn');
  if (!btn) {
    return;
  }

  if (hasReviewed && myReviewId) {
    btn.className = 'flex-1 sm:flex-initial inline-flex justify-center items-center gap-2 px-4 sm:px-6 py-3 text-[0.8rem] md:text-[0.85rem] font-bold text-text bg-white rounded-full border-[1.5px] border-[#ddd] transition-all duration-200 hover:-translate-y-px hover:border-text hover:shadow-[0_4px_12px_rgba(0,0,0,0.08)] whitespace-nowrap';
    btn.innerHTML = `<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M14.5 2.5a2.121 2.121 0 0 1 3 3L6 17l-4 1 1-4L14.5 2.5z"/></svg>
      Edit Ulasan`;

    btn.dataset.myReviewId = myReviewId;
  } else {
    btn.className = 'flex-1 sm:flex-initial inline-flex justify-center items-center gap-2 px-4 sm:px-6 py-3 text-[0.8rem] md:text-[0.85rem] font-bold text-text bg-accent rounded-full border-[1.5px] border-text transition-all duration-200 hover:-translate-y-px hover:shadow-[0_4px_12px_rgba(0,0,0,0.1)] whitespace-nowrap';
    btn.innerHTML = `
      <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M14.5 2.5a2.121 2.121 0 0 1 3 3L6 17l-4 1 1-4L14.5 2.5z"/></svg>
      Tulis Ulasan
    `;
    delete btn.dataset.myReviewId;
  }
}

export function renderBookDetail(book) {
  // Page title & meta
  document.title = `${book.judul} — ${book.penulis} | Alinea`;
  const metaDesc = document.querySelector('meta[name="description"]');
  if (metaDesc) metaDesc.content = `Detail buku ${book.judul} karya ${book.penulis}. Baca ulasan, lihat rating, dan pinjam buku di Alinea.`;

  // Background cover backdrop
  const backdrop = document.getElementById('detailBackdrop');
  if (backdrop && book.foto_sampul) {
    backdrop.style.backgroundImage = `url(${book.foto_sampul})`;
  }

  // Cover
  const cover = document.getElementById('bookCover');
  if (cover && book.foto_sampul) {
    cover.innerHTML = `<img src="${book.foto_sampul}" alt="Sampul ${book.judul}" class="w-full h-full object-cover" />`;
  }

  // Sticky bar details
  setTextById('stickyBookTitle', book.judul);
  setTextById('stickyBookAuthor', book.penulis);
  const stickyCover = document.getElementById('stickyBookCover');
  if (stickyCover && book.foto_sampul) {
    stickyCover.innerHTML = `<img src="${book.foto_sampul}" alt="Sampul ${book.judul}" class="w-full h-full object-cover" />`;
  }

  // Basic info
  setTextById('bookCategory', book.kategori);
  setTextById('bookTitle', book.judul);
  setTextById('bookMeta', `${book.penulis} • ${book.tahun_terbit} • ${book.jumlah_halaman} Halaman`);

  // Sinopsis dengan Show More
  const synopsisEl = document.getElementById('bookSynopsis');
  if (synopsisEl) {
    synopsisEl.textContent = book.sinopsis;
    synopsisEl.classList.add('line-clamp-4'); // Truncate text to 4 lines
    synopsisEl.classList.replace('mb-5', 'mb-1'); // Reduce bottom margin since button will be below it

    // Hapus button lama jika ada (saat render ulang)
    const oldBtn = document.getElementById('toggleSynopsisBtn');
    if (oldBtn) oldBtn.remove();

    requestAnimationFrame(() => {
      // Cek apakah teks terpotong
      if (synopsisEl.scrollHeight > synopsisEl.clientHeight || synopsisEl.scrollHeight > 100) {
        const btn = document.createElement('button');
        btn.id = 'toggleSynopsisBtn';
        btn.className = 'text-[#444444]/60 text-[0.78rem] font-bold underline decoration-[1.5px] decoration-[#444444]/30 hover:text-[#444444] hover:decoration-[#444444] transition-all mb-5 block';
        btn.textContent = 'Baca Selengkapnya';
        synopsisEl.parentNode.insertBefore(btn, synopsisEl.nextSibling);

        let isExpanded = false;
        btn.addEventListener('click', () => {
          isExpanded = !isExpanded;
          if (isExpanded) {
            synopsisEl.classList.remove('line-clamp-4');
            btn.textContent = 'Sembunyikan';
          } else {
            synopsisEl.classList.add('line-clamp-4');
            btn.textContent = 'Baca Selengkapnya';
          }
        });
      } else {
        // Jika tidak dipotong, kembalikan margin bottom
        synopsisEl.classList.replace('mb-1', 'mb-5');
      }
    });
  }

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
    `<span class="px-5 py-1 text-[0.75rem] font-semibold text-[#444444]/70 bg-[#F5F5F5] rounded-full transition-all duration-200 hover:bg-[#EAEAEA]">${g}</span>`
  ).join(''));

  // Info grid
  const owners = window.__BOOK_DATA__.owners ?? [];
  const isAvailable = owners.length > 0;

  const statusColor = isAvailable ? 'text-[#22c55e]' : 'text-rose-500';
  const statusText = isAvailable ? 'Tersedia' : 'Tidak Tersedia';

  const infoItems = [
    ['Penerbit', book.penerbit],
    ['ISBN', book.isbn],
    ['Bahasa', book.bahasa],
    ['Ketersediaan', `<span class="font-semibold ${statusColor}">${statusText}</span>`],
  ];
  setHtmlById('bookInfoGrid', infoItems.map(([label, value]) => `
    <div>
      <span class="block text-[0.68rem] font-bold text-[#444444]/40 uppercase tracking-[0.08em] mb-1">${label}</span>
      <span class="text-[0.85rem] font-medium text-[#444444]">${value}</span>
    </div>
  `).join(''));

  const pinjamBtn = document.getElementById('pinjamBtn');
  const stickyPinjamBtn = document.getElementById('stickyPinjamBtn');
  if (!isAvailable) {
    pinjamBtn.disabled = true;
    pinjamBtn.style.opacity = '0.45';
    pinjamBtn.style.cursor = 'not-allowed';
    pinjamBtn.title = 'Belum ada pemilik yang meminjamkan buku ini.';
    if (stickyPinjamBtn) {
      stickyPinjamBtn.disabled = true;
      stickyPinjamBtn.style.opacity = '0.45';
      stickyPinjamBtn.style.cursor = 'not-allowed';
      stickyPinjamBtn.title = 'Belum ada pemilik yang meminjamkan buku ini.';
    }
  }
}

export function renderRatingBreakdown(book) {
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
