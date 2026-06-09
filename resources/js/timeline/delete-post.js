import { showToast } from './toast.js';

const deleteConfirmHtml = `
<div id="deleteConfirmOverlay" class="fixed inset-0 z-[200] flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-200" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px)">
    <div id="deleteConfirmModal" class="bg-white border-[1.5px] border-[#444] rounded-2xl w-full max-w-sm p-6 shadow-[4px_4px_0_0_rgba(68,68,68,1)] transform scale-95 transition-transform duration-200 text-center">
        <div class="w-14 h-14 bg-red-100 border-[1.5px] border-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-red-500">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
        </div>
        <h3 class="text-lg font-bold text-[#222] mb-2">Hapus Unggahan?</h3>
        <p class="text-sm text-gray-600 mb-6">Tindakan ini tidak dapat dibatalkan. Unggahan akan dihapus dari profil dan linimasa.</p>
        <div class="flex gap-3">
            <button id="deleteConfirmCancelBtn" class="flex-1 px-4 py-2 bg-white text-[#444] border-[1.5px] border-[#444] rounded-full font-bold hover:bg-gray-50 transition-colors">Batal</button>
            <button id="deleteConfirmConfirmBtn" class="flex-1 px-4 py-2 bg-red-500 text-white border-[1.5px] border-[#444] rounded-full font-bold hover:bg-red-600 transition-colors">Hapus</button>
        </div>
    </div>
</div>`;

if (!document.getElementById('deleteConfirmOverlay')) {
    document.body.insertAdjacentHTML('beforeend', deleteConfirmHtml);
}

function confirmDeleteAction() {
    return new Promise((resolve) => {
        const overlay = document.getElementById('deleteConfirmOverlay');
        const modal = document.getElementById('deleteConfirmModal');
        const cancelBtn = document.getElementById('deleteConfirmCancelBtn');
        const confirmBtn = document.getElementById('deleteConfirmConfirmBtn');

        overlay.classList.remove('hidden');
        void overlay.offsetWidth;
        overlay.classList.remove('opacity-0');
        modal.classList.remove('scale-95');
        modal.classList.add('scale-100');

        const close = (result) => {
            overlay.classList.add('opacity-0');
            modal.classList.remove('scale-100');
            modal.classList.add('scale-95');
            setTimeout(() => overlay.classList.add('hidden'), 200);
            cancelBtn.onclick = null;
            confirmBtn.onclick = null;
            resolve(result);
        };

        cancelBtn.onclick = () => close(false);
        confirmBtn.onclick = () => close(true);
    });
}

document.addEventListener('click', async (e) => {
    const deleteBtn = e.target.closest('[data-post-delete]');
    if (deleteBtn) {
        e.preventDefault();
        const article = deleteBtn.closest('article[data-post-id]');
        const postId = article?.dataset.postId;
        if (!postId) return;

        const dropdown = deleteBtn.closest('[data-post-menu-dropdown]');
        if (dropdown) dropdown.classList.add('hidden');

        const confirmed = await confirmDeleteAction();
        if (!confirmed) return;

        deleteBtn.disabled = true;

        try {
            const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
            const token = csrfTokenMeta ? csrfTokenMeta.content : '';
            const res = await fetch(`/timeline/posts/${postId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                }
            });

            if (!res.ok) throw new Error();

            article.style.transition = 'all 0.3s ease';
            article.style.opacity = '0';
            article.style.transform = 'scale(0.95)';
            setTimeout(() => article.remove(), 300);

            showToast('Unggahan berhasil dihapus.');
        } catch (err) {
            deleteBtn.disabled = false;
            showToast('Gagal menghapus unggahan.');
        }
    }
});
