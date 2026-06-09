import { initNavbar, initBackToTop, initMobileBottomNav, initSidebarNav, initMediaCarousel } from './ui.js';
import { initFeedTabs, initProfileTabs } from './tabs.js';
import { initComposer } from './composer.js';
import { initFollowModal, initFollowButton } from './follow.js';
import { initSearch } from './search.js';
import { initClubFilters } from './club-filters.js';
import { bindPostActions, bindMediaGalleries } from './posts.js';

document.addEventListener('DOMContentLoaded', () => {
    const feedPanel = document.getElementById('feed-panel');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const currentUserName = document.querySelector('meta[name="user-name"]')?.content ?? '';
    const currentUserAvatar = document.querySelector('meta[name="user-avatar-url"]')?.content ?? '';

    initNavbar();
    initBackToTop();
    initMobileBottomNav();
    initSidebarNav();
    initMediaCarousel();

    initFeedTabs();
    initProfileTabs();

    initFollowModal();
    initFollowButton();

    initSearch();
    initClubFilters();

    initComposer({ feedPanel, csrfToken, currentUserName, currentUserAvatar });

    bindPostActions(document, csrfToken);
    bindMediaGalleries(document);
});
