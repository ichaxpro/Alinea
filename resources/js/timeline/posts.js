import { escapeHtml, formatCount, renderAvatarHtml } from './helpers.js';
import { showToast } from './toast.js';

export function buildAttachmentGallery(attachments, options = {}) {
    const items = Array.isArray(attachments) ? attachments.filter(Boolean) : [];
    const imageItems = items.filter(item => item.type === 'image');
    const otherItems = items.filter(item => item.type !== 'image');

    const imageSlides = imageItems.map((item, index) => {
        return '<img src="' + item.url + '" data-media-url="' + item.url + '" data-media-type="image" class="w-full h-40 object-cover rounded-xl border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity" alt="Attachment" />';
    }).join('');

    const nonImageHtml = otherItems.map(item => {
        if (item.type === 'video') {
            return '<video src="' + item.url + '" data-media-url="' + item.url + '" data-media-type="video" class="w-full h-40 object-cover rounded-xl border border-gray-200 cursor-pointer hover:opacity-90 transition-opacity" controls></video>';
        }

        return '<div class="mt-3 text-sm"><a href="' + item.url + '" class="underline">' + escapeHtml(item.original_name || 'Unduh file') + '</a></div>';
    }).join('');

    if (!imageItems.length && !otherItems.length) return '';

    return '<div class="grid grid-cols-2 max-sm:grid-cols-1 gap-2 mb-4">' + imageSlides + nonImageHtml + '</div>';
}

export function bindMediaGalleries(scope) {
    scope.querySelectorAll('[data-media-gallery]').forEach(gallery => {
        if (gallery.dataset.bound === 'true') return;
        gallery.dataset.bound = 'true';

        const track = gallery.querySelector('[data-media-track]');
        const counter = gallery.querySelector('[data-media-counter]');
        const prevBtn = gallery.querySelector('[data-gallery-prev]');
        const nextBtn = gallery.querySelector('[data-gallery-next]');
        const count = parseInt(gallery.dataset.mediaGalleryCount || '1', 10);

        if (!track) return;

        const updateCounter = () => {
            if (!counter) return;
            const slideWidth = track.clientWidth || 1;
            const index = Math.min(count, Math.max(1, Math.round(track.scrollLeft / slideWidth) + 1));
            counter.textContent = index + '/' + count;
        };

        prevBtn?.addEventListener('click', () => {
            track.scrollBy({ left: -(track.clientWidth || 1), behavior: 'smooth' });
        });

        nextBtn?.addEventListener('click', () => {
            track.scrollBy({ left: (track.clientWidth || 1), behavior: 'smooth' });
        });

        track.addEventListener('scroll', () => window.requestAnimationFrame(updateCounter), { passive: true });
        updateCounter();
    });
}

export function renderCommentPanel(post, currentUserAvatar, currentUserName) {
    let commentsUrl = '/timeline_home/posts/' + encodeURIComponent(post.id) + '/comments';
    if (location.pathname.includes('timeline_komunitas') && post.klub) {
        commentsUrl = '/timeline_komunitas/posts/' + encodeURIComponent(post.id) + '/comments';
    }
    const storeUrl = commentsUrl;
    const authenticated = document.querySelector('meta[name="user-auth"]')?.content === 'true';

    return '<div data-comments-panel class="hidden mt-4 pt-4 border-t border-gray-100" data-comments-loaded="false" data-comments-limit="5" data-comments-url="' + commentsUrl + '" data-comments-store-url="' + storeUrl + '">' +
        '<div class="flex items-center justify-between mb-3">' +
            '<h4 class="text-sm font-bold text-[#444]">Komentar</h4>' +
            '<span class="text-xs text-gray-400">Klik ikon komentar untuk membuka</span>' +
        '</div>' +
        '<div data-comment-list class="space-y-3 mb-4"></div>' +
        (authenticated
            ? '<form data-comment-form class="flex gap-3 items-start">' +
                '<div class="w-9 h-9 rounded-full border border-[#444] overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center flex-shrink-0">' +
                    renderAvatarHtml({ avatar_url: currentUserAvatar, name: currentUserName || 'U' }) +
                '</div>' +
                '<div class="flex-1 min-w-0">' +
                    '<textarea data-comment-input rows="1" maxlength="500" placeholder="Tulis komentar..." class="w-full border-[1.5px] border-gray-200 rounded-xl px-3 py-2 text-sm placeholder-gray-300 outline-none focus:border-[#444] resize-none transition-colors overflow-hidden"></textarea>' +
                    '<input type="file" data-comment-media-input class="hidden" accept="image/*,video/*,*/*" multiple />' +
                    '<div class="flex flex-wrap items-center justify-between gap-2 mt-2">' +
                        '<div class="flex items-center gap-2">' +
                            '<button type="button" data-comment-media-trigger="image" aria-label="Unggah gambar komentar" title="Unggah gambar" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>' +
                            '</button>' +
                            '<button type="button" data-comment-media-trigger="video" aria-label="Unggah video komentar" title="Unggah video" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>' +
                            '</button>' +
                            '<button type="button" data-comment-media-trigger="file" aria-label="Lampirkan file komentar" title="Lampirkan file" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>' +
                            '</button>' +
                            '<span data-comment-media-label class="text-xs text-gray-400"></span>' +
                        '</div>' +
                        '<button type="submit" data-comment-submit class="w-9 h-9 bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full flex items-center justify-center hover:bg-[#ffcf90] transition-colors cursor-pointer shrink-0" title="Kirim">' +
                            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12L2 22l3-10L2 2z"></path><line x1="5" y1="12" x2="14" y2="12"></line></svg>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</form>'
            : '<div class="text-sm text-gray-400">Silakan login untuk menulis komentar.</div>') +
    '</div>';
}

