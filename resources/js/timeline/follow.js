import { escapeHtml } from './helpers.js';
import { showToast } from './toast.js';

export function initFollowModal() {
    const modalOverlay = document.getElementById('follow-modal-overlay');
    const modal = document.getElementById('follow-modal');
    const modalBody = document.getElementById('follow-modal-body');
    const modalClose = document.getElementById('follow-modal-close');
    const followingTrigger = document.getElementById('profile-following-trigger');
    const followersTrigger = document.getElementById('profile-followers-trigger');
    const tabFollowing = document.getElementById('follow-tab-following');
    const tabFollowers = document.getElementById('follow-tab-followers');
    let followModalActiveTab = 'following';

    function openFollowModal(tab) {
        if (!modalOverlay || !modal) return;
        followModalActiveTab = tab;
        modalOverlay.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-0', 'translate-y-4');
        document.body.style.overflow = 'hidden';
        activateFollowTab(tab);
        loadFollowList(tab);
    }

    function closeFollowModal() {
        if (!modalOverlay || !modal) return;
        modalOverlay.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-0', 'translate-y-4');
        document.body.style.overflow = '';
    }

    function activateFollowTab(tab) {
        const tabs = [tabFollowing, tabFollowers];
        tabs.forEach(btn => {
            if (!btn) return;
            const isActive = btn.dataset.followTab === tab;
            btn.classList.toggle('text-[#111]', isActive);
            btn.classList.toggle('text-gray-400', !isActive);
            const indicator = btn.querySelector('span');
            if (indicator) {
                indicator.classList.toggle('bg-[#5DA9FF]', isActive);
                indicator.classList.toggle('bg-transparent', !isActive);
            }
        });
    }

    function renderFollowUser(user) {
        const initial = (user.name || 'U').charAt(0).toUpperCase();
        const avatarHtml = user.avatar_url ? `<img src="${user.avatar_url}" alt="${escapeHtml(user.name)}" class="w-full h-full object-cover">` : `<span class="text-xs font-bold text-[#444]">${initial}</span>`;

        const profileUrl = `/u/${encodeURIComponent(user.username || '')}`;

        let actionBtn = '';
        const isAuth = document.querySelector('meta[name="user-auth"]')?.content === 'true';
        if (isAuth) {
            const currentUserId = document.querySelector('meta[name="user-id"]')?.content;
            const isOwnProfile = currentUserId === String(user.id);
            if (!isOwnProfile) {
                actionBtn = `<button type="button"
                    data-modal-follow-btn
                    data-user-id="${user.id}"
                    data-following="${user.is_following ? 'true' : 'false'}"
                    class="ml-auto shrink-0 px-4 py-1.5 rounded-full text-xs font-bold border-[1.5px] border-[#444] transition-colors cursor-pointer ${
                        user.is_following
                            ? 'bg-[#444] text-white'
                            : 'bg-[#FFDDAF] hover:bg-[#ffcf90]'
                }">
                    ${user.is_following ? 'Mengikuti' : 'Ikuti'}
                </button>
                `
            }
        }

        return `<div class="flex items-center gap-3 py-3 border-b border-gray-100 last:border-b-0">
            <a href="${profileUrl}" class="w-10 h-10 rounded-full border-2 border-[#444] overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center flex-shrink-0 hover:opacity-80 transition-opacity">
                ${avatarHtml}
            </a>
            <a href="${profileUrl}" class="flex-1 min-w-0 hover:opacity-80 transition-opacity">
                <div class="font-bold text-sm text-[#222] leading-tight truncate">${escapeHtml(user.name)}</div>
                <div class="text-xs text-gray-400 truncate">${user.username ? '@' + escapeHtml(user.username) : 'tanpa_username'}</div>
            </a>
            ${actionBtn}
        </div>
        `
    }

    async function loadFollowList(tab) {
        if (!modalBody) return;

        modalBody.innerHTML = `<div class="flex items-center justify-center h-32">
            <div class="w-6 h-6 border-2 border-[#444] border-t-transparent rounded-full animate-spin"></div>
        </div>`;

        let url;
        const userId = followingTrigger?.dataset?.userId;
        if (!userId) {
            modalBody.innerHTML = `<p class="text-center text-gray-400 py-8">Gagal memuat data</p>`
            return;
        }

        if (tab === 'following') {
            url = `/u/${userId}/following`;
        } else {
            url = `/u/${userId}/followers`;
        }

        try {
            const resp = await fetch(url, {
                headers: {'Accept': 'application/json'},
            });
            if (!resp.ok) throw new Error('Failed to load');
            const result = await resp.json();
            const users = Array.isArray(result.users) ? result.users : [];

            if (users.length === 0) {
                const msg = tab === 'following' ? 'Belum mengikuti siapa pun.' : 'Belum ada pengikut.';
                modalBody.innerHTML = `<p class="text-center text-gray-400 py-8">${msg}</p>`;
                return;
            }

            modalBody.innerHTML = users.map(renderFollowUser).join('');
            bindModalFollowButtons();
        } catch (err) {
            modalBody.innerHTML = `<p class="text-center text-red-400 py-8">Gagal memuat data.</p>`;
        }
    }

    function bindModalFollowButtons() {
        document.querySelectorAll('[data-modal-follow-btn]').forEach(btn => {
            if (btn.dataset.bound === 'true') return;
            btn.dataset.bound = 'true';

            btn.addEventListener('click', async () => {
                const userId = btn.dataset.userId;
                const currentlyFollowing = btn.dataset.following === 'true';

                btn.disabled = true;
                btn.textContent = '...';

                const followUrl = `/u/${userId}/follow`;

                try {
                    const resp = await fetch(followUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                    });
                    const result = await resp.json();
                    if (!resp.ok) throw new Error(result.message || 'Gagal');

                    const nowFollowing = result.following;
                    btn.dataset.following = nowFollowing ? 'true' : 'false';
                    btn.textContent = nowFollowing ? 'Following' : 'Follow';
                    btn.className = 'ml-auto shrink-0 px-4 py-1.5 rounded-full text-xs font-bold border-[1.5px] border-[#444] transition-colors cursor-pointer ' +
                        (nowFollowing ? 'bg-[#444] text-white' : 'bg-[#FFDDAF] hover:bg-[#ffcf90]');

                    if (result.followers_count !== undefined) {
                        const followerTrigger = document.getElementById('profile-followers-trigger');
                        if (followerTrigger) {
                            const span = followerTrigger.querySelector('span.font-bold');
                            if (span) span.textContent = result.followers_count;
                        }
                    }
                    loadFollowList(followModalActiveTab);
                } catch (err) {
                    showToast(err.message);
                } finally {
                    btn.disabled = false;
                }
            });
        });
    }

    if (followingTrigger) {
        followingTrigger.addEventListener('click', () => openFollowModal('following'));
    }
    if (followersTrigger) {
        followersTrigger.addEventListener('click', () => openFollowModal('followers'));
    }
    if (modalClose) {
        modalClose.addEventListener('click', closeFollowModal);
    }
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) closeFollowModal();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeFollowModal();
    });

    if (tabFollowing) {
        tabFollowing.addEventListener('click', () => {
            followModalActiveTab = 'following';
            activateFollowTab('following');
            loadFollowList('following');
        });
    }
    if (tabFollowers) {
        tabFollowers.addEventListener('click', () => {
            followModalActiveTab = 'followers';
            activateFollowTab('followers');
            loadFollowList('followers');
        });
    }
}

