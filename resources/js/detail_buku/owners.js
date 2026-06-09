import { state } from './state';
import { setTextById, showToast } from './utils';

const CURRENT_USER = {
  name: 'Current User',
  domicile: 'Surabaya' 
};

export function renderOwnersTable(book, filterLoc = 'all') {
  const tbody = document.getElementById('ownersTableBody');
  const mobileList = document.getElementById('ownersMobileList');
  if (!book.owners) return;
  
  // Filter by location
  let filteredOwners = [...book.owners];
  if (filterLoc !== 'all') {
    filteredOwners = filteredOwners.filter(owner => owner.location === filterLoc);
  }
  
  // Sort so that owners with the same domicile as the current user appear first
  filteredOwners.sort((a, b) => {
    const aMatch = a.location === CURRENT_USER.domicile ? 1 : 0;
    const bMatch = b.location === CURRENT_USER.domicile ? 1 : 0;
    return bMatch - aMatch;
  });

  if (filteredOwners.length === 0) {
    if (tbody) tbody.innerHTML = `<tr><td colspan="3" class="py-6 text-center text-[0.85rem] text-[#444444]/60">Tidak ada pemilik di lokasi ini yang memiliki buku.</td></tr>`;
    if (mobileList) mobileList.innerHTML = `<div class="py-6 text-center text-[0.85rem] text-[#444444]/60">Tidak ada pemilik di lokasi ini yang memiliki buku.</div>`;
    return;
  }
  
  // Render desktop table rows
  if (tbody) {
    tbody.innerHTML = filteredOwners.map(owner => `
      <tr class="border-b-[1.5px] border-[#eee] last:border-0 hover:bg-[#FBFBFB] transition-colors">
        <td class="py-4 px-4">
          <div class="flex items-center gap-3">
            ${owner.avatar_url
              ? `<img src="${owner.avatar_url}" alt="${owner.name}" class="w-8 h-8 rounded-full object-cover shrink-0" />`
              : `<div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#D4F6FF] flex items-center justify-center text-[0.7rem] font-bold text-[#444444]">${owner.name[0].toUpperCase()}</div>`
            }
            <span class="text-[0.9rem] font-semibold text-[#444444]">${owner.name}</span>
          </div>
        </td>
        <td class="py-4 px-4 text-[0.85rem] text-[#444444]/70">${owner.location}</td>
        <td class="py-4 px-4 text-center">
          <button class="btn-pilih-owner px-4 py-1.5 text-[0.8rem] font-bold text-[#444444] bg-white border-[1.5px] border-[#ddd] rounded-full transition-all duration-200 hover:border-[#444444] hover:bg-[#FBFBFB] cursor-pointer" data-name="${owner.name}" data-pbid="${owner.personal_book_id}">Pilih</button>
        </td>
      </tr>
    `).join('');
  }

  // Render mobile cards list
  if (mobileList) {
    mobileList.innerHTML = filteredOwners.map(owner => `
      <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100 gap-2">
        <div class="flex items-center gap-3 min-w-0">
          ${owner.avatar_url
            ? `<img src="${owner.avatar_url}" alt="${owner.name}" class="w-10 h-10 rounded-full object-cover shrink-0" />`
            : `<div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#D4F6FF] flex items-center justify-center text-xs font-bold text-[#444444] shrink-0">${owner.name[0].toUpperCase()}</div>`
          }
          <div class="min-w-0">
            <h4 class="text-sm font-semibold text-text truncate leading-tight">${owner.name}</h4>
            <p class="text-[0.68rem] text-text/50 mt-0.5">${owner.location}</p>
          </div>
        </div>
        <button class="btn-pilih-owner px-4 py-2 text-xs font-bold text-text bg-white border-[1.5px] border-text rounded-full hover:bg-gray-50 active:bg-gray-100 transition-colors shrink-0 cursor-pointer" data-name="${owner.name}" data-pbid="${owner.personal_book_id}">Pilih</button>
      </div>
    `).join('');
  }
}

export function openOwnersModal() {
  const modal = document.getElementById('ownersModalOverlay');
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

export function closeOwnersModal() {
  const modal = document.getElementById('ownersModalOverlay');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

export function openPinjamModal() {
  const modal = document.getElementById('pinjamModalOverlay');
  if (modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

export function closePinjamModal() {
  const modal = document.getElementById('pinjamModalOverlay');
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

export function initOwnersEvents() {
  document.getElementById('pinjamBtn')?.addEventListener('click', () => {
    renderOwnersTable(window.__BOOK_DATA__);
    openOwnersModal();
  });

  const ownersModalOverlay = document.getElementById('ownersModalOverlay');
  document.getElementById('ownersModalClose')?.addEventListener('click', closeOwnersModal);
  if (ownersModalOverlay) {
    ownersModalOverlay.addEventListener('click', e => { if (e.target === ownersModalOverlay) closeOwnersModal(); });
  }

  const lokasiFilter = document.getElementById('lokasiFilter');
  if (lokasiFilter) {
    lokasiFilter.addEventListener('change', (e) => {
      renderOwnersTable(window.__BOOK_DATA__, e.target.value);
    });
  }

  const pinjamModalOverlay = document.getElementById('pinjamModalOverlay');
  document.getElementById('pinjamModalClose')?.addEventListener('click', closePinjamModal);
  if (pinjamModalOverlay) {
    pinjamModalOverlay.addEventListener('click', e => { if (e.target === pinjamModalOverlay) closePinjamModal(); });
  }

  document.addEventListener('keydown', e => { 
    if (e.key === 'Escape') {
      if (ownersModalOverlay && ownersModalOverlay.classList.contains('active')) closeOwnersModal();
      if (pinjamModalOverlay && pinjamModalOverlay.classList.contains('active')) closePinjamModal();
    }
  });

  document.getElementById('ownersModal')?.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-pilih-owner');
    if (!btn) return;
    
    const selectedOwnerName = btn.dataset.name;
    state.selectedPersonalBookId = btn.dataset.pbid;
    
    setTextById('pinjamBookOwner', selectedOwnerName);
    
    closeOwnersModal();
    openPinjamModal();
  });

  document.getElementById('submitPinjamBtn')?.addEventListener('click', async () => {
    const durasi = document.getElementById('durasiPeminjaman').value.trim();
    const titik = document.getElementById('titikTemu').value.trim();
    if (!durasi || !titik) { showToast('⚠️ Lengkapi durasi dan titik temu'); return; }
    
    if (!state.selectedPersonalBookId) { showToast('⚠️ Pilih pemilik buku terlebih dahulu.'); return; }
    
    const btn = document.getElementById('submitPinjamBtn');
    btn.disabled = true;
    btn.textContent = 'Memproses...';

    try {
        const response = await fetch('/transactions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                book_id: state.selectedPersonalBookId, 
                titik_temu: titik, 
                durasi_hari: parseInt(durasi) 
            })
        });

        if (response.ok) {
            closePinjamModal();
            document.getElementById('durasiPeminjaman').value = '';
            document.getElementById('titikTemu').value = '';
            showToast('📚 Permintaan peminjaman diajukan! Cek notifikasi secara berkala.');
        } else {
            const err = await response.json();
            showToast('⚠️ Gagal mengirim pengajuan: ' + (err.message || 'Error'));
        }
    } catch (error) {
        showToast('⚠️ Terjadi kesalahan jaringan.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Kirim Pengajuan';
    }
  });

  // Sticky bottom action bar scroll behavior on mobile
  const stickyBar = document.getElementById('mobileStickyBar');
  const mainPinjamBtn = document.getElementById('pinjamBtn');
  
  if (stickyBar && mainPinjamBtn) {
    window.addEventListener('scroll', () => {
      if (window.innerWidth < 640) {
        const btnPosition = mainPinjamBtn.getBoundingClientRect().bottom + window.scrollY;
        if (window.scrollY > btnPosition) {
          stickyBar.classList.remove('translate-y-full');
        } else {
          stickyBar.classList.add('translate-y-full');
        }
      } else {
        stickyBar.classList.add('translate-y-full');
      }
    }, { passive: true });

    // Link sticky borrow button click directly to the main borrow flow
    const stickyPinjamBtn = document.getElementById('stickyPinjamBtn');
    if (stickyPinjamBtn) {
      stickyPinjamBtn.addEventListener('click', () => {
        if (!mainPinjamBtn.disabled) {
          mainPinjamBtn.click();
        }
      });
    }
  }
}