export function createPostElement(post, currentUserAvatar, currentUserName) {
    if (!post) return null;

    const article = document.createElement('article');
    article.className = 'bg-white border-[1.5px] border-[#444] rounded-2xl p-5 hover:bg-gray-50 transition-colors animate-fade-in-down';
    article.dataset.postKlub = post.klub || '';
    if (post.id) {
        article.dataset.postId = String(post.id);
    }
    const postUrl = post.post_url || `/timeline/posts/${post.id}`;
    article.dataset.href = postUrl;

    const bookTag = post.book
        ? '<div class="inline-flex items-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold">' + escapeHtml(post.book) + '</div>'
        : '';

    const klubTag = post.klub
        ? '<div class="inline-flex items-center bg-[#C7E7FF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold text-[#444]">👥 ' + escapeHtml(post.klub) + '</div>'
        : '';

    const tagsHtml = (bookTag || klubTag)
        ? '<div class="flex flex-wrap gap-2 mb-3">' + bookTag + klubTag + '</div>'
        : '';

    const attachments = Array.isArray(post.attachments) && post.attachments.length
        ? post.attachments
        : (post.media_url ? [{ url: post.media_url, type: post.media_type, original_name: post.media_original_name }] : []);

    const mediaHtml = buildAttachmentGallery(attachments);

    article.innerHTML =
        '<div class="flex items-center gap-3 mb-3 justify-between">' +
            '<a href="' + (post.profile_url || '#') + '" class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0 overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center cursor-pointer hover:opacity-80 transition-opacity">' + renderAvatarHtml(post) + '</a>' +
            '<div class="flex-1">' +
                '<a href="' + (post.profile_url || '#') + '" class="font-bold text-[15px] leading-tight hover:underline cursor-pointer">' + escapeHtml(post.name || 'Pengguna') + '</a>' +
                '<div class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400">' +
                    '<a href="' + (post.profile_url || '#') + '" class="whitespace-nowrap hover:underline cursor-pointer">' + escapeHtml(post.handle || '@pengguna') + '</a>' +
                    '<span class="text-gray-200">•</span>' +
                    '<span class="flex items-center gap-1 whitespace-nowrap">' +
                        '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
                            '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>' +
                        '</svg>' +
                        escapeHtml(post.location || 'Online') +
                    '</span>' +
                    '<span class="text-gray-200">•</span>' +
                    '<span class="whitespace-nowrap" title="' + escapeHtml(post.absolute_time || '') + '">' + escapeHtml(post.time || 'Baru saja') + '</span>' +
                '</div>' +
            '</div>' +
            '<div class="flex items-center gap-2">' +
                '<div class="bg-[#fff176] border-2 inline-flex items-center rounded-full border-text px-3.5 py-0.5 text-xs font-bold">' + escapeHtml(post.tag || 'Post') + '</div>' +
                '<div class="relative">' +
                    '<button class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors" data-post-menu-trigger>' +
                        '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/><circle cx="12" cy="5" r="1"/></svg>' +
                    '</button>' +
                    '<div class="absolute right-0 top-full mt-1 w-48 bg-white border-[1.5px] border-[#444] rounded-xl overflow-hidden hidden z-[60]" data-post-menu-dropdown>' +
                        '<button class="w-full px-4 py-2.5 text-left text-sm text-red-500 font-semibold hover:bg-red-50 flex items-center gap-2 transition-colors" data-post-delete>' +
                            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>' +
                            'Hapus Unggahan' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>' +

        tagsHtml +

        '<p class="text-sm max-sm:text-xs text-gray-600 leading-relaxed mb-4">' + escapeHtml(post.body || '') + '</p>' +
        mediaHtml +
        '<div class="flex items-center gap-5 pt-3 border-t border-gray-100">' +
            '<button id="comment-btn-' + post.id + '" data-comment-toggle aria-label="Komentar" class="flex items-center gap-1.5 text-gray-400 text-[13px] font-medium hover:text-[#444] transition-colors cursor-pointer">' +
                '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>' +
                '<span data-comment-count>' + escapeHtml(String(post.comments ?? '0')) + '</span>' +
            '</button>' +
            '<button id="like-btn-' + post.id + '" data-like-btn data-base="' + (post.likes_base ?? 0) + '" data-liked="' + (post.liked ? 'true' : 'false') + '" aria-pressed="' + (post.liked ? 'true' : 'false') + '" aria-label="Suka" class="flex items-center gap-1.5 text-[13px] font-medium transition-colors cursor-pointer ' + (post.liked ? 'text-red-500' : 'text-gray-400 hover:text-red-400') + '">' +
                '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>' +
                '<span data-like-count>' + escapeHtml(String(post.likes_label ?? '0')) + '</span>' +
            '</button>' +
            '<div class="ml-auto flex items-center gap-2">' +
                '<button id="bookmark-btn-' + post.id + '" data-bookmark-btn aria-pressed="' + (post.bookmarked ? 'true' : 'false') + '" aria-label="Simpan" class="w-8 h-8 flex items-center justify-center rounded-full transition-colors cursor-pointer ' + (post.bookmarked ? 'text-yellow-500' : 'text-gray-400 hover:text-yellow-500') + '">' +
                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="' + (post.bookmarked ? 'currentColor' : 'none') + '" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>' +
                '</button>' +
                '<button id="share-btn-' + post.id + '" data-share-btn aria-label="Bagikan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">' +
                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>' +
                '</button>' +
            '</div>' +
        '</div>';

    article.innerHTML += renderCommentPanel(post, currentUserAvatar, currentUserName);

    return article;
}

