import "./custom-select";
/**
 * Klub (Club) page — card grid, filter/sort, detail modal, CREATE CLUB form.
 *
 * Data source: window.__KLUB_DATA__ (injected from Blade).
 * DB-READY: Ketika backend siap, cukup ganti sumber $clubs di controller.
 */

// ── State ──
const CURRENT_USER = window.__CURRENT_USER__ || null;
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';

function mapClub(c) {
    return {
        id:              c.id,
        name:            c.name,
        category:        c.category,
        members:         c.members,
        founded:         c.founded,
        description:     c.description,
        fullDescription: c.full_description,
        admin:           c.admin,
        adminAvatar:     c.admin_avatar,
        membersList:     c.members_list,
        recentBooks:     c.recent_books,
        schedule:        c.schedule,
        gradientFrom:    c.gradient_from,
        gradientTo:      c.gradient_to,
        coverUrl:        c.foto_klub || c.cover_url || null,
        adminUsername:   c.admin_username || null,
        membersData:     c.members_data || [],
        joined:          Boolean(c.joined),
        ownerId:         c.owner?.id ?? c.owner_id ?? null,
        isOwner:         Boolean(c.owner && CURRENT_USER && Number(c.owner.id) === Number(CURRENT_USER.id)),
    };
}

const CLUBS = (window.__KLUB_DATA__ || []).map(mapClub);

const PER_PAGE = 12;
let currentPage = 1;

// ── DOM refs ──
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
const submitBtn      = document.getElementById('btn-submit-klub');

// ── Populate category filter from data ──
function populateCategories() {
    // If server rendered options are present, don't duplicate
    if (filterCategory.options.length > 1) return;

    const serverCats = window.__KLUB_CATEGORIES__ || null;
    const cats = serverCats ? serverCats.slice().sort() : [...new Set(CLUBS.map(c => c.category))].sort();
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

    // Sync with mobile controls
    const mobileCategory = document.getElementById('mobile-filter-category');
    const mobileSort = document.getElementById('mobile-sort');
    if (mobileCategory && mobileCategory.value !== category) {
        mobileCategory.value = category;
        mobileCategory.dispatchEvent(new Event('change'));
    }
    if (mobileSort && mobileSort.value !== sortKey) {
        mobileSort.value = sortKey;
        mobileSort.dispatchEvent(new Event('change'));
    }

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
    renderCards(result.slice(start, start + PER_PAGE));
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

function getJoinButtonLabel(club) {
    return club.joined ? 'Sudah Bergabung' : 'Bergabung';
}

function getPrimaryActionLabel(club) {
    if (club.isOwner) return 'Edit Klub';
    if (club.joined) return 'Keluar Klub';
    return 'Bergabung';
}

function getPrimaryActionKind(club) {
    if (club.isOwner) return 'edit';
    if (club.joined) return 'leave';
    return 'join';
}

function getMemberRoleLabel(role) {
    switch (role) {
        case 'owner':
            return 'Owner';
        case 'admin':
        case 'moderator':
            return 'Admin';
        default:
            return 'Anggota';
    }
}

function syncClubFromResponse(data) {
    const updatedClub = mapClub(data);
    const index = CLUBS.findIndex(club => club.id === updatedClub.id);

    if (index >= 0) {
        CLUBS[index] = updatedClub;
    } else {
        CLUBS.unshift(updatedClub);
    }

    return updatedClub;
}

function getClubPayload(clubId) {
    return fetch(`/klub/${clubId}/payload`, {
        headers: { 'Accept': 'application/json' },
        credentials: 'same-origin',
    }).then(async (response) => {
        if (!response.ok) {
            const errorBody = await response.json().catch(() => ({}));
            throw new Error(errorBody.message || 'Gagal memuat data klub');
        }
        return response.json();
    });
}

function getUserAvatar(user) {
    const seed = user?.username || user?.name;
    return seed ? `https://api.dicebear.com/7.x/thumbs/svg?seed=${encodeURIComponent(seed)}` : null;
}

function setJoined(clubId) {
    const club = CLUBS.find(c => c.id === clubId);
    if (!club || club.joined || !CURRENT_USER) return;

    fetch(`/klub/${clubId}/join`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        credentials: 'same-origin',
        body: JSON.stringify({}),
    })
        .then(async (response) => {
            if (!response.ok) {
                // Try to get any JSON error message, but continue to attempt a refresh
                const errorBody = await response.json().catch(() => ({}));
                throw { status: response.status, message: errorBody.message || 'Gagal join klub' };
            }
            return response.json();
        })
        .then((data) => {
            const updatedClub = syncClubFromResponse(data);
            applyFilters(false);
            if (!modal.classList.contains('hidden')) {
                openModal(updatedClub);
            }
        })
        .catch(async (err) => {
            // On error, try to re-sync club state from server (payload endpoint)
            try {
                const resp = await fetch(`/klub/${clubId}/payload`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                    if (resp.ok) {
                        const payload = await resp.json();
                        const updatedClub = syncClubFromResponse(payload);
                        applyFilters(false);
                        if (!modal.classList.contains('hidden')) openModal(updatedClub);
                        return;
                    }
            } catch (e) {
                // ignore
            }

            alert(typeof err === 'string' ? err : (err?.message || 'Gagal join klub.'));
        });
}

function leaveClub(clubId) {
    const club = CLUBS.find(c => c.id === clubId);
    if (!club || !CURRENT_USER || club.isOwner) return;

    fetch(`/klub/${clubId}/leave`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        credentials: 'same-origin',
        body: JSON.stringify({}),
    })
        .then(async (response) => {
            if (!response.ok) {
                const errorBody = await response.json().catch(() => ({}));
                throw { status: response.status, message: errorBody.message || 'Gagal keluar klub' };
            }
            return response.json();
        })
        .then((data) => {
            const updatedClub = syncClubFromResponse(data);
            applyFilters(false);
            if (!modal.classList.contains('hidden')) {
                openModal(updatedClub);
            }
        })
        .catch((err) => {
            alert(typeof err === 'string' ? err : (err?.message || 'Gagal keluar klub.'));
        });
}

