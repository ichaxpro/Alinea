import { showToast } from './toast.js';

document.addEventListener('DOMContentLoaded', () => {
    const reportModal = document.getElementById('report-post-modal');
    if (!reportModal) return;

    const reportPanel = document.getElementById('report-post-panel');
    const reportClose = document.getElementById('report-post-close');
    const reportCancel = document.getElementById('report-post-cancel');
    const reportBackdrop = document.getElementById('report-post-backdrop');
    const reportForm = document.getElementById('report-post-form');
    const reportIdInput = document.getElementById('report-post-id');
    const reportReason = document.getElementById('report-reason');
    const reportCounter = document.getElementById('report-reason-counter');
    const reportSubmit = document.getElementById('btn-submit-report');

    function closeReportModal() {
        reportModal.classList.add('opacity-0');
        reportPanel.classList.add('opacity-0');
        reportPanel.classList.remove('scale-100');
        reportPanel.classList.add('scale-95');
        setTimeout(() => {
            reportModal.classList.add('hidden');
        }, 300);
    }

    function openReportModal(postId) {
        reportIdInput.value = postId;
        reportReason.value = '';
        reportCounter.textContent = '0 karakter (min. 8)';
        reportSubmit.disabled = true;

        reportModal.classList.remove('hidden');
        void reportModal.offsetWidth;
        reportModal.classList.remove('opacity-0');
        reportPanel.classList.remove('opacity-0');
        reportPanel.classList.remove('scale-95');
        reportPanel.classList.add('scale-100');
    }

    reportClose?.addEventListener('click', closeReportModal);
    reportCancel?.addEventListener('click', closeReportModal);
    reportBackdrop?.addEventListener('click', closeReportModal);

    reportReason?.addEventListener('input', () => {
        const len = reportReason.value.length;
        reportCounter.textContent = `${len} karakter (min. 8)`;
        reportSubmit.disabled = len < 8;
    });

    reportForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const postId = reportIdInput.value;
        const reason = reportReason.value;
        const url = `/timeline/posts/${postId}/report`;

        reportSubmit.disabled = true;
        reportSubmit.textContent = 'Mengirim...';

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ reason })
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal melaporkan unggahan');

            showToast(data.message || 'Laporan berhasil dikirim');
            closeReportModal();
        } catch (err) {
            console.error(err);
            showToast(err.message);
        } finally {
            reportSubmit.disabled = false;
            reportSubmit.textContent = 'Kirim';
        }
    });

    document.addEventListener('click', async (e) => {
        const reportBtn = e.target.closest('[data-report-post-btn]');
        const unfollowBtn = e.target.closest('[data-unfollow-btn]');

        if (reportBtn) {
            e.preventDefault();
            const postId = reportBtn.dataset.postId;
            if (postId) openReportModal(postId);

            const dropdown = reportBtn.closest('[data-post-menu-dropdown]');
            if (dropdown) dropdown.classList.add('hidden');
        }

        if (unfollowBtn) {
            e.preventDefault();
            const userId = unfollowBtn.dataset.userId;
            if (!userId) return;

            const dropdown = unfollowBtn.closest('[data-post-menu-dropdown]');
            if (dropdown) dropdown.classList.add('hidden');

            unfollowBtn.disabled = true;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                const res = await fetch(`/u/${userId}/follow`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    }
                });

                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal berhenti mengikuti');

                showToast('Berhasil berhenti mengikuti.');

                const posts = document.querySelectorAll(`[data-unfollow-btn][data-user-id="${userId}"]`);
                posts.forEach(btn => {
                    const article = btn.closest('article');
                    if (article) {
                        article.style.transition = 'all 0.3s ease';
                        article.style.opacity = '0';
                        article.style.transform = 'scale(0.95)';
                        setTimeout(() => article.remove(), 300);
                    }
                });

            } catch (err) {
                console.error(err);
                showToast(err.message);
            } finally {
                unfollowBtn.disabled = false;
            }
        }
    });
});