export function bindPostActions(scope, csrfToken) {
    scope.querySelectorAll('[data-comment-toggle]').forEach(btn => {
        if (btn.dataset.bound === 'true') return;
        btn.dataset.bound = 'true';
        btn.addEventListener('click', async (e) => {
            const article = btn.closest('article[data-post-id]');
            const panel = article?.querySelector('[data-comments-panel]');
            if (!panel) return;

            const willOpen = panel.classList.contains('hidden');
            panel.classList.toggle('hidden');

            if (willOpen) {
                const { loadComments } = await import('./comments.js');
                await loadComments(panel);
                // Only focus if it's a real user click, not a simulated one on page load
                if (e.isTrusted) {
                    panel.querySelector('[data-comment-input]')?.focus({ preventScroll: true });
                }
            }
        });
    });

    scope.querySelectorAll('[data-like-btn]').forEach(btn => {
        if (btn.dataset.bound === 'true') return;
        btn.dataset.bound = 'true';
        btn.addEventListener('click', async () => {
            const liked = btn.dataset.liked === 'true';
            const heart = btn.querySelector('path');
            const count = btn.querySelector('[data-like-count]');
            const article = btn.closest('article[data-post-id]');
            const postId = article?.dataset.postId;

            if (!postId) return;

            btn.dataset.liked = liked ? 'false' : 'true';
            btn.setAttribute('aria-pressed', btn.dataset.liked);
            btn.classList.toggle('text-red-500', !liked);
            btn.classList.toggle('text-gray-400', liked);
            if (heart) heart.setAttribute('fill', liked ? 'none' : 'currentColor');
            if (count) count.textContent = formatCount(parseInt(btn.dataset.base) + (liked ? -1 : 1));

            try {
                let likeUrl = `/timeline_home/posts/${postId}/like`;

                const res = await fetch(likeUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });

                if (!res.ok) throw new Error();
                const data = await res.json();

                btn.dataset.liked = data.liked ? 'true' : 'false';
                btn.setAttribute('aria-pressed', btn.dataset.liked);
                btn.classList.toggle('text-red-500', data.liked);
                btn.classList.toggle('text-gray-400', !data.liked);
                if (heart) heart.setAttribute('fill', data.liked ? 'currentColor' : 'none');
                if (count) count.textContent = formatCount(data.likes_base);
                btn.dataset.base = data.likes_base;

            } catch (e) {
                btn.dataset.liked = liked ? 'true' : 'false';
                btn.setAttribute('aria-pressed', btn.dataset.liked);
                btn.classList.toggle('text-red-500', liked);
                btn.classList.toggle('text-gray-400', !liked);
                if (heart) heart.setAttribute('fill', liked ? 'currentColor' : 'none');
                if (count) count.textContent = formatCount(btn.dataset.base);
                showToast('Gagal menyukai unggahan.');
            }
        });
    });

    scope.querySelectorAll('[data-bookmark-btn]').forEach(btn => {
        if (btn.dataset.bound === 'true') return;
        btn.dataset.bound = 'true';
        btn.addEventListener('click', async () => {
            const article = btn.closest('article[data-post-id]');
            const postId = article?.dataset.postId;
            if (!postId) return;

            const active = btn.getAttribute('aria-pressed') === 'true';

            btn.setAttribute('aria-pressed', !active);
            btn.classList.toggle('text-yellow-500', !active);
            btn.classList.toggle('text-gray-400', active);
            const path = btn.querySelector('path');
            if (path) path.setAttribute('fill', !active ? 'currentColor' : 'none');

            try {
                const res = await fetch(`/timeline/posts/${postId}/bookmark`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });

                if (!res.ok) throw new Error();
                const data = await res.json();

                btn.setAttribute('aria-pressed', data.bookmarked);
                btn.classList.toggle('text-yellow-500', data.bookmarked);
                btn.classList.toggle('text-gray-400', !data.bookmarked);
                if (path) path.setAttribute('fill', data.bookmarked ? 'currentColor' : 'none');
            } catch (e) {
                btn.setAttribute('aria-pressed', active);
                btn.classList.toggle('text-yellow-500', active);
                btn.classList.toggle('text-gray-400', !active);
                if (path) path.setAttribute('fill', active ? 'currentColor' : 'none');
                showToast('Gagal menyimpan postingan.');
            }
        });
    });

    scope.querySelectorAll('[data-comment-form]').forEach(form => {
        if (form.dataset.bound === 'true') return;
        form.dataset.bound = 'true';
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const { submitComment } = await import('./comments.js');
            await submitComment(form, csrfToken);
        });
    });

    scope.querySelectorAll('[data-comment-input]').forEach(input => {
        if (input.dataset.bound === 'true') return;
        input.dataset.bound = 'true';
        input.addEventListener('input', () => {
            input.style.height = 'auto';
            const borderHeight = input.offsetHeight - input.clientHeight;
            input.style.height = (input.scrollHeight + borderHeight) + 'px';
        });
    });

    scope.querySelectorAll('[data-comment-media-input]').forEach(input => {
        if (input.dataset.bound === 'true') return;
        input.dataset.bound = 'true';

        input.addEventListener('change', () => {
            const label = input.closest('form')?.querySelector('[data-comment-media-label]');
            if (label) {
                const files = input.files ? Array.from(input.files) : [];
                label.textContent = files.length > 0 ? (files.length + ' file terpilih') : '';
            }
        });
    });

    scope.querySelectorAll('[data-comment-media-trigger]').forEach(btn => {
        if (btn.dataset.bound === 'true') return;
        btn.dataset.bound = 'true';

        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            const input = form?.querySelector('[data-comment-media-input]');
            if (!input) return;

            const mode = btn.dataset.commentMediaTrigger || 'file';
            if (mode === 'image') {
                input.accept = 'image/*';
            } else if (mode === 'video') {
                input.accept = 'video/*';
            } else {
                input.accept = '*/*';
            }

            input.click();
        });
    });

    scope.querySelectorAll('[data-comment-love]').forEach(btn => {
        if (btn.dataset.bound === 'true') return;
        btn.dataset.bound = 'true';

        btn.addEventListener('click', async () => {
            const commentId = btn.dataset.commentId;
            if (!commentId) return;

            const liked = btn.getAttribute('aria-pressed') === 'true';
            const label = btn.querySelector('[data-comment-love-label]');
            const path = btn.querySelector('path');

            btn.setAttribute('aria-pressed', liked ? 'false' : 'true');
            btn.classList.toggle('text-red-500', !liked);
            btn.classList.toggle('text-gray-400', liked);
            if (path) path.setAttribute('fill', liked ? 'none' : 'currentColor');

            let base = parseInt(btn.dataset.base || '0', 10);
            if (label) {
                label.textContent = formatCount(base + (liked ? -1 : 1));
            }

            try {
                let likeUrl = `/timeline_home/comments/${commentId}/like`;

                const res = await fetch(likeUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });

                if (!res.ok) throw new Error();
                const data = await res.json();

                btn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
                btn.classList.toggle('text-red-500', data.liked);
                btn.classList.toggle('text-gray-400', !data.liked);
                if (path) path.setAttribute('fill', data.liked ? 'currentColor' : 'none');
                if (label) label.textContent = data.likes_label;
                btn.dataset.base = data.likes_base;

            } catch (e) {
                btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
                btn.classList.toggle('text-red-500', liked);
                btn.classList.toggle('text-gray-400', !liked);
                if (path) path.setAttribute('fill', liked ? 'currentColor' : 'none');
                if (label) label.textContent = formatCount(base);
                showToast('Gagal menyukai komentar.');
            }
        });
    });
}
