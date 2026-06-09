import { state } from './state.js';
import { apiHeaders } from './api.js';
import { avatarHTML } from './utils.js';
import { loadMessages, markAsRead } from './messages.js';
import { subscribeToConversation } from './echo.js';
import { fetchConversations } from './sidebar.js';
import { closeUserDetailPanel } from './user-detail.js';

export async function openConversation(id, pushState = true) {
    document.querySelector('.chat-item.active')?.classList.remove('active');
    document.getElementById(`item-${id}`)?.classList.add('active');
    state.currentConversationId = id;
    state.cursor = null;

    const conv = state.conversations.find(c => c.id == id);
    if (conv?.other_user) {
        state.currentOtherUser = conv.other_user;
        document.getElementById('chatName').textContent = conv.other_user.name;
        document.getElementById('chatAvatarWrapper').innerHTML =
            avatarHTML(conv.other_user, 'w-11 h-11', true);
        const usernameEl = document.getElementById('chatUsername');
        if (usernameEl) {
            usernameEl.textContent = conv.other_user.username ? `@${conv.other_user.username}` : '';
        }
    }

    document.getElementById('chatEmpty')?.classList.add('hidden');
    document.getElementById('chatHeader')?.classList.remove('hidden');
    document.getElementById('chatBox')?.classList.remove('hidden');
    
    if (conv?.is_blocked_by_me || conv?.is_blocked_by_them) {
        document.getElementById('chatInputArea')?.classList.add('hidden');
        document.getElementById('blockedNoticeArea')?.classList.remove('hidden');
        const textEl = document.querySelector('#blockedNoticeArea p');
        if (textEl) {
            textEl.textContent = conv?.is_blocked_by_me 
                ? 'Anda telah memblokir pengguna ini' 
                : 'Pengguna ini tidak dapat menerima pesan saat ini';
        }
    } else {
        document.getElementById('chatInputArea')?.classList.remove('hidden');
        document.getElementById('blockedNoticeArea')?.classList.add('hidden');
    }

    document.getElementById('chatContainer')?.classList.add('conversation-open');

    if (pushState) {
        const url = new URL(window.location);
        url.searchParams.set('conv', id);
        window.history.pushState({ conversationId: id }, '', url.toString());
    }

    await markAsRead(id);
    await loadMessages(id);
    subscribeToConversation(id);
    if (!conv?.is_blocked_by_me) document.getElementById('messageInput')?.focus();
}

export function closeActiveConversation(pushState = true) {
    state.currentConversationId = null;
    state.currentOtherUser = null;
    document.querySelector('.chat-item.active')?.classList.remove('active');
    document.getElementById('chatContainer')?.classList.remove('conversation-open');
    document.getElementById('chatHeader')?.classList.add('hidden');
    document.getElementById('chatBox')?.classList.add('hidden');
    document.getElementById('chatInputArea')?.classList.add('hidden');
    document.getElementById('blockedNoticeArea')?.classList.add('hidden');
    document.getElementById('chatEmpty')?.classList.remove('hidden');

    if (pushState) {
        const url = new URL(window.location);
        url.searchParams.delete('conv');
        window.history.pushState(null, '', url.toString());
    }
}

export async function handleReportUser() {
    if (!state.currentOtherUser) return;
    const reason = prompt(`Laporkan ${state.currentOtherUser.name}?\nTuliskan alasanmu (opsional):`);
    if (reason === null) return;

    try {
        const res = await fetch(`/api/users/${state.currentOtherUser.id}/report`, {
            method:  'POST',
            headers: apiHeaders(),
            body:    JSON.stringify({ reason }),
        });
        alert(res.ok ? 'Laporan telah dikirim. Terima kasih.' : 'Gagal mengirim laporan. Coba lagi.');
    } catch {
        alert('Terjadi kesalahan. Coba lagi.');
    }
}

export async function handleBlockUser() {
    if (!state.currentOtherUser) return;
    
    const conv = state.conversations.find(c => c.other_user.id === state.currentOtherUser.id);
    const currentlyBlocked = conv && conv.is_blocked_by_me;
    
    const confirmMsg = currentlyBlocked 
        ? `Buka blokir ${state.currentOtherUser.name}?`
        : `Blokir ${state.currentOtherUser.name}?\nMereka tidak akan bisa mengirim pesan kepadamu.`;
        
    if (!confirm(confirmMsg)) return;

    try {
        const res = await fetch(`/api/users/${state.currentOtherUser.id}/block`, {
            method:  'POST',
            headers: apiHeaders(),
        });
        if (res.ok) {
            const data = await res.json();
            const isBlocked = data.action === 'blocked';
            
            if (conv) conv.is_blocked_by_me = isBlocked;

            if (isBlocked || conv?.is_blocked_by_them) {
                document.getElementById('chatInputArea')?.classList.add('hidden');
                document.getElementById('blockedNoticeArea')?.classList.remove('hidden');
                const textEl = document.querySelector('#blockedNoticeArea p');
                if (textEl) {
                    textEl.textContent = isBlocked 
                        ? 'Anda telah memblokir pengguna ini' 
                        : 'Pengguna ini tidak dapat menerima pesan saat ini';
                }
            } else {
                document.getElementById('chatInputArea')?.classList.remove('hidden');
                document.getElementById('blockedNoticeArea')?.classList.add('hidden');
            }

            if (isBlocked) {
                if(document.getElementById('udBlockText')) document.getElementById('udBlockText').textContent = 'Buka Blokir Pengguna';
                if(document.getElementById('udBlockDesc')) document.getElementById('udBlockDesc').textContent = 'Izinkan pengguna ini mengirim pesan';
            } else {
                if(document.getElementById('udBlockText')) document.getElementById('udBlockText').textContent = 'Blokir Pengguna';
                if(document.getElementById('udBlockDesc')) document.getElementById('udBlockDesc').textContent = 'Pengguna tidak bisa mengirim pesan';
            }
        } else {
            alert('Gagal memproses permintaan.');
        }
    } catch {
        alert('Terjadi kesalahan. Coba lagi.');
    }
}

export async function handleDeleteConversation() {
    if (!state.currentConversationId) return;
    if (!confirm('Hapus semua riwayat percakapan ini?\nTindakan ini tidak dapat dibatalkan.')) return;

    const deletingId = state.currentConversationId;

    try {
        const res = await fetch(`${state.apiBase}/conversations/${deletingId}`, {
            method:  'DELETE',
            headers: apiHeaders(),
        });

        if (res.ok) {
            closeUserDetailPanel();
            state.currentConversationId = null;
            state.currentOtherUser      = null;
            state.currentMessages       = [];

            document.getElementById('chatHeader')?.classList.add('hidden');
            document.getElementById('chatBox')?.classList.add('hidden');
            document.getElementById('chatInputArea')?.classList.add('hidden');
            document.getElementById('chatEmpty')?.classList.remove('hidden');
            document.getElementById('chatContainer')?.classList.remove('conversation-open');

            state.conversations = state.conversations.filter(c => c.id !== deletingId);
            await fetchConversations();
        } else {
            alert('Gagal menghapus percakapan.');
        }
    } catch {
        alert('Terjadi kesalahan. Coba lagi.');
    }
}
