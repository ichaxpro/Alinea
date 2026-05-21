/**
 * Klub (Club) page — card grid, filter/sort, detail modal, CREATE CLUB form.
 *
 * Data source: window.__KLUB_DATA__ (injected from Blade).
 * DB-READY: Ketika backend siap, cukup ganti sumber $clubs di controller.
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
    adminAvatar:     c.admin_avatar,
    membersList:     c.members_list,
    recentBooks:     c.recent_books,
    schedule:        c.schedule,
    gradientFrom:    c.gradient_from,
    gradientTo:      c.gradient_to,
}));

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

// ── Render Pagination ──
function renderPagination(totalPages) {
    if (totalPages <= 1) { pagination.innerHTML = ''; return; }
    const btnBase = 'w-9 h-9 rounded-full border-[1.5px] border-[#444] flex items-center justify-center text-sm font-medium transition-colors cursor-pointer';
    const btnActive = 'bg-[#FFDDAF] text-[#444]';
    const btnInactive = 'bg-white text-[#444] hover:bg-gray-50';
    let html = '';
    html += `<button data-page="prev" ${currentPage === 1 ? 'disabled' : ''} class="${btnBase} ${currentPage === 1 ? 'opacity-30 cursor-not-allowed' : btnInactive}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></button>`;
    for (let i = 1; i <= totalPages; i++) {
        html += `<button data-page="${i}" class="${btnBase} ${i === currentPage ? btnActive : btnInactive}">${i}</button>`;
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
    grid.innerHTML = clubs.map(club => `
        <article data-club-id="${club.id}"
                 class="group bg-white border-[1.5px] border-[#444] rounded-2xl p-5 cursor-pointer
                        flex flex-col w-full hover:-translate-y-1 transition-all duration-300">
            <div class="flex items-start gap-4 mb-3">
                <div class="w-20 h-20 rounded-xl border-[1.5px] border-[#444] flex-shrink-0"
                     style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})"></div>
                <div class="min-w-0">
                    <h3 class="font-bold text-base leading-tight mb-1.5">${club.name}</h3>
                    <span class="inline-block text-xs font-medium px-3 py-0.5 rounded-lg border-[1.5px] border-[#444]">${club.category}</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed flex-1 line-clamp-3 overflow-hidden">${club.description}</p>
            <div class="flex items-center justify-between pt-4 mt-4 border-t border-gray-200">
                <button class="bg-[#FFDDAF] text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors">Bergabung</button>
                <span class="text-xs font-semibold text-gray-400">${club.members} Member</span>
            </div>
        </article>
    `).join('');
    grid.querySelectorAll('[data-club-id]').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('button')) return;
            openModal(CLUBS.find(c => c.id === Number(card.dataset.clubId)));
        });
    });
}

// ── Detail Modal ──
function openModal(club) {
    if (!club) return;
    modalContent.innerHTML = `
        <div class="h-36 rounded-t-2xl relative" style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})">
            <div class="absolute -bottom-10 left-6">
                <div class="w-20 h-20 rounded-xl border-[2.5px] border-[#444] bg-white p-1">
                    <div class="w-full h-full rounded-lg" style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})"></div>
                </div>
            </div>
        </div>
        <div class="pt-14 px-6 pb-6">
            <div class="flex items-start justify-between mb-1">
                <h2 class="font-bold text-xl">${club.name}</h2>
                <button class="bg-[#FFDDAF] text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors flex-shrink-0">Bergabung</button>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="inline-block text-xs font-medium px-3 py-0.5 rounded-full border-[1.5px] border-[#444]">${club.category}</span>
                <span class="text-xs text-gray-400">${club.members} Member</span>
                <span class="text-xs text-gray-300">•</span>
                <span class="text-xs text-gray-400">Didirikan ${club.founded}</span>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed mb-6">${club.fullDescription}</p>
            <div class="space-y-5">
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Admin</h4>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full border-[1.5px] border-[#444]" style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})"></div>
                        <span class="text-sm font-semibold">${club.admin}</span>
                    </div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Buku Terakhir Dibaca</h4>
                    <div class="flex flex-wrap gap-2">${(club.recentBooks||[]).map(b => `<span class="text-xs font-medium px-3 py-1.5 rounded-full bg-[#FFDDAF] border-[1.5px] border-[#444]">${b}</span>`).join('')}</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Anggota (${club.members})</h4>
                    <div class="grid grid-cols-2 gap-2">${(club.membersList||[]).map((m, i) => `
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full border border-[#444] flex items-center justify-center text-[10px] font-bold" style="background: linear-gradient(135deg, ${club.gradientFrom}, ${club.gradientTo})">${m.charAt(0)}</div>
                            <span class="text-xs font-medium truncate">${m}${i === 0 ? ' <span class="text-gray-400">(Admin)</span>' : ''}</span>
                        </div>`).join('')}</div>
                </div>
            </div>
        </div>`;
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
    setTimeout(() => { buatModal.classList.add('hidden'); document.body.style.overflow = ''; }, 300);
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

// ── Custom category toggle ──
const kategoriSelect = document.getElementById('input-kategori');
const kategoriCustom = document.getElementById('input-kategori-custom');
if (kategoriSelect && kategoriCustom) {
    kategoriSelect.addEventListener('change', () => {
        if (kategoriSelect.value === '__custom__') {
            kategoriCustom.classList.remove('hidden');
            kategoriCustom.focus();
        } else {
            kategoriCustom.classList.add('hidden');
            kategoriCustom.value = '';
        }
    });
}

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
    buatForm.addEventListener('submit', (e) => {
        e.preventDefault(); // HAPUS baris ini setelah backend siap

        const formData = new FormData(buatForm);
        // Jika custom category dipilih, override kategori
        if (formData.get('kategori') === '__custom__') {
            const custom = formData.get('kategori_custom')?.trim();
            if (!custom) { alert('Masukkan nama kategori.'); return; }
            formData.set('kategori', custom);
        }

        // Preview: tampilkan alert konfirmasi (ganti dengan fetch POST nanti)
        const nama = formData.get('nama_klub');
        alert(`Klub "${nama}" berhasil dibuat! (preview — belum tersimpan ke DB)`);
        closeBuatKlub();
        buatForm.reset();
        // Reset gradient preview
        if (previewBanner) previewBanner.style.background = 'linear-gradient(135deg, #FFDDAF, #C7E7FF)';
        if (fotoLabel) { fotoLabel.textContent = 'Klik atau seret gambar ke sini'; fotoLabel.className = 'text-xs text-gray-300'; }

        /*
        // ── UNCOMMENT setelah backend siap ──
        // fetch(buatForm.action, {
        //     method: 'POST',
        //     body: formData,
        //     headers: { 'X-CSRF-TOKEN': formData.get('_token') }
        // })
        // .then(r => r.json())
        // .then(data => { window.location.reload(); })
        // .catch(err => alert('Gagal membuat klub.'));
        */
    });
}

// ── Event Listeners ──
searchInput.addEventListener('input', applyFilters);
filterCategory.addEventListener('change', applyFilters);
sortSelect.addEventListener('change', applyFilters);
modalClose.addEventListener('click', closeModal);
modalBackdrop.addEventListener('click', closeModal);
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!modal.classList.contains('hidden')) closeModal();
        if (buatModal && !buatModal.classList.contains('hidden')) closeBuatKlub();
    }
});

// ── Init ──
populateCategories();
applyFilters();
