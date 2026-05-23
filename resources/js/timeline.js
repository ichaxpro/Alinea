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

    // ── Profile tabs (Posts / Replies / Highlights / Media) ──

    const profileTabBtns = document.querySelectorAll('[data-profile-tab]');
    if (profileTabBtns.length) {
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

            const bodyText = composerBody.value;
            const activeTagBtn = document.querySelector('[data-composer-tag].bg-\\[\\#FFDDAF\\]');
            const activeTag = activeTagBtn ? activeTagBtn.textContent.trim() : 'Post';

            const klubSelect = document.getElementById('composer-klub');
            let klubName = '';
            if (klubSelect && klubSelect.selectedIndex > 0) {
                klubName = klubSelect.options[klubSelect.selectedIndex].text;
            }

            const titleInput = document.getElementById('composer-title');
            const titleVal = titleInput ? titleInput.value.trim() : '';

            const postId = 'new-' + Date.now();

            const newPostHTML = `
            <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-5 hover:bg-gray-50 transition-colors animate-fade-in-down" data-post-klub="${klubName}">
                <div class="flex items-center gap-3 mb-3 justify-between">
                    <div class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0" style="background: linear-gradient(135deg, #FFDDAF, #C7E7FF)"></div>
                    <div class="flex-1">
                        <span class="font-bold text-[15px] leading-tight">Pengguna Saya</span>
                        <div class="flex items-center gap-1.5 text-xs text-gray-400">
                            <span>@pengguna_saya</span>
                            <span class="text-gray-200">•</span>
                            <span class="flex items-center gap-1">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                </svg>
                                Jakarta
                            </span>
                            <span class="text-gray-200">•</span>
                            <span>Baru Saja</span>
                        </div>
                    </div>
                    <div class="bg-[#fff176] border-2 inline-flex items-center rounded-full border-[#444] px-3.5 py-0.5 text-xs font-bold">${activeTag}</div>
                </div>

                <div class="flex flex-wrap gap-2 mb-3">
                    ${titleVal ? `<div class="inline-flex items-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold">📖 ${titleVal}</div>` : ''}
                    ${klubName ? `<div class="inline-flex items-center bg-[#C7E7FF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold text-[#444]">👥 ${klubName}</div>` : ''}
                </div>
                
                <p class="text-sm text-gray-600 leading-relaxed mb-4">${bodyText}</p>

                <div class="flex items-center gap-5 pt-3 border-t border-gray-100">
                    <button class="flex items-center gap-1.5 text-gray-400 text-[13px] font-medium hover:text-[#444] transition-colors cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        <span>0</span>
                    </button>
                    <button id="like-btn-${postId}" data-like-btn data-base="0" data-liked="false" aria-pressed="false" class="flex items-center gap-1.5 text-[13px] font-medium text-gray-400 hover:text-red-400 transition-colors cursor-pointer">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span data-like-count>0</span>
                    </button>
                    <div class="ml-auto flex items-center gap-2">
                        <button id="bookmark-btn-${postId}" data-bookmark-btn aria-pressed="false" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
                        </button>
                        <button id="share-btn-${postId}" data-share-btn class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                        </button>
                    </div>
                </div>
            </article>
            `;

            const feedPanel = document.getElementById('feed-panel') || document.querySelector('[role="tabpanel"]');
            if (feedPanel) {
                // Apply active filters to the new post
                const activeFilters = Array.from(document.querySelectorAll('[data-klub-filter].bg-\\[\\#FFDDAF\\]'))
                                           .map(b => b.dataset.klubFilter);
                
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = newPostHTML.trim();
                const newPostEl = tempDiv.firstChild;
                
                if (activeFilters.length > 0 && !activeFilters.includes(klubName)) {
                    newPostEl.style.display = 'none';
                }
                
                feedPanel.prepend(newPostEl);
                
                const newLikeBtn = document.getElementById(`like-btn-${postId}`);
                if (newLikeBtn) {
                    newLikeBtn.addEventListener('click', () => {
                        const liked = newLikeBtn.dataset.liked === 'true';
                        const heart = newLikeBtn.querySelector('path');
                        const count = newLikeBtn.querySelector('[data-like-count]');
                        newLikeBtn.dataset.liked = liked ? 'false' : 'true';
                        newLikeBtn.setAttribute('aria-pressed', newLikeBtn.dataset.liked);
                        newLikeBtn.classList.toggle('text-red-500', !liked);
                        newLikeBtn.classList.toggle('text-gray-400', liked);
                        if (heart) heart.setAttribute('fill', liked ? 'none' : 'currentColor');
                        if (count) count.textContent = formatCount(parseInt(newLikeBtn.dataset.base) + (liked ? 0 : 1));
                    });
                }

                const newBookmarkBtn = document.getElementById(`bookmark-btn-${postId}`);
                if (newBookmarkBtn) {
                    newBookmarkBtn.addEventListener('click', () => {
                        const active = newBookmarkBtn.getAttribute('aria-pressed') === 'true';
                        newBookmarkBtn.setAttribute('aria-pressed', !active);
                        newBookmarkBtn.classList.toggle('text-[#444]', !active);
                        newBookmarkBtn.classList.toggle('text-gray-400', active);
                        const path = newBookmarkBtn.querySelector('path');
                        if (path) path.setAttribute('fill', active ? 'none' : 'currentColor');
                    });
                }
            }

            showToast('Postingan berhasil dikirim!');
            
            if (titleInput) titleInput.value = '';
            if (klubSelect) klubSelect.selectedIndex = 0;
            
            composerBody.value = '';
            composerBody.style.height = 'auto';
            if (charCounter) { 
                charCounter.textContent = '0/' + MAX_CHARS; 
                charCounter.classList.remove('text-red-500'); 
                charCounter.classList.add('text-gray-300'); 
            }
            kirimBtn.disabled = true;
            kirimBtn.classList.add('opacity-40', 'cursor-not-allowed');
            kirimBtn.classList.remove('cursor-pointer');
        });
    }

    // ── Left sidebar nav highlight ──

    const navBtns = document.querySelectorAll('[data-sidenav]');
    navBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Navigate to timeline_home if Beranda is clicked
            if (btn.id === 'sidenav-beranda') {
                window.location.href = '/timeline_home';
                return;
            }else if (btn.id === 'sidenav-profil') {
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

    // ── Media carousel scroll buttons ──

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

    // ── Club filters (timeline_komunitas) ──
    const clubFilters = document.querySelectorAll('[data-klub-filter]');
    if (clubFilters.length) {
        clubFilters.forEach(btn => {
            btn.addEventListener('click', () => {
                const isActive = btn.classList.contains('bg-[#FFDDAF]');
                if (isActive) {
                    btn.classList.remove('bg-[#FFDDAF]');
                    btn.classList.add('bg-white');
                } else {
                    btn.classList.add('bg-[#FFDDAF]');
                    btn.classList.remove('bg-white');
                }

                const activeFilters = Array.from(document.querySelectorAll('[data-klub-filter].bg-\\[\\#FFDDAF\\]'))
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
    }
});
