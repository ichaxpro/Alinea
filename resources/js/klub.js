/**
 * Klub (Club) page — card grid, filter/sort, detail modal.
 *
 * Data source: window.__KLUB_DATA__ (injected from Blade).
 * When you connect a database, just change the $clubs array in the Blade
 * file (or pass it from a controller) — this JS needs zero changes.
 */

// ── State ──
const CLUBS = (window.__KLUB_DATA__ || []).map(c => ({
    id:              c.id,
    name:            c.name,
    category:        c.category,
    members:         c.members,
    founded:         c.founded,
    description:     c.description,
    fullDescription: c.full_description,
    admin:           c.admin,
    membersList:     c.members_list,
    recentBooks:     c.recent_books,
    schedule:        c.schedule,
    gradientFrom:    c.gradient_from,
    gradientTo:      c.gradient_to,
}));

// ── DOM refs ──
const PER_PAGE = 12;
let currentPage = 1;

const grid           = document.getElementById('klub-grid');
const pagination     = document.getElementById('klub-pagination');
const searchInput    = document.getElementById('klub-search-input');
const filterCategory = document.getElementById('klub-filter-category');
const sortSelect     = document.getElementById('klub-sort');
const modal          = document.getElementById('klub-modal');
const modalBackdrop  = document.getElementById('klub-modal-backdrop');
const modalPanel     = document.getElementById('klub-modal-panel');
const modalContent   = document.getElementById('klub-modal-content');
const modalClose     = document.getElementById('klub-modal-close');

// ── Populate category filter from data ──
function populateCategories() {
    const cats = [...new Set(CLUBS.map(c => c.category))].sort();
    cats.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat;
        opt.textContent = cat;
        filterCategory.appendChild(opt);
    });
}

