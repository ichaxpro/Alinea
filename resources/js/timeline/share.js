import { showToast } from './toast.js';

document.addEventListener('click', async (e) => {
    const copyBtn = e.target.closest('[data-share-copy-btn]');
    if (copyBtn) {
        e.preventDefault();
        const article = copyBtn.closest('article[data-post-id]');
        const postId = article?.dataset.postId;
        if (!postId) return;

        const dropdown = copyBtn.closest('[data-share-dropdown]');
        if (dropdown) dropdown.classList.add('hidden');

        const url = window.location.origin + '/timeline/posts/' + postId;
        try {
            await navigator.clipboard.writeText(url);
            showToast('Tautan berhasil disalin ke papan klip.');
        } catch (err) {
            console.error('Failed to copy: ', err);
            alert('Gagal menyalin tautan.');
        }
    }
});

const shareChatModalHtml = `
<div id="shareChatOverlay" class="fixed inset-0 z-[200] flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-200" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px)">
    <div id="shareChatModal" class="bg-white border-[1.5px] border-[#444] rounded-2xl w-full max-w-sm flex flex-col shadow-[4px_4px_0_0_rgba(68,68,68,1)] transform scale-95 transition-transform duration-200 h-[60vh] max-h-[500px]">
        <div class="px-5 py-4 border-b-[1.5px] border-gray-100 flex items-center justify-between shrink-0">
            <h3 class="text-lg font-black text-[#222]">Bagikan ke-</h3>
            <button id="shareChatCloseBtn" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-[#444] transition-colors">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="p-3 shrink-0">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="shareChatSearch" class="w-full border-[1.5px] border-gray-200 rounded-xl py-2 pl-9 pr-3 text-sm focus:border-[#444] outline-none transition-colors" placeholder="Cari teman...">
            </div>
        </div>
        <div id="shareChatList" class="flex-1 overflow-y-auto p-2">
            <div class="flex justify-center p-4">
                <div class="w-6 h-6 border-2 border-[#444] border-t-transparent rounded-full animate-spin"></div>
            </div>
        </div>
    </div>
</div>
`;

if (!document.getElementById('shareChatOverlay')) {
    document.body.insertAdjacentHTML('beforeend', shareChatModalHtml);
}