function kickMember(clubId, userId, userName) {
    if (!CURRENT_USER) return;

    const club = CLUBS.find(c => c.id === clubId);
    if (!club) return;

    const myData = club.membersData.find(m => Number(m.id) === Number(CURRENT_USER.id));
    const isOwner = club.isOwner;
    const isAdmin = myData?.role === 'admin' || myData?.role === 'moderator';

    if (!isOwner && !isAdmin) return;

    if (!confirm(`Kick "${userName}" dari klub ini?`)) return;

    fetch(`/klub/${clubId}/members/${userId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        credentials: 'same-origin',
    })
    .then(async (res) => {
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal kick member');
        }
        return res.json();
    })
    .then((data) => {
        const updatedClub = syncClubFromResponse(data);
        applyFilters(false);
        if (!modal.classList.contains('hidden')) {
            openModal(updatedClub);
        }
    })
    .catch((err) => alert(err.message || 'Gagal kick member.'));
}

function updateMemberRole(clubId, userId, userName, newRole) {
    if (!CURRENT_USER) return;

    const club = CLUBS.find(c => c.id === clubId);
    if (!club || !club.isOwner) return;

    const roleLabel = {admin: 'Admin', member: 'Anggota', owner: 'Owner'}[newRole] || newRole;
    const confirmMsg = newRole === 'owner'
        ? `Transfer ownership ke "${userName}"? Kamu akan menjadi Admin setelahnya.`
        : `Jadikan "${userName}" sebagai ${roleLabel}?`;

    if (!confirm(confirmMsg)) return;

    fetch(`/klub/${clubId}/members/${userId}/role`, {
        method: 'PATCH',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        credentials: 'same-origin',
        body: JSON.stringify({role: newRole}),
    })
    .then(async (res) => {
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal mengubah role');
        }
        return res.json();
    })
    .then((data) => {
        const updatedClub = syncClubFromResponse(data);
        applyFilters(false);
        if (!modal.classList.contains('hidden')) {
            openModal(updatedClub);
        }
    })
    .catch ((err) => alert(err.message || 'Gagal mengubah role.'));
}

function buildMemberRow(m, club) {
    const isCurrentUser = CURRENT_USER && Number(m.id) === Number(CURRENT_USER.id);
    const isTargetOwner = m.role === 'owner';

    const myData = club.membersData.find(x => CURRENT_USER && Number(x.id) === Number(CURRENT_USER.id));
    const iAmOwner = club.isOwner;
    const iAmAdmin = myData?.role === 'admin' || myData?.role === 'moderator';

    let actionBtns = '';
    if (!isCurrentUser && !isTargetOwner && CURRENT_USER) {
        let menuItems = '';
        if (iAmOwner) {
            if (m.role !== 'admin' && m.role !== 'moderator') {
                menuItems += `<button data-role-btn data-club="${club.id}" data-user="${m.id}" data-name="${m.name}" data-role="admin"
                    class="w-full text-left text-xs px-3 py-3 sm:py-1.5 hover:bg-gray-100 transition-colors whitespace-nowrap">Jadikan Admin</button>`;
            } else {
                menuItems += `<button data-role-btn data-club="${club.id}" data-user="${m.id}" data-name="${m.name}" data-role="member"
                    class="w-full text-left text-xs px-3 py-3 sm:py-1.5 hover:bg-gray-100 transition-colors whitespace-nowrap">Demote</button>`;
            }
            menuItems += `<button data-role-btn data-club="${club.id}" data-user="${m.id}" data-name="${m.name}" data-role="owner"
                class="w-full text-left text-xs px-3 py-3 sm:py-1.5 hover:bg-gray-100 transition-colors whitespace-nowrap">Transfer Owner</button>`;
            menuItems += `<button data-kick-btn data-club="${club.id}" data-user="${m.id}" data-name="${m.name}"
                class="w-full text-left text-xs px-3 py-3 sm:py-1.5 hover:bg-red-50 text-red-500 transition-colors whitespace-nowrap">Kick</button>`;
        } else if (iAmAdmin && m.role !== 'admin' && m.role !== 'moderator') {
            menuItems += `<button data-kick-btn data-club="${club.id}" data-user="${m.id}" data-name="${m.name}"
                class="w-full text-left text-xs px-3 py-3 sm:py-1.5 hover:bg-red-50 text-red-500 transition-colors whitespace-nowrap">Kick</button>`;
        }

        if (menuItems) {
            actionBtns = `
                <div class="relative flex-shrink-0" data-dropdown>
                    <button data-dropdown-btn
                        class="text-gray-400 hover:text-[#444] transition-colors px-2 sm:px-1 text-lg leading-none min-w-[44px] min-h-[44px] flex items-center justify-center">⋮</button>
                    <div class="hidden">${menuItems}</div>
                </div>`;
        }
    }

    const avatarStyle = m.avatar
        ? `background-image: url('${m.avatar}')`
        : `background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})`;

    return `
    <div class="flex items-center justify-between gap-2 min-w-0 py-1.5">
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-7 h-7 rounded-full border border-[#444] bg-center bg-cover flex-shrink-0" style="${avatarStyle}"></div>
            <div class="min-w-0">
                <div class="text-xs font-medium truncate">${m.name} <span class="text-gray-400">(${getMemberRoleLabel(m.role)})</span></div>
                ${m.username ? `<div class="text-[10px] text-gray-400 truncate">@${m.username}</div>` : ''}
            </div>
        </div>
        ${actionBtns || ''}
    </div>`;
}

let deleteConfirmEl = null;

function closeDeleteConfirm() {
    if (!deleteConfirmEl) return;
    deleteConfirmEl.classList.add('opacity-0');
    deleteConfirmEl.classList.add('pointer-events-none');
    setTimeout(() => {
        deleteConfirmEl?.remove();
        deleteConfirmEl = null;
        document.body.style.overflow = '';
    }, 220);
}

function openDeleteConfirm(club) {
    if (!club || !CURRENT_USER || !club.isOwner) return;

    closeDeleteConfirm();

    deleteConfirmEl = document.createElement('div');
    deleteConfirmEl.className = 'fixed inset-0 z-[99999] flex items-center justify-center px-4 bg-black/40 backdrop-blur-sm transition-opacity duration-200 opacity-0';
    deleteConfirmEl.innerHTML = `
        <div class="w-full max-w-md bg-white border-[1.5px] border-[#444] rounded-3xl shadow-2xl overflow-hidden">
            <div class="px-6 pt-6 pb-4 bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-b-[1.5px] border-[#444]">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#444]">Konfirmasi Hapus</p>
                <h3 class="mt-2 text-lg font-bold text-[#444] break-words">Apakah anda yakin ingin menghapus klub ini?</h3>
            </div>
            <div class="px-6 py-5">
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 mb-5">
                    <p class="text-xs text-gray-400 mb-1">Nama klub</p>
                    <p class="text-sm font-semibold text-[#444] break-words">${club.name}</p>
                </div>
                <p class="text-sm text-gray-500 leading-relaxed">Tindakan ini akan menghapus klub beserta data anggota yang terhubung. Aksi ini tidak bisa dibatalkan.</p>
                <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-end">
                    <button type="button" data-delete-cancel class="px-5 py-2.5 rounded-full border-[1.5px] border-gray-300 text-gray-600 font-bold text-sm hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="button" data-delete-confirm class="px-5 py-2.5 rounded-full border-[1.5px] border-red-600 bg-red-500 text-white font-bold text-sm hover:bg-red-600 transition-colors">
                        Ya, hapus klub
                    </button>
                </div>
            </div>
        </div>
    `;

    deleteConfirmEl.addEventListener('click', (e) => {
        if (e.target === deleteConfirmEl) {
            closeDeleteConfirm();
        }
    });

    deleteConfirmEl.querySelector('[data-delete-cancel]')?.addEventListener('click', closeDeleteConfirm);
    deleteConfirmEl.querySelector('[data-delete-confirm]')?.addEventListener('click', () => {
        closeDeleteConfirm();
        deleteClub(club.id);
    });

    document.body.appendChild(deleteConfirmEl);
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        deleteConfirmEl?.classList.remove('opacity-0');
    });
}

function deleteClub(clubId) {
    const club = CLUBS.find(c => c.id === clubId);
    if (!club || !CURRENT_USER || !club.isOwner) return;

    fetch(`/klub/${clubId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        credentials: 'same-origin',
        body: JSON.stringify({}),
    })
        .then(async (response) => {
            if (!response.ok) {
                const errorBody = await response.json().catch(() => ({}));
                throw { status: response.status, message: errorBody.message || 'Gagal menghapus klub' };
            }
            return response.json();
        })
        .then(() => {
            const index = CLUBS.findIndex(c => c.id === clubId);
            if (index >= 0) CLUBS.splice(index, 1);
            applyFilters();
            closeModal();
        })
        .catch((err) => {
            alert(typeof err === 'string' ? err : (err?.message || 'Gagal menghapus klub.'));
        });
}

