import { state } from './state.js';
import { getPrimaryActionLabel } from './utils.js';
import { openModal } from './modal.js';
import { openEditClub } from './form.js';
import { leaveClub, setJoined } from './api.js';

export function populateCategories() {
    const filterCategory = document.getElementById('klub-filter-category');
    if (!filterCategory || filterCategory.options.length > 1) return;

    const serverCats = window.__KLUB_CATEGORIES__ || null;
    const cats = serverCats ? serverCats.slice().sort() : [...new Set(state.CLUBS.map(c => c.category))].sort();
    cats.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat;
        opt.textContent = cat;
        filterCategory.appendChild(opt);
    });
}

export function sortClubs(list, key) {
    const sorted = [...list];
    switch (key) {
        case 'name-asc':     return sorted.sort((a, b) => a.name.localeCompare(b.name));
        case 'name-desc':    return sorted.sort((a, b) => b.name.localeCompare(a.name));
        case 'members-desc': return sorted.sort((a, b) => b.members - a.members);
        case 'members-asc':  return sorted.sort((a, b) => a.members - b.members);
        case 'newest':       return sorted.reverse();
        default:             return sorted;
    }
}

export function applyFilters(resetPage = true) {
    if (resetPage) state.currentPage = 1;

    const searchInput = document.getElementById('klub-search-input');
    const filterCategory = document.getElementById('klub-filter-category');
    const filterStatus = document.getElementById('klub-filter-status');
    const sortSelect = document.getElementById('klub-sort');

    const query    = (searchInput?.value || '').toLowerCase().trim();
    const category = filterCategory?.value || '';
    const status   = filterStatus?.value || 'all';
    const sortKey  = sortSelect?.value || 'name-asc';

    // Sync with mobile controls
    const mobileCategory = document.getElementById('mobile-filter-category');
    const mobileStatus = document.getElementById('mobile-filter-status');
    const mobileSort = document.getElementById('mobile-sort');
    if (mobileCategory && mobileCategory.value !== category) {
        mobileCategory.value = category;
        mobileCategory.dispatchEvent(new Event('change'));
    }
    if (mobileStatus && mobileStatus.value !== status) {
        mobileStatus.value = status;
        mobileStatus.dispatchEvent(new Event('change'));
    }
    if (mobileSort && mobileSort.value !== sortKey) {
        mobileSort.value = sortKey;
        mobileSort.dispatchEvent(new Event('change'));
    }

    let result = state.CLUBS.filter(c => {
        const matchSearch = !query
            || c.name.toLowerCase().includes(query)
            || c.category.toLowerCase().includes(query);
        const matchCat = !category || c.category === category;
        const matchStatus = status === 'all' || (status === 'joined' && c.joined) || (status === 'owned' && c.isOwner);
        return matchSearch && matchCat && matchStatus;
    });

    result = sortClubs(result, sortKey);
    const totalPages = Math.max(1, Math.ceil(result.length / state.PER_PAGE));
    if (state.currentPage > totalPages) state.currentPage = totalPages;
    const start = (state.currentPage - 1) * state.PER_PAGE;
    renderCards(result.slice(start, start + state.PER_PAGE));
    renderPagination(totalPages);
}

export function renderPagination(totalPages) {
    const pagination = document.getElementById('klub-pagination');
    const grid = document.getElementById('klub-grid');
    if (!pagination || !grid) return;

    if (totalPages <= 1) { pagination.innerHTML = ''; return; }
    const btnBase = 'w-9 h-9 rounded-full border-[1.5px] border-[#444] flex items-center justify-center text-sm font-medium transition-colors cursor-pointer';
    const btnActive = 'bg-[#FFDDAF] text-[#444]';
    const btnInactive = 'bg-white text-[#444] hover:bg-gray-50';
    const isMobile = window.innerWidth < 640;
    const maxVisible = isMobile ? 5 : 10;
    let startPage = Math.max(1, state.currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    let html = '';
    html += `<button data-page="prev" ${state.currentPage === 1 ? 'disabled' : ''} class="${btnBase} ${state.currentPage === 1 ? 'opacity-30 cursor-not-allowed' : btnInactive}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></button>`;
    if (startPage > 1) {
        html += `<button data-page="1" class="${btnBase} ${btnInactive}">1</button>`;
        if (startPage > 2) html += `<span class="text-gray-400 text-xs px-1">...</span>`;
    }
    for (let i = startPage; i <= endPage; i++) {
        html += `<button data-page="${i}" class="${btnBase} ${i === state.currentPage ? btnActive : btnInactive}">${i}</button>`;
    }
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += `<span class="text-gray-400 text-xs px-1">...</span>`;
        html += `<button data-page="${totalPages}" class="${btnBase} ${btnInactive}">${totalPages}</button>`;
    }
    html += `<button data-page="next" ${state.currentPage === totalPages ? 'disabled' : ''} class="${btnBase} ${state.currentPage === totalPages ? 'opacity-30 cursor-not-allowed' : btnInactive}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></button>`;
    
    pagination.innerHTML = html;
    pagination.querySelectorAll('[data-page]').forEach(btn => {
        btn.addEventListener('click', () => {
            const val = btn.dataset.page;
            if (val === 'prev' && state.currentPage > 1) state.currentPage--;
            else if (val === 'next' && state.currentPage < totalPages) state.currentPage++;
            else if (val !== 'prev' && val !== 'next') state.currentPage = Number(val);
            applyFilters(false);
            window.scrollTo({ top: grid.offsetTop - 80, behavior: 'smooth' });
        });
    });
}

