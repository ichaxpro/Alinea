import { state } from './state';
import { starsHtml, truncateSynopsis } from './utils';
import { applyFilters } from './filters';

export function renderSkeletons() {
  state.resultCount.textContent = '...';
  state.grid.classList.remove('hidden');
  state.emptyState.classList.add('hidden');
  state.pagination.innerHTML = '';
  
  state.grid.innerHTML = Array(8).fill(0).map(() => `
    <div class="card-animate bg-white border-[1.5px] border-[#e8e8e8] rounded-2xl overflow-hidden flex flex-col">
      <div class="w-full aspect-[2/3] skeleton"></div>
      <div class="p-3 md:p-5 flex flex-col flex-1 gap-2 mt-1">
        <div class="h-4 md:h-5 skeleton w-3/4 mb-1 rounded"></div>
        <div class="h-3 skeleton w-1/2 mb-3 rounded"></div>
        <div class="hidden md:block h-3 skeleton w-full mb-1 mt-auto rounded"></div>
        <div class="hidden md:block h-3 skeleton w-5/6 rounded"></div>
      </div>
    </div>
  `).join('');
}

export function renderCards(books) {
  try {
    state.grid.innerHTML = books.map((book, i) => {
      const avg = Number(book.rating_avg) || 0;
      const count = Number(book.rating_count) || 0;
      const genres = book.genres || [];
      return `
      <article class="card-animate group bg-white border-[1.5px] border-[#e8e8e8] rounded-2xl overflow-hidden cursor-pointer
                      flex flex-col hover:border-[#444] hover:-translate-y-1 transition-all duration-300"
               style="animation-delay: ${i * 0.06}s"
               data-book-id="${book.id}"
               data-google-id="${book.google_id || ''}">
        
        <!-- Cover -->
        <div class="relative aspect-[2/3] overflow-hidden bg-gray-100">
          ${book.cover
            ? `<img src="${book.cover}" alt="Sampul ${book.judul}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />`
            : `<div class="w-full h-full flex items-center justify-center text-3xl font-black text-white/20 group-hover:scale-105 transition-transform duration-500"
                    style="background: linear-gradient(135deg, ${book.gradient_from || '#C7E7FF'}, ${book.gradient_to || '#FFDDAF'})">
                  ${book.judul.charAt(0)}
               </div>`
          }
          <!-- Rating Badge on Cover -->
          <div class="md:hidden absolute top-2 left-2 z-10 flex items-center gap-1 px-2 py-1 rounded-lg bg-black/60 backdrop-blur-md text-white text-[0.68rem] font-bold">
            <span class="text-[#F5C518]">★</span>
            <span>${avg > 0 ? avg.toFixed(1) : '-'}</span>
          </div>
          <!-- Hover overlay -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 hidden md:flex items-end p-4">
            <span class="text-white text-xs font-semibold bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-full">
              Lihat Detail →
            </span>
          </div>
        </div>

        <!-- Info -->
        <div class="p-3 md:p-5 flex flex-col flex-1">
          <h3 class="text-xs md:text-base font-bold text-text leading-tight mb-0.5 md:mb-1 line-clamp-2">${book.judul}</h3>
          <p class="text-[0.68rem] md:text-[0.78rem] text-text/50 mb-2 line-clamp-1">${book.penulis}</p>

          <!-- Rating (desktop only) -->
          <div class="hidden md:flex items-center gap-1.5 mb-3">
            <div class="flex gap-0.5">${starsHtml(avg)}</div>
            <span class="text-[0.75rem] font-bold text-text">${avg > 0 ? avg.toFixed(1) : '0'}</span>
            <span class="text-[0.7rem] text-text/35">(${count} Ulasan)</span>
          </div>
          <!-- Rating (mobile only) -->
          <div class="flex md:hidden items-center gap-1 mb-2">
            <span class="text-[0.65rem] text-text/40">(${count} Ulasan)</span>
          </div>

          <!-- Synopsis (desktop only) -->
          <p class="hidden md:block text-[0.78rem] text-text/55 leading-relaxed mb-4 flex-1 line-clamp-3">${truncateSynopsis(book.sinopsis, 150)}</p>

          <!-- Genre pills (desktop only) -->
          <div class="hidden md:flex gap-1.5 flex-wrap mb-4">
            ${genres.map(g => `
              <span class="px-3 py-1 text-[0.68rem] font-medium text-text/70 border border-[#e0e0e0] rounded-full">${g}</span>
            `).join('')}
          </div>

          <!-- CTA (desktop only) -->
          <a href="/detail-buku/${book.id || book.google_id}" 
             class="hidden md:inline-flex items-center gap-2 px-5 py-2 text-[0.8rem] font-bold text-text bg-[#FFDDAF] rounded-full border-[1.5px] border-text
                    hover:bg-amber-300 hover:-translate-y-px transition-all duration-200 self-start"
             onclick="event.stopPropagation()">
            Lihat Ulasan
          </a>
        </div>
      </article>
    `;
    }).join('');
  } catch(err) {
    console.error('renderCards error:', err);
  }

  // Click on card = navigate
  state.grid.querySelectorAll('[data-book-id]').forEach(card => {
    card.addEventListener('click', (e) => {
      if (e.target.closest('a')) return; // let <a> handle itself
      const id = card.dataset.bookId;
      const googleId = card.dataset.googleId;
      const target = googleId || id;
      if (target) window.location.href = '/detail-buku/' + target;
    });
  });
}

export function renderActiveFilters(query, genres, minRating) {
  const chips = [];

  if (query) {
    chips.push(`
      <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#FFDDAF]/40 border border-[#FFDDAF] rounded-full hover:bg-[#FFDDAF] transition-colors"
              data-clear="search">
        🔍 "${query}" <span class="text-text/40">×</span>
      </button>
    `);
  }
  if (genres && genres.length > 0) {
    genres.forEach(g => {
      chips.push(`
        <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#C7E7FF]/40 border border-[#C7E7FF] rounded-full hover:bg-[#C7E7FF] transition-colors"
                data-clear="genre" data-genre-value="${g}">
          📂 ${g} <span class="text-text/40">×</span>
        </button>
      `);
    });
  }
  if (minRating) {
    chips.push(`
      <button class="inline-flex items-center gap-1.5 px-3 py-1 text-[0.72rem] font-semibold text-text bg-[#D4F6FF]/40 border border-[#D4F6FF] rounded-full hover:bg-[#D4F6FF] transition-colors"
              data-clear="rating">
        ⭐ ${minRating}+ <span class="text-text/40">×</span>
      </button>
    `);
  }

  state.activeFilters.innerHTML = chips.join('');

  // Chip click to remove that filter
  state.activeFilters.querySelectorAll('[data-clear]').forEach(btn => {
    btn.addEventListener('click', () => {
      const type = btn.dataset.clear;
      if (type === 'search') { state.searchInput.value = ''; }
      if (type === 'genre') {
        const genreVal = btn.dataset.genreValue;
        const container = document.getElementById('ulasan-filter-genre-container');
        if (container) {
          const cb = container.querySelector(`input[type="checkbox"][value="${genreVal}"]`);
          if (cb) { cb.checked = false; cb.dispatchEvent(new Event('change', { bubbles: true })); return; }
        }
        state.filterGenre.value = '';
      }
      if (type === 'rating') { state.filterRating.value = ''; state.filterRating.dispatchEvent(new Event('change')); }
      applyFilters();
    });
  });
}

export function renderPagination(totalPages) {
  if (totalPages <= 1) { state.pagination.innerHTML = ''; return; }

  const btnBase = 'w-9 h-9 rounded-full border-[1.5px] border-[#444] flex items-center justify-center text-sm font-medium transition-all duration-200 cursor-pointer';
  const btnActive = 'bg-[#FFDDAF] text-[#444] scale-105';
  const btnInactive = 'bg-white text-[#444] hover:bg-gray-50 hover:scale-105';

  let html = '';

  // Prev
  html += `<button data-page="prev" ${state.currentPage === 1 ? 'disabled' : ''}
              class="${btnBase} ${state.currentPage === 1 ? 'opacity-30 cursor-not-allowed' : btnInactive}">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
           </button>`;

  // Pages (smart ellipsis)
  const pages = getPageNumbers(state.currentPage, totalPages);
  pages.forEach(p => {
    if (p === '...') {
      html += `<span class="w-9 h-9 flex items-center justify-center text-sm text-text/30">…</span>`;
    } else {
      html += `<button data-page="${p}" class="${btnBase} ${p === state.currentPage ? btnActive : btnInactive}">${p}</button>`;
    }
  });

  // Next
  html += `<button data-page="next" ${state.currentPage === totalPages ? 'disabled' : ''}
              class="${btnBase} ${state.currentPage === totalPages ? 'opacity-30 cursor-not-allowed' : btnInactive}">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
           </button>`;

  state.pagination.innerHTML = html;

  state.pagination.querySelectorAll('[data-page]').forEach(btn => {
    btn.addEventListener('click', () => {
      const val = btn.dataset.page;
      if (val === 'prev' && state.currentPage > 1) state.currentPage--;
      else if (val === 'next' && state.currentPage < totalPages) state.currentPage++;
      else if (val !== 'prev' && val !== 'next') state.currentPage = Number(val);
      applyFilters(false);
      window.scrollTo({ top: state.grid.offsetTop - 100, behavior: 'smooth' });
    });
  });
}

function getPageNumbers(current, total) {
  if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
  const pages = [];
  if (current <= 4)       { pages.push(1,2,3,4,5,'...',total); }
  else if (current >= total - 3) { pages.push(1,'...',total-4,total-3,total-2,total-1,total); }
  else                    { pages.push(1,'...',current-1,current,current+1,'...',total); }
  return pages;
}