function openEditClub(clubId) {
    const club = CLUBS.find(c => c.id === clubId);
    if (!club || !club.isOwner) return;

    const form = document.getElementById('buat-klub-form');
    const modalEl = document.getElementById('buat-klub-modal');
    const panelEl = document.getElementById('buat-klub-panel');
    const bannerEl = document.getElementById('buat-klub-preview-banner');
    const titleInput = document.getElementById('input-nama-klub');
    const categorySelect = document.getElementById('input-kategori');
    const descriptionInput = document.getElementById('input-deskripsi');
    const gradientFromInput = document.getElementById('input-gradient-from');
    const gradientToInput = document.getElementById('input-gradient-to');
    const fotoLabel = document.getElementById('foto-klub-label');
    const submitBtn = document.getElementById('btn-submit-klub');

    if (!form || !modalEl || !panelEl || !bannerEl || !titleInput || !categorySelect || !descriptionInput || !gradientFromInput || !gradientToInput || !submitBtn) return;

    form.dataset.mode = 'edit';
    form.dataset.clubId = String(club.id);
    form.action = `/klub/${club.id}`;

    titleInput.value = club.name || '';
    descriptionInput.value = club.fullDescription || club.description || '';
    gradientFromInput.value = club.gradientFrom || '#FFDDAF';
    gradientToInput.value = club.gradientTo || '#C7E7FF';
    bannerEl.style.background = `linear-gradient(135deg, ${gradientFromInput.value}, ${gradientToInput.value})`;
    submitBtn.textContent = 'Simpan Perubahan';

    if (fotoLabel) {
        fotoLabel.textContent = club.coverUrl ? 'Cover saat ini digunakan' : 'Klik atau seret gambar ke sini';
        fotoLabel.className = club.coverUrl ? 'text-xs text-[#444] font-medium' : 'text-xs text-gray-300';
    }

    const categories = Array.from(categorySelect.options).map(opt => opt.value);
    if (categories.includes(club.category)) {
        categorySelect.value = club.category;
    } else {
        categorySelect.value = '';
    }

    modalEl.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        panelEl.classList.remove('scale-95', 'opacity-0');
        panelEl.classList.add('scale-100', 'opacity-100');
    });
}

