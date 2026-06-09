import { state } from './state';
import { renderCards, renderPagination, renderActiveFilters } from './ui';

export function populateGenres() {
  const allBooks = window.__BOOKS_DATA__ || [];
  const predefinedGenres = [
    'Biografi', 'Bisnis', 'Distopia', 'Edukasi', 'Fantasi', 'Fiksi', 'Horror', 'Komik',
    'Misteri', 'Non-Fiksi', 'Pengembangan Diri', 'Petualangan', 'Puisi', 'Religi',
    'Romansa', 'Sains & Teknologi', 'Sci-Fi', 'Sejarah', 'Thriller', 'Teenlit'
  ];
  const genres = [...new Set([...predefinedGenres, ...(allBooks.flatMap(b => b.genres))])].sort();
  
  // Desktop genre select container
  const desktopGenreContainer = document.getElementById('ulasan-filter-genre-container');
  const desktopSelect = document.getElementById('ulasan-filter-genre');
  const desktopOptionsDiv = desktopGenreContainer?.querySelector('.custom-select-options');
  
  // Mobile genre select container
  const mobileGenreContainer = document.getElementById('mobile-filter-genre-container');
  const mobileSelect = document.getElementById('mobile-filter-genre');
  const mobileOptionsDiv = mobileGenreContainer?.querySelector('.custom-select-options');
  
  genres.forEach(g => {
    // Hidden Select options
    const optDesktop = document.createElement('option');
    optDesktop.value = g;
    optDesktop.textContent = g;
    if (desktopSelect) desktopSelect.appendChild(optDesktop);
    
    if (mobileSelect) {
      const optMobile = optDesktop.cloneNode(true);
      mobileSelect.appendChild(optMobile);
    }
    
    // Custom Checkbox for Desktop
    if (desktopOptionsDiv) {
        const label = document.createElement('label');
        label.className = 'custom-select-option px-4 py-2 text-sm hover:bg-gray-50 transition-colors cursor-pointer flex items-start gap-2';
        label.innerHTML = `
            <div class="mt-0.5 relative flex items-center justify-center w-4 h-4 border-2 border-gray-300 rounded focus-within:border-[#444] bg-white transition-colors">
                <input type="checkbox" name="ulasan-filter-genre[]" value="${g}" data-label="${g}" class="peer absolute inset-0 opacity-0 cursor-pointer w-full h-full m-0">
                <svg class="peer-checked:opacity-100 opacity-0 pointer-events-none text-[#444]" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <span class="flex-1 text-gray-600 peer-checked:font-bold peer-checked:text-[#444] leading-tight select-none">${g}</span>
        `;
        desktopOptionsDiv.appendChild(label);
        
        // Add event listener to new checkbox
        const cb = label.querySelector('input[type="checkbox"]');
        cb.addEventListener('change', () => {
            const container = cb.closest('.custom-select-container');
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            const selected = Array.from(checkboxes).filter(c => c.checked);
            const labelEl = container.querySelector('.custom-select-label');
            
            if (selected.length === 0) {
                labelEl.textContent = 'Semua Genre';
            } else if (selected.length === 1) {
                labelEl.textContent = selected[0].dataset.label;
            } else if (selected.length <= 2) {
                 labelEl.textContent = selected.map(c => c.dataset.label).join(', ');
            } else {
                labelEl.textContent = `${selected.length} Dipilih`;
            }
            
            Array.from(desktopSelect.options).forEach(opt => {
                opt.selected = selected.some(c => c.value === opt.value);
            });
            desktopSelect.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    // Custom Checkbox for Mobile
    if (mobileOptionsDiv) {
        const label = document.createElement('label');
        label.className = 'custom-select-option px-4 py-2 text-sm hover:bg-gray-50 transition-colors cursor-pointer flex items-start gap-2';
        label.innerHTML = `
            <div class="mt-0.5 relative flex items-center justify-center w-4 h-4 border-2 border-gray-300 rounded focus-within:border-[#444] bg-white transition-colors">
                <input type="checkbox" name="mobile-filter-genre[]" value="${g}" data-label="${g}" class="peer absolute inset-0 opacity-0 cursor-pointer w-full h-full m-0">
                <svg class="peer-checked:opacity-100 opacity-0 pointer-events-none text-[#444]" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <span class="flex-1 text-gray-600 peer-checked:font-bold peer-checked:text-[#444] leading-tight select-none">${g}</span>
        `;
        mobileOptionsDiv.appendChild(label);
        
        // Add event listener to new checkbox
        const cb = label.querySelector('input[type="checkbox"]');
        cb.addEventListener('change', () => {
            const container = cb.closest('.custom-select-container');
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            const selected = Array.from(checkboxes).filter(c => c.checked);
            const labelEl = container.querySelector('.custom-select-label');
            
            if (selected.length === 0) {
                labelEl.textContent = 'Semua Genre';
            } else if (selected.length === 1) {
                labelEl.textContent = selected[0].dataset.label;
            } else if (selected.length <= 2) {
                 labelEl.textContent = selected.map(c => c.dataset.label).join(', ');
            } else {
                labelEl.textContent = `${selected.length} Dipilih`;
            }
            
            Array.from(mobileSelect.options).forEach(opt => {
                opt.selected = selected.some(c => c.value === opt.value);
            });
            // We don't dispatch change immediately on mobile, it relies on "Terapkan" button
        });
    }
  });
}

export function applyFilters(resetPage = true) {
  if (resetPage) state.currentPage = 1;

  const allBooks = window.__BOOKS_DATA__ || [];
  const query      = state.searchInput.value.toLowerCase().trim();
  
  // Read selected genres (multiple)
  const selectedGenres = Array.from(state.filterGenre.selectedOptions).map(opt => opt.value).filter(val => val !== "");
  
  const minRating  = state.filterRating.value ? Number(state.filterRating.value) : 0;
  const sortKey    = state.sortSelect.value;

  // Sync with mobile controls
  const mobileGenre = document.getElementById('mobile-filter-genre');
  const mobileRating = document.getElementById('mobile-filter-rating');
  const mobileSort = document.getElementById('mobile-sort');
  
  if (mobileGenre) {
      Array.from(mobileGenre.options).forEach(opt => {
          opt.selected = selectedGenres.includes(opt.value);
      });
      mobileGenre.dispatchEvent(new Event('change'));
  }
  if (mobileRating) {
      mobileRating.value = minRating ? String(minRating) : '';
      mobileRating.dispatchEvent(new Event('change'));
  }
  if (mobileSort) {
      mobileSort.value = sortKey;
      mobileSort.dispatchEvent(new Event('change'));
  }

  // Toggle clear button
  state.searchClear.classList.toggle('hidden', !query);

  let result = allBooks.filter(b => {
    const matchSearch = !query
      || b.judul.toLowerCase().includes(query)
      || b.penulis.toLowerCase().includes(query)
      || b.genres.some(g => g.toLowerCase().includes(query));
      
    // Match ANY of the selected genres. If none selected, allow all.
    const matchGenre = selectedGenres.length === 0 || b.genres.some(g => selectedGenres.includes(g));
    const matchRating = b.rating_avg >= minRating;
    return matchSearch && matchGenre && matchRating;
  });

  // Sort
  result = sortBooks(result, sortKey);

  // Paginate
  const totalPages = Math.max(1, Math.ceil(result.length / state.PER_PAGE));
  if (state.currentPage > totalPages) state.currentPage = totalPages;
  const start = (state.currentPage - 1) * state.PER_PAGE;
  const paged = result.slice(start, start + state.PER_PAGE);

  // Render
  state.resultCount.textContent = result.length;
  renderCards(paged);
  renderPagination(totalPages);
  renderActiveFilters(query, selectedGenres, minRating);

  // Empty state
  if (result.length === 0) {
    state.grid.classList.add('hidden');
    state.emptyState.classList.remove('hidden');
  } else {
    state.grid.classList.remove('hidden');
    state.emptyState.classList.add('hidden');
  }
}

export function sortBooks(list, key) {
  const sorted = [...list];
  switch (key) {
    case 'rating-desc':  return sorted.sort((a, b) => b.rating_avg - a.rating_avg);
    case 'rating-asc':   return sorted.sort((a, b) => a.rating_avg - b.rating_avg);
    case 'reviews-desc': return sorted.sort((a, b) => b.rating_count - a.rating_count);
    case 'title-asc':    return sorted.sort((a, b) => a.judul.localeCompare(b.judul));
    case 'title-desc':   return sorted.sort((a, b) => b.judul.localeCompare(a.judul));
    case 'newest':       return sorted.sort((a, b) => b.tahun - a.tahun);
    default:             return sorted;
  }
}
