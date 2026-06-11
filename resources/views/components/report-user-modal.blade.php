<div id="report-user-modal" class="fixed inset-0 z-[100] hidden">
    <div id="report-user-backdrop" class="absolute inset-0 bg-black/40 backdrop-blur-sm cursor-pointer"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div id="report-user-panel"
             class="relative bg-white border-2 border-[#444] rounded-2xl w-full max-w-md max-h-[90vh] overflow-y-auto
                    transform scale-95 opacity-0 transition-all duration-300 pointer-events-auto">
            
            <button id="report-user-close" aria-label="Tutup"
                    class="absolute top-2 right-2 z-10 w-9 h-9 rounded-full flex items-center justify-center
                           text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer bg-white">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M4 4l12 12M16 4L4 16"/>
                </svg>
            </button>

            <div class="px-6 pb-6 pt-5">
                <h2 class="font-bold text-xl mb-1 flex items-center gap-2 text-[#222]">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-red-500"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    Laporkan Pengguna
                </h2>
                <p id="report-user-desc" class="text-xs text-gray-500 mb-6">Mohon beritahu kami mengapa pengguna ini melanggar pedoman komunitas Alinea.</p>

                <form id="report-user-form">
                    <input type="hidden" id="report-user-id" name="user_id" value="">
                    
                    <div class="mb-5">
                        <label for="report-user-reason" class="block text-xs font-bold text-[#444] mb-1.5 uppercase tracking-wider">
                            Alasan <span class="text-red-400">*</span>
                        </label>
                        <textarea id="report-user-reason" name="reason" required rows="4" minlength="8"
                                  placeholder="Tuliskan alasan minimal 8 karakter..."
                                  class="w-full border-[1.5px] border-gray-200 rounded-xl px-4 py-2.5 text-sm placeholder-gray-300 outline-none focus:border-[#444] transition-colors bg-[#FBFBFB] resize-y min-h-[80px]"></textarea>
                        <span id="report-user-reason-counter" class="block text-right text-[10px] text-gray-400 mt-1">0 karakter (min. 8)</span>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="button" id="report-user-cancel" class="flex-1 py-2.5 text-sm font-bold text-[#444] bg-white rounded-full border-[1.5px] border-gray-200 hover:border-[#444] hover:bg-gray-50 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="btn-submit-user-report" disabled
                                class="flex-1 py-2.5 text-sm font-bold text-[#444] bg-[#FFDDAF] rounded-full border-[1.5px] border-[#444]
                                       hover:-translate-y-[1px] hover:bg-[#ffcf90] transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:bg-[#FFDDAF]">
                            Kirim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('report-user-modal');
        if (!modal) return;
        
        const panel = document.getElementById('report-user-panel');
        const form = document.getElementById('report-user-form');
        const reasonInput = document.getElementById('report-user-reason');
        const counter = document.getElementById('report-user-reason-counter');
        const submitBtn = document.getElementById('btn-submit-user-report');
        const closeBtn = document.getElementById('report-user-close');
        const cancelBtn = document.getElementById('report-user-cancel');
        const backdrop = document.getElementById('report-user-backdrop');
        const descText = document.getElementById('report-user-desc');

        function closeReportUserModal() {
            panel.classList.remove('scale-100', 'opacity-100');
            panel.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                form.reset();
                updateCounter();
            }, 300);
        }

        window.openReportUserModal = function(userId, name) {
            document.getElementById('report-user-id').value = userId;
            descText.innerHTML = `Mohon beritahu kami mengapa <span class="font-bold text-[#444]">${name}</span> melanggar pedoman komunitas Alinea.`;
            modal.classList.remove('hidden');
            
            // Allow display:block to render before triggering transition
            setTimeout(() => {
                panel.classList.remove('scale-95', 'opacity-0');
                panel.classList.add('scale-100', 'opacity-100');
                reasonInput.focus();
            }, 10);
        };

        function updateCounter() {
            const len = reasonInput.value.length;
            counter.textContent = `${len} karakter (min. 8)`;
            if (len >= 8) {
                counter.classList.remove('text-red-500');
                counter.classList.add('text-gray-400');
                submitBtn.disabled = false;
            } else {
                if (len > 0) {
                    counter.classList.add('text-red-500');
                    counter.classList.remove('text-gray-400');
                } else {
                    counter.classList.remove('text-red-500');
                    counter.classList.add('text-gray-400');
                }
                submitBtn.disabled = true;
            }
        }

        reasonInput.addEventListener('input', updateCounter);
        closeBtn.addEventListener('click', closeReportUserModal);
        cancelBtn.addEventListener('click', closeReportUserModal);
        backdrop.addEventListener('click', closeReportUserModal);

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const userId = document.getElementById('report-user-id').value;
            const reason = reasonInput.value;
            
            if (reason.length < 8) return;
            
            submitBtn.disabled = true;
            const originalText = submitBtn.textContent;
            submitBtn.innerHTML = '<div class="w-4 h-4 border-2 border-[#444] border-t-transparent rounded-full animate-spin mx-auto"></div>';

            try {
                const res = await fetch(`/api/users/${userId}/report`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason })
                });

                if (res.ok) {
                    alert('Laporan telah dikirim. Terima kasih.');
                    closeReportUserModal();
                } else {
                    alert('Gagal mengirim laporan. Coba lagi.');
                }
            } catch (err) {
                alert('Terjadi kesalahan. Coba lagi.');
            } finally {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        });
    });
</script>
