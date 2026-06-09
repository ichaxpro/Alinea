export function initClubFilters() {
    const clubFilters = document.querySelectorAll('[data-klub-filter]');
    if (!clubFilters.length) return;

    const params = new URLSearchParams(window.location.search);
    const initialKlub = params.get('klub_filter');

    let matchedFilterBtn = null;

    clubFilters.forEach(btn => {
        if (initialKlub && btn.dataset.klubFilter === initialKlub) {
            matchedFilterBtn = btn;
        }

        btn.addEventListener('click', () => {
            const isActive = btn.classList.contains('bg-[#FFDDAF]');
            if (isActive) {
                btn.classList.remove('bg-[#FFDDAF]');
                btn.classList.add('bg-white');
            } else {
                btn.classList.add('bg-[#FFDDAF]');
                btn.classList.remove('bg-white');
            }

            const activeFilters = Array.from(document.querySelectorAll('[data-klub-filter]'))
                                       .filter(b => b.classList.contains('bg-[#FFDDAF]'))
                                       .map(b => b.dataset.klubFilter);

            const posts = document.querySelectorAll('article[data-post-klub]');
            posts.forEach(post => {
                const postKlub = post.dataset.postKlub;
                if (activeFilters.length === 0 || activeFilters.includes(postKlub)) {
                    post.style.display = 'block';
                } else {
                    post.style.display = 'none';
                }
            });
        });
    });

    if (matchedFilterBtn) {
        matchedFilterBtn.click();
    }
}
