import { fetchGoogleBooks, fetchLocalSearch } from './global-search/api.js';
import { buildOverlay, showLoading, showEmpty, renderResults, updateSelection, escapeHtml } from './global-search/ui.js';

const IS_AUTHENTICATED = document.querySelector('meta[name="user-auth"]')?.content === 'true';

let dom = null;
let debounceTimer = null;
let currentAbort = null;
let selectedIndex = -1;
let currentResults = [];

function openSearch() {
    if (!dom) dom = buildOverlay();
    dom.overlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        dom.panel.classList.remove('opacity-0', 'scale-95');
        dom.panel.classList.add('opacity-100', 'scale-100');
        dom.input.focus();
    });
}

function closeSearch() {
    if (!dom) return;
    dom.panel.classList.remove('opacity-100', 'scale-100');
    dom.panel.classList.add('opacity-0', 'scale-95');
    setTimeout(() => {
        dom.overlay.classList.add('hidden');
        document.body.style.overflow = '';
    }, 200);
    dom.input.value = '';
    dom.results.innerHTML = `<div id="global-search-empty" class="hidden text-center py-10 text-gray-400 text-sm">Mulai mengetik untuk mencari...</div>`;
    currentResults = [];
    selectedIndex = -1;
}

function navigateToResult(idx, resultsArray = currentResults) {
    if (idx < 0 || idx >= resultsArray.length) return;
    const result = resultsArray[idx];
    closeSearch();
    window.location.href = result.url;
}

async function performSearch(query) {
    if (query.length < 2) {
        dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Minimal 2 karakter...</div>`;
        currentResults = [];
        return;
    }

    if (currentAbort) currentAbort.abort();
    const abortController = new AbortController();
    currentAbort = abortController;

    showLoading(dom);

    try {
        const [apiData, googleBooks] = await Promise.all([
            fetchLocalSearch(query, abortController).catch(() => ({ users: [], clubs: [], books: [] })),
            fetchGoogleBooks(query, 5).catch(() => []),
        ]);

        if (abortController.signal.aborted) return;

        const localTitles = new Set((apiData.books || []).map(b => b.judul.toLowerCase()));
        const mergedBooks = [...(apiData.books || [])];
        for (const gb of googleBooks) {
            if (!localTitles.has(gb.judul.toLowerCase())) {
                mergedBooks.push({
                    id: null,
                    google_id: gb.google_id,
                    judul: gb.judul,
                    penulis: gb.penulis,
                    cover_url: gb.cover,
                    isbn: '',
                    gradient_from: gb.gradient_from,
                    gradient_to: gb.gradient_to,
                });
                localTitles.add(gb.judul.toLowerCase());
            }
        }

        const totalResults = (apiData.users?.length || 0) + (apiData.clubs?.length || 0) + mergedBooks.length;

        if (totalResults === 0) {
            showEmpty(dom, query);
            currentResults = [];
            return;
        }

        currentResults = renderResults(dom, apiData.users || [], apiData.clubs || [], mergedBooks, navigateToResult);
    } catch (err) {
        if (err.name === 'AbortError') return;
        dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Terjadi kesalahan. Coba lagi.</div>`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    dom = buildOverlay();
    dom.overlay.classList.add('hidden');

    document.getElementById('navbar-search-btn')?.addEventListener('click', (e) => {
        e.preventDefault();
        openSearch();
    });

    dom.backdrop.addEventListener('click', closeSearch);
    dom.closeBtn.addEventListener('click', closeSearch);

    dom.wrapper.addEventListener('click', closeSearch);
    dom.panel.addEventListener('click', (e) => e.stopPropagation());

    dom.input.addEventListener('input', () => {
        if (!IS_AUTHENTICATED) {
            dom.results.innerHTML = `
            <div class="text-center py-10 text-gray-400 text-sm">
                    Silakan <a href="/login" class="text-[#5DA9FF] font-semibold underline">masuk</a> terlebih dahulu untuk mencari.
                </div>
            `;
            return;
        }
        clearTimeout(debounceTimer);
        selectedIndex = -1;
        const query = dom.input.value.trim();
        if (query.length < 2) {
            dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Minimal 2 karakter...</div>`;
            currentResults = [];
            return;
        }
        debounceTimer = setTimeout(() => performSearch(query), 300);
    });

    dom.input.addEventListener('keydown', (e) => {
        const items = dom.results.querySelectorAll('.result-item');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, currentResults.length - 1);
            updateSelection(dom, items, selectedIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            updateSelection(dom, items, selectedIndex);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0) {
                navigateToResult(selectedIndex);
            }
        } else if (e.key === 'Escape') {
            closeSearch();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && dom && !dom.overlay.classList.contains('hidden')) {
            closeSearch();
        }
    });
});