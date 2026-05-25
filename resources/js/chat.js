import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// ── Setup Echo ─────────────────────────────────────────────────────────────
window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster:       'reverb',
    key:               import.meta.env.VITE_REVERB_APP_KEY,
    wsHost:            import.meta.env.VITE_REVERB_HOST,
    wsPort:            import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort:           import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS:          (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// ── Boot ───────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {

    const authUser   = window.authUser || { id: null, name: 'Saya', initial: 'S', avatar_url: '' };
    const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const apiBase    = '/api/chat';

    let conversations         = [];
    let currentMessages       = [];
    let currentConversationId = null;
    let currentOtherUser      = null;   // ← other side's user object for avatar
    let cursor                = null;
    let loadingMessages       = false;
    let typingTimeout         = null;
    let searchTimeout         = null;
    let activeSubscriptions   = [];
    let subscribeIds          = new Set();

    // Media state
    let pendingMediaBlob = null;
    let pendingMediaType = null;
    let pendingMediaName = null;

    const emojis = ['😀','😂','😍','🥰','😎','🤔','😅','🙏','👍','❤️','🔥','🎉','📚','✨','💬','😭','🥺','😊','👀','💪'];

    // ── API helpers ────────────────────────────────────────────────────────

    function apiHeaders(extra = {}) {
        return {
            'Accept':       'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Socket-ID':  window.Echo?.socketId() || '',
            ...extra,
        };
    }

    function apiHeadersMultipart() {
        return {
            'Accept':       'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Socket-ID':  window.Echo?.socketId() || '',
        };
    }

    // ── Sidebar ────────────────────────────────────────────────────────────

    async function fetchConversations() {
        try {
            const res = await fetch(`${apiBase}/conversations`, { headers: apiHeaders() });
            if (!res.ok) return;
            const data = await res.json();
            conversations = data.data || [];
            renderSidebar();
        } catch (e) {
            console.error('fetchConversations:', e);
        }
    }

    function renderSidebar() {
        const list = document.getElementById('chatList');
        list.innerHTML = '';

        if (conversations.length === 0) {
            list.innerHTML = '<p class="text-xs text-gray-400 text-center py-6">Belum ada percakapan</p>';
            updateTotalBadge();
            return;
        }

        conversations.forEach(conv => {
            const user = conv.other_user;
            if (!user) return;

            const div = document.createElement('div');
            div.id        = `item-${conv.id}`;
            div.className = `chat-item flex gap-3 px-3 py-2 rounded-xl cursor-pointer hover:bg-gray-50 transition${currentConversationId === conv.id ? ' active' : ''}`;
            div.dataset.conversationId = conv.id;
            div.innerHTML = `
                <div class="shrink-0">${avatarHTML(user, 'w-11 h-11', true)}</div>
                <div class="flex-1 min-w-0">
                    <p class="chat-name font-semibold text-sm truncate">${escapeHtml(user.name)}</p>
                    <p class="sidebar-preview text-xs text-gray-400 truncate">
                        ${conv.last_message ? escapeHtml(conv.last_message.content) : 'Belum ada pesan'}
                    </p>
                </div>
                <div class="flex flex-col items-end gap-1 shrink-0">
                    ${conv.last_message
                        ? `<span class="sidebar-time text-xs text-gray-400">${formatTime(conv.last_message.created_at)}</span>`
                        : ''}
                    ${conv.unread_count > 0
                        ? `<span class="unread-badge">${conv.unread_count}</span>`
                        : ''}
                </div>
            `;
            div.addEventListener('click', () => openConversation(conv.id));
            list.appendChild(div);
        });

        updateTotalBadge();
    }

    // ── Open conversation ──────────────────────────────────────────────────

    async function openConversation(id) {
        document.querySelector('.chat-item.active')?.classList.remove('active');
        document.getElementById(`item-${id}`)?.classList.add('active');
        currentConversationId = id;
        cursor = null;

        const conv = conversations.find(c => c.id === id);
        if (conv?.other_user) {
            currentOtherUser = conv.other_user;
            document.getElementById('chatName').textContent = conv.other_user.name;
            document.getElementById('chatAvatarWrapper').innerHTML =
                avatarHTML(conv.other_user, 'w-11 h-11', true);
        }

        document.getElementById('chatEmpty')?.classList.add('hidden');
        document.getElementById('chatHeader')?.classList.remove('hidden');
        document.getElementById('chatBox')?.classList.remove('hidden');
        document.getElementById('chatInputArea')?.classList.remove('hidden');

        await markAsRead(id);
        await loadMessages(id);
        subscribeToConversation(id);
        document.getElementById('messageInput')?.focus();
    }

    // ── Messages ───────────────────────────────────────────────────────────

    async function loadMessages(conversationId, isLoadMore = false) {
        if (loadingMessages) return;
        loadingMessages = true;

        try {
            const url = isLoadMore && cursor
                ? cursor
                : `${apiBase}/conversations/${conversationId}/messages`;

            const res = await fetch(url, { headers: apiHeaders() });
            if (!res.ok) return;

            const data     = await res.json();
            cursor         = data.links?.next || null;
            const messages = (data.data || []).reverse();

            currentMessages = isLoadMore
                ? [...messages, ...currentMessages]
                : messages;

            renderMessages(isLoadMore);
        } catch (e) {
            console.error('loadMessages:', e);
        } finally {
            loadingMessages = false;
        }
    }

    function renderMessages(isLoadMore = false) {
        const chatBox = document.getElementById('chatBox');
        const prevScrollHeight = chatBox.scrollHeight;

        chatBox.innerHTML = '';

        if (cursor) {
            const btn = document.createElement('button');
            btn.className   = 'block mx-auto text-xs text-gray-400 hover:text-[#444] py-2 transition';
            btn.textContent = '↑ Muat pesan sebelumnya';
            btn.addEventListener('click', () => loadMessages(currentConversationId, true));
            chatBox.appendChild(btn);
        }

        currentMessages.forEach(msg => {
            chatBox.appendChild(buildBubble(msg));
        });

        if (isLoadMore) {
            chatBox.scrollTop = chatBox.scrollHeight - prevScrollHeight;
        } else {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }

    // ── Bubble builder ─────────────────────────────────────────────────────

    function buildBubble(msg) {
        const isRight   = msg.is_mine;
        const isDeleted = msg.is_deleted;

        // Outer wrapper — used for hover target on delete btn
        const wrapper = document.createElement('div');
        wrapper.className     = (isRight ? 'flex flex-col items-end' : 'flex flex-col items-start') + ' bubble-in';
        wrapper.dataset.msgId = msg.id;

        if (isRight) {
            // ── Own message ──────────────────────────────────────────────
            const row = document.createElement('div');
            row.className = 'bubble-wrapper flex items-end gap-2';

            // Delete button (visible on hover, only for non-deleted own messages)
            if (!isDeleted) {
                const delBtn = document.createElement('button');
                delBtn.className = 'msg-delete-btn';
                delBtn.title     = 'Hapus pesan';
                delBtn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/>
                    <path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
                </svg>`;
                delBtn.addEventListener('click', () => confirmDeleteMessage(msg.id));
                row.appendChild(delBtn);
            }

            const bubble = document.createElement('div');
            if (isDeleted) {
                bubble.className  = 'bubble-deleted rounded-2xl';
                bubble.textContent = 'Pesan dihapus';
            } else {
                bubble.className = 'bg-[#FFDDAF] border border-[#444] px-3 py-2 rounded-2xl text-sm max-w-xs break-words';
                appendBubbleContent(bubble, msg);
            }

            row.appendChild(bubble);
            row.appendChild(document.createRange().createContextualFragment(
                avatarHTML(authUser, 'w-8 h-8', false)
            ));
            wrapper.appendChild(row);

            // Time + status (only for non-deleted)
            if (!isDeleted) {
                const meta    = document.createElement('div');
                meta.className = 'flex items-center gap-1 mt-1 pr-10';
                const timeEl    = document.createElement('span');
                timeEl.className = 'msg-time';
                timeEl.textContent = formatTime(msg.created_at);
                const statusEl    = document.createElement('span');
                statusEl.className = 'msg-status';
                statusEl.innerHTML = svgSingleTick();
                meta.appendChild(timeEl);
                meta.appendChild(statusEl);
                wrapper.appendChild(meta);
            }

        } else {
            // ── Other person's message ───────────────────────────────────
            const row = document.createElement('div');
            row.className = 'flex items-end gap-2';

            // Other user's avatar next to their bubble
            const avatarEl = currentOtherUser
                ? document.createRange().createContextualFragment(avatarHTML(currentOtherUser, 'w-8 h-8', true))
                : null;
            if (avatarEl) row.appendChild(avatarEl);

            const bubble = document.createElement('div');
            if (isDeleted) {
                bubble.className   = 'bubble-deleted rounded-2xl';
                bubble.textContent = '🚫 Pesan dihapus';
            } else {
                bubble.className = 'bg-white shadow-sm border border-gray-100 px-3 py-2 rounded-2xl text-sm max-w-xs break-words';
                appendBubbleContent(bubble, msg);
            }
            row.appendChild(bubble);
            wrapper.appendChild(row);

            if (!isDeleted) {
                const timeEl    = document.createElement('p');
                timeEl.className = 'msg-time ml-10';   // indent past the avatar
                timeEl.textContent = formatTime(msg.created_at);
                wrapper.appendChild(timeEl);
            }
        }

        return wrapper;
    }

    function appendBubbleContent(bubble, msg) {
        if (msg.media_type === 'image' && msg.media_url) {
            const img = document.createElement('img');
            img.src       = msg.media_url;
            img.className = 'media-bubble-img';
            img.alt       = msg.media_original_name || 'Gambar';
            img.loading   = 'lazy';
            img.addEventListener('click', () => window.open(msg.media_url, '_blank'));
            bubble.appendChild(img);
            if (msg.content) {
                const cap = document.createElement('p');
                cap.className   = 'media-caption';
                cap.textContent = msg.content;
                bubble.appendChild(cap);
            }
        } else if (msg.media_type === 'audio' && msg.media_url) {
            const audio = document.createElement('audio');
            audio.src       = msg.media_url;
            audio.controls  = true;
            audio.className = 'media-bubble-audio';
            bubble.appendChild(audio);
            if (msg.content) {
                const cap = document.createElement('p');
                cap.className   = 'media-caption';
                cap.textContent = msg.content;
                bubble.appendChild(cap);
            }
        } else if (msg.media_type === 'video' && msg.media_url) {
            const video = document.createElement('video');
            video.src       = msg.media_url;
            video.controls  = true;
            video.className = 'media-bubble-video';
            video.preload   = 'metadata';
            bubble.appendChild(video);
            if (msg.content) {
                const cap = document.createElement('p');
                cap.className   = 'media-caption';
                cap.textContent = msg.content;
                bubble.appendChild(cap);
            }
        } else {
            bubble.textContent = msg.content;
        }
    }

    // ── Delete message ─────────────────────────────────────────────────────

    function confirmDeleteMessage(msgId) {
        if (!confirm('Hapus pesan ini? Pesan akan terlihat sebagai "Pesan dihapus".')) return;
        deleteMessage(msgId);
    }

    async function deleteMessage(msgId) {
        try {
            const res = await fetch(
                `${apiBase}/conversations/${currentConversationId}/messages/${msgId}`,
                { method: 'DELETE', headers: apiHeaders() }
            );
            if (!res.ok) return;

            // Mark as deleted locally
            const msg = currentMessages.find(m => m.id === msgId);
            if (msg) {
                msg.is_deleted          = true;
                msg.content             = null;
                msg.media_url           = null;
                msg.media_type          = null;
                msg.media_original_name = null;
            }
            renderMessages();

            // Update sidebar if it was the last message
            updateSidebarPreview(currentConversationId, 'Pesan dihapus');
        } catch (e) {
            console.error('deleteMessage:', e);
        }
    }

    // ── Image compression ──────────────────────────────────────────────────

    function compressImage(file) {
        return new Promise((resolve) => {
            const MAX_DIM = 1280;
            const QUALITY = 0.80;
            const img = new Image();
            const url = URL.createObjectURL(file);

            img.onload = () => {
                URL.revokeObjectURL(url);
                let { width, height } = img;
                if (width > MAX_DIM || height > MAX_DIM) {
                    if (width >= height) {
                        height = Math.round(height * MAX_DIM / width);
                        width  = MAX_DIM;
                    } else {
                        width  = Math.round(width * MAX_DIM / height);
                        height = MAX_DIM;
                    }
                }
                const canvas  = document.createElement('canvas');
                canvas.width  = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                canvas.toBlob(blob => resolve(blob || file), 'image/jpeg', QUALITY);
            };

            img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
            img.src = url;
        });
    }

    function formatFileSize(bytes) {
        if (bytes < 1024)        return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    // ── Media selection & preview ──────────────────────────────────────────

    async function handleMediaSelect(file) {
        if (!file) return;

        const maxBytes = { image: 20 * 1024 * 1024, audio: 16 * 1024 * 1024, video: 100 * 1024 * 1024 };
        let mediaType = null;
        if (file.type.startsWith('image/'))      mediaType = 'image';
        else if (file.type.startsWith('audio/')) mediaType = 'audio';
        else if (file.type.startsWith('video/')) mediaType = 'video';
        else { alert('Tipe file tidak didukung.'); return; }

        if (file.size > maxBytes[mediaType]) {
            alert(`Ukuran file terlalu besar. Maksimal ${formatFileSize(maxBytes[mediaType])} untuk ${mediaType}.`);
            return;
        }

        let blob = file;
        if (mediaType === 'image') blob = await compressImage(file);

        pendingMediaBlob = blob;
        pendingMediaType = mediaType;
        pendingMediaName = file.name;

        const strip  = document.getElementById('mediaPreviewStrip');
        const thumb  = document.getElementById('mediaPreviewThumb');
        const icon   = document.getElementById('mediaPreviewIcon');
        const nameEl = document.getElementById('mediaPreviewName');
        const sizeEl = document.getElementById('mediaPreviewSize');

        nameEl.textContent = file.name;
        sizeEl.textContent = `${formatFileSize(file.size)}${mediaType === 'image' ? ` → ${formatFileSize(blob.size)} (terkompresi)` : ''}`;

        if (mediaType === 'image') {
            const objUrl = URL.createObjectURL(blob);
            thumb.src = objUrl;
            thumb.onload = () => URL.revokeObjectURL(objUrl);
            thumb.style.display = '';
            icon.style.display  = 'none';
        } else {
            icon.textContent    = mediaType === 'audio' ? '🎵' : '🎬';
            icon.style.display  = '';
            thumb.style.display = 'none';
        }

        strip.classList.add('open');
    }

    function clearPendingMedia() {
        pendingMediaBlob = null;
        pendingMediaType = null;
        pendingMediaName = null;
        document.getElementById('mediaPreviewStrip')?.classList.remove('open');
        const thumb = document.getElementById('mediaPreviewThumb');
        if (thumb) thumb.src = '';
        const fileInput = document.getElementById('mediaFileInput');
        if (fileInput) fileInput.value = '';
    }

    // ── Progress bar ───────────────────────────────────────────────────────

    function setProgress(fraction) {
        const bar = document.getElementById('uploadProgressBar');
        if (!bar) return;
        bar.style.transform = `scaleX(${fraction})`;
        if (fraction >= 1) setTimeout(() => { bar.style.transform = 'scaleX(0)'; }, 400);
    }

    // ── Send ───────────────────────────────────────────────────────────────

    async function sendMessage() {
        const input = document.getElementById('messageInput');
        const text  = input.value.trim();

        if (!currentConversationId) return;
        if (!text && !pendingMediaBlob) return;

        const mediaBlob = pendingMediaBlob;
        const mediaType = pendingMediaType;
        const mediaName = pendingMediaName;

        input.value = '';
        input.style.height = 'auto';
        clearPendingMedia();

        const tempId  = 'tmp_' + Date.now();
        const tempMsg = {
            id: tempId, content: text, is_mine: true, is_deleted: false,
            sender_id: authUser.id, created_at: new Date().toISOString(),
            media_url: mediaBlob ? URL.createObjectURL(mediaBlob) : null,
            media_type: mediaType,
            media_original_name: mediaName,
        };
        currentMessages.push(tempMsg);
        renderMessages();

        setProgress(0.15);

        try {
            let res;

            if (mediaBlob) {
                const form = new FormData();
                if (text) form.append('content', text);
                form.append('media', mediaBlob, mediaName || `media.${mediaType === 'image' ? 'jpg' : mediaType === 'audio' ? 'mp3' : 'mp4'}`);
                setProgress(0.4);
                res = await fetch(`${apiBase}/conversations/${currentConversationId}/messages`, {
                    method: 'POST', headers: apiHeadersMultipart(), body: form,
                });
                setProgress(0.85);
            } else {
                res = await fetch(`${apiBase}/conversations/${currentConversationId}/messages`, {
                    method: 'POST', headers: apiHeaders(), body: JSON.stringify({ content: text }),
                });
            }

            if (!res.ok) {
                currentMessages = currentMessages.filter(m => m.id !== tempId);
                renderMessages();
                setProgress(0);
                return;
            }

            const json = await res.json();
            const idx  = currentMessages.findIndex(m => m.id === tempId);
            if (idx !== -1) currentMessages[idx] = json.data;
            renderMessages();
            setProgress(1);

            const previewText = mediaType === 'image' ? '📷 Foto'
                              : mediaType === 'audio' ? '🎵 Audio'
                              : mediaType === 'video' ? '🎬 Video'
                              : text;
            updateSidebarPreview(currentConversationId, previewText || text);

        } catch (e) {
            console.error('sendMessage:', e);
            currentMessages = currentMessages.filter(m => m.id !== tempId);
            renderMessages();
            setProgress(0);
        }

        emitTyping(false);
    }

    // ── Utilities ──────────────────────────────────────────────────────────

    async function markAsRead(conversationId) {
        try {
            await fetch(`${apiBase}/conversations/${conversationId}/read`, {
                method: 'POST', headers: apiHeaders(),
            });
            const conv = conversations.find(c => c.id === conversationId);
            if (conv) conv.unread_count = 0;
            updateTotalBadge();
            document.querySelector(`#item-${conversationId} .unread-badge`)?.remove();
        } catch { /* ignore */ }
    }

    function emitTyping(isTyping) {
        if (!currentConversationId) return;
        fetch(`${apiBase}/conversations/${currentConversationId}/typing`, {
            method: 'POST', headers: apiHeaders(), body: JSON.stringify({ is_typing: isTyping }),
        }).catch(() => {});
    }

    function updateSidebarPreview(convId, text) {
        const preview = document.querySelector(`#item-${convId} .sidebar-preview`);
        if (preview) preview.textContent = text;
        const time = document.querySelector(`#item-${convId} .sidebar-time`);
        if (time) time.textContent = formatTime(new Date().toISOString());
    }

    function updateTotalBadge() {
        const total = conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
        const el    = document.getElementById('totalUnread');
        if (el) { el.textContent = total; el.style.display = total > 0 ? '' : 'none'; }
    }

    function filterChats(query) {
        const q = query.toLowerCase();
        conversations.forEach(conv => {
            const el = document.getElementById(`item-${conv.id}`);
            if (!el) return;
            el.style.display = (conv.other_user?.name?.toLowerCase() || '').includes(q) ? '' : 'none';
        });
    }

    // ── WebSocket subscriptions ────────────────────────────────────────────

    function subscribeToConversation(id) {
        if (!window.Echo) return;
        if (subscribeIds.has(id)) return;
        subscribeIds.add(id);

        const channel = window.Echo.private(`conversation.${id}`);
        channel._id   = id;

        channel.listen('.MessageSent', (e) => {
            if (e.sender_id === authUser.id) return;

            if (currentConversationId === id) {
                currentMessages.push(e);
                renderMessages();
                markAsRead(id);
            }

            const previewText = e.media_type === 'image' ? '📷 Foto'
                              : e.media_type === 'audio' ? '🎵 Audio'
                              : e.media_type === 'video' ? '🎬 Video'
                              : e.content;
            updateSidebarPreview(id, previewText);
            const conv = conversations.find(c => c.id == id);
            if (conv) {
                conv.unread_count = (conv.unread_count || 0) + 1;
                conv.last_message = { content: previewText, created_at: e.created_at };
            }
            renderSidebar();
        });

        channel.listen('.MessageDeleted', (e) => {
            // Mark the message as deleted in local state
            const msg = currentMessages.find(m => m.id === e.id);
            if (msg) {
                msg.is_deleted          = true;
                msg.content             = null;
                msg.media_url           = null;
                msg.media_type          = null;
                msg.media_original_name = null;
            }
            if (currentConversationId === id) renderMessages();

            updateSidebarPreview(id, 'Pesan dihapus');
        });

        channel.listen('.TypingIndicator', (e) => {
            if (e.user_id === authUser.id) return;
            if (currentConversationId !== id) return;
            document.getElementById('typingIndicator')?.classList.toggle('hidden', !e.is_typing);
        });

        activeSubscriptions.push(channel);
    }

    // ── New Chat Modal ─────────────────────────────────────────────────────

    function openNewChatModal() {
        document.getElementById('newChatModal')?.classList.remove('hidden');
        document.getElementById('newChatSearch')?.focus();
        document.getElementById('newChatResults').innerHTML = '';
        document.getElementById('newChatEmpty')?.classList.add('hidden');
    }

    function closeNewChatModal() {
        document.getElementById('newChatModal')?.classList.add('hidden');
        if (document.getElementById('newChatSearch'))
            document.getElementById('newChatSearch').value = '';
        document.getElementById('newChatResults').innerHTML = '';
    }

    async function searchUsers(query) {
        const results  = document.getElementById('newChatResults');
        const emptyMsg = document.getElementById('newChatEmpty');
        results.innerHTML = '';

        if (query.length < 1) { emptyMsg?.classList.add('hidden'); return; }

        try {
            const res = await fetch(`/api/users?search=${encodeURIComponent(query)}`, { headers: apiHeaders() });
            if (!res.ok) return;
            const users = await res.json();

            if (users.length === 0) { emptyMsg?.classList.remove('hidden'); return; }
            emptyMsg?.classList.add('hidden');

            users.forEach(user => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 px-3 py-2 rounded-xl cursor-pointer hover:bg-gray-50 transition';
                div.innerHTML = `
                    <div class="shrink-0">${avatarHTML(user, 'w-10 h-10', true)}</div>
                    <div>
                        <p class="font-semibold text-sm">${escapeHtml(user.name)}</p>
                        ${user.username ? `<p class="text-xs text-gray-400">@${escapeHtml(user.username)}</p>` : ''}
                    </div>
                `;
                div.addEventListener('click', () => startNewConversation(user));
                results.appendChild(div);
            });
        } catch (e) {
            console.error('searchUsers:', e);
        }
    }

    async function startNewConversation(user) {
        closeNewChatModal();
        try {
            const res = await fetch(`${apiBase}/conversations`, {
                method: 'POST', headers: apiHeaders(), body: JSON.stringify({ user_id: user.id }),
            });
            if (!res.ok) return;
            const json = await res.json();
            const conv = json.data;

            if (!conversations.find(c => c.id === conv.id)) {
                conversations.unshift(conv);
                renderSidebar();
            }
            openConversation(conv.id);
        } catch (e) {
            console.error('startNewConversation:', e);
        }
    }

    // ── SVG helpers ────────────────────────────────────────────────────────

    function svgSingleTick() {
        return `<svg width="14" height="10" viewBox="0 0 14 10" fill="none">
                    <polyline points="1,5 5,9 13,1" stroke="#aaa" stroke-width="1.8"
                              stroke-linecap="round" stroke-linejoin="round"/>
                </svg>`;
    }

    // ── DOM helpers ────────────────────────────────────────────────────────

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function formatTime(isoString) {
        if (!isoString) return '';
        const d = new Date(isoString);
        return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
    }

    function avatarHTML(user, size, border = true) {
        const borderClass = border ? 'border-2 border-[#444]' : '';
        if (user.avatar_url) {
            return `<img src="${user.avatar_url}" alt="avatar"
                         class="${size} rounded-full ${borderClass} object-cover flex-shrink-0">`;
        }
        const initial = user.initial || user.name?.charAt(0)?.toUpperCase() || '?';
        return `<div class="${size} rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF]
                             ${borderClass} flex items-center justify-center
                             text-[#444] font-bold text-xs flex-shrink-0">
                    ${escapeHtml(initial)}
                </div>`;
    }

    // ── Emoji picker ───────────────────────────────────────────────────────

    function buildEmojiPicker() {
        const picker = document.getElementById('emojiPicker');
        if (!picker) return;
        emojis.forEach(e => {
            const btn = document.createElement('span');
            btn.className   = 'emoji-btn';
            btn.textContent = e;
            btn.onclick = () => {
                document.getElementById('messageInput').value += e;
                document.getElementById('messageInput').focus();
                toggleEmoji(false);
            };
            picker.appendChild(btn);
        });
    }

    function toggleEmoji(forceClose) {
        const picker = document.getElementById('emojiPicker');
        if (!picker) return;
        if (forceClose === false || picker.classList.contains('open')) {
            picker.classList.remove('open');
        } else {
            picker.classList.add('open');
        }
    }

    // ── Event listeners ────────────────────────────────────────────────────

    document.getElementById('backBtn')?.addEventListener('click', () => history.back());
    document.getElementById('sendBtn')?.addEventListener('click', sendMessage);

    document.getElementById('attachBtn')?.addEventListener('click', () => {
        document.getElementById('mediaFileInput')?.click();
    });

    document.getElementById('mediaFileInput')?.addEventListener('change', function () {
        if (this.files?.[0]) handleMediaSelect(this.files[0]);
    });

    document.getElementById('mediaCancelBtn')?.addEventListener('click', clearPendingMedia);

    document.getElementById('searchInput')?.addEventListener('input', function () {
        filterChats(this.value);
    });

    const msgInput = document.getElementById('messageInput');
    if (msgInput) {
        msgInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
        const autoResize = () => {
            msgInput.style.height = 'auto';
            msgInput.style.height = msgInput.scrollHeight + 'px';
            msgInput.style.overflowY = msgInput.scrollHeight > msgInput.clientHeight ? 'auto' : 'hidden';
            msgInput.style.overflowX = 'hidden';
        };
        msgInput.addEventListener('input', () => {
            autoResize();
            clearTimeout(typingTimeout);
            emitTyping(true);
            typingTimeout = setTimeout(() => emitTyping(false), 2000);
        });
    }

    // Drag-and-drop
    const chatBox = document.getElementById('chatBox');
    if (chatBox) {
        chatBox.addEventListener('dragover', e => { e.preventDefault(); chatBox.style.opacity = '0.7'; });
        chatBox.addEventListener('dragleave', () => { chatBox.style.opacity = ''; });
        chatBox.addEventListener('drop', e => {
            e.preventDefault();
            chatBox.style.opacity = '';
            const file = e.dataTransfer?.files?.[0];
            if (file && currentConversationId) handleMediaSelect(file);
        });
    }

    document.getElementById('emojiToggle')?.addEventListener('click', toggleEmoji);
    document.getElementById('newChatBtn')?.addEventListener('click', openNewChatModal);
    document.getElementById('closeNewChat')?.addEventListener('click', closeNewChatModal);

    const newChatSearch = document.getElementById('newChatSearch');
    if (newChatSearch) {
        newChatSearch.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => searchUsers(this.value.trim()), 300);
        });
        newChatSearch.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeNewChatModal();
        });
    }

    document.addEventListener('click', e => {
        const toggle = document.getElementById('emojiToggle');
        const picker = document.getElementById('emojiPicker');
        if (toggle && picker && !toggle.contains(e.target) && !picker.contains(e.target)) {
            picker.classList.remove('open');
        }
        const modal = document.getElementById('newChatModal');
        if (modal && e.target === modal) closeNewChatModal();
    });

    // ── Boot ───────────────────────────────────────────────────────────────
    buildEmojiPicker();
    fetchConversations();
});
