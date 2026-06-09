import { state } from './state.js';
import { getMemberRoleLabel } from './utils.js';
import { deleteClub, leaveClub, setJoined, kickMember, updateMemberRole } from './api.js';
import { openEditClub } from './form.js';

let deleteConfirmEl = null;

export function buildMemberRow(m, club) {
    const isCurrentUser = state.CURRENT_USER && Number(m.id) === Number(state.CURRENT_USER.id);
    const isTargetOwner = m.role === 'owner';

    const myData = club.membersData.find(x => state.CURRENT_USER && Number(x.id) === Number(state.CURRENT_USER.id));
    const iAmOwner = club.isOwner;
    const iAmAdmin = myData?.role === 'admin' || myData?.role === 'moderator';

    let actionBtns = '';
    if (!isCurrentUser && !isTargetOwner && state.CURRENT_USER) {
        let menuItems = '';
        if (iAmOwner) {
            if (m.role !== 'admin' && m.role !== 'moderator') {
                menuItems += `<button data-role-btn data-club="${club.id}" data-user="${m.id}" data-name="${m.name}" data-role="admin"
                    class="w-full text-left text-xs px-3 py-3 sm:py-1.5 hover:bg-gray-100 transition-colors whitespace-nowrap">Jadikan Admin</button>`;
            } else {
                menuItems += `<button data-role-btn data-club="${club.id}" data-user="${m.id}" data-name="${m.name}" data-role="member"
                    class="w-full text-left text-xs px-3 py-3 sm:py-1.5 hover:bg-gray-100 transition-colors whitespace-nowrap">Cabut Akses Admin</button>`;
            }
            menuItems += `<button data-role-btn data-club="${club.id}" data-user="${m.id}" data-name="${m.name}" data-role="owner"
                class="w-full text-left text-xs px-3 py-3 sm:py-1.5 hover:bg-gray-100 transition-colors whitespace-nowrap">Transfer Kepemilikan</button>`;
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

export function closeDeleteConfirm() {
    if (!deleteConfirmEl) return;
    deleteConfirmEl.classList.add('opacity-0');
    deleteConfirmEl.classList.add('pointer-events-none');
    setTimeout(() => {
        deleteConfirmEl?.remove();
        deleteConfirmEl = null;
        document.body.style.overflow = '';
    }, 220);
}

export function openDeleteConfirm(club) {
    if (!club || !state.CURRENT_USER || !club.isOwner) return;

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

export function openModal(club) {
    const modal = document.getElementById('klub-modal');
    const modalContent = document.getElementById('klub-modal-content');
    const modalPanel = document.getElementById('klub-modal-panel');
    if (!club || !modal || !modalContent || !modalPanel) return;

    const actionButtonsHtml = club.isOwner
        ? `
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a href="/timeline_komunitas?klub_filter=${encodeURIComponent(club.name)}" class="bg-white text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-gray-50 transition-colors flex-shrink-0 text-center">Diskusi Klub</a>
                <button data-edit-club-btn="${club.id}" class="bg-white text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-gray-50 transition-colors flex-shrink-0">Edit Klub</button>
                <button data-delete-club-btn="${club.id}" class="bg-white text-red-500 font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-red-500 hover:bg-red-50 hover:border-red-600 hover:text-red-600 transition-colors flex-shrink-0">Hapus Klub</button>
            </div>
        `
        : club.joined
            ? `
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <a href="/timeline_komunitas?klub_filter=${encodeURIComponent(club.name)}" class="bg-white text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-gray-50 transition-colors flex-shrink-0 text-center">Diskusi Klub</a>
                <button data-leave-club-btn="${club.id}" class="bg-red-50 text-red-500 font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-red-500 hover:bg-red-100 hover:border-red-600 hover:text-red-600 transition-colors flex-shrink-0">Keluar Klub</button>
            </div>
            `
            : `
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <button data-join-club-btn="${club.id}" class="bg-[#FFDDAF] text-[#444] font-bold text-xs px-5 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] hover:-translate-y-0.5 hover:-translate-x-0.5 hover:shadow-[3px_3px_0px_#444] active:translate-y-0 active:translate-x-0 active:shadow-none transition-all flex-shrink-0">Bergabung</button>
            </div>
            `;

    const memberList = (club.membersData && club.membersData.length) ? club.membersData : (club.membersList||[]).map((m, i) => ({ name: m, username: null, avatar: null, role: i === 0 ? 'owner' : 'member' }));
    
    const owners = memberList.filter(m => m.role === 'owner');
    if (owners.length === 0 && club.owner) {
        owners.push(club.owner);
    }
    const admins = memberList.filter(m => m.role === 'admin' || m.role === 'moderator');
    const regularMembers = memberList.filter(m => m.role !== 'owner' && m.role !== 'admin' && m.role !== 'moderator');

    let membersHtml = '';

    if (owners.length > 0) {
        membersHtml += `
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Pemilik</h4>
                <div class="flex flex-col divide-y divide-gray-100">
                    ${owners.map(m => buildMemberRow(m, club)).join('')}
                </div>
            </div>`;
    }

    if (admins.length > 0) {
        membersHtml += `
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Admin</h4>
                <div class="flex flex-col divide-y divide-gray-100">
                    ${admins.map(m => buildMemberRow(m, club)).join('')}
                </div>
            </div>`;
    }

    if (regularMembers.length > 0) {
        membersHtml += `
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Anggota (${regularMembers.length})</h4>
                <div class="flex flex-col divide-y divide-gray-100">
                    ${regularMembers.map(m => buildMemberRow(m, club)).join('')}
                </div>
            </div>`;
    }

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
                <span class="text-xs text-gray-400">Dibentuk ${club.founded}</span>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed mb-6 break-words whitespace-normal">${club.fullDescription}</p>
            <div class="space-y-5">
                ${membersHtml}
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

export function closeModal() {
    const modal = document.getElementById('klub-modal');
    const modalPanel = document.getElementById('klub-modal-panel');
    if (!modal || !modalPanel) return;

    document.querySelector('[data-dropdown-portal]')?.remove();
    modalPanel.classList.remove('scale-100', 'opacity-100');
    modalPanel.classList.add('scale-95', 'opacity-0');
    setTimeout(() => { modal.classList.add('hidden'); document.body.style.overflow = ''; }, 300);
}
