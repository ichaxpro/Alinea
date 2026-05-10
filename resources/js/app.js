import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const closeBtn = document.getElementById('close-mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.remove('hidden-menu');
            mobileMenu.classList.add('visible-menu');
        });
    }

    window.closeMobileMenu = () => {
        mobileMenu.classList.remove('visible-menu');
        mobileMenu.classList.add('hidden-menu');
    };

    closeBtn?.addEventListener('click', closeMobileMenu);
});