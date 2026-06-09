import { state } from './state.js';
import { apiHeaders } from './api.js';
import { avatarHTML, escapeHtml, formatTime } from './utils.js';
import { openConversation } from './conversation.js';

export async function fetchConversations() {
    try {
        const res = await fetch(`${state.apiBase}/conversations`, { headers: apiHeaders() });
        if (!res.ok) return;
        const data = await res.json();
        state.conversations = data.data || [];
        renderSidebar();
    } catch (e) {
        console.error('fetchConversations:', e);
    }
}

export function renderSidebar() {
    const list = document.getElementById('chatList');
    if (!list) return;
    list.innerHTML = '';

    if (state.conversations.length === 0) {
        list.innerHTML = '<p class="text-xs text-gray-400 text-center py-6">Belum ada percakapan</p>';
        updateTotalBadge();
        return;
    }

    state.conversations.forEach(conv => {
        const user = conv.other_user;
        if (!user) return;

        const div = document.createElement('div');
        div.id        = `item-${conv.id}`;
        div.className = `chat-item flex gap-3 px-3 py-2 rounded-xl cursor-pointer hover:bg-gray-50 transition${state.currentConversationId == conv.id ? ' active' : ''}`;
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

export function updateSidebarPreview(convId, text) {
    const preview = document.querySelector(`#item-${convId} .sidebar-preview`);
    if (preview) preview.textContent = text;
    const time = document.querySelector(`#item-${convId} .sidebar-time`);
    if (time) time.textContent = formatTime(new Date().toISOString());
}

export function updateTotalBadge() {
    const total = state.conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
    const el    = document.getElementById('totalUnread');
    if (el) { el.textContent = total; el.style.display = total > 0 ? '' : 'none'; }
}

export function filterChats(query) {
    const q = query.toLowerCase();
    state.conversations.forEach(conv => {
        const el = document.getElementById(`item-${conv.id}`);
        if (!el) return;
        el.style.display = (conv.other_user?.name?.toLowerCase() || '').includes(q) ? '' : 'none';
    });
}
