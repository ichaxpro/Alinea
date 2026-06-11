import { state, syncClubFromResponse } from './state.js';
import { applyFilters } from './ui.js';
import { openModal, closeModal } from './modal.js';

export function getClubPayload(clubId) {
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

export function setJoined(clubId) {
    const club = state.CLUBS.find(c => c.id === clubId);
    if (!club || club.joined || !state.CURRENT_USER) return;

    const modal = document.getElementById('klub-modal');

    fetch(`/klub/${clubId}/join`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': state.CSRF_TOKEN,
        },
        credentials: 'same-origin',
        body: JSON.stringify({}),
    })
        .then(async (response) => {
            if (!response.ok) {
                const errorBody = await response.json().catch(() => ({}));
                throw { status: response.status, message: errorBody.message || 'Gagal join klub' };
            }
            return response.json();
        })
        .then((data) => {
            const updatedClub = syncClubFromResponse(data);
            applyFilters(false);
            if (modal && !modal.classList.contains('hidden')) {
                openModal(updatedClub);
            }
        })
        .catch(async (err) => {
            try {
                const resp = await fetch(`/klub/${clubId}/payload`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (resp.ok) {
                    const payload = await resp.json();
                    const updatedClub = syncClubFromResponse(payload);
                    applyFilters(false);
                    if (modal && !modal.classList.contains('hidden')) openModal(updatedClub);
                    return;
                }
            } catch (e) {
                // ignore
            }
            alert(typeof err === 'string' ? err : (err?.message || 'Gagal join klub.'));
        });
}

export function leaveClub(clubId) {
    const club = state.CLUBS.find(c => c.id === clubId);
    if (!club || !state.CURRENT_USER || club.isOwner) return;

    const modal = document.getElementById('klub-modal');

    fetch(`/klub/${clubId}/leave`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': state.CSRF_TOKEN,
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
            if (modal && !modal.classList.contains('hidden')) {
                openModal(updatedClub);
            }
        })
        .catch((err) => {
            alert(typeof err === 'string' ? err : (err?.message || 'Gagal keluar klub.'));
        });
}

export function kickMember(clubId, userId, userName) {
    if (!state.CURRENT_USER) return;

    const club = state.CLUBS.find(c => c.id === clubId);
    if (!club) return;

    const myData = club.membersData.find(m => Number(m.id) === Number(state.CURRENT_USER.id));
    const isOwner = club.isOwner;
    const isAdmin = myData?.role === 'admin' || myData?.role === 'moderator';

    if (!isOwner && !isAdmin) return;

    if (!confirm(`Keluarkan "${userName}" dari klub ini?`)) return;

    const modal = document.getElementById('klub-modal');

    fetch(`/klub/${clubId}/members/${userId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': state.CSRF_TOKEN,
        },
        credentials: 'same-origin',
    })
    .then(async (res) => {
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal mengeluarkan member');
        }
        return res.json();
    })
    .then((data) => {
        const updatedClub = syncClubFromResponse(data);
        applyFilters(false);
        if (modal && !modal.classList.contains('hidden')) {
            openModal(updatedClub);
        }
    })
    .catch((err) => alert(err.message || 'Gagal mengeluarkan member.'));
}

export function updateMemberRole(clubId, userId, userName, newRole) {
    if (!state.CURRENT_USER) return;

    const club = state.CLUBS.find(c => c.id === clubId);
    if (!club || !club.isOwner) return;

    const roleLabel = {admin: 'Admin', member: 'Anggota', owner: 'Owner'}[newRole] || newRole;
    const confirmMsg = newRole === 'owner'
        ? `Transfer ownership ke "${userName}"? Kamu akan menjadi Admin setelahnya.`
        : `Jadikan "${userName}" sebagai ${roleLabel}?`;

    if (!confirm(confirmMsg)) return;

    const modal = document.getElementById('klub-modal');

    fetch(`/klub/${clubId}/members/${userId}/role`, {
        method: 'PATCH',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': state.CSRF_TOKEN,
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
        if (modal && !modal.classList.contains('hidden')) {
            openModal(updatedClub);
        }
    })
    .catch ((err) => alert(err.message || 'Gagal mengubah role.'));
}

export function deleteClub(clubId) {
    const club = state.CLUBS.find(c => c.id === clubId);
    if (!club || !state.CURRENT_USER || !club.isOwner) return;

    fetch(`/klub/${clubId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': state.CSRF_TOKEN,
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
            const index = state.CLUBS.findIndex(c => c.id === clubId);
            if (index >= 0) state.CLUBS.splice(index, 1);
            applyFilters();
            closeModal();
        })
        .catch((err) => {
            alert(typeof err === 'string' ? err : (err?.message || 'Gagal menghapus klub.'));
        });
}
