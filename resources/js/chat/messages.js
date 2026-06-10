import { state } from './state.js';
import { apiHeaders, apiHeadersMultipart } from './api.js';
import { formatTime, svgSingleTick, avatarHTML } from './utils.js';
import { openMediaModal, clearPendingMedia, setProgress } from './media.js';
import { updateSidebarPreview, updateTotalBadge } from './sidebar.js';

export async function loadMessages(conversationId, isLoadMore = false) {
    if (state.loadingMessages) return;
    state.loadingMessages = true;

    try {
        const url = isLoadMore && state.cursor
            ? state.cursor
            : `${state.apiBase}/conversations/${conversationId}/messages`;

        const res = await fetch(url, { headers: apiHeaders() });
        if (!res.ok) return;

        const data     = await res.json();
        state.cursor   = data.next_page_url || null;
        const messages = (data.data || []).reverse();

        state.currentMessages = isLoadMore
            ? [...messages, ...state.currentMessages]
            : messages;

        renderMessages(isLoadMore);
    } catch (e) {
        console.error('loadMessages:', e);
    } finally {
        state.loadingMessages = false;
    }
}

export function renderMessages(isLoadMore = false) {
    const chatBox = document.getElementById('chatBox');
    const prevScrollHeight = chatBox.scrollHeight;

    chatBox.innerHTML = '';

    if (state.cursor) {
        const btn = document.createElement('button');
        btn.className   = 'block mx-auto text-xs text-gray-400 hover:text-[#444] py-2 transition';
        btn.textContent = '↑ Muat pesan sebelumnya';
        btn.addEventListener('click', () => loadMessages(state.currentConversationId, true));
        chatBox.appendChild(btn);
    }

    let lastDateString = null;
    const todayStr = new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = yesterday.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });

    state.currentMessages.forEach(msg => {
        if (msg.created_at) {
            const msgDate = new Date(msg.created_at);
            const dateStr = msgDate.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
            
            if (dateStr !== lastDateString) {
                const divider = document.createElement('div');
                divider.className = 'flex justify-center my-4';
                
                let displayText = dateStr;
                if (dateStr === todayStr) displayText = 'Hari ini';
                else if (dateStr === yesterdayStr) displayText = 'Kemarin';
                
                divider.innerHTML = `<span class="bg-white shadow-sm border border-gray-100 text-gray-500 text-[11px] px-3 py-1 rounded-full font-medium">${displayText}</span>`;
                chatBox.appendChild(divider);
                
                lastDateString = dateStr;
            }
        }

        chatBox.appendChild(buildBubble(msg));
    });

    if (isLoadMore) {
        chatBox.scrollTop = chatBox.scrollHeight - prevScrollHeight;
    } else {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

function buildBubble(msg) {
    const isRight   = msg.is_mine;
    const isDeleted = msg.is_deleted;

    const wrapper = document.createElement('div');
    wrapper.className     = (isRight ? 'flex flex-col items-end' : 'flex flex-col items-start') + ' bubble-in';
    wrapper.dataset.msgId = msg.id;

    if (isRight) {
        const row = document.createElement('div');
        row.className = 'bubble-wrapper flex items-end gap-2';

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
            avatarHTML(state.authUser, 'w-8 h-8', false)
        ));
        wrapper.appendChild(row);

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
        const row = document.createElement('div');
        row.className = 'flex items-end gap-2';

        const avatarEl = state.currentOtherUser
            ? document.createRange().createContextualFragment(avatarHTML(state.currentOtherUser, 'w-8 h-8', true))
            : null;
        if (avatarEl) row.appendChild(avatarEl);

        const bubble = document.createElement('div');
        if (isDeleted) {
            bubble.className   = 'bubble-deleted rounded-2xl';
            bubble.textContent = 'Pesan dihapus';
        } else {
            bubble.className = 'bg-white shadow-sm border border-gray-100 px-3 py-2 rounded-2xl text-sm max-w-xs break-words';
            appendBubbleContent(bubble, msg);
        }
        row.appendChild(bubble);
        wrapper.appendChild(row);

        if (!isDeleted) {
            const timeEl    = document.createElement('p');
            timeEl.className = 'msg-time ml-10';
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
        img.addEventListener('click', () => openMediaModal(msg.media_url, 'image', msg.media_original_name));
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
        const wrapper = document.createElement('div');
        wrapper.className = 'video-thumb-wrapper';
        wrapper.title     = 'Putar video';
        wrapper.addEventListener('click', () =>
            openMediaModal(msg.media_url, 'video', msg.media_original_name)
        );

        const thumb = document.createElement('video');
        thumb.src      = msg.media_url;
        thumb.preload  = 'metadata';
        thumb.muted    = true;
        thumb.className = 'video-thumb';
        thumb.addEventListener('loadedmetadata', () => { thumb.currentTime = 0.1; });

        const playBtn = document.createElement('div');
        playBtn.className = 'video-play-btn';
        playBtn.innerHTML = `<svg width="22" height="22" viewBox="0 0 24 24" fill="white">
            <polygon points="5,3 19,12 5,21"/>
        </svg>`;

        wrapper.appendChild(thumb);
        wrapper.appendChild(playBtn);
        bubble.appendChild(wrapper);

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

export function confirmDeleteMessage(msgId) {
    if (!confirm('Hapus pesan ini? Pesan akan terlihat sebagai "Pesan dihapus".')) return;
    deleteMessage(msgId);
}

export async function deleteMessage(msgId) {
    try {
        const res = await fetch(
            `${state.apiBase}/conversations/${state.currentConversationId}/messages/${msgId}`,
            { method: 'DELETE', headers: apiHeaders() }
        );
        if (!res.ok) return;

        const msg = state.currentMessages.find(m => m.id === msgId);
        if (msg) {
            msg.is_deleted          = true;
            msg.content             = null;
            msg.media_url           = null;
            msg.media_type          = null;
            msg.media_original_name = null;
        }
        renderMessages();

        updateSidebarPreview(state.currentConversationId, 'Pesan dihapus');
    } catch (e) {
        console.error('deleteMessage:', e);
    }
}

export async function sendMessage() {
    const input = document.getElementById('messageInput');
    const text  = input.value.trim();

    if (!state.currentConversationId) return;
    if (!text && !state.pendingMediaBlob) return;

    const mediaBlob = state.pendingMediaBlob;
    const mediaType = state.pendingMediaType;
    const mediaName = state.pendingMediaName;

    input.value = '';
    input.style.height = 'auto';
    clearPendingMedia();

    const tempId  = 'tmp_' + Date.now();
    const tempMsg = {
        id: tempId, content: text, is_mine: true, is_deleted: false,
        sender_id: state.authUser.id, created_at: new Date().toISOString(),
        media_url: mediaBlob ? URL.createObjectURL(mediaBlob) : null,
        media_type: mediaType,
        media_original_name: mediaName,
    };
    state.currentMessages.push(tempMsg);
    renderMessages();

    setProgress(0.15);

    try {
        let res;

        if (mediaBlob) {
            const form = new FormData();
            if (text) form.append('content', text);
            form.append('media', mediaBlob, mediaName || `media.${mediaType === 'image' ? 'jpg' : mediaType === 'audio' ? 'mp3' : 'mp4'}`);
            setProgress(0.4);
            res = await fetch(`${state.apiBase}/conversations/${state.currentConversationId}/messages`, {
                method: 'POST', headers: apiHeadersMultipart(), body: form,
            });
            setProgress(0.85);
        } else {
            res = await fetch(`${state.apiBase}/conversations/${state.currentConversationId}/messages`, {
                method: 'POST', headers: apiHeaders(), body: JSON.stringify({ content: text }),
            });
        }

        if (!res.ok) {
            state.currentMessages = state.currentMessages.filter(m => m.id !== tempId);
            renderMessages();
            setProgress(0);
            return;
        }

        const json = await res.json();
        const idx  = state.currentMessages.findIndex(m => m.id === tempId);
        if (idx !== -1) state.currentMessages[idx] = json.data;
        renderMessages();
        setProgress(1);

        const previewText = mediaType === 'image' ? '📷 Foto'
                          : mediaType === 'audio' ? '🎵 Audio'
                          : mediaType === 'video' ? '🎬 Video'
                          : text;
        updateSidebarPreview(state.currentConversationId, previewText || text);

    } catch (e) {
        console.error('sendMessage:', e);
        state.currentMessages = state.currentMessages.filter(m => m.id !== tempId);
        renderMessages();
        setProgress(0);
    }

    emitTyping(false);
}

export async function markAsRead(conversationId) {
    try {
        await fetch(`${state.apiBase}/conversations/${conversationId}/read`, {
            method: 'POST', headers: apiHeaders(),
        });
        const conv = state.conversations.find(c => c.id == conversationId);
        if (conv) conv.unread_count = 0;
        updateTotalBadge();
        document.querySelector(`#item-${conversationId} .unread-badge`)?.remove();
    } catch { /* ignore */ }
}

export function emitTyping(isTyping) {
    if (!state.currentConversationId) return;
    fetch(`${state.apiBase}/conversations/${state.currentConversationId}/typing`, {
        method: 'POST', headers: apiHeaders(), body: JSON.stringify({ is_typing: isTyping }),
    }).catch(() => {});
}
