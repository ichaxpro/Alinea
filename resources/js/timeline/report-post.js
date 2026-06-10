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
                if (!res.ok) throw new Error(data.message || 'Gagal mengubah status mengikuti');

                showToast(data.following ? 'Berhasil mengikuti.' : 'Berhasil berhenti mengikuti.');

                // Update all buttons for this user
                const btns = document.querySelectorAll(`[data-unfollow-btn][data-user-id="${userId}"]`);
                btns.forEach(btn => {
                    const svg = btn.querySelector('svg');
                    const textSpan = btn.querySelector('.btn-text');
                    
                    if (data.following) {
                        if (svg) svg.innerHTML = '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="18" y1="8" x2="23" y2="13"></line><line x1="23" y1="8" x2="18" y2="13"></line>';
                        if (textSpan) textSpan.textContent = 'Berhenti mengikuti';
                    } else {
                        if (svg) svg.innerHTML = '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line>';
                        if (textSpan) textSpan.textContent = 'Ikuti';
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
