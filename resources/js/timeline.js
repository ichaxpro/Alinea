/**
 * timeline.js — Alinea Timeline Page Interactivity
 *
 * Handles: navbar visibility, back-to-top button, tab switching,
 * composer tags, post actions (like/bookmark/share), sidebar nav,
 * and toast notifications.
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Navbar: visible only when scroll position is exactly 0 ──

    const navbar = document.getElementById('main-navbar');
    if (navbar) {
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

    // ── Back-to-top button: appears after 300px scroll ──

    const topBtn = document.getElementById('back-to-top');
    if (topBtn) {
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

    // ── Feed tab switcher (For You / Following) ──

    const tabBtns = document.querySelectorAll('[data-tab-btn]');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => {
                b.classList.remove('bg-[#FFDDAF]', 'text-[#444]', 'font-bold');
                b.classList.add('text-gray-400');
                b.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('bg-[#FFDDAF]', 'text-[#444]', 'font-bold');
            btn.classList.remove('text-gray-400');
            btn.setAttribute('aria-selected', 'true');
        });
    });

    // ── Composer category pill selector ──

    const tags = document.querySelectorAll('[data-composer-tag]');
    tags.forEach(tag => {
        tag.addEventListener('click', () => {
            tags.forEach(t => {
                t.classList.remove('border-[#444]', 'bg-[#FFDDAF]', 'text-[#444]');
                t.classList.add('border-gray-300', 'text-gray-500');
            });
            tag.classList.add('border-[#444]', 'bg-[#FFDDAF]', 'text-[#444]');
            tag.classList.remove('border-gray-300', 'text-gray-500');
        });
    });

    // ── Like toggle ──

    document.querySelectorAll('[data-like-btn]').forEach(btn => {
        btn.addEventListener('click', () => {
            const liked = btn.dataset.liked === 'true';
            const heart = btn.querySelector('path');
            const count = btn.querySelector('[data-like-count]');

            btn.dataset.liked = liked ? 'false' : 'true';
            btn.setAttribute('aria-pressed', btn.dataset.liked);
            btn.classList.toggle('text-red-500', !liked);
            btn.classList.toggle('text-gray-400', liked);

            if (heart) heart.setAttribute('fill', liked ? 'none' : 'currentColor');
            if (count) count.textContent = formatCount(parseInt(btn.dataset.base) + (liked ? 0 : 1));
        });
    });

    // ── Bookmark toggle ──

    document.querySelectorAll('[data-bookmark-btn]').forEach(btn => {
        btn.addEventListener('click', () => {
            const active = btn.getAttribute('aria-pressed') === 'true';
            btn.setAttribute('aria-pressed', !active);
            btn.classList.toggle('text-[#444]', !active);
            btn.classList.toggle('text-gray-400', active);
            const path = btn.querySelector('path');
            if (path) path.setAttribute('fill', active ? 'none' : 'currentColor');
        });
    });

    // ── Share ──

    document.querySelectorAll('[data-share-btn]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (navigator.share) {
                navigator.share({ title: 'Alinea', url: location.href });
            } else {
                navigator.clipboard.writeText(location.href)
                    .then(() => showToast('Tautan disalin ke clipboard!'));
            }
        });
    });

    // ── Composer: auto-grow textarea + character counter + button state ──

    const composerBody = document.getElementById('composer-body');
    const charCounter = document.getElementById('char-counter');
    const kirimBtn = document.getElementById('kirim-btn');
    const MAX_CHARS = 250;

    if (composerBody) {
        const update = () => {
            // Auto-grow
            composerBody.style.height = 'auto';
            composerBody.style.height = composerBody.scrollHeight + 'px';

            // Character counter
            const len = composerBody.value.length;
            const over = len >= MAX_CHARS;
            if (charCounter) {
                charCounter.textContent = `${len}/${MAX_CHARS}`;
                charCounter.classList.toggle('text-red-500', over);
                charCounter.classList.toggle('text-gray-300', !over);
            }

            // Disable button when over limit or empty
            if (kirimBtn) {
                const disabled = over || !composerBody.value.trim();
                kirimBtn.disabled = disabled;
                kirimBtn.classList.toggle('opacity-40', disabled);
                kirimBtn.classList.toggle('cursor-not-allowed', disabled);
                kirimBtn.classList.toggle('cursor-pointer', !disabled);
            }
        };

        composerBody.addEventListener('input', update);
    }

    // ── Composer submit ──

    if (kirimBtn) {
        kirimBtn.addEventListener('click', () => {
            const len = composerBody?.value.length ?? 0;
            if (!composerBody?.value.trim() || len >= MAX_CHARS) return;

            showToast('Postingan berhasil dikirim!');
            const title = document.getElementById('composer-title');
            if (title) title.value = '';
            composerBody.value = '';
            composerBody.style.height = 'auto';
            if (charCounter) { charCounter.textContent = '0/' + MAX_CHARS; charCounter.classList.remove('text-red-500'); charCounter.classList.add('text-gray-300'); }
            kirimBtn.disabled = true;
            kirimBtn.classList.add('opacity-40', 'cursor-not-allowed');
            kirimBtn.classList.remove('cursor-pointer');
        });
    }

    // ── Left sidebar nav highlight ──

    const navBtns = document.querySelectorAll('[data-sidenav]');
    navBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            navBtns.forEach(b => {
                b.classList.remove('bg-[#FFDDAF]', 'text-[#444]', 'font-semibold');
                b.classList.add('text-gray-500');
            });
            btn.classList.add('bg-[#FFDDAF]', 'text-[#444]', 'font-semibold');
            btn.classList.remove('text-gray-500');
        });
    });

    // ── Helpers ──

    function formatCount(n) {
        if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
        if (n >= 1_000)     return (n / 1_000).toFixed(0) + 'K';
        return String(n);
    }

    let toastTimeout;
    function showToast(msg) {
        let el = document.getElementById('toast-msg');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toast-msg';
            el.className = 'fixed bottom-24 left-1/2 -translate-x-1/2 z-[9999] bg-[#444] text-white text-sm font-medium px-5 py-3 rounded-full transition-all duration-300 opacity-0 translate-y-2';
            document.body.appendChild(el);
        }
        el.textContent = msg;
        requestAnimationFrame(() => {
            el.classList.remove('opacity-0', 'translate-y-2');
            el.classList.add('opacity-100', 'translate-y-0');
        });
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            el.classList.add('opacity-0', 'translate-y-2');
            el.classList.remove('opacity-100', 'translate-y-0');
        }, 2500);
    }
});
