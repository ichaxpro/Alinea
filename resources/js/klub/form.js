import { state, syncClubFromResponse } from './state.js';
import { applyFilters } from './ui.js';

export function openBuatKlub() {
    const myClubsCount = state.CLUBS.filter(c => c.isOwner).length;
    if (myClubsCount >= 3) {
        alert('Maksimal pembuatan klub adalah 3. Anda telah mencapai batas maksimal.');
        return;
    }

    const buatForm      = document.getElementById('buat-klub-form');
    const buatModal     = document.getElementById('buat-klub-modal');
    const buatPanel     = document.getElementById('buat-klub-panel');
    const submitBtn     = document.getElementById('btn-submit-klub');

    if (!buatModal || !buatPanel) return;

    if (buatForm) {
        buatForm.reset();
        delete buatForm.dataset.mode;
        delete buatForm.dataset.clubId;
        buatForm.action = '/klub';
    }
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

export function closeBuatKlub() {
    const buatModal     = document.getElementById('buat-klub-modal');
    const buatPanel     = document.getElementById('buat-klub-panel');
    const buatForm      = document.getElementById('buat-klub-form');
    const submitBtn     = document.getElementById('btn-submit-klub');

    if (!buatModal || !buatPanel) return;

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

export function setupCounter(inputId, counterId, max) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (!input || !counter) return;
    input.addEventListener('input', () => {
        const len = input.value.length;
        counter.textContent = `${len}/${max}`;
        counter.classList.toggle('text-red-400', len >= max);
    });
}

export function openEditClub(clubId) {
    const club = state.CLUBS.find(c => c.id === clubId);
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

    // Trigger input events to update counters
    titleInput.dispatchEvent(new Event('input'));
    descriptionInput.dispatchEvent(new Event('input'));

    modalEl.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    requestAnimationFrame(() => {
        panelEl.classList.remove('scale-95', 'opacity-0');
        panelEl.classList.add('scale-100', 'opacity-100');
    });
}

export function bindFormEvents() {
    setupCounter('input-nama-klub', 'nama-klub-counter', 100);
    setupCounter('input-deskripsi', 'deskripsi-counter', 500);

    const buatBtn       = document.getElementById('buat-klub-btn');
    const buatCloseBtn  = document.getElementById('buat-klub-close');
    const buatBackdrop  = document.getElementById('buat-klub-backdrop');
    
    if (buatBtn) buatBtn.addEventListener('click', openBuatKlub);
    if (buatCloseBtn) buatCloseBtn.addEventListener('click', closeBuatKlub);
    if (buatBackdrop) buatBackdrop.addEventListener('click', closeBuatKlub);

    // Gradient picker
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
            if (gradFromInput) gradFromInput.value = from;
            if (gradToInput)   gradToInput.value   = to;
            if (previewBanner) previewBanner.style.background = `linear-gradient(135deg, ${from}, ${to})`;
            
            gradientPicker.querySelectorAll('button').forEach(b => {
                b.classList.remove('border-[#444]', 'ring-2', 'ring-[#444]', 'ring-offset-2');
                b.classList.add('border-gray-200');
            });
            btn.classList.remove('border-gray-200');
            btn.classList.add('border-[#444]', 'ring-2', 'ring-[#444]', 'ring-offset-2');
        });
    }

    // Dropzone
    const dropzone  = document.getElementById('foto-klub-dropzone');
    const fotoInput = document.getElementById('input-foto-klub');
    const fotoLabel = document.getElementById('foto-klub-label');

    function updateFotoLabel() {
        if (fotoInput.files.length && fotoLabel) {
            fotoLabel.textContent = fotoInput.files[0].name;
            fotoLabel.classList.remove('text-gray-300');
            fotoLabel.classList.add('text-[#444]', 'font-medium');
        }
    }

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

    // Form submit
    const buatForm = document.getElementById('buat-klub-form');
    if (buatForm) {
        buatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = document.getElementById('btn-submit-klub');

            const formData = new FormData(buatForm);
            const isEditMode = buatForm.dataset.mode === 'edit';
            const requestUrl = isEditMode ? (buatForm.dataset.clubId ? `/klub/${buatForm.dataset.clubId}` : buatForm.action) : buatForm.action;
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

                let data = null;
                try { data = await res.json(); } catch (e) { /* ignore parse errors */ }

                if (data) {
                    if (isEditMode) {
                        syncClubFromResponse(data);
                    } else {
                        const updated = syncClubFromResponse(data);
                        if (!state.CLUBS.find(c => c.id === updated.id)) state.CLUBS.unshift(updated);
                    }
                }

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
                if (previewBanner) previewBanner.style.background = 'linear-gradient(135deg, #FFDDAF, #C7E7FF)';
                if (fotoLabel) { fotoLabel.textContent = 'Klik atau seret gambar ke sini'; fotoLabel.className = 'text-xs text-gray-300'; }

            } catch (err) {
                console.error('Error submitting buat-klub form:', err);
            }
        });
    }
}