export function renderCards(clubs) {
    const grid = document.getElementById('klub-grid');
    const pagination = document.getElementById('klub-pagination');
    if (!grid) return;

    if (clubs.length === 0) {
        grid.innerHTML = `<div class="col-span-full text-center py-16 text-gray-400"><p class="text-sm font-medium">Tidak ada klub ditemukan.</p></div>`;
        if (pagination) pagination.innerHTML = '';
        return;
    }
    grid.innerHTML = clubs.map(club => {
        const coverHtml = club.coverUrl
            ? `<div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl border-[1.5px] border-[#444] flex-shrink-0 bg-cover bg-center" style="background-image: url('${club.coverUrl}')"></div>`
            : `<div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl border-[1.5px] border-[#444] flex-shrink-0" style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})"></div>`;

        let btnClass = '';
        if (club.isOwner) {
            btnClass = 'bg-white text-[#444] hover:bg-gray-50';
        } else if (club.joined) {
            btnClass = 'bg-red-50 text-red-500 border-red-500 hover:bg-red-100 hover:border-red-600 hover:text-red-600';
        } else {
            btnClass = 'bg-[#FFDDAF] text-[#444] border-[#444] hover:bg-[#ffcf90] hover:-translate-y-0.5 hover:-translate-x-0.5 hover:shadow-[3px_3px_0px_#444] active:translate-y-0 active:translate-x-0 active:shadow-none';
        }
        
        // Base classes for the button
        const baseBtnClass = 'font-bold text-xs sm:text-sm px-4 sm:px-6 py-2 sm:py-2.5 rounded-full border-[1.5px] transition-all';

        return `
        <article data-club-id="${club.id}"
                 class="group bg-white border-[1.5px] border-[#e8e8e8] rounded-[1.25rem] p-4 sm:p-5 cursor-pointer
                        flex flex-col w-full hover:-translate-y-1 hover:border-[#444] transition-all duration-300 h-full">
            <div class="flex items-start gap-3 sm:gap-4 mb-3 sm:mb-4">
                ${coverHtml}
                <div class="min-w-0 pt-0.5 sm:pt-1">
                    <h3 class="font-bold text-lg sm:text-xl text-[#444] leading-tight mb-2 sm:mb-3 line-clamp-2 whitespace-normal break-words overflow-hidden">${club.name}</h3>
                    <span class="inline-block text-[0.65rem] sm:text-xs font-bold px-3 py-1 sm:px-4 sm:py-1.5 rounded-full border-[1.5px] border-[#444] text-[#444]">${club.category}</span>
                </div>
            </div>
            <p class="text-[0.8rem] sm:text-sm text-[#444] leading-relaxed line-clamp-3 overflow-hidden break-words mb-4 h-[60px] sm:h-[68px]">${club.description}</p>
            <div class="flex items-center justify-between pt-3 sm:pt-4 mt-auto border-t-[1.5px] border-gray-200">
                <button data-primary-action-btn="${club.id}" class="${baseBtnClass} ${btnClass} ${!club.joined && !club.isOwner ? '' : 'hover:-translate-y-0.5 hover:-translate-x-0.5 hover:shadow-[3px_3px_0px_currentColor] active:translate-y-0 active:translate-x-0 active:shadow-none'}">${getPrimaryActionLabel(club)}</button>
                <span class="text-xs sm:text-sm font-bold text-[#444]">${club.members} Anggota</span>
            </div>
        </article>
    `}).join('');

    grid.querySelectorAll('[data-club-id]').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('button')) return;
            openModal(state.CLUBS.find(c => c.id === Number(card.dataset.clubId)));
        });
    });

    grid.querySelectorAll('[data-primary-action-btn]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const clubId = Number(btn.dataset.primaryActionBtn);
            const club = state.CLUBS.find(c => c.id === clubId);
            if (!club) return;

            if (club.isOwner) {
                openEditClub(clubId);
                return;
            }

            if (club.joined) {
                leaveClub(clubId);
                return;
            }

            setJoined(clubId);
        });
    });
}
