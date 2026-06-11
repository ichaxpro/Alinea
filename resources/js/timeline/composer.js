import { escapeHtml } from './helpers.js';
import { showToast } from './toast.js';
import { createPostElement, bindMediaGalleries, bindPostActions } from './posts.js';

function renderSelectedMediaPreview(files) {
    if (!files || !files.length) {
        return '';
    }

    return files.map((file, index) => {
        const url = URL.createObjectURL(file);
        const isImage = file.type.startsWith('image/');
        const mediaHtml = isImage
            ? `<img src="${url}" class="w-full h-full object-cover rounded-xl" alt="Preview">`
            : `<video src="${url}" class="w-full h-full object-cover rounded-xl bg-gray-200" autoplay loop muted playsinline></video>`;

        return `
            <div class="relative w-24 h-24 flex-shrink-0 group">
                ${mediaHtml}
                <button type="button" data-remove-media="${index}" class="absolute top-1 right-1 w-6 h-6 bg-black/60 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity" aria-label="Hapus media">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
        `;
    }).join('');
}

export function initComposer({ feedPanel, csrfToken, currentUserName, currentUserAvatar }) {
    const composerPreview = document.getElementById('composer-media-preview') || document.querySelector('[data-composer-media-preview]');

    let selectedMediaFiles = [];

    const mediaInput = document.getElementById('composer-media');
    if (mediaInput) {
        const btnImage = document.querySelector('button[aria-label="Unggah gambar"]');
        const btnVideo = document.querySelector('button[aria-label="Unggah video"]');
        const btnFile = document.querySelector('button[aria-label="Lampirkan file"]');

        if (btnImage) btnImage.addEventListener('click', () => { mediaInput.accept = 'image/*'; mediaInput.click(); });
        if (btnVideo) btnVideo.addEventListener('click', () => { mediaInput.accept = 'video/*'; mediaInput.click(); });
        if (btnFile) btnFile.addEventListener('click', () => { mediaInput.accept = '*/*'; mediaInput.click(); });

        mediaInput.multiple = true;

        function bindRemoveButtons() {
            if (!composerPreview) return;
            composerPreview.querySelectorAll('[data-remove-media]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const index = parseInt(e.currentTarget.dataset.removeMedia, 10);
                    selectedMediaFiles.splice(index, 1);
                    composerPreview.innerHTML = renderSelectedMediaPreview(selectedMediaFiles);
                    if (selectedMediaFiles.length === 0) {
                        composerPreview.classList.add('hidden');
                    } else {
                        bindRemoveButtons();
                    }
                });
            });
        }

        mediaInput.addEventListener('change', async (e) => {
            if (mediaInput.files && mediaInput.files.length > 0) {
                const newFiles = Array.from(mediaInput.files);
                if (selectedMediaFiles.length + newFiles.length > 4) {
                    showToast('Maksimal 4 file (gambar/video) yang dapat diunggah.');
                    mediaInput.value = '';
                    return;
                }

                let validFiles = [];
                let hasError = false;
                for (const file of newFiles) {
                    const isVideo = file.type.startsWith('video/');
                    const maxSizeMB = isVideo ? 100 : 20;

                    if (file.size > maxSizeMB * 1024 * 1024) {
                        showToast(`File "${file.name}" terlalu besar (maks ${maxSizeMB}MB).`);
                        hasError = true;
                        continue;
                    }

                    if (isVideo) {
                        const isValidDuration = await new Promise(resolve => {
                            const video = document.createElement('video');
                            const objectUrl = window.URL.createObjectURL(file);
                            video.preload = 'metadata';
                            video.onloadedmetadata = () => {
                                window.URL.revokeObjectURL(video.src);
                                resolve(video.duration <= 180);
                            };
                            video.onerror = () => {
                                window.URL.revokeObjectURL(objectUrl);
                                resolve(true);
                            };
                            video.src = objectUrl;
                        });

                        if (!isValidDuration) {
                            showToast(`Video "${file.name}" lebih dari 3 menit tidak dapat diunggah.`);
                            continue;
                        }
                    }
                    validFiles.push(file);
                }

                selectedMediaFiles = selectedMediaFiles.concat(validFiles).slice(0, 4);

                if (composerPreview) {
                    composerPreview.innerHTML = renderSelectedMediaPreview(selectedMediaFiles);
                    composerPreview.classList.remove('hidden');
                    bindRemoveButtons();
                }

                if (typeof update === 'function') update();

                if (validFiles.length > 0) {
                    showToast(`${validFiles.length} file ditambahkan.`);
                } else if (!hasError) {
                    showToast('Gagal menambahkan file.');
                }

                mediaInput.value = '';
            }
        });
    }

    const tags = document.querySelectorAll('[data-composer-tag]');
    tags.forEach(tag => {
        tag.addEventListener('click', () => {
            tags.forEach(t => {
                t.classList.remove('border-[#444]', 'bg-[#FFDDAF]', 'text-[#444]');
                t.classList.add('border-gray-300', 'text-gray-500');
            });
            tag.classList.add('border-[#444]', 'bg-[#FFDDAF]', 'text-[#444]');
            tag.classList.remove('border-gray-300', 'text-gray-500');
        });
    });

    const composerBody = document.getElementById('composer-body');
    const charCounter = document.getElementById('char-counter');
    const kirimBtn = document.getElementById('kirim-btn');
    const MAX_CHARS = 500;

    if (composerBody) {
        const update = () => {
            composerBody.style.height = 'auto';
            const borderHeight = composerBody.offsetHeight - composerBody.clientHeight;
            composerBody.style.height = (composerBody.scrollHeight + borderHeight) + 'px';

            const len = composerBody.value.length;
            const over = len >= MAX_CHARS;
            if (charCounter) {
                charCounter.textContent = `${len}/${MAX_CHARS}`;
                charCounter.classList.toggle('text-red-500', over);
                charCounter.classList.toggle('text-gray-300', !over);
            }

            if (kirimBtn) {
                const hasText = !!composerBody.value.trim();
                const hasMedia = selectedMediaFiles && selectedMediaFiles.length > 0;
                const disabled = over || (!hasText && !hasMedia);
                kirimBtn.disabled = disabled;
                kirimBtn.classList.toggle('opacity-40', disabled);
                kirimBtn.classList.toggle('cursor-not-allowed', disabled);
                kirimBtn.classList.toggle('cursor-pointer', !disabled);
            }
        };

        composerBody.addEventListener('input', update);
    }

    const titleInput = document.getElementById('composer-title');
    const autocompleteDropdown = document.getElementById('composer-autocomplete-dropdown');
    const autocompleteList = document.getElementById('composer-autocomplete-list');
    let autocompleteTimeout = null;

    if (titleInput && autocompleteDropdown && autocompleteList) {
        titleInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            clearTimeout(autocompleteTimeout);

            if (query.length < 2) {
                autocompleteDropdown.classList.add('hidden');
                return;
            }

            autocompleteTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`/api/books/autocomplete?q=${encodeURIComponent(query)}`);
                    if (!res.ok) throw new Error();
                    const books = await res.json();

                    autocompleteList.innerHTML = '';
                    if (books.length === 0) {
                        autocompleteList.innerHTML = '<li class="px-4 py-2 text-sm text-gray-500">Tidak ditemukan.</li>';
                    } else {
                        books.forEach(book => {
                            const li = document.createElement('li');
                            li.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm flex items-center gap-3 border-b border-gray-100 last:border-0';

                            const imgSrc = book.cover_url ? book.cover_url : '/images/book.svg';
                            li.innerHTML = `
                                <img src="${imgSrc}" class="w-8 h-12 object-cover rounded shadow-sm flex-shrink-0" alt="Cover" onerror="this.src='/images/book.svg'">
                                <div>
                                    <div class="font-bold text-[#444]">${escapeHtml(book.judul)}</div>
                                    <div class="text-xs text-gray-500">${escapeHtml(book.penulis || '')}</div>
                                </div>
                            `;

                            li.addEventListener('click', () => {
                                titleInput.value = book.judul;
                                autocompleteDropdown.classList.add('hidden');
                            });

                            autocompleteList.appendChild(li);
                        });
                    }
                    autocompleteDropdown.classList.remove('hidden');
                } catch (e) {
                    autocompleteDropdown.classList.add('hidden');
                }
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!titleInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
                autocompleteDropdown.classList.add('hidden');
            }
        });
    }

    if (kirimBtn) {
        kirimBtn.addEventListener('click', async () => {
            const len = composerBody?.value.length ?? 0;
            const hasText = !!composerBody?.value.trim();
            const hasMedia = selectedMediaFiles.length > 0;
            
            if ((!hasText && !hasMedia) || len >= MAX_CHARS) return;

            const bodyText = composerBody.value;
            const activeTagBtn = Array.from(document.querySelectorAll('[data-composer-tag]')).find(b => b.classList.contains('bg-[#FFDDAF]'));
            const activeTag = activeTagBtn ? activeTagBtn.textContent.trim() : 'Post';

            const klubSelect = document.getElementById('composer-klub');
            let klubId = '';
            if (klubSelect && klubSelect.selectedIndex > 0) {
                klubId = klubSelect.value;
            }

            const titleInput = document.getElementById('composer-title');
            const titleVal = titleInput ? titleInput.value.trim() : '';

            const storeUrl = feedPanel?.dataset.postStoreUrl;
            if (!storeUrl) {
                showToast('Terjadi kesalahan URL.');
                return;
            }

            const originalText = kirimBtn.textContent;
            kirimBtn.disabled = true;
            kirimBtn.textContent = 'Mengunggah...';

            try {
                let response;
                if (selectedMediaFiles.length > 0) {
                    const fd = new FormData();
                    if (klubId) fd.append('id_klub', klubId);
                    fd.append('judul_buku_dibahas', titleVal);
                    fd.append('pesan', bodyText);
                    fd.append('tag', activeTag);

                    for (const file of selectedMediaFiles) {
                        if (file.type.startsWith('image/') && typeof browserImageCompression !== 'undefined') {
                            const options = {
                                maxSizeMB: 1,
                                maxWidthOrHeight: 1920,
                                useWebWorker: true
                            };
                            try {
                                const compressedFile = await browserImageCompression(file, options);
                                fd.append('media[]', compressedFile, file.name);
                            } catch (e) {
                                fd.append('media[]', file);
                            }
                        } else {
                            fd.append('media[]', file);
                        }
                    }

                    response = await fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: fd,
                    });
                } else {
                    response = await fetch(storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            id_klub: klubId,
                            judul_buku_dibahas: titleVal,
                            pesan: bodyText,
                            tag: activeTag,
                        }),
                    });
                }

                const result = await response.json();

                if (!response.ok) {
                    if (result.errors) {
                        const firstError = Object.values(result.errors)[0][0];
                        throw new Error(firstError);
                    }
                    throw new Error(result.message || 'Gagal menyimpan postingan.');
                }

                try {
                    const newPostEl = createPostElement(result.post, currentUserAvatar, currentUserName);
                    if (newPostEl && feedPanel) {
                        const existing = feedPanel.querySelector('article[data-post-id="' + result.post.id + '"]');
                        if (!existing) {
                            const activeFilters = Array.from(document.querySelectorAll('[data-klub-filter]'))
                                .filter(b => b.classList.contains('bg-[#FFDDAF]'))
                                .map(b => b.dataset.klubFilter);

                            if (activeFilters.length > 0 && !activeFilters.includes(result.post.klub)) {
                                newPostEl.style.display = 'none';
                            }

                            feedPanel.prepend(newPostEl);
                            bindPostActions(newPostEl, csrfToken);
                        } else {
                            showToast('Postingan sudah ada.');
                        }
                    }
                } catch (uiError) {
                    console.error('Error updating UI with new post:', uiError);
                }

                showToast(result.message || 'Postingan berhasil dikirim!');

                if (titleInput) titleInput.value = '';
                if (klubSelect) klubSelect.selectedIndex = 0;
                if (mediaInput) mediaInput.value = '';
                if (composerPreview) {
                    composerPreview.innerHTML = '';
                    composerPreview.classList.add('hidden');
                }
                selectedMediaFiles = [];

                composerBody.value = '';
                composerBody.style.height = 'auto';
                if (charCounter) {
                    charCounter.textContent = '0/' + MAX_CHARS;
                    charCounter.classList.remove('text-red-500');
                    charCounter.classList.add('text-gray-300');
                }
                kirimBtn.disabled = true;
                kirimBtn.classList.add('opacity-40', 'cursor-not-allowed');
                kirimBtn.classList.remove('cursor-pointer');
            } catch (error) {
                showToast(error.message || 'Gagal mengirim postingan.');
            } finally {
                kirimBtn.disabled = false;
                kirimBtn.textContent = originalText;
            }
        });
    }
}
