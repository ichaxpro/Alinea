import { showToast } from './toast.js';

export function initSearch() {
    const sidebarSearchInput = document.getElementById('sidebar-search-input');
    const sidebarSearchDropdown = document.getElementById('sidebar-search-dropdown');
    const mobileSearchDropdown = document.getElementById('mobile-search-dropdown');
    const mobileSearchTrigger = document.getElementById('mobile-top-search-btn');
    const mobileSearchOverlay = document.getElementById('mobile-search-overlay');
    const mobileSearchClose = document.getElementById('mobile-search-close');
    const mobileSearchBack = document.getElementById('mobile-search-back');
    const mobileSearchInput = document.getElementById('mobile-search-input');
    const mobileSearchTrending = document.getElementById('mobile-search-trending');

    let sidebarSearchTimeout;
    let mobileSearchQuery = '';

    document.addEventListener('click', (e) => {
        if (sidebarSearchDropdown && !e.target.closest('#sidebar-search-input') && !e.target.closest('#sidebar-search-dropdown')) {
            sidebarSearchDropdown.classList.add('hidden');
        }
        if (mobileSearchDropdown && !e.target.closest('#mobile-search-input') && !e.target.closest('#mobile-search-dropdown') && !e.target.closest('#mobile-search-overlay') && !e.target.closest('#mobile-search-trending')) {
            mobileSearchDropdown.classList.add('hidden');
        }
    });

    function filterTimelinePosts(query) {
        const feedPanel = document.getElementById('feed-panel');
        if (!feedPanel) return;

        const posts = feedPanel.querySelectorAll('article[data-post-id]');
        let visibleCount = 0;

        posts.forEach(post => {
            const match = !query || post.textContent.toLowerCase().includes(query.toLowerCase());
            post.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        let noResults = document.getElementById('search-no-results');
        if (query && visibleCount === 0 && posts.length > 0) {
            if (!noResults) {
                noResults = document.createElement('div');
                noResults.id = 'search-no-results';
                noResults.className = 'text-center py-10 text-gray-400';
                noResults.innerHTML = '<p>Tidak ada postingan yang cocok.</p>';
                feedPanel.appendChild(noResults);
            }
            noResults.classList.remove('hidden');
        } else if (noResults) {
            noResults.classList.add('hidden');
        }
    }

    function fetchUsers(query) {
        const isMobileSearch = mobileSearchOverlay && !mobileSearchOverlay.classList.contains('hidden');
        const dropdown = isMobileSearch ? mobileSearchDropdown : sidebarSearchDropdown;

        fetch(`/api/search?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (!data.users || data.users.length === 0) {
                    dropdown?.classList.add('hidden');
                    return;
                }

                const html = data.users.map(u => {
                    const avatarHtml = u.avatar_url ? `<img src="${u.avatar_url}" class="w-9 h-9 rounded-full border-2 border-[#444] object-cover flex-shrink-0" alt="${u.name}">`
                        : `<div class="w-9 h-9 rounded-full border-2 border-[#444] flex-shrink-0 flex items-center justify-center bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] text-xs font-bold">${u.initial}</div>`;

                    return `<a href="/u/${u.username}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors no-underline text-[#444]">
                        ${avatarHtml}
                        <div class="min-w-0">
                            <div class="text-sm font-bold truncate">${u.name}</div>
                            <div class="text-xs text-gray-400 truncate">@${u.username}</div>
                        </div>
                    </a>`;
                }).join('<div class="border-t border-gray-100 last:hidden"></div>');

                dropdown.innerHTML = html;
                dropdown.classList.remove('hidden');
            })
            .catch(() => {
                dropdown?.classList.add('hidden');
            })
    }

    function debouncedUserSearch(query) {
        clearTimeout(sidebarSearchTimeout);
        const isMobileSearch = mobileSearchOverlay && !mobileSearchOverlay.classList.contains('hidden');
        const dropdown = isMobileSearch ? mobileSearchDropdown : sidebarSearchDropdown;
        if (query.length < 2) {
            dropdown?.classList.add('hidden');
            return;
        }
        sidebarSearchTimeout = setTimeout(() => fetchUsers(query), 300);
    }

    function closeMobileSearch() {
        mobileSearchOverlay.classList.add('hidden');
        if (mobileSearchDropdown) mobileSearchDropdown.classList.add('hidden');
        showMobileTrending(false);
        if (mobileSearchInput) mobileSearchInput.blur();
    }

    function showMobileTrending(show) {
        if (mobileSearchTrending) mobileSearchTrending.classList.toggle('hidden', !show);
    }

    if (sidebarSearchInput) {
        sidebarSearchInput.addEventListener('input', () => {
            const query = sidebarSearchInput.value.trim();
            filterTimelinePosts(query);
            debouncedUserSearch(query);
        });

        sidebarSearchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                sidebarSearchDropdown?.classList.add('hidden');
                sidebarSearchInput.blur();
            }
        });
    }

    if (mobileSearchTrigger && mobileSearchOverlay) {
        mobileSearchTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (mobileSearchInput) {
                mobileSearchInput.value = mobileSearchQuery;
                setTimeout(() => mobileSearchInput.focus(), 100);
            }
            showMobileTrending(!mobileSearchQuery);
            mobileSearchOverlay.classList.remove('hidden');
        });

        if (mobileSearchClose) {
            mobileSearchClose.addEventListener('click', closeMobileSearch);
        }

        if (mobileSearchBack) {
            mobileSearchBack.addEventListener('click', closeMobileSearch);
        }

        if (mobileSearchInput) {
            mobileSearchInput.addEventListener('input', () => {
                mobileSearchQuery = mobileSearchInput.value.trim();
                filterTimelinePosts(mobileSearchQuery);
                debouncedUserSearch(mobileSearchQuery);
                showMobileTrending(!mobileSearchQuery);
                if (mobileSearchQuery && mobileSearchDropdown) mobileSearchDropdown.classList.add('hidden');
            });

            mobileSearchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === 'Escape') {
                    closeMobileSearch();
                }
            });
        }
    }
}
