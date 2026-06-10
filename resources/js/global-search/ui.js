export function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

export function buildOverlay() {
    const overlay = document.createElement('div');
    overlay.id = 'global-search-overlay';
    overlay.innerHTML = `
    <div id="global-search-backdrop" class="fixed inset-0 z-[999] bg-black/40 backdrop-blur-sm"></div>
        <div id="global-search-wrapper" class="fixed inset-0 z-[1000] flex items-start justify-center pt-[15vh] px-4">
            <div id="global-search-panel" class="w-full max-w-xl bg-white border-[1.5px] border-[#444] rounded-2xl shadow-2xl overflow-hidden opacity-0 scale-95 transition-all duration-200">
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-200">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input id="global-search-input" type="text" placeholder="Cari pengguna, buku, atau klub..."
                           class="flex-1 border-none outline-none bg-transparent text-base placeholder-gray-300" autocomplete="off" />
                    <button id="global-search-close" class="text-gray-400 hover:text-[#444] transition-colors p-1" aria-label="Tutup pencarian">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                <div id="global-search-results" class="max-h-[60vh] overflow-y-auto p-2">
                    <div id="global-search-empty" class="hidden text-center py-10 text-gray-400 text-sm">Mulai mengetik untuk mencari...</div>
                    <div id="global-search-loading" class="hidden text-center py-10 text-gray-400 text-sm">Mencari...</div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(overlay);
    return {
        overlay,
        backdrop: document.getElementById('global-search-backdrop'),
        wrapper: document.getElementById('global-search-wrapper'),
        panel: document.getElementById('global-search-panel'),
        input: document.getElementById('global-search-input'),
        results: document.getElementById('global-search-results'),
        empty: document.getElementById('global-search-empty'),
        loading: document.getElementById('global-search-loading'),
        closeBtn: document.getElementById('global-search-close'),
    };
}

export function showLoading(dom) {
    dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Mencari...</div>`;
}

export function showEmpty(dom, query) {
    dom.results.innerHTML = `<div class="text-center py-10 text-gray-400 text-sm">Tidak ada hasil untuk "<strong>${escapeHtml(query)}</strong>"</div>`;
}

export function updateSelection(dom, items, selectedIndex) {
    items.forEach((el, i) => {
        if (i === selectedIndex) {
            el.classList.add('bg-gray-100');
            el.scrollIntoView({ block: 'nearest' });
        } else {
            el.classList.remove('bg-gray-100');
        }
    });
    if (selectedIndex === -1 && dom) {
        dom.input.focus();
    }
}

export function renderResults(dom, users, clubs, books, onResultClick) {
    let html = '';
    const currentResults = [];

    if (users.length) {
        html += `<div class="px-3 pt-3 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Pengguna</div>`;
        users.forEach((u) => {
            const idx = currentResults.length;
            html += `
                <button data-result-idx="${idx}" data-url="${u.username ? `/u/${u.username}` : `/user/${u.id}`}"
                        class="result-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors hover:bg-gray-50 focus:bg-gray-50 outline-none">
                    <div class="w-9 h-9 rounded-full border-[1.5px] border-[#444] flex-shrink-0 overflow-hidden
                                bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center">
                        ${u.avatar_url
                            ? `<img src="${u.avatar_url}" alt="" class="w-full h-full object-cover">`
                            : `<span class="text-sm font-black text-text/60">${u.initial}</span>`
                        }
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold truncate">${escapeHtml(u.name)}</div>
                        ${u.username ? `<div class="text-xs text-gray-400 truncate">@${escapeHtml(u.username)}</div>` : ''}
                    </div>
                </button>
            `;
            currentResults.push({type: 'user', url: u.username ? `/u/${u.username}` : `/user/${u.id}`});
        });
    }

    if (clubs.length) {
        html += `<div class="px-3 pt-4 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Klub</div>`;
        clubs.forEach((c) => {
            const idx = currentResults.length;
            const coverStyle = c.foto_klub ? `background-image: url('${c.foto_klub}'); background-size: cover; background-position: center;`
                : `background: linear-gradient(135deg, ${c.gradient_from}, ${c.gradient_to})`;
            html += `
                <button data-result-idx="${idx}" data-url="/klub?highlight=${c.id}"
                        class="result-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors hover:bg-gray-50 focus:bg-gray-50 outline-none">
                    <div class="w-9 h-9 rounded-lg border-[1.5px] border-[#444] flex-shrink-0" style="${coverStyle}"></div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold truncate">${escapeHtml(c.nama_klub)}</div>
                        <div class="text-xs text-gray-400">
                            <span>${escapeHtml(c.kategori)}</span>
                            <span class="mx-1">·</span>
                            <span>${c.member_count} member</span>
                        </div>
                    </div>
                </button>
            `;
            currentResults.push({type: 'club', url: `/klub?highlight=${c.id}`});
        });
    }

    if (books.length) {
        html += `<div class="px-3 pt-4 pb-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Buku</div>`;
        books.forEach((b) => {
            const idx = currentResults.length;
            const detailParam = b.id ?? b.google_id;
            const coverStyle = b.cover_url
                ? `background-image: url('${b.cover_url}'); background-size: cover; background-position: center;`
                : `background: linear-gradient(135deg, ${b.gradient_from}, ${b.gradient_to})`;
            html += `
                <button data-result-idx="${idx}" data-url="/detail-buku/${detailParam}"
                        class="result-item w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-colors hover:bg-gray-50 focus:bg-gray-50 outline-none">
                    <div class="w-9 h-12 rounded-lg border-[1.5px] border-[#444] flex-shrink-0 bg-cover bg-center" style="${coverStyle}"></div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold truncate">${escapeHtml(b.judul)}</div>
                        <div class="text-xs text-gray-400 truncate">${escapeHtml(b.penulis)}</div>
                    </div>
                </button>
            `;
            currentResults.push({ type: 'book', url: `/detail-buku/${detailParam}` });
        });
    }

    dom.results.innerHTML = html;

    dom.results.querySelectorAll('.result-item').forEach(el => {
        el.addEventListener('click', () => {
            const idx = parseInt(el.dataset.resultIdx);
            onResultClick(idx, currentResults);
        });
    });

    return currentResults;
}