// ── Filter + Sort + Paginate pipeline ──
function applyFilters(resetPage = true) {
    if (resetPage) currentPage = 1;

    const query    = searchInput.value.toLowerCase().trim();
    const category = filterCategory.value;
    const sortKey  = sortSelect.value;

    let result = CLUBS.filter(c => {
        const matchSearch = !query
            || c.name.toLowerCase().includes(query)
            || c.category.toLowerCase().includes(query);
        const matchCat = !category || c.category === category;
        return matchSearch && matchCat;
    });

    result = sortClubs(result, sortKey);

    const totalPages = Math.max(1, Math.ceil(result.length / PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * PER_PAGE;
    const paged = result.slice(start, start + PER_PAGE);

    renderCards(paged);
    renderPagination(totalPages);
}

function sortClubs(list, key) {
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

// ── Render Pagination ──
function renderPagination(totalPages) {
    if (totalPages <= 1) { pagination.innerHTML = ''; return; }

    const btnBase = 'w-9 h-9 rounded-full border-[1.5px] border-[#444] flex items-center justify-center text-sm font-medium transition-colors cursor-pointer';
    const btnActive = 'bg-[#FFDDAF] text-[#444]';
    const btnInactive = 'bg-white text-[#444] hover:bg-gray-50';

    let html = '';

    // Prev
    html += `<button data-page="prev" ${currentPage === 1 ? 'disabled' : ''}
                class="${btnBase} ${currentPage === 1 ? 'opacity-30 cursor-not-allowed' : btnInactive}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>`;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        html += `<button data-page="${i}" class="${btnBase} ${i === currentPage ? btnActive : btnInactive}">${i}</button>`;
    }

    // Next
    html += `<button data-page="next" ${currentPage === totalPages ? 'disabled' : ''}
                class="${btnBase} ${currentPage === totalPages ? 'opacity-30 cursor-not-allowed' : btnInactive}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>`;

    pagination.innerHTML = html;

    pagination.querySelectorAll('[data-page]').forEach(btn => {
        btn.addEventListener('click', () => {
            const val = btn.dataset.page;
            if (val === 'prev' && currentPage > 1) currentPage--;
            else if (val === 'next' && currentPage < totalPages) currentPage++;
            else if (val !== 'prev' && val !== 'next') currentPage = Number(val);
            applyFilters(false);
            window.scrollTo({ top: grid.offsetTop - 80, behavior: 'smooth' });
        });
    });
}

// ── Render Cards ──
function renderCards(clubs) {
    if (clubs.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full text-center py-16 text-gray-400">
                <p class="text-sm font-medium">Tidak ada klub ditemukan.</p>
            </div>`;
        pagination.innerHTML = '';
        return;
    }

    grid.innerHTML = clubs.map(club => `
        <article data-club-id="${club.id}"
                 class="group bg-white border-[1.5px] border-[#444] rounded-2xl p-5 cursor-pointer
                        flex flex-col w-full hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start gap-4 mb-3">
                <div class="w-20 h-20 rounded-xl border-[1.5px] border-[#444] flex-shrink-0"
                     style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})"></div>
                <div class="min-w-0">
                    <h3 class="font-bold text-base leading-tight mb-1.5">${club.name}</h3>
                    <span class="inline-block text-xs font-medium px-3 py-0.5 rounded-lg border-[1.5px] border-[#444]">
                        ${club.category}
                    </span>
                </div>
            </div>

            <p class="text-xs text-gray-500 leading-relaxed flex-1 line-clamp-3 overflow-hidden">${club.description}</p>

            <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-200">
                <button class="bg-[#FFDDAF] text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444]
                               hover:bg-[#ffcf90] transition-colors">
                    Bergabung
                </button>
                <span class="text-xs font-semibold text-gray-400">${club.members} Member</span>
            </div>
        </article>
    `).join('');

    // Attach click handlers
    grid.querySelectorAll('[data-club-id]').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('button')) return;
            const club = CLUBS.find(c => c.id === Number(card.dataset.clubId));
            openModal(club);
        });
    });
}

// ── Modal ──
function openModal(club) {
    if (!club) return;

    modalContent.innerHTML = `
        <!-- Banner -->
        <div class="h-36 rounded-t-2xl relative"
             style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})">
            <div class="absolute -bottom-10 left-6">
                <div class="w-20 h-20 rounded-xl border-[2.5px] border-[#444] bg-white p-1">
                    <div class="w-full h-full rounded-lg"
                         style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})"></div>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="pt-14 px-6 pb-6">
            <div class="flex items-start justify-between mb-1">
                <h2 class="font-bold text-xl">${club.name}</h2>
                <button class="bg-[#FFDDAF] text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444]
                               hover:bg-[#ffcf90] transition-colors flex-shrink-0">
                    Bergabung
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="inline-block text-xs font-medium px-3 py-0.5 rounded-full border-[1.5px] border-[#444]">
                    ${club.category}
                </span>
                <span class="text-xs text-gray-400">${club.members} Member</span>
                <span class="text-xs text-gray-300">•</span>
                <span class="text-xs text-gray-400">Didirikan ${club.founded}</span>
            </div>

            <p class="text-sm text-gray-600 leading-relaxed mb-6">${club.fullDescription}</p>

            <!-- Detail sections -->
            <div class="space-y-5">
                <!-- Admin -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Admin</h4>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full border-[1.5px] border-[#444]"
                             style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})"></div>
                        <span class="text-sm font-semibold">${club.admin}</span>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Jadwal Diskusi</h4>
                    <div class="flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#444]">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <span class="text-sm">${club.schedule}</span>
                    </div>
                </div>

                <!-- Recent Books -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Buku Terakhir Dibaca</h4>
                    <div class="flex flex-wrap gap-2">
                        ${club.recentBooks.map(b => `
                            <span class="text-xs font-medium px-3 py-1.5 rounded-full bg-[#FFDDAF] border-[1.5px] border-[#444]">${b}</span>
                        `).join('')}
                    </div>
                </div>

                <!-- Members list -->
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Anggota (${club.members})</h4>
                    <div class="grid grid-cols-2 gap-2">
                        ${club.membersList.map((m, i) => `
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full border border-[#444] flex items-center justify-center text-[10px] font-bold"
                                     style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})">${m.charAt(0)}</div>
                                <span class="text-xs font-medium truncate">${m}${i === 0 ? ' <span class="text-gray-400">(Admin)</span>' : ''}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        modalPanel.classList.remove('scale-95', 'opacity-0');
        modalPanel.classList.add('scale-100', 'opacity-100');
    });
}

function closeModal() {
    modalPanel.classList.remove('scale-100', 'opacity-100');
    modalPanel.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }, 300);
}

// ── Event Listeners ──
searchInput.addEventListener('input', applyFilters);
filterCategory.addEventListener('change', applyFilters);
sortSelect.addEventListener('change', applyFilters);
modalClose.addEventListener('click', closeModal);
modalBackdrop.addEventListener('click', closeModal);
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
});

// ── Init ──
populateCategories();
applyFilters();
