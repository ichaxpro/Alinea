import { $, $$ } from './dashboard/utils.js';
import { state } from './dashboard/state.js';
import { initBookSearch, clearBookSelection } from './dashboard/book-search.js';
import { loadCatalog, renderCatalog, handleAddBook, openAddBookModal, closeAddBookModal } from './dashboard/catalog.js';
import { loadTransactions, renderTransactions, handleReturnRequest } from './dashboard/transactions.js';
import { loadPengajuan, renderPengajuan, handlePengajuanAction, handleAcceptReturn } from './dashboard/pengajuan.js';
import { initProfile, renderGenrePicker, handleSaveProfile, handleChangePassword, loadBookmarks, populateProfileForm, renderSidebarProfile } from './dashboard/profile.js';
import { initAvatarUpload } from './avatar-upload.js';

function initDashboard() {
    // 1. Initializations
    initProfile();
    if (typeof initAvatarUpload === 'function') initAvatarUpload();
    renderSidebarProfile();
    renderGenrePicker();
    populateProfileForm();
    initBookSearch();

    // Proactively fetch data for all tabs in the background to populate sidebar stats instantly
    Promise.all([
        loadCatalog(),
        loadTransactions(),
        loadPengajuan(),
        loadBookmarks()
    ]).catch(console.error);

    // 2. Load Initial Data (lazy load based on active tab)
    const urlParams = new URLSearchParams(window.location.search);
    const initialTabId = urlParams.get('tab') || 'personal';
    
    // 3. Tab Switching Logic
    const tabs = $$('[data-tab-btn]');
    const contents = $$('[data-tab-panel]');

    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabId = btn.dataset.tabBtn;
            switchTab(tabId, true);
        });
    });

    function switchTab(tabId, updateUrl = true) {
        tabs.forEach(b => {
            const isActive = b.dataset.tabBtn === tabId;
            b.classList.toggle('bg-[#FFDDAF]', isActive);
            b.classList.toggle('text-[#444]', isActive);
            b.classList.toggle('font-bold', isActive);
            b.classList.toggle('text-gray-400', !isActive);
            b.classList.toggle('font-medium', !isActive);
        });

        contents.forEach(p => {
            p.classList.toggle('hidden', p.dataset.tabPanel !== tabId);
        });

        if (updateUrl) {
            const url = new URL(window.location);
            url.searchParams.set('tab', tabId);
            window.history.pushState({}, '', url);
        }

        // Lazy Load Data
        if (tabId === 'katalog' && !state.catalogLoaded) loadCatalog();
        if (tabId === 'transaksi') loadTransactions();
        if (tabId === 'pengajuan') loadPengajuan();
        if (tabId === 'tersimpan') loadBookmarks();
        if (tabId === 'personal') renderGenrePicker();
    }

    // Call it once
    switchTab(initialTabId, false);

    // 4. Global Event Listeners (Event Delegation)
    document.addEventListener('click', (e) => {
        const actionBtn = e.target.closest('[data-action]');
        if (!actionBtn) return;

        const action = actionBtn.dataset.action;
        const id = actionBtn.dataset.id;

        if (action === 'return-request') {
            handleReturnRequest(id);
        } else if (action === 'pengajuan-terima') {
            handlePengajuanAction(id, 'terima');
        } else if (action === 'pengajuan-tolak') {
            handlePengajuanAction(id, 'tolak');
        } else if (action === 'accept-return') {
            handleAcceptReturn(id);
        } else if (action === 'close-add-book-modal') {
            closeAddBookModal();
        }
    });

    // Mobile sidebar toggle
    $('#mobile-sidebar-toggle')?.addEventListener('click', () => {
        $('#mobile-sidebar-menu')?.classList.toggle('hidden');
    });

    // Password visibility toggles
    $$('[data-toggle-pw]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.togglePw);
            if (input) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                btn.querySelector('.eye-open')?.classList.toggle('hidden', isPassword);
                btn.querySelector('.eye-closed')?.classList.toggle('hidden', !isPassword);
            }
        });
    });

    // 5. Specific Element Event Listeners
    $('#btn-add-book')?.addEventListener('click', openAddBookModal);
    $('#close-add-book')?.addEventListener('click', closeAddBookModal);
    $('#add-book-modal')?.addEventListener('click', (e) => { if(e.target.id==='add-book-modal') closeAddBookModal(); });
    $('#btn-clear-selection')?.addEventListener('click', clearBookSelection);
    
    $('#add-book-form')?.addEventListener('submit', handleAddBook);
    $('#profile-form')?.addEventListener('submit', handleSaveProfile);
    $('#security-form')?.addEventListener('submit', handleChangePassword);

    // Search and Filter Listeners
    $('#catalog-search')?.addEventListener('input', (e) => {
        state.catalogSearch = e.target.value.trim();
        state.catalogPage = 1;
        renderCatalog();
    });

    $$('[data-tx-filter]').forEach(btn => {
        btn.addEventListener('click', () => {
            state.txFilter = btn.dataset.txFilter;
            $$('[data-tx-filter]').forEach(b => {
                const isActive = b.dataset.txFilter === state.txFilter;
                b.classList.toggle('bg-[#FFDDAF]', isActive);
                b.classList.toggle('border-[#444]', isActive);
                b.classList.toggle('text-[#444]', isActive);
                b.classList.toggle('font-bold', isActive);
                b.classList.toggle('bg-white', !isActive);
                b.classList.toggle('border-gray-200', !isActive);
                b.classList.toggle('text-gray-400', !isActive);
                b.classList.toggle('font-medium', !isActive);
            });
            renderTransactions();
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboard);
} else {
    initDashboard();
}
