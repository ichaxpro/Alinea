import "./custom-select";
import { state, initData, mapClub } from './klub/state.js';
import { getClubPayload } from './klub/api.js';
import { populateCategories, applyFilters } from './klub/ui.js';
import { openModal, closeModal } from './klub/modal.js';
import { closeBuatKlub, bindFormEvents } from './klub/form.js';

// Init State
initData();

// Init UI
populateCategories();
applyFilters();
bindFormEvents();

// ── Event Listeners ──
const searchInput    = document.getElementById('klub-search-input');
const filterCategory = document.getElementById('klub-filter-category');
const filterStatus   = document.getElementById('klub-filter-status');
const sortSelect     = document.getElementById('klub-sort');
const modal          = document.getElementById('klub-modal');
const modalClose     = document.getElementById('klub-modal-close');
const buatModal      = document.getElementById('buat-klub-modal');

if (searchInput) searchInput.addEventListener('input', applyFilters);
if (filterCategory) filterCategory.addEventListener('change', applyFilters);
if (filterStatus) filterStatus.addEventListener('change', applyFilters);
if (sortSelect) sortSelect.addEventListener('change', applyFilters);
if (modalClose) modalClose.addEventListener('click', closeModal);
if (modal) {
    modal.addEventListener('click', (e) => {
        if (!e.target.closest('#klub-modal-panel')) closeModal();
    });
}
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (modal && !modal.classList.contains('hidden')) closeModal();
        if (buatModal && !buatModal.classList.contains('hidden')) closeBuatKlub();
    }
});

// ── Mobile Filter Dialog Event Listeners ──
const mobileFilterDialog = document.getElementById('mobile-filter-dialog');
const mobileFilterBtn = document.getElementById('klub-mobile-filter-btn');
const mobileFilterClose = document.getElementById('close-filter-dialog');
const mobileFilterReset = document.getElementById('mobile-filter-reset');
const mobileFilterCategory = document.getElementById('mobile-filter-category');
const mobileFilterStatus = document.getElementById('mobile-filter-status');
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
            if (mobileFilterCategory && filterCategory) filterCategory.value = mobileFilterCategory.value;
            if (mobileFilterStatus && filterStatus) filterStatus.value = mobileFilterStatus.value;
            if (mobileSort && sortSelect) sortSelect.value = mobileSort.value;
            applyFilters();
        });
    }
}

if (mobileFilterReset && mobileFilterDialog) {
    mobileFilterReset.addEventListener('click', () => {
        if (searchInput) searchInput.value = '';
        if (filterCategory) filterCategory.value = '';
        if (filterStatus) filterStatus.value = 'all';
        if (sortSelect) sortSelect.value = 'name-asc';
        applyFilters();
        mobileFilterDialog.close();
    });
}

// ── Highlight URL param ──
(function () {
    const params = new URLSearchParams(window.location.search);
    const highlightId = params.get('highlight');
    if (highlightId) {
        const club = state.CLUBS.find(c => c.id == Number(highlightId));
        if (club) {
            setTimeout(() => openModal(club), 300);
        } else {
            getClubPayload(highlightId).then(data => {
                const c = mapClub(data);
                state.CLUBS.unshift(c);
                openModal(c);
            }).catch(() => {});
        }
    }
})();