export function initFollowButton() {
    const followBtn = document.getElementById('follow-btn');
    if (!followBtn) return;

    followBtn.addEventListener('click', async () => {
        const url = followBtn.dataset.followUrl;
        followBtn.disabled = true;
        followBtn.textContent = '...';

        try {
            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
            });
            const result = await resp.json();
            if (!resp.ok) throw new Error(result.message || 'Gagal');

            const nowFollowing = result.following;
            followBtn.dataset.following = nowFollowing ? 'true' : 'false';
            followBtn.textContent = nowFollowing ? 'Mengikuti' : 'Ikuti';
            followBtn.className = 'ml-auto px-5 py-2 rounded-full text-sm font-bold border-2 border-text transition-colors cursor-pointer ' + (nowFollowing ? 'bg-[#444] text-white' : 'bg-[#FFDDAF] hover:bg-[#FFCF90]');

            const text = document.querySelector('.text-sm.text-gray-500.mt-2');
            if (text && result.followers_count !== undefined) {
                text.innerHTML = `<span class="font-bold text-[#222]">${followBtn.dataset.followingCount}</span> Mengikuti <span class="mx-2">|</span> <span class="font-bold text-[#222]">${result.followers_count}</span> Pengikut`;
            }
        } catch (err) {
            showToast(err.message);
        } finally {
            followBtn.disabled = false;
        }
    })
}
