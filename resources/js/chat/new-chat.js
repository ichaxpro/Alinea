import { state } from './state.js';
import { apiHeaders } from './api.js';
import { avatarHTML, escapeHtml } from './utils.js';
import { openConversation } from './conversation.js';
import { renderSidebar } from './sidebar.js';

export function openNewChatModal() {
    document.getElementById('newChatModal')?.classList.remove('hidden');
    if (window.innerWidth >= 768) document.getElementById('newChatSearch')?.focus();
    document.getElementById('newChatResults').innerHTML = '';
    document.getElementById('newChatEmpty')?.classList.add('hidden');
}

export function closeNewChatModal() {
    document.getElementById('newChatModal')?.classList.add('hidden');
    const searchInput = document.getElementById('newChatSearch');
    if (searchInput) searchInput.value = '';
    document.getElementById('newChatResults').innerHTML = '';
}

export async function searchUsers(query) {
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

export async function startNewConversation(user) {
    closeNewChatModal();
    try {
        const res = await fetch(`${state.apiBase}/conversations`, {
            method: 'POST', headers: apiHeaders(), body: JSON.stringify({ user_id: user.id }),
        });
        if (!res.ok) return;
        const json = await res.json();
        const conv = json.data;

        if (!state.conversations.find(c => c.id === conv.id)) {
            state.conversations.unshift(conv);
            renderSidebar();
        }
        openConversation(conv.id);
    } catch (e) {
        console.error('startNewConversation:', e);
    }
}