document.addEventListener('click', async (e) => {
    const shareChatBtn = e.target.closest('[data-share-chat-btn]');
    if (shareChatBtn) {
        e.preventDefault();
        const article = shareChatBtn.closest('article[data-post-id]');
        const postId = article?.dataset.postId;
        if (!postId) return;

        const dropdown = shareChatBtn.closest('[data-share-dropdown]');
        if (dropdown) dropdown.classList.add('hidden');

        const overlay = document.getElementById('shareChatOverlay');
        const modal = document.getElementById('shareChatModal');
        const closeBtn = document.getElementById('shareChatCloseBtn');
        const listContainer = document.getElementById('shareChatList');
        const searchInput = document.getElementById('shareChatSearch');

        searchInput.value = '';
        listContainer.innerHTML = '<div class="flex justify-center p-4"><div class="w-6 h-6 border-2 border-[#444] border-t-transparent rounded-full animate-spin"></div></div>';

        overlay.classList.remove('hidden');
        void overlay.offsetWidth;
        overlay.classList.remove('opacity-0');
        modal.classList.remove('scale-95');
        modal.classList.add('scale-100');

        closeBtn.onclick = () => {
            overlay.classList.add('opacity-0');
            modal.classList.remove('scale-100');
            modal.classList.add('scale-95');
            setTimeout(() => overlay.classList.add('hidden'), 200);
        };

        const currentUserId = document.querySelector('meta[name="user-id"]')?.content;
        if (!currentUserId) {
            listContainer.innerHTML = '<div class="text-center p-4 text-sm text-gray-500">Anda harus login.</div>';
            return;
        }

        try {
            const res = await fetch(`/u/${currentUserId}/following`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Network response was not ok');
            const data = await res.json();
            const users = data.users || [];

            if (users.length === 0) {
                listContainer.innerHTML = '<div class="text-center p-4 text-sm text-gray-500">Anda belum mengikuti siapapun.</div>';
                return;
            }

            const postUrl = window.location.origin + '/timeline/posts/' + postId;

            const renderUsers = (usersToRender) => {
                listContainer.innerHTML = '';
                if (usersToRender.length === 0) {
                    listContainer.innerHTML = '<div class="text-center p-4 text-sm text-gray-500">Pengguna tidak ditemukan.</div>';
                    return;
                }
                usersToRender.forEach(user => {
                    const avatar = user.avatar_url
                        ? `<img src="${user.avatar_url}" class="w-10 h-10 rounded-full border-[1.5px] border-[#444] object-cover">`
                        : `<div class="w-10 h-10 rounded-full border-[1.5px] border-[#444] flex items-center justify-center font-bold text-sm bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] text-[#444]">${user.name.charAt(0).toUpperCase()}</div>`;

                    const el = document.createElement('div');
                    el.className = 'flex items-center justify-between p-2 hover:bg-gray-50 rounded-xl transition-colors';
                    el.innerHTML = `
                        <div class="flex items-center gap-3 min-w-0">
                            ${avatar}
                            <div class="min-w-0">
                                <div class="font-bold text-sm text-[#222] truncate">${user.name}</div>
                                <div class="text-xs text-gray-400 truncate">${user.username ? '@' + user.username.replace('@', '') : ''}</div>
                            </div>
                        </div>
                        <button class="shrink-0 px-4 py-1.5 bg-[#FFDDAF] border-[1.5px] border-[#444] text-[#444] text-xs font-bold rounded-full hover:bg-[#ffcf90] transition-colors" data-send-to="${user.id}">Kirim</button>
                    `;

                    const sendBtn = el.querySelector('button');
                    sendBtn.addEventListener('click', async () => {
                        sendBtn.disabled = true;
                        sendBtn.textContent = 'Mengirim...';
                        sendBtn.classList.remove('bg-[#FFDDAF]', 'hover:bg-[#ffcf90]');
                        sendBtn.classList.add('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');

                        try {
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                            const convRes = await fetch('/api/chat/conversations', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                                body: JSON.stringify({ user_id: user.id })
                            });
                            if (!convRes.ok) throw new Error('Start conv failed');
                            const convData = await convRes.json();
                            const convId = convData.data.id;

                            const msgRes = await fetch(`/api/chat/conversations/${convId}/messages`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                                body: JSON.stringify({ content: `Lihat postingan ini: ${postUrl}` })
                            });
                            if (!msgRes.ok) throw new Error('Send msg failed');

                            sendBtn.textContent = 'Terkirim';
                            sendBtn.classList.remove('bg-gray-100', 'text-gray-400');
                            sendBtn.classList.add('bg-green-100', 'text-green-600', 'border-green-500');

                            showToast(`Dibagikan ke ${user.name}`);
                        } catch (err) {
                            console.error(err);
                            sendBtn.disabled = false;
                            sendBtn.textContent = 'Kirim';
                            sendBtn.classList.add('bg-[#FFDDAF]', 'hover:bg-[#ffcf90]');
                            sendBtn.classList.remove('bg-gray-100', 'text-gray-400', 'cursor-not-allowed');
                            alert('Gagal membagikan postingan.');
                        }
                    });

                    listContainer.appendChild(el);
                });
            };

            renderUsers(users);

            searchInput.oninput = (e) => {
                const q = e.target.value.toLowerCase();
                const filtered = users.filter(u => u.name.toLowerCase().includes(q) || (u.username && u.username.toLowerCase().includes(q)));
                renderUsers(filtered);
            };

        } catch (err) {
            listContainer.innerHTML = '<div class="text-center p-4 text-sm text-red-500">Gagal memuat daftar teman.</div>';
        }
    }
});
