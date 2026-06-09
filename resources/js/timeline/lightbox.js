let timelineGallery = [];
let timelineGalleryIndex = 0;

const lightboxHtml = `
<div id="timelineLightbox" class="fixed inset-0 z-[100] flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-200" style="background: rgba(0,0,0,0.85); backdrop-filter: blur(6px)">
    <button id="timelineLightboxClose" class="absolute top-4 right-4 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition text-white" title="Tutup">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="18" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
    <button id="timelineLightboxPrev" class="absolute top-1/2 left-4 -translate-y-1/2 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition text-white hidden">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <button id="timelineLightboxNext" class="absolute top-1/2 right-4 -translate-y-1/2 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition text-white hidden">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>
    <div id="timelineLightboxContent" class="relative max-w-5xl max-h-[90vh] w-full flex items-center justify-center transition-transform duration-200 scale-95"></div>
    <p id="timelineLightboxCounter" class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/60 text-sm font-semibold tracking-wide"></p>
</div>`;

if (!document.getElementById('timelineLightbox')) {
    document.body.insertAdjacentHTML('beforeend', lightboxHtml);
}

const lightbox = document.getElementById('timelineLightbox');
const lightboxClose = document.getElementById('timelineLightboxClose');
const lightboxPrev = document.getElementById('timelineLightboxPrev');
const lightboxNext = document.getElementById('timelineLightboxNext');
const lightboxContent = document.getElementById('timelineLightboxContent');
const lightboxCounter = document.getElementById('timelineLightboxCounter');

function openLightbox(gallery, startIndex) {
    timelineGallery = gallery;
    timelineGalleryIndex = startIndex;

    lightbox.classList.remove('hidden');
    void lightbox.offsetWidth;
    lightbox.classList.remove('opacity-0');
    lightboxContent.classList.remove('scale-95');
    lightboxContent.classList.add('scale-100');
    document.body.style.overflow = 'hidden';

    updateLightbox();
}

function closeLightbox() {
    lightbox.classList.add('opacity-0');
    lightboxContent.classList.remove('scale-100');
    lightboxContent.classList.add('scale-95');
    document.body.style.overflow = '';
    setTimeout(() => {
        lightbox.classList.add('hidden');
        lightboxContent.innerHTML = '';
    }, 200);
}

function updateLightbox() {
    if (timelineGallery.length === 0) return;
    const item = timelineGallery[timelineGalleryIndex];

    if (item.type === 'video') {
        lightboxContent.innerHTML = `<video src="${item.url}" controls autoplay class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain bg-black"></video>`;
    } else {
        lightboxContent.innerHTML = `<img src="${item.url}" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain" />`;
    }

    if (timelineGallery.length > 1) {
        lightboxPrev.classList.remove('hidden');
        lightboxNext.classList.remove('hidden');
        lightboxCounter.textContent = `${timelineGalleryIndex + 1} / ${timelineGallery.length}`;
    } else {
        lightboxPrev.classList.add('hidden');
        lightboxNext.classList.add('hidden');
        lightboxCounter.textContent = '';
    }
}

lightboxClose?.addEventListener('click', closeLightbox);
lightboxPrev?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (timelineGalleryIndex > 0) {
        timelineGalleryIndex--;
    } else {
        timelineGalleryIndex = timelineGallery.length - 1;
    }
    updateLightbox();
});
lightboxNext?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (timelineGalleryIndex < timelineGallery.length - 1) {
        timelineGalleryIndex++;
    } else {
        timelineGalleryIndex = 0;
    }
    updateLightbox();
});
lightbox?.addEventListener('click', (e) => {
    if (e.target === lightbox || e.target === lightboxContent) closeLightbox();
});

document.addEventListener('keydown', (e) => {
    if (lightbox.classList.contains('hidden')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') lightboxPrev.click();
    if (e.key === 'ArrowRight') lightboxNext.click();
});

document.addEventListener('click', (e) => {
    const mediaEl = e.target.closest('[data-media-url]');
    if (mediaEl) {
        e.preventDefault();
        const article = mediaEl.closest('article[data-post-id]') || mediaEl.closest('.grid') || mediaEl.closest('div');
        if (!article) return;

        let siblings = Array.from(article.querySelectorAll('[data-media-url]'));
        if (siblings.length === 0) siblings = [mediaEl];

        const gallery = siblings.map(el => ({
            url: el.dataset.mediaUrl,
            type: el.dataset.mediaType
        }));

        const startIndex = siblings.indexOf(mediaEl) > -1 ? siblings.indexOf(mediaEl) : 0;
        openLightbox(gallery, startIndex);
    }
});
