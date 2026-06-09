import "./custom-select";
import { state } from './katalog/state';
import { fetchBooks, fetchRatingStats } from './katalog/api';
import { showToast } from './katalog/utils';
import { renderSkeletons } from './katalog/ui';
import { populateGenres, applyFilters } from './katalog/filters';

/**
 * katalog.js — Alinea Book Browser / Review Listing
 */

function initKatalog() {
  // DOM REFS
  state.grid           = document.getElementById('ulasan-grid');
  state.pagination     = document.getElementById('ulasan-pagination');
  state.searchInput    = document.getElementById('ulasan-search-input');
  state.searchClear    = document.getElementById('ulasan-search-clear');
  state.filterGenre    = document.getElementById('ulasan-filter-genre');
  state.filterRating   = document.getElementById('ulasan-filter-rating');
  state.sortSelect     = document.getElementById('ulasan-sort');
  state.resultCount    = document.getElementById('ulasan-result-count');
  state.emptyState     = document.getElementById('ulasan-empty');
  state.resetBtn       = document.getElementById('ulasan-reset-filters');
  state.activeFilters  = document.getElementById('ulasan-active-filters');
  state.scrollTopBtn   = document.getElementById('scrollTopBtn');

  // Guard: if we're not on the katalog page, bail out
  if (!state.grid || !state.searchInput) return;

  // EVENT LISTENERS

  // Debounced search
  state.searchInput.addEventListener('input', () => {
    clearTimeout(state.debounceTimer);
    const query = state.searchInput.value.trim();
    if (query.length < 3) {
      window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
      applyFilters();
      return;
    }
    
    // Show loading skeleton while typing/waiting for debounce
    renderSkeletons();
    
    state.debounceTimer = setTimeout(async () => {
      try {
        const books = await fetchBooks(query);
        window.__BOOKS_DATA__ = books;
        applyFilters();
        const allIds = books.map(b => b.google_id || String(b.id)).filter(Boolean);
        const stats = await fetchRatingStats(allIds);
        window.__BOOKS_DATA__ = window.__BOOKS_DATA__.map(b => {
          const key = b.google_id || String(b.id);
          const s = stats[key];
          if (s) {
            return {...b, rating_avg: s.rating_avg, rating_count: s.rating_count};
          }
          return b;
        });
        applyFilters(false);
      } catch (err) {
        showToast('Gagal mencari buku');
        window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
        applyFilters();
      }
    }, 400);
  });

  // Clear search
  state.searchClear.addEventListener('click', () => {
    state.searchInput.value = '';
    window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
    applyFilters();
    state.searchInput.focus();
  });

  // Keyboard shortcut: "/" to focus search
  document.addEventListener('keydown', (e) => {
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
      e.preventDefault();
      state.searchInput.focus();
      showToast('💡 Tekan / untuk mencari buku');
    }
    if (e.key === 'Escape' && document.activeElement === state.searchInput) {
      state.searchInput.blur();
    }
  });

  // Filters & sort
  state.filterGenre.addEventListener('change', () => applyFilters());
  state.filterRating.addEventListener('change', () => applyFilters());
  state.sortSelect.addEventListener('change', () => applyFilters());

  // Reset all filters
  state.resetBtn.addEventListener('click', () => {
    state.searchInput.value = '';
    state.filterGenre.value = '';
    state.filterRating.value = '';
    state.sortSelect.value = 'rating-desc';
    window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
    applyFilters();
    showToast('Filter telah direset');
  });

  // Scroll-to-top button
  window.addEventListener('scroll', () => {
    state.scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
  }, { passive: true });

  state.scrollTopBtn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // Mobile Filter Dialog Event Listeners
  const mobileFilterDialog = document.getElementById('mobile-filter-dialog');
  const mobileFilterBtn = document.getElementById('ulasan-mobile-filter-btn');
  const mobileFilterClose = document.getElementById('close-filter-dialog');
  const mobileFilterReset = document.getElementById('mobile-filter-reset');

  if (mobileFilterBtn && mobileFilterDialog) {
    mobileFilterBtn.addEventListener('click', () => {
      mobileFilterDialog.showModal();
    });
  }

  if (mobileFilterClose && mobileFilterDialog) {
    mobileFilterClose.addEventListener('click', () => {
      mobileFilterDialog.close();
    });
  }

  if (mobileFilterDialog) {
    mobileFilterDialog.addEventListener('click', (e) => {
      const rect = mobileFilterDialog.getBoundingClientRect();
      const isInDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height &&
        rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
      if (!isInDialog) {
        mobileFilterDialog.close();
      }
    });

    const form = mobileFilterDialog.querySelector('form');
    if (form) {
      form.addEventListener('submit', (e) => {
        state.filterGenre.value = document.getElementById('mobile-filter-genre').value;
        state.filterRating.value = document.getElementById('mobile-filter-rating').value;
        state.sortSelect.value = document.getElementById('mobile-sort').value;
        applyFilters();
      });
    }
  }

  if (mobileFilterReset && mobileFilterDialog) {
    mobileFilterReset.addEventListener('click', () => {
      state.searchInput.value = '';
      state.filterGenre.value = '';
      state.filterRating.value = '';
      state.sortSelect.value = 'rating-desc';
      window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
      applyFilters();
      mobileFilterDialog.close();
      showToast('Filter telah direset');
    });
  }

  // Load data and render
  window.__BOOKS_DATA__ = window.__FEATURED_BOOKS__;
  populateGenres();
  applyFilters();

  // Fetch live rating stats for featured books
  const ids = (window.__FEATURED_BOOKS__ || []).map(b => String(b.id));
  fetchRatingStats(ids).then(stats => {
    if (!window.__BOOKS_DATA__) return;
    window.__BOOKS_DATA__ = window.__BOOKS_DATA__.map(b => {
      const key = String(b.id);
      const s = stats[key];
      if (s) {
        return {...b, rating_avg: s.rating_avg, rating_count: s.rating_count};
      }
      return b;
    });
    // only re-render if user hasn't typed in search yet
    if (state.searchInput.value.trim().length < 3) {
      applyFilters(false);
    }
  });
}

document.addEventListener('DOMContentLoaded', initKatalog);
