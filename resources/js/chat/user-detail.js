import { state } from './state.js';
import { apiHeaders } from './api.js';
import { avatarHTML } from './utils.js';
import { openMediaModal } from './media.js';

export function openUserDetailPanel() {
    if (!state.currentOtherUser) return;

    // Populate profile info
    document.getElementById('udAvatarWrapper').innerHTML =
        avatarHTML(state.currentOtherUser, 'w-24 h-24', true);
    document.getElementById('udName').textContent =
        state.currentOtherUser.name || '';
    document.getElementById('udUsername').textContent =
        state.currentOtherUser.username ? `@${state.currentOtherUser.username}` : '';

    // Show overlay + panel
    document.getElementById('userDetailOverlay')?.classList.add('open');
    document.getElementById('userDetailPanel')?.classList.add('open');
    state.userDetailOpen = true;

    // Update block button text
    const conv = state.conversations.find(c => c.other_user.id === state.currentOtherUser.id);
    if (conv && conv.is_blocked_by_me) {
        document.getElementById('udBlockText').textContent = 'Buka Blokir Pengguna';
        document.getElementById('udBlockDesc').textContent = 'Izinkan pengguna ini mengirim pesan';
    } else {
        document.getElementById('udBlockText').textContent = 'Blokir Pengguna';
        document.getElementById('udBlockDesc').textContent = 'Pengguna tidak bisa mengirim pesan';
    }

    // Load media async
    loadConversationMedia();
}

export function closeUserDetailPanel() {
    document.getElementById('userDetailOverlay')?.classList.remove('open');
    document.getElementById('userDetailPanel')?.classList.remove('open');
    state.userDetailOpen = false;
}

export async function loadConversationMedia() {
    if (!state.currentConversationId) return;

    const grid = document.getElementById('udMediaGrid');
    if (!grid) return;
    grid.innerHTML = '<p class="col-span-3 text-xs text-gray-400 text-center py-4">Memuat media…</p>';

    try {
        const res = await fetch(`${state.apiBase}/conversations/${state.currentConversationId}/media`, {
            headers: apiHeaders(),
        });
        if (!res.ok) throw new Error('fetch failed');

        const json  = await res.json();
        const items = json.data || [];

        grid.innerHTML = '';

        if (items.length === 0) {
            grid.innerHTML = '<p class="col-span-3 text-xs text-gray-400 text-center py-6">Belum ada media bersama</p>';
            document.getElementById('udMediaMore')?.classList.add('hidden');
            return;
        }

        // Show max 9 thumbnails (3×3 grid)
        const shown = items.slice(0, 9);
        shown.forEach(item => {
            if (item.type === 'image') {
                const img      = document.createElement('img');
                img.src        = item.url;
                img.className  = 'ud-media-thumb';
                img.alt        = item.name || 'foto';
                img.loading    = 'lazy';
                img.addEventListener('click', () => openMediaModal(item.url, 'image', item.name));
                grid.appendChild(img);
            } else if (item.type === 'video') {
                const wrap      = document.createElement('div');
                wrap.className  = 'ud-media-video-wrap';
                wrap.addEventListener('click', () => openMediaModal(item.url, 'video', item.name));

                const vid       = document.createElement('video');
                vid.src         = item.url;
                vid.preload     = 'metadata';
                vid.muted       = true;
                vid.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block';
                vid.addEventListener('loadedmetadata', () => { vid.currentTime = 0.1; });

                const play      = document.createElement('div');
                play.className  = 'ud-video-play';
                play.innerHTML  = `<svg width="20" height="20" viewBox="0 0 24 24" fill="white">
                    <polygon points="5,3 19,12 5,21"/>
                </svg>`;

                wrap.appendChild(vid);
                wrap.appendChild(play);
                grid.appendChild(wrap);
            }
        });

        // "View all" button when more than 9
        const moreBtn = document.getElementById('udMediaMore');
        if (moreBtn) {
            if (items.length > 9) {
                moreBtn.textContent = `Lihat semua (${items.length} media)`;
                moreBtn.classList.remove('hidden');
            } else {
                moreBtn.classList.add('hidden');
            }
        }

    } catch (e) {
        console.error('loadConversationMedia:', e);
        if (grid) grid.innerHTML = '<p class="col-span-3 text-xs text-red-400 text-center py-4">Gagal memuat media</p>';
    }
}
