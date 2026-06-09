document.addEventListener('click', (e) => {
    const postMenuTrigger = e.target.closest('[data-post-menu-trigger]');
    const postMenuDropdown = e.target.closest('[data-post-menu-dropdown]');
    const shareTrigger = e.target.closest('[data-share-btn]');
    const shareDropdown = e.target.closest('[data-share-dropdown]');

    let targetMenu = null;
    if (postMenuTrigger) {
        targetMenu = postMenuTrigger.nextElementSibling;
    } else if (shareTrigger) {
        targetMenu = shareTrigger.nextElementSibling;
    }

    document.querySelectorAll('[data-post-menu-dropdown], [data-share-dropdown]').forEach(d => {
        if (d !== postMenuDropdown && d !== shareDropdown && d !== targetMenu) {
            d.classList.add('hidden');
        }
    });

    if (postMenuTrigger && targetMenu && targetMenu.hasAttribute('data-post-menu-dropdown')) {
        targetMenu.classList.toggle('hidden');
    }

    if (shareTrigger && targetMenu && targetMenu.hasAttribute('data-share-dropdown')) {
        targetMenu.classList.toggle('hidden');
    }
});
