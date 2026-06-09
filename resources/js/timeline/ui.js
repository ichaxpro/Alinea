export function initNavbar() {
    const navbar = document.getElementById('main-navbar');
    if (!navbar) return;

    let ticking = false;
    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => {
            navbar.classList.toggle('-translate-y-full', window.scrollY !== 0);
            ticking = false;
        });
    }, { passive: true });
}

export function initBackToTop() {
    const topBtn = document.getElementById('back-to-top');
    if (!topBtn) return;

    const show = ['opacity-100', 'pointer-events-auto', 'translate-y-0'];
    const hide = ['opacity-0', 'pointer-events-none', 'translate-y-4'];

    window.addEventListener('scroll', () => {
        const visible = window.scrollY > 300;
        topBtn.classList.remove(...(visible ? hide : show));
        topBtn.classList.add(...(visible ? show : hide));
    }, { passive: true });

    topBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

export function initMobileBottomNav() {
    const bottomNav = document.getElementById('mobile-bottom-nav');
    if (!bottomNav) return;

    let lastScrollY = window.scrollY;
    let ticking2 = false;

    window.addEventListener('scroll', () => {
        if (ticking2) return;
        ticking2 = true;
        requestAnimationFrame(() => {
            const currentScrollY = window.scrollY;
            if (currentScrollY > lastScrollY && currentScrollY > 100) {
                bottomNav.classList.add('hidden-nav');
            } else {
                bottomNav.classList.remove('hidden-nav');
            }
            lastScrollY = currentScrollY;
            ticking2 = false;
        });
    }, { passive: true });
}

export function initSidebarNav() {
    const navBtns = document.querySelectorAll('[data-sidenav]');
    navBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.id === 'sidenav-beranda') {
                window.location.href = '/timeline_home';
                return;
            } else if (btn.id === 'sidenav-profil') {
                window.location.href = '/timeline_profile';
                return;
            }

            navBtns.forEach(b => {
                b.classList.remove('bg-[#FFDDAF]', 'text-[#444]', 'font-semibold');
                b.classList.add('text-gray-500');
            });
            btn.classList.add('bg-[#FFDDAF]', 'text-[#444]', 'font-semibold');
            btn.classList.remove('text-gray-500');
        });
    });
}

export function initMediaCarousel() {
    document.querySelectorAll('[data-carousel-next]').forEach(btn => {
        btn.addEventListener('click', () => {
            const mediaId = btn.dataset.carouselNext;
            const carousel = document.querySelector(`[data-carousel-scroll-${mediaId}]`);
            if (carousel) {
                carousel.scrollBy({ left: 300, behavior: 'smooth' });
            }
        });
    });

    document.querySelectorAll('[data-carousel-prev]').forEach(btn => {
        btn.addEventListener('click', () => {
            const mediaId = btn.dataset.carouselPrev;
            const carousel = document.querySelector(`[data-carousel-scroll-${mediaId}]`);
            if (carousel) {
                carousel.scrollBy({ left: -300, behavior: 'smooth' });
            }
        });
    });
}
