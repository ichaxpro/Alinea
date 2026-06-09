export function initFeedTabs() {
    const tabBtns = document.querySelectorAll('[data-tab-btn]');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const isFollowing = btn.id === 'tab-following';
            window.location.href = '?tab=' + (isFollowing ? 'mengikuti' : 'untukmu');
        });
    });
}

export function initProfileTabs() {
    const profileTabBtns = document.querySelectorAll('[data-profile-tab]');
    if (!profileTabBtns.length) return;

    const profilePanels = document.querySelectorAll('[data-profile-panel]');

    const setActiveProfileTab = (activeBtn) => {
        const activeTarget = activeBtn.dataset.profileTabTarget;

        profileTabBtns.forEach(btn => {
            const indicator = btn.querySelector('[data-profile-tab-indicator]');
            const isActive = btn === activeBtn;

            btn.classList.toggle('text-[#111]', isActive);
            btn.classList.toggle('text-gray-400', !isActive);
            btn.setAttribute('aria-selected', isActive ? 'true' : 'false');

            if (indicator) {
                indicator.classList.toggle('hidden', !isActive);
            }
        });

        profilePanels.forEach(panel => {
            panel.classList.toggle('hidden', panel.dataset.profilePanel !== activeTarget);
        });
    };

    const initiallyActive = Array.from(profileTabBtns).find(btn => btn.getAttribute('aria-selected') === 'true') ?? profileTabBtns[0];
    setActiveProfileTab(initiallyActive);

    profileTabBtns.forEach(btn => {
        btn.addEventListener('click', () => setActiveProfileTab(btn));
    });
}