// ── Render Pagination ──
function renderPagination(totalPages) {
    if (totalPages <= 1) { pagination.innerHTML = ''; return; }
    const btnBase = 'w-9 h-9 rounded-full border-[1.5px] border-[#444] flex items-center justify-center text-sm font-medium transition-colors cursor-pointer';
    const btnActive = 'bg-[#FFDDAF] text-[#444]';
    const btnInactive = 'bg-white text-[#444] hover:bg-gray-50';
    const isMobile = window.innerWidth < 640;
    const maxVisible = isMobile ? 5 : 10;
    let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
    }
    let html = '';
    html += `<button data-page="prev" ${currentPage === 1 ? 'disabled' : ''} class="${btnBase} ${currentPage === 1 ? 'opacity-30 cursor-not-allowed' : btnInactive}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></button>`;
    if (startPage > 1) {
        html += `<button data-page="1" class="${btnBase} ${btnInactive}">1</button>`;
        if (startPage > 2) html += `<span class="text-gray-400 text-xs px-1">...</span>`;
    }
    for (let i = startPage; i <= endPage; i++) {
        html += `<button data-page="${i}" class="${btnBase} ${i === currentPage ? btnActive : btnInactive}">${i}</button>`;
    }
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += `<span class="text-gray-400 text-xs px-1">...</span>`;
        html += `<button data-page="${totalPages}" class="${btnBase} ${btnInactive}">${totalPages}</button>`;
    }
    html += `<button data-page="next" ${currentPage === totalPages ? 'disabled' : ''} class="${btnBase} ${currentPage === totalPages ? 'opacity-30 cursor-not-allowed' : btnInactive}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></button>`;
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
        grid.innerHTML = `<div class="col-span-full text-center py-16 text-gray-400"><p class="text-sm font-medium">Tidak ada klub ditemukan.</p></div>`;
        pagination.innerHTML = '';
        return;
    }
    grid.innerHTML = clubs.map(club => {
        const coverHtml = club.coverUrl
            ? `<div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl border-[1.5px] border-[#444] flex-shrink-0 bg-cover bg-center" style="background-image: url('${club.coverUrl}')"></div>`
            : `<div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl border-[1.5px] border-[#444] flex-shrink-0" style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})"></div>`;

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
                <button data-primary-action-btn="${club.id}" class="bg-[#FFDDAF] text-[#444] font-bold text-xs sm:text-sm px-4 sm:px-6 py-2 sm:py-2.5 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] hover:-translate-y-0.5 hover:-translate-x-0.5 hover:shadow-[3px_3px_0px_#444] active:translate-y-0 active:translate-x-0 active:shadow-none transition-all">${getPrimaryActionLabel(club)}</button>
                <span class="text-xs sm:text-sm font-bold text-[#444]">${club.members} Anggota</span>
            </div>
        </article>
    `}).join('');
    grid.querySelectorAll('[data-club-id]').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('button')) return;
            openModal(CLUBS.find(c => c.id === Number(card.dataset.clubId)));
        });
    });
    grid.querySelectorAll('[data-primary-action-btn]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const clubId = Number(btn.dataset.primaryActionBtn);
            const club = CLUBS.find(c => c.id === clubId);
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

// ── Detail Modal ──
function openModal(club) {
    if (!club) return;
    const actionButtonsHtml = club.isOwner
        ? `
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <button data-edit-club-btn="${club.id}" class="bg-[#FFDDAF] text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors flex-shrink-0">Edit Klub</button>
                <button data-delete-club-btn="${club.id}" class="bg-red-500 text-white font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-red-600 hover:bg-red-600 transition-colors flex-shrink-0">Hapus Klub</button>
            </div>
        `
        : club.joined
            ? `<button data-leave-club-btn="${club.id}" class="bg-[#FFDDAF] text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors flex-shrink-0">Keluar Klub</button>`
            : `<button data-join-club-btn="${club.id}" class="bg-[#FFDDAF] text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors flex-shrink-0">Bergabung</button>`;

    modalContent.innerHTML = `
        <div class="h-32 sm:h-48 rounded-t-2xl relative border-b-[1.5px] border-[#444]" style="${club.coverUrl ? `background-image: url('${club.coverUrl}'); background-size: cover; background-position: center;` : `background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})`}">
            <div class="absolute -bottom-12 left-6 sm:left-8">
                <div class="w-24 h-24 rounded-2xl border-[2.5px] border-[#444] bg-white p-1">
                    <div class="w-full h-full rounded-xl bg-cover bg-center" style="${club.coverUrl ? `background-image: url('${club.coverUrl}');` : `background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})`}"></div>
                </div>
            </div>
        </div>
        <div class="pt-16 px-6 sm:px-8 pb-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-2 gap-2 sm:gap-3">
                <h2 class="font-bold text-2xl sm:text-3xl break-words whitespace-normal text-[#444] leading-tight">${club.name}</h2>
                <div class="flex-shrink-0 self-start sm:self-auto">${actionButtonsHtml}</div>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-5">
                <span class="inline-block text-xs font-medium px-3 py-0.5 rounded-full border-[1.5px] border-[#444]">${club.category}</span>
                <span class="text-xs text-gray-400">${club.members} Anggota</span>
                <span class="text-xs text-gray-300">•</span>
                <span class="text-xs text-gray-400">Didirikan ${club.founded}</span>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed mb-6 break-words whitespace-normal">${club.fullDescription}</p>
            <div class="space-y-5">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Admin</h4>
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full border-[1.5px] border-[#444] bg-center bg-cover flex-shrink-0" style="${club.adminAvatar ? `background-image: url('${club.adminAvatar}')` : `background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})`}"></div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold leading-tight truncate">${club.admin}</div>
                            ${club.adminUsername ? `<div class="text-[11px] text-gray-400 leading-tight truncate">@${club.adminUsername}</div>` : ''}
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Anggota (${club.members})</h4>
                    <div class="flex flex-col divide-y divide-gray-100">${(((club.membersData && club.membersData.length) ? club.membersData : (club.membersList||[]).map((m, i) => ({ name: m, username: null, avatar: null, role: i === 0 ? 'owner' : 'member' }))))
                        .map((m) => buildMemberRow(m, club)).join('')}</div>
                </div>
            </div>
        </div>`;
    modalContent.querySelector('[data-edit-club-btn]')?.addEventListener('click', (e) => {
        e.stopPropagation();
        const clubId = Number(e.currentTarget.dataset.editClubBtn);
        closeModal();
        setTimeout(() => openEditClub(clubId), 320);
    });

    modalContent.querySelector('[data-delete-club-btn]')?.addEventListener('click', (e) => {
        e.stopPropagation();
        openDeleteConfirm(club);
    });

    modalContent.querySelector('[data-leave-club-btn]')?.addEventListener('click', (e) => {
        e.stopPropagation();
        leaveClub(Number(e.currentTarget.dataset.leaveClubBtn));
        closeModal();
    });

    modalContent.querySelector('[data-join-club-btn]')?.addEventListener('click', (e) => {
        e.stopPropagation();
        setJoined(Number(e.currentTarget.dataset.joinClubBtn));
    });

    modalContent.querySelectorAll('[data-kick-btn]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            kickMember(Number(btn.dataset.club), Number(btn.dataset.user), btn.dataset.name);
        });
    });

    modalContent.querySelectorAll('[data-role-btn]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            updateMemberRole(Number(btn.dataset.club), Number(btn.dataset.user), btn.dataset.name, btn.dataset.role);
        });
    });

    modalContent.querySelectorAll('[data-dropdown-btn]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();

            document.querySelector('[data-dropdown-portal]')?.remove();

            const templateMenu = btn.nextElementSibling;
            if (!templateMenu) return;

            const btnRect = btn.getBoundingClientRect();

            const portal = document.createElement('div');
            portal.dataset.dropdownPortal = '';
            portal.className = 'fixed bg-white border border-gray-200 rounded-lg shadow-lg z-[200] min-w-[140px] py-1';
            portal.innerHTML = templateMenu.innerHTML;

            let top = btnRect.bottom + 4;
            const right = window.innerWidth - btnRect.right;
            portal.style.top = top + 'px';
            portal.style.right = right + 'px';
            document.body.appendChild(portal);

            requestAnimationFrame(() => {
                const portalRect = portal.getBoundingClientRect();
                if (portalRect.bottom > window.innerHeight) {
                    top = Math.max(4, btnRect.top - portalRect.height - 4);
                    portal.style.top = top + 'px';
                }
            });

            portal.querySelectorAll('[data-kick-btn]').forEach(b => {
                b.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closePortal();
                    kickMember(Number(b.dataset.club), Number(b.dataset.user), b.dataset.name);
                });
            });
            portal.querySelectorAll('[data-role-btn]').forEach(b => {
                b.addEventListener('click', (e) => {
                    e.stopPropagation();
                    closePortal();
                    updateMemberRole(Number(b.dataset.club), Number(b.dataset.user), b.dataset.name, b.dataset.role);
                });
            });

            function closePortal() {
                portal.remove();
                modalPanel.removeEventListener('scroll', closePortal);
                document.removeEventListener('click', closePortal);
            }

            modalPanel.addEventListener('scroll', closePortal);
            setTimeout(() => document.addEventListener('click', closePortal), 0);
        });
    });

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        modalPanel.classList.remove('scale-95', 'opacity-0');
        modalPanel.classList.add('scale-100', 'opacity-100');
    });
}

function closeModal() {
    document.querySelector('[data-dropdown-portal]')?.remove();
    modalPanel.classList.remove('scale-100', 'opacity-100');
    modalPanel.classList.add('scale-95', 'opacity-0');
    setTimeout(() => { modal.classList.add('hidden'); document.body.style.overflow = ''; }, 300);
}

// ══════════════════════════════════════════════════════════
// ── BUAT KLUB MODAL (Create Club)
// ══════════════════════════════════════════════════════════
const buatModal     = document.getElementById('buat-klub-modal');
const buatBackdrop  = document.getElementById('buat-klub-backdrop');
const buatPanel     = document.getElementById('buat-klub-panel');
const buatCloseBtn  = document.getElementById('buat-klub-close');
const buatBtn       = document.getElementById('buat-klub-btn');
const buatForm      = document.getElementById('buat-klub-form');

function openBuatKlub() {
    // Ensure form is reset for create mode
    if (buatForm) {
        buatForm.reset();
        delete buatForm.dataset.mode;
        delete buatForm.dataset.clubId;
        buatForm.action = '/klub';
    }
    // Reset counters/preview/labels
    const namaCounterEl = document.getElementById('nama-klub-counter');
    const deskripsiCounterEl = document.getElementById('deskripsi-counter');
    const previewBannerEl = document.getElementById('buat-klub-preview-banner');
    const fotoLabelEl = document.getElementById('foto-klub-label');
    if (namaCounterEl) namaCounterEl.textContent = '0/100';
    if (deskripsiCounterEl) deskripsiCounterEl.textContent = '0/500';
    if (previewBannerEl) previewBannerEl.style.background = 'linear-gradient(135deg, #FFDDAF, #C7E7FF)';
    if (fotoLabelEl) { fotoLabelEl.textContent = 'Klik atau seret gambar ke sini'; fotoLabelEl.className = 'text-xs text-gray-300'; }
    if (submitBtn) submitBtn.textContent = 'Buat Klub Sekarang';

    buatModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        buatPanel.classList.remove('scale-95', 'opacity-0');
        buatPanel.classList.add('scale-100', 'opacity-100');
    });
}

function closeBuatKlub() {
    buatPanel.classList.remove('scale-100', 'opacity-100');
    buatPanel.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        buatModal.classList.add('hidden');
        document.body.style.overflow = '';

        if (buatForm) {
            delete buatForm.dataset.mode;
            delete buatForm.dataset.clubId;
            buatForm.action = '/klub';
            buatForm.reset();
        }
        if (submitBtn) submitBtn.textContent = 'Buat Klub Sekarang';
    }, 300);
}

if (buatBtn) buatBtn.addEventListener('click', openBuatKlub);
if (buatCloseBtn) buatCloseBtn.addEventListener('click', closeBuatKlub);
if (buatBackdrop) buatBackdrop.addEventListener('click', closeBuatKlub);

// ── Character counters ──
function setupCounter(inputId, counterId, max) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (!input || !counter) return;
    input.addEventListener('input', () => {
        const len = input.value.length;
        counter.textContent = `${len}/${max}`;
        counter.classList.toggle('text-red-400', len >= max);
    });
}
setupCounter('input-nama-klub', 'nama-klub-counter', 100);
setupCounter('input-deskripsi', 'deskripsi-counter', 500);

// Note: custom categories removed — users must choose from katalog genres.

// ── Gradient picker ──
const gradientPicker = document.getElementById('gradient-picker');
const previewBanner  = document.getElementById('buat-klub-preview-banner');
const gradFromInput  = document.getElementById('input-gradient-from');
const gradToInput    = document.getElementById('input-gradient-to');

if (gradientPicker) {
    gradientPicker.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-gradient-from]');
        if (!btn) return;
        const from = btn.dataset.gradientFrom;
        const to   = btn.dataset.gradientTo;
        // Update hidden inputs
        if (gradFromInput) gradFromInput.value = from;
        if (gradToInput)   gradToInput.value   = to;
        // Update preview banner
        if (previewBanner) previewBanner.style.background = `linear-gradient(135deg, ${from}, ${to})`;
        // Update active state
        gradientPicker.querySelectorAll('button').forEach(b => {
            b.classList.remove('border-[#444]', 'ring-2', 'ring-[#444]', 'ring-offset-2');
            b.classList.add('border-gray-200');
        });
        btn.classList.remove('border-gray-200');
        btn.classList.add('border-[#444]', 'ring-2', 'ring-[#444]', 'ring-offset-2');
    });
}

// ── Foto dropzone ──
const dropzone  = document.getElementById('foto-klub-dropzone');
const fotoInput = document.getElementById('input-foto-klub');
const fotoLabel = document.getElementById('foto-klub-label');

if (dropzone && fotoInput) {
    dropzone.addEventListener('click', () => fotoInput.click());
    dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.classList.add('border-[#444]', 'bg-gray-50'); });
    dropzone.addEventListener('dragleave', () => { dropzone.classList.remove('border-[#444]', 'bg-gray-50'); });
    dropzone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzone.classList.remove('border-[#444]', 'bg-gray-50');
        if (e.dataTransfer.files.length) { fotoInput.files = e.dataTransfer.files; updateFotoLabel(); }
    });
    fotoInput.addEventListener('change', updateFotoLabel);
}

function updateFotoLabel() {
    if (fotoInput.files.length && fotoLabel) {
        fotoLabel.textContent = fotoInput.files[0].name;
        fotoLabel.classList.remove('text-gray-300');
        fotoLabel.classList.add('text-[#444]', 'font-medium');
    }
}

// ── Form submit (DB-READY) ──
// Saat backend belum siap, form akan di-handle client-side.
// Setelah backend siap, hapus preventDefault() dan biarkan form POST normal.
if (buatForm) {
    buatForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(buatForm);
        // kategori must be one of the provided options (enforced by server)

        const isEditMode = buatForm.dataset.mode === 'edit';
        const requestUrl = isEditMode ? (buatForm.dataset.clubId ? `/klub/${buatForm.dataset.clubId}` : buatForm.action) : buatForm.action;
        // Use POST + _method override for edit to ensure Laravel handles multipart properly
        const requestMethod = 'POST';
        if (isEditMode) formData.append('_method', 'PATCH');

        try {
            const res = await fetch(requestUrl, {
                method: requestMethod,
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': formData.get('_token')
                }
            });

            if (!res.ok) {
                let errorBody = {};
                try { errorBody = await res.json(); } catch (e) { /* ignore */ }
                const msg = errorBody?.message || `Gagal membuat klub (status ${res.status})`;
                alert(msg);
                return;
            }

            // Try to parse JSON; if parsing fails, continue — we'll fetch payload below to sync.
            let data = null;
            try { data = await res.json(); } catch (e) { /* ignore parse errors */ }

            // If server returned a payload, sync it. Otherwise we'll re-fetch the canonical payload.
            if (data) {
                if (isEditMode) {
                    syncClubFromResponse(data);
                } else {
                    const updated = syncClubFromResponse(data);
                    if (!CLUBS.find(c => c.id === updated.id)) CLUBS.unshift(updated);
                }
            }

            // Determine club id for payload re-sync: prefer dataset.clubId (edit) then data.id (create)
            const clubId = isEditMode ? buatForm.dataset.clubId : (data?.id || null);
            if (clubId) {
                try {
                    const payloadResp = await fetch(`/klub/${clubId}/payload`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                    if (payloadResp.ok) {
                        const payload = await payloadResp.json();
                        syncClubFromResponse(payload);
                    }
                } catch (e) {
                    // ignore payload sync failures
                }
            }
            applyFilters();

            closeBuatKlub();
            buatForm.reset();
            delete buatForm.dataset.mode;
            delete buatForm.dataset.clubId;
            buatForm.action = '/klub';
            if (submitBtn) submitBtn.textContent = 'Buat Klub Sekarang';
            // Reset gradient preview
            if (previewBanner) previewBanner.style.background = 'linear-gradient(135deg, #FFDDAF, #C7E7FF)';
            if (fotoLabel) { fotoLabel.textContent = 'Klik atau seret gambar ke sini'; fotoLabel.className = 'text-xs text-gray-300'; }

        } catch (err) {
            console.error('Error submitting buat-klub form:', err);
            // Do not show blocking alert here to avoid duplicate/confusing alerts when server actually created the club.
            // Optionally reload to ensure client reflects server state in error scenarios:
            // window.location.reload();
        }
    });
}

// ── Event Listeners ──
searchInput.addEventListener('input', applyFilters);
filterCategory.addEventListener('change', applyFilters);
sortSelect.addEventListener('change', applyFilters);
modalClose.addEventListener('click', closeModal);
modal.addEventListener('click', (e) => {
    if (!e.target.closest('#klub-modal-panel')) closeModal();
});
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!modal.classList.contains('hidden')) closeModal();
        if (buatModal && !buatModal.classList.contains('hidden')) closeBuatKlub();
    }
});

// ── Mobile Filter Dialog Event Listeners ──
const mobileFilterDialog = document.getElementById('mobile-filter-dialog');
const mobileFilterBtn = document.getElementById('klub-mobile-filter-btn');
const mobileFilterClose = document.getElementById('close-filter-dialog');
const mobileFilterReset = document.getElementById('mobile-filter-reset');
const mobileFilterCategory = document.getElementById('mobile-filter-category');
const mobileSort = document.getElementById('mobile-sort');

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
    // Light dismiss on backdrop click
    mobileFilterDialog.addEventListener('click', (e) => {
        const rect = mobileFilterDialog.getBoundingClientRect();
        const isInDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height &&
            rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
        if (!isInDialog) {
            mobileFilterDialog.close();
        }
    });

    // Handle Form Submission within Dialog
    const form = mobileFilterDialog.querySelector('form');
    if (form) {
        form.addEventListener('submit', (e) => {
            if (mobileFilterCategory) filterCategory.value = mobileFilterCategory.value;
            if (mobileSort) sortSelect.value = mobileSort.value;
            applyFilters();
        });
    }
}

if (mobileFilterReset && mobileFilterDialog) {
    mobileFilterReset.addEventListener('click', () => {
        searchInput.value = '';
        filterCategory.value = '';
        sortSelect.value = 'name-asc';
        applyFilters();
        mobileFilterDialog.close();
    });
}

// ── Init ──
populateCategories();
applyFilters();

(function () {
    const params = new URLSearchParams(window.location.search);
    const highlightId = params.get('highlight');
    if (highlightId) {
        const club = CLUBS.find(c => c.id == Number(highlightId));
        if (club) {
            setTimeout(() => openModal(club), 300);
        } else {
            getClubPayload(highlightId).then(data => {
                const c = mapClub(data);
                CLUBS.unshift(c);
                openModal(c);
            }).catch(() => {});
        }
    }
})