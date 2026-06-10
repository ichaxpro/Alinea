import { escapeHtml, formatCount, renderAvatarHtml } from './helpers.js';
import { showToast } from './toast.js';
import { buildAttachmentGallery, bindMediaGalleries, bindPostActions } from './posts.js';

export function renderCommentMediaHtml(comment) {
    const attachments = Array.isArray(comment?.attachments) && comment.attachments.length
        ? comment.attachments
        : (comment?.media_url ? [{ url: comment.media_url, type: comment.media_type, original_name: comment.media_original_name }] : []);

    if (!attachments.length) return '';

    return '<div class="mt-3">' + buildAttachmentGallery(attachments) + '</div>';
}

export function renderCommentActionsHtml(comment) {
    const liked = comment.liked === true;
    const count = comment.likes_label || '0';
    return '<div class="flex items-center gap-3 mt-3 pt-3 border-t border-gray-100">' +
        '<button type="button" data-comment-love data-comment-id="' + escapeHtml(comment.id) + '" data-base="' + escapeHtml(comment.likes_base || 0) + '" aria-pressed="' + (liked ? 'true' : 'false') + '" class="inline-flex items-center gap-1.5 text-xs font-medium ' + (liked ? 'text-red-500' : 'text-gray-400 hover:text-red-500') + ' transition-colors cursor-pointer">' +
            '<svg width="15" height="15" viewBox="0 0 24 24" fill="' + (liked ? 'currentColor' : 'none') + '" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>' +
            '<span data-comment-love-label>' + escapeHtml(count) + '</span>' +
        '</button>' +
    '</div>';
}

export function renderCommentItem(comment) {
    return '<div class="flex gap-3 rounded-xl bg-gray-50 p-3 border border-gray-100">' +
        '<a href="' + (comment.profile_url || '#') + '" class="w-8 h-8 rounded-full border border-[#444] overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center flex-shrink-0 cursor-pointer hover:opacity-80 transition-opacity">' +
            renderAvatarHtml(comment) +
        '</a>' +
        '<div class="min-w-0 flex-1">' +
            '<div class="flex flex-wrap items-center gap-2 text-xs text-gray-400 mb-1">' +
                '<a href="' + (comment.profile_url || '#') + '" class="font-semibold text-gray-700 whitespace-nowrap hover:underline cursor-pointer">' + escapeHtml(comment.name || 'Pengguna') + '</a>' +
                '<a href="' + (comment.profile_url || '#') + '" class="whitespace-nowrap hover:underline cursor-pointer">' + escapeHtml(comment.handle || '@pengguna') + '</a>' +
                '<span class="whitespace-nowrap">•</span>' +
                '<span class="whitespace-nowrap" title="' + escapeHtml(comment.absolute_time || '') + '">' + escapeHtml(comment.time || 'Baru saja') + '</span>' +
            '</div>' +
            '<p class="text-sm text-gray-600 leading-relaxed break-words">' + escapeHtml(comment.body || '') + '</p>' +
            renderCommentMediaHtml(comment) +
            renderCommentActionsHtml(comment) +
        '</div>' +
    '</div>';
}

export async function loadComments(panel) {
    if (!panel || panel.dataset.commentsLoaded === 'true') return;

    const list = panel.querySelector('[data-comment-list]');
    let url = panel.dataset.commentsUrl;
    if (!list || !url) return;

    const limit = panel.dataset.commentsLimit;
    if (limit) {
        url += (url.includes('?') ? '&' : '?') + 'limit=' + limit;
    }

    panel.dataset.commentsLoaded = 'loading';
    list.innerHTML = '<div class="text-sm text-gray-400">Memuat komentar...</div>';

    try {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) throw new Error('Failed to load comments');

        const result = await response.json();
        const comments = Array.isArray(result.comments) ? result.comments : [];
        const total = result.total ?? comments.length;

        let html = comments.length
            ? comments.map(renderCommentItem).join('')
            : '<div class="text-sm text-gray-400">Belum ada komentar. Jadilah yang pertama.</div>';

        if (limit && total > parseInt(limit, 10)) {
            const article = panel.closest('[data-post-id]');
            const postId = article?.dataset.postId;
            if (postId) {
                const detailUrl = window.location.origin + '/timeline/posts/' + postId;
                html += '<div class="mt-3 text-center">' +
                    '<a href="' + detailUrl + '" class="text-sm font-bold text-[#444] hover:underline transition-colors">' +
                        'Tampilkan semua komentar (' + total + ')' +
                    '</a>' +
                '</div>';
            }
        }

        list.innerHTML = html;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        bindPostActions(list, csrfToken);
        panel.dataset.commentsLoaded = 'true';
    } catch (error) {
        panel.dataset.commentsLoaded = 'false';
        list.innerHTML = '<div class="text-sm text-red-500">Gagal memuat komentar.</div>';
    }
}

export async function submitComment(form, csrfToken) {
    const panel = form.closest('[data-comments-panel]');
    const input = form.querySelector('[data-comment-input]');
    const submitBtn = form.querySelector('[data-comment-submit]');
    const mediaInput = form.querySelector('[data-comment-media-input]');
    const mediaLabel = form.querySelector('[data-comment-media-label]');
    const url = panel?.dataset.commentsStoreUrl;

    if (!panel || !input || !submitBtn || !url) return;

    const commentText = input.value.trim();
    if (!commentText) return;

    const isAuthenticated = document.querySelector('meta[name="user-auth"]')?.content === 'true';
    if (!isAuthenticated) {
        showToast('Silakan login untuk mengirim komentar.');
        return;
    }

    submitBtn.disabled = true;

    try {
        const mediaFiles = mediaInput?.files ? Array.from(mediaInput.files) : [];
        let response;

        if (mediaFiles.length > 0) {
            const formData = new FormData();
            formData.append('isi_komentar', commentText);
            mediaFiles.forEach(file => {
                formData.append('media[]', file);
            });

            response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });
        } else {
            response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ isi_komentar: commentText }),
            });
        }

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result?.message || 'Failed to submit comment');
        }

        const countEl = panel.closest('article')?.querySelector('[data-comment-count]');
        if (countEl) {
            countEl.textContent = formatCount(parseInt(result.comments_count ?? countEl.textContent, 10));
        }

        if (panel.dataset.commentsLimit) {
            panel.dataset.commentsLoaded = 'false';
            await loadComments(panel);
        } else {
            const list = panel.querySelector('[data-comment-list]');
            if (list) {
                if (list.textContent.includes('Belum ada komentar')) {
                    list.innerHTML = '';
                }
                list.insertAdjacentHTML('beforeend', renderCommentItem(result.comment));
                bindPostActions(list, csrfToken);
            }
        }

        input.value = '';
        input.style.height = 'auto';
        if (mediaInput) {
            mediaInput.value = '';
        }
        if (mediaLabel) {
            mediaLabel.textContent = '';
        }
        // showToast(result.message || 'Komentar berhasil dikirim.');
    } catch (error) {
        showToast(error.message || 'Gagal mengirim komentar.');
    } finally {
        submitBtn.disabled = false;
    }
}
