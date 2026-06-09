document.addEventListener('click', (e) => {
    const article = e.target.closest('article[data-href]');
    if (!article) return;

    if (e.target.closest('a') || e.target.closest('button') ||
        e.target.closest('input') || e.target.closest('textarea') ||
        e.target.closest('form') || e.target.closest('video') ||
        e.target.closest('img') || e.target.closest('select')) return;

    window.location.href = article.dataset.href;
});
