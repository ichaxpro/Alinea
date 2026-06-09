import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { state } from './state.js';
import { renderMessages, markAsRead } from './messages.js';
import { renderSidebar, updateSidebarPreview } from './sidebar.js';

export function initEcho() {
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
}

export function subscribeToConversation(id) {
    if (!window.Echo) return;
    if (state.subscribeIds.has(id)) return;
    state.subscribeIds.add(id);

    const channel = window.Echo.private(`conversation.${id}`);
    channel._id   = id;

    channel.listen('.MessageSent', (e) => {
        if (e.sender_id === state.authUser.id) return;

        if (state.currentConversationId == id) {
            state.currentMessages.push(e);
            renderMessages();
            markAsRead(id);
        }

        const previewText = e.media_type === 'image' ? '📷 Foto'
                          : e.media_type === 'audio' ? '🎵 Audio'
                          : e.media_type === 'video' ? '🎬 Video'
                          : e.content;
        updateSidebarPreview(id, previewText);
        const conv = state.conversations.find(c => c.id == id);
        if (conv) {
            conv.unread_count = (conv.unread_count || 0) + 1;
            conv.last_message = { content: previewText, created_at: e.created_at };
        }
        renderSidebar();
    });

    channel.listen('.MessageDeleted', (e) => {
        const msg = state.currentMessages.find(m => m.id === e.id);
        if (msg) {
            msg.is_deleted          = true;
            msg.content             = null;
            msg.media_url           = null;
            msg.media_type          = null;
            msg.media_original_name = null;
        }
        if (state.currentConversationId == id) renderMessages();

        updateSidebarPreview(id, 'Pesan dihapus');
    });

    channel.listen('.TypingIndicator', (e) => {
        if (e.user_id === state.authUser.id) return;
        if (state.currentConversationId != id) return;
        document.getElementById('typingIndicator')?.classList.toggle('hidden', !e.is_typing);
    });

    channel.listen('.ConversationBlockUpdated', (e) => {
        const conv = state.conversations.find(c => c.id == e.conversationId);
        if (!conv) return;

        // Jika user saya adalah yang memblokir, perbarui is_blocked_by_me.
        // Tapi event ini diterima oleh 'toOthers', jadi yang menerima adalah pihak lain (yang diblokir).
        // Sehingga, bagi saya, ini berarti 'is_blocked_by_them'.
        conv.is_blocked_by_them = e.isBlocked;

        if (state.currentConversationId == e.conversationId) {
            const inputArea = document.getElementById('chatInputArea');
            const noticeArea = document.getElementById('blockedNoticeArea');
            const textEl = document.querySelector('#blockedNoticeArea p');

            if (conv.is_blocked_by_me || conv.is_blocked_by_them) {
                inputArea?.classList.add('hidden');
                noticeArea?.classList.remove('hidden');
                if (textEl) {
                    textEl.textContent = conv.is_blocked_by_me 
                        ? 'Anda telah memblokir pengguna ini' 
                        : 'Pengguna ini tidak dapat menerima pesan saat ini';
                }
            } else {
                inputArea?.classList.remove('hidden');
                noticeArea?.classList.add('hidden');
            }
        }
    });

    state.activeSubscriptions.push(channel);
}
