/**
 * timeline.js — Alinea Timeline Page Interactivity
 *
 * Handles: navbar visibility, back-to-top button, tab switching,
 * composer tags, post actions (like/bookmark/share), sidebar nav,
 * and toast notifications.
 */

document.addEventListener('DOMContentLoaded', () => {

    const feedPanel = document.getElementById('feed-panel');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const currentUserName = document.querySelector('meta[name="user-name"]')?.content ?? '';
    const currentUserAvatar = document.querySelector('meta[name="user-avatar-url"]')?.content ?? '';
    let selectedMediaFiles = [];

    function renderSelectedMediaPreview(files) {
        if (!files || !files.length) {
            return '';
        }

        return files.map((file, index) => {
            const url = URL.createObjectURL(file);
            const isImage = file.type.startsWith('image/');
            const mediaHtml = isImage
                ? `<img src="${url}" class="w-full h-full object-cover rounded-xl" alt="Preview">`
                : `<video src="${url}" class="w-full h-full object-cover rounded-xl" muted></video>`;

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

    const composerPreview = document.getElementById('composer-media-preview') || document.querySelector('[data-composer-media-preview]');

    // Wire composer media buttons to hidden file input
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
                for (const file of newFiles) {
                    const isVideo = file.type.startsWith('video/');
                    const maxSizeMB = isVideo ? 35 : 20;
                    
                    if (file.size > maxSizeMB * 1024 * 1024) {
                        showToast(`File "${file.name}" terlalu besar (maks ${maxSizeMB}MB).`);
                        continue;
                    }

                    if (isVideo) {
                        const isValidDuration = await new Promise(resolve => {
                            const video = document.createElement('video');
                            video.preload = 'metadata';
                            video.onloadedmetadata = () => {
                                window.URL.revokeObjectURL(video.src);
                                resolve(video.duration <= 60);
                            };
                            video.onerror = () => resolve(false);
                            video.src = window.URL.createObjectURL(file);
                        });

                        if (!isValidDuration) {
                            showToast(`Video "${file.name}" lebih dari 1 menit tidak dapat diunggah.`);
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

                showToast(validFiles.length ? `${validFiles.length} file ditambahkan.` : 'Gagal menambahkan file.');
                mediaInput.value = ''; 
            }
        });
    }

    // ── Navbar: visible only when scroll position is exactly 0 ──

    const navbar = document.getElementById('main-navbar');
    if (navbar) {
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => {
                navbar.classList.toggle('-translate-y-full', window.scrollY !== 0);
                ticking = false;
            });
        }, { passive: true });
    }

    // ── Back-to-top button: appears after 300px scroll ──

    const topBtn = document.getElementById('back-to-top');
    if (topBtn) {
        const show = ['opacity-100', 'pointer-events-auto', 'translate-y-0'];
        const hide = ['opacity-0', 'pointer-events-none', 'translate-y-4'];

        window.addEventListener('scroll', () => {
            const visible = window.scrollY > 300;
            topBtn.classList.remove(...(visible ? hide : show));
            topBtn.classList.add(...(visible ? show : hide));
        }, { passive: true });

        topBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // ── Feed tab switcher (For You / Following) ──

    const tabBtns = document.querySelectorAll('[data-tab-btn]');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const isFollowing = btn.id === 'tab-following';
            window.location.href = '?tab=' + (isFollowing ? 'mengikuti' : 'untukmu');
        });
    });

    // ── Profile tabs (Posts / Replies / Highlights / Media) ──

    const profileTabBtns = document.querySelectorAll('[data-profile-tab]');
    if (profileTabBtns.length) {
        const profilePanels = document.querySelectorAll('[data-profile-panel]');

        const setActiveProfileTab = (activeBtn) => {
            const activeTarget = activeBtn.dataset.profileTabTarget;

            profileTabBtns.forEach(btn => {
                const indicator = btn.querySelector('[data-profile-tab-indicator]');
                const isActive = btn === activeBtn;

                btn.classList.toggle('text-[#111]', isActive);
                btn.classList.toggle('text-gray-400', !isActive);
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');

                if (indicator) {
                    indicator.classList.toggle('hidden', !isActive);
                }
            });

            profilePanels.forEach(panel => {
                panel.classList.toggle('hidden', panel.dataset.profilePanel !== activeTarget);
            });
        };

        const initiallyActive = Array.from(profileTabBtns).find(btn => btn.getAttribute('aria-selected') === 'true') ?? profileTabBtns[0];
        setActiveProfileTab(initiallyActive);

        profileTabBtns.forEach(btn => {
            btn.addEventListener('click', () => setActiveProfileTab(btn));
        });
    }

    // -- Following / Follower Modal --

    const modalOverlay = document.getElementById('follow-modal-overlay');
    const modal = document.getElementById('follow-modal');
    const modalBody = document.getElementById('follow-modal-body');
    const modalClose = document.getElementById('follow-modal-close');
    const followingTrigger = document.getElementById('profile-following-trigger');
    const followersTrigger = document.getElementById('profile-followers-trigger');
    const tabFollowing = document.getElementById('follow-tab-following');
    const tabFollowers = document.getElementById('follow-tab-followers');
    let followModalActiveTab = 'following';

    function openFollowModal(tab) {
        if (!modalOverlay || !modal) return;
        followModalActiveTab = tab;
        modalOverlay.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-0', 'translate-y-4');
        document.body.style.overflow = 'hidden';
        activateFollowTab(tab);
        loadFollowList(tab);
    }

    function closeFollowModal() {
        if (!modalOverlay || !modal) return;
        modalOverlay.classList.add('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-0', 'translate-y-4');
        document.body.style.overflow = '';
    }

    function activateFollowTab(tab) {
        const tabs = [tabFollowing, tabFollowers];
        tabs.forEach(btn => {
            if (!btn) return;
            const isActive = btn.dataset.followTab === tab;
            btn.classList.toggle('text-[#111]', isActive);
            btn.classList.toggle('text-gray-400', !isActive);
            const indicator = btn.querySelector('span');
            if (indicator) {
                indicator.classList.toggle('bg-[#5DA9FF]', isActive);
                indicator.classList.toggle('bg-transparent', !isActive);
            }
        });
    }

    function renderFollowUser(user) {
        const initial = (user.name || 'U').charAt(0).toUpperCase();
        const avatarHtml = user.avatar_url ? `<img src="${user.avatar_url}" alt="${escapeHtml(user.name)}" class="w-full h-full object-cover">` : `<span class="text-xs font-bold text-[#444]">${initial}</span>`;

        const profileUrl = `/u/${encodeURIComponent(user.username || '')}`;

        let actionBtn = '';
        const isAuth = document.querySelector('meta[name="user-auth"]')?.content === 'true';
        if (isAuth) {
            const currentUserId = document.querySelector('meta[name="user-id"]')?.content;
            const isOwnProfile = currentUserId === String(user.id);
            if (!isOwnProfile) {
                actionBtn = `<button type="button"
                    data-modal-follow-btn
                    data-user-id="${user.id}"
                    data-following="${user.is_following ? 'true' : 'false'}"
                    class="ml-auto shrink-0 px-4 py-1.5 rounded-full text-xs font-bold border-[1.5px] border-[#444] transition-colors cursor-pointer ${
                        user.is_following
                            ? 'bg-[#444] text-white'
                            : 'bg-[#FFDDAF] hover:bg-[#ffcf90]'
                }">
                    ${user.is_following ? 'Mengikuti' : 'Pengikut'}
                </button>
                `
            }
        }

        return `<div class="flex items-center gap-3 py-3 border-b border-gray-100 last:border-b-0">
            <a href="${profileUrl}" class="w-10 h-10 rounded-full border-2 border-[#444] overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center flex-shrink-0 hover:opacity-80 transition-opacity">
                ${avatarHtml}
            </a>
            <a href="${profileUrl}" class="flex-1 min-w-0 hover:opacity-80 transition-opacity">
                <div class="font-bold text-sm text-[#222] leading-tight truncate">${escapeHtml(user.name)}</div>
                <div class="text-xs text-gray-400 truncate">${user.username ? '@' + escapeHtml(user.username) : 'tanpa_username'}</div>
            </a>
            ${actionBtn}
        </div>
        `
    }

    async function loadFollowList(tab) {
        if (!modalBody) return;
        
        modalBody.innerHTML = `<div class="flex items-center justify-center h-32">
            <div class="w-6 h-6 border-2 border-[#444] border-t-transparent rounded-full animate-spin"></div>
        </div>`;

        let url;
        const userId = followingTrigger?.dataset?.userId;
        if (!userId) {
            modalBody.innerHTML = `<p class="text-center text-gray-400 py-8">Gagal memuat data</p>`
            return;
        }

        if (tab === 'following') {
            url = `/u/${userId}/following`;
        } else {
            url = `/u/${userId}/followers`;
        }

        try {
            const resp = await fetch(url, {
                headers: {'Accept': 'application/json'},
            });
            if (!resp.ok) throw new Error('Failed to load');
            const result = await resp.json();
            const users = Array.isArray(result.users) ? result.users : [];

            if (users.length === 0) {
                const msg = tab === 'following' ? 'Belum mengikuti siapa pun.' : 'Belum ada pengikut.';
                modalBody.innerHTML = `<p class="text-center text-gray-400 py-8">${msg}</p>`;
                return;
            }

            modalBody.innerHTML = users.map(renderFollowUser).join('');
            bindModalFollowButtons();
        } catch (err) {
            modalBody.innerHTML = `<p class="text-center text-red-400 py-8">Gagal memuat data.</p>`;
        }
    }

    function bindModalFollowButtons() {
        document.querySelectorAll('[data-modal-follow-btn]').forEach(btn => {
            if (btn.dataset.bound === 'true') return;
            btn.dataset.bound = 'true';

            btn.addEventListener('click', async () => {
                const userId = btn.dataset.userId;
                const currentlyFollowing = btn.dataset.following === 'true';

                btn.disabled = true;
                btn.textContent = '...';

                const followUrl = `/u/${userId}/follow`;

                try {
                    const resp = await fetch(followUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        },
                    });
                    const result = await resp.json();
                    if (!resp.ok) throw new Error(result.message || 'Gagal');

                    const nowFollowing = result.following;
                    btn.dataset.following = nowFollowing ? 'true' : 'false';
                    btn.textContent = nowFollowing ? 'Following' : 'Follow';
                    btn.className = 'ml-auto shrink-0 px-4 py-1.5 rounded-full text-xs font-bold border-[1.5px] border-[#444] transition-colors cursor-pointer ' + 
                        (nowFollowing ? 'bg-[#444] text-white' : 'bg-[#FFDDAF] hover:bg-[#ffcf90]');

                    if (result.followers_count !== undefined) {
                        const followerTrigger = document.getElementById('profile-followers-trigger');
                        if (followerTrigger) {
                            const span = followerTrigger.querySelector('span.font-bold');
                            if (span) span.textContent = result.followers_count;
                        }
                    }
                    loadFollowList(followModalActiveTab);
                } catch (err) {
                    showToast(err.message);
                } finally {
                    btn.disabled = false;
                }
            });
        });
    }

    if (followingTrigger) {
        followingTrigger.addEventListener('click', () => openFollowModal('following'));
    }
    if (followersTrigger) {
        followersTrigger.addEventListener('click', () => openFollowModal('followers'));
    }
    if (modalClose) {
        modalClose.addEventListener('click', closeFollowModal);
    }
    if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) closeFollowModal();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeFollowModal();
    });

    if (tabFollowing) {
        tabFollowing.addEventListener('click', () => {
            followModalActiveTab = 'following';
            activateFollowTab('following');
            loadFollowList('following');
        });
    }
    if (tabFollowers) {
        tabFollowers.addEventListener('click', () => {
            followModalActiveTab = 'followers';
            activateFollowTab('followers');
            loadFollowList('followers');
        });
    }

    // ── Composer category pill selector ──

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

    // ── Like toggle ──

    bindPostActions(document);

    // Follow Button
    const followBtn = document.getElementById('follow-btn');
    if (followBtn) {
        followBtn.addEventListener('click', async () => {
            const url = followBtn.dataset.followUrl;
            followBtn.disabled = true;
            followBtn.textContent = '...';

            try {
                const resp = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                });
                const result = await resp.json();
                if (!resp.ok) throw new Error(result.message || 'Gagal');

                const nowFollowing = result.following;
                followBtn.dataset.following = nowFollowing ? 'true' : 'false';
                followBtn.textContent = nowFollowing ? 'Following' : 'Follow';
                followBtn.className = 'ml-auto px-5 py-2 rounded-full text-sm font-bold border-2 border-text transition-colors cursor-pointer ' + (nowFollowing ? 'bg-[#444] text-white' : 'bg-[#FFDDAF] hover:bg-[#FFCF90]');

                const text = document.querySelector('.text-sm.text-gray-500.mt-2');
                if (text && result.followers_count !== undefined) {
                    text.innerHTML = `<span class="font-bold text-[#222]">${followBtn.dataset.followingCount}</span> Following <span class="mx-2">|</span> <span class="font-bold text-[#222]">${result.followers_count}</span> Followers`;
                }
            } catch (err) {
                showToast(err.message);
            } finally {
                followBtn.disabled = false;
            }
        })
    }

    // ── Bookmark toggle ──

    // ── Share ──

    // ── Composer: auto-grow textarea + character counter + button state ──

    const composerBody = document.getElementById('composer-body');
    const charCounter = document.getElementById('char-counter');
    const kirimBtn = document.getElementById('kirim-btn');
    const MAX_CHARS = 500;

    if (composerBody) {
        const update = () => {
            // Auto-grow
            composerBody.style.height = 'auto';
            composerBody.style.height = composerBody.scrollHeight + 'px';

            // Character counter
            const len = composerBody.value.length;
            const over = len >= MAX_CHARS;
            if (charCounter) {
                charCounter.textContent = `${len}/${MAX_CHARS}`;
                charCounter.classList.toggle('text-red-500', over);
                charCounter.classList.toggle('text-gray-300', !over);
            }

            // Disable button when over limit or empty
            if (kirimBtn) {
                const disabled = over || !composerBody.value.trim();
                kirimBtn.disabled = disabled;
                kirimBtn.classList.toggle('opacity-40', disabled);
                kirimBtn.classList.toggle('cursor-not-allowed', disabled);
                kirimBtn.classList.toggle('cursor-pointer', !disabled);
            }
        };

        composerBody.addEventListener('input', update);
    }

    // ── Autocomplete Book Search ──
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
                                <img src="${imgSrc}" class="w-8 h-12 object-cover rounded shadow-sm flex-shrink-0" alt="Cover">
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

        // Hide on outside click
        document.addEventListener('click', (e) => {
            if (!titleInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
                autocompleteDropdown.classList.add('hidden');
            }
        });
    }

    // ── Composer submit ──

    if (kirimBtn) {
        kirimBtn.addEventListener('click', async () => {
            const len = composerBody?.value.length ?? 0;
            if (!composerBody?.value.trim() || len >= MAX_CHARS) return;

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

            kirimBtn.disabled = true;

            try {
                let response;
                if (selectedMediaFiles.length > 0) {
                    const fd = new FormData();
                    if (klubId) fd.append('id_klub', klubId);
                    fd.append('judul_buku_dibahas', titleVal);
                    fd.append('pesan', bodyText);
                    fd.append('tag', activeTag);
                    
                    // Compress images
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
                                fd.append('media[]', file); // Fallback to original
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
                    const newPostEl = createPostElement(result.post);
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
                            bindPostActions(newPostEl);
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
            }
        });
    }

    // ── Left sidebar nav highlight ──

    const navBtns = document.querySelectorAll('[data-sidenav]');
    navBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Navigate to timeline_home if Beranda is clicked
            if (btn.id === 'sidenav-beranda') {
                window.location.href = '/timeline_home';
                return;
            }else if (btn.id === 'sidenav-profil') {
                window.location.href = '/timeline_profile';
                return;
            }

            navBtns.forEach(b => {
                b.classList.remove('bg-[#FFDDAF]', 'text-[#444]', 'font-semibold');
                b.classList.add('text-gray-500');
            });
            btn.classList.add('bg-[#FFDDAF]', 'text-[#444]', 'font-semibold');
            btn.classList.remove('text-gray-500');
        });
    });

    // ── Helpers ──

    function formatCount(n) {
        if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
        if (n >= 1_000)     return (n / 1_000).toFixed(0) + 'K';
        return String(n);
    }

    function renderAvatarHtml(data) {
        const initial = escapeHtml(String(data?.name || 'U').charAt(0).toUpperCase() || 'U');
        if (data?.avatar_url) {
            return '<img src="' + data.avatar_url + '" alt="Avatar ' + escapeHtml(data?.name || 'Pengguna') + '" class="w-full h-full object-cover" />';
        }

        return '<span class="text-xs font-bold text-[#444]">' + initial + '</span>';
    }

    function buildAttachmentGallery(attachments, options = {}) {
        const items = Array.isArray(attachments) ? attachments.filter(Boolean) : [];
        const imageItems = items.filter(item => item.type === 'image');
        const otherItems = items.filter(item => item.type !== 'image');

            // Render each image using the same single-image styling (stacked), so multi-image posts look like single-image posts
            const imageSlides = imageItems.map((item, index) => {
                const caption = item.original_name ? escapeHtml(item.original_name) : 'Lampiran gambar ' + (index + 1);
                return '<div class="mb-3"><img src="' + item.url + '" data-media-url="' + item.url + '" data-media-type="image" alt="' + caption + '" class="w-full max-w-[560px] h-auto object-contain rounded-2xl shadow-sm mx-auto cursor-pointer hover:opacity-90 transition-opacity" /></div>';
            }).join('');

        const nonImageHtml = otherItems.map(item => {
            if (item.type === 'video') {
                return '<div class="mt-3"><video src="' + item.url + '" data-media-url="' + item.url + '" data-media-type="video" controls class="w-full h-auto max-h-[560px] rounded-2xl cursor-pointer hover:opacity-90 transition-opacity"></video></div>';
            }

            return '<div class="mt-3 text-sm"><a href="' + item.url + '" class="underline">' + escapeHtml(item.original_name || 'Unduh file') + '</a></div>';
        }).join('');

        if (!imageItems.length && !otherItems.length) return '';

        // For consistent design, always render images stacked with the single-image styling
        return '<div class="mb-3">' + imageSlides + nonImageHtml + '</div>';

        const trackId = 'media-track-' + Math.random().toString(36).slice(2, 10);
        const counterId = 'media-count-' + Math.random().toString(36).slice(2, 10);

        return '<div class="mb-3" data-media-gallery data-media-gallery-count="' + imageItems.length + '">' +
            '<div class="relative">' +
                '<button type="button" data-gallery-prev aria-label="Sebelumnya" class="absolute left-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/90 border border-gray-200 shadow flex items-center justify-center text-[#444] hover:bg-white">' +
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"></path></svg>' +
                '</button>' +
                '<button type="button" data-gallery-next aria-label="Berikutnya" class="absolute right-2 top-1/2 -translate-y-1/2 z-10 w-9 h-9 rounded-full bg-white/90 border border-gray-200 shadow flex items-center justify-center text-[#444] hover:bg-white">' +
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"></path></svg>' +
                '</button>' +
                '<div data-media-track id="' + trackId + '" class="flex gap-3 overflow-x-auto snap-x snap-mandatory scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">' +
                    imageSlides +
                '</div>' +
                '<div class="absolute bottom-2 right-2 rounded-full bg-black/60 px-2.5 py-1 text-[11px] font-semibold text-white" data-media-counter id="' + counterId + '">1/' + imageItems.length + '</div>' +
            '</div>' +
            nonImageHtml +
        '</div>';
    }

    function bindMediaGalleries(scope) {
        scope.querySelectorAll('[data-media-gallery]').forEach(gallery => {
            if (gallery.dataset.bound === 'true') return;
            gallery.dataset.bound = 'true';

            const track = gallery.querySelector('[data-media-track]');
            const counter = gallery.querySelector('[data-media-counter]');
            const prevBtn = gallery.querySelector('[data-gallery-prev]');
            const nextBtn = gallery.querySelector('[data-gallery-next]');
            const count = parseInt(gallery.dataset.mediaGalleryCount || '1', 10);

            if (!track) return;

            const updateCounter = () => {
                if (!counter) return;
                const slideWidth = track.clientWidth || 1;
                const index = Math.min(count, Math.max(1, Math.round(track.scrollLeft / slideWidth) + 1));
                counter.textContent = index + '/' + count;
            };

            prevBtn?.addEventListener('click', () => {
                track.scrollBy({ left: -(track.clientWidth || 1), behavior: 'smooth' });
            });

            nextBtn?.addEventListener('click', () => {
                track.scrollBy({ left: (track.clientWidth || 1), behavior: 'smooth' });
            });

            track.addEventListener('scroll', () => window.requestAnimationFrame(updateCounter), { passive: true });
            updateCounter();
        });
    }

    function renderCommentMediaHtml(comment) {
        const attachments = Array.isArray(comment?.attachments) && comment.attachments.length
            ? comment.attachments
            : (comment?.media_url ? [{ url: comment.media_url, type: comment.media_type, original_name: comment.media_original_name }] : []);

        if (!attachments.length) return '';

        return '<div class="mt-3">' + buildAttachmentGallery(attachments) + '</div>';
    }
    bindMediaGalleries(document);

    function renderCommentActionsHtml(comment) {
        const liked = comment.liked === true;
        const count = comment.likes_label || '0';
        return '<div class="flex items-center gap-3 mt-3 pt-3 border-t border-gray-100">' +
            '<button type="button" data-comment-love data-comment-id="' + escapeHtml(comment.id) + '" data-base="' + escapeHtml(comment.likes_base || 0) + '" aria-pressed="' + (liked ? 'true' : 'false') + '" class="inline-flex items-center gap-1.5 text-xs font-medium ' + (liked ? 'text-red-500' : 'text-gray-400 hover:text-red-500') + ' transition-colors cursor-pointer">' +
                '<svg width="15" height="15" viewBox="0 0 24 24" fill="' + (liked ? 'currentColor' : 'none') + '" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>' +
                '<span data-comment-love-label>' + escapeHtml(count) + '</span>' +
            '</button>' +
        '</div>';
    }

    function renderCommentItem(comment) {
        return '<div class="flex gap-3 rounded-xl bg-gray-50 p-3 border border-gray-100">' +
            '<div class="w-8 h-8 rounded-full border border-[#444] overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center flex-shrink-0">' +
                renderAvatarHtml(comment) +
            '</div>' +
            '<div class="min-w-0 flex-1">' +
                '<div class="flex flex-wrap items-center gap-2 text-xs text-gray-400 mb-1">' +
                    '<span class="font-semibold text-gray-700 whitespace-nowrap">' + escapeHtml(comment.name || 'Pengguna') + '</span>' +
                    '<span class="whitespace-nowrap">' + escapeHtml(comment.handle || '@pengguna') + '</span>' +
                    '<span class="whitespace-nowrap">•</span>' +
                    '<span class="whitespace-nowrap" title="' + escapeHtml(comment.absolute_time || '') + '">' + escapeHtml(comment.time || 'Baru saja') + '</span>' +
                '</div>' +
                '<p class="text-sm text-gray-600 leading-relaxed break-words">' + escapeHtml(comment.body || '') + '</p>' +
                renderCommentMediaHtml(comment) +
                renderCommentActionsHtml(comment) +
            '</div>' +
        '</div>';
    }

    async function loadComments(panel) {
        if (!panel || panel.dataset.commentsLoaded === 'true') return;

        const list = panel.querySelector('[data-comment-list]');
        const url = panel.dataset.commentsUrl;
        if (!list || !url) return;

        panel.dataset.commentsLoaded = 'loading';
        list.innerHTML = '<div class="text-sm text-gray-400">Memuat komentar...</div>';

        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
            });

            if (!response.ok) throw new Error('Failed to load comments');

            const result = await response.json();
            const comments = Array.isArray(result.comments) ? result.comments : [];

            list.innerHTML = comments.length
                ? comments.map(renderCommentItem).join('')
                : '<div class="text-sm text-gray-400">Belum ada komentar. Jadilah yang pertama.</div>';

            bindPostActions(list);
            panel.dataset.commentsLoaded = 'true';
        } catch (error) {
            panel.dataset.commentsLoaded = 'false';
            list.innerHTML = '<div class="text-sm text-red-500">Gagal memuat komentar.</div>';
        }
    }

    async function submitComment(form) {
        const panel = form.closest('[data-comments-panel]');
        const input = form.querySelector('[data-comment-input]');
        const submitBtn = form.querySelector('[data-comment-submit]');
        const mediaInput = form.querySelector('[data-comment-media-input]');
        const mediaLabel = form.querySelector('[data-comment-media-label]');
        const url = panel?.dataset.commentsStoreUrl;

        if (!panel || !input || !submitBtn || !url) return;

        const commentText = input.value.trim();
        if (!commentText) return;

        const isAuthenticated = document.querySelector('meta[name="user-auth"]')?.content === 'true';
        if (!isAuthenticated) {
            showToast('Silakan login untuk mengirim komentar.');
            return;
        }

        submitBtn.disabled = true;

        try {
            const mediaFiles = mediaInput?.files ? Array.from(mediaInput.files) : [];
            let response;

            if (mediaFiles.length > 0) {
                const formData = new FormData();
                formData.append('isi_komentar', commentText);
                mediaFiles.forEach(file => {
                    formData.append('media[]', file);
                });

                response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: formData,
                });
            } else {
                response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ isi_komentar: commentText }),
                });
            }

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result?.message || 'Failed to submit comment');
            }

            const list = panel.querySelector('[data-comment-list]');
            if (list) {
                if (list.textContent.includes('Belum ada komentar')) {
                    list.innerHTML = '';
                }
                list.insertAdjacentHTML('beforeend', renderCommentItem(result.comment));
                bindPostActions(list);
            }

            const countEl = panel.closest('article')?.querySelector('[data-comment-count]');
            if (countEl) {
                countEl.textContent = formatCount(parseInt(result.comments_count ?? countEl.textContent, 10));
            }

            input.value = '';
            input.style.height = 'auto';
            if (mediaInput) {
                mediaInput.value = '';
            }
            if (mediaLabel) {
                mediaLabel.textContent = '';
            }
            showToast(result.message || 'Komentar berhasil dikirim.');
        } catch (error) {
            showToast(error.message || 'Gagal mengirim komentar.');
        } finally {
            submitBtn.disabled = false;
        }
    }

    function bindPostActions(scope) {
        scope.querySelectorAll('[data-comment-toggle]').forEach(btn => {
            if (btn.dataset.bound === 'true') return;
            btn.dataset.bound = 'true';
            btn.addEventListener('click', async () => {
                const article = btn.closest('article[data-post-id]');
                const panel = article?.querySelector('[data-comments-panel]');
                if (!panel) return;

                const willOpen = panel.classList.contains('hidden');
                panel.classList.toggle('hidden');

                if (willOpen) {
                    await loadComments(panel);
                    panel.querySelector('[data-comment-input]')?.focus();
                }
            });
        });

        scope.querySelectorAll('[data-like-btn]').forEach(btn => {
            if (btn.dataset.bound === 'true') return;
            btn.dataset.bound = 'true';
            btn.addEventListener('click', async () => {
                const liked = btn.dataset.liked === 'true';
                const heart = btn.querySelector('path');
                const count = btn.querySelector('[data-like-count]');
                const article = btn.closest('article[data-post-id]');
                const postId = article?.dataset.postId;

                if (!postId) return;

                // Optimistic UI
                btn.dataset.liked = liked ? 'false' : 'true';
                btn.setAttribute('aria-pressed', btn.dataset.liked);
                btn.classList.toggle('text-red-500', !liked);
                btn.classList.toggle('text-gray-400', liked);
                if (heart) heart.setAttribute('fill', liked ? 'none' : 'currentColor');
                if (count) count.textContent = formatCount(parseInt(btn.dataset.base) + (liked ? -1 : 1));

                try {
                    let likeUrl = `/timeline_home/posts/${postId}/like`;

                    const res = await fetch(likeUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        }
                    });
                    
                    if (!res.ok) throw new Error();
                    const data = await res.json();
                    
                    // Sync with server data
                    btn.dataset.liked = data.liked ? 'true' : 'false';
                    btn.setAttribute('aria-pressed', btn.dataset.liked);
                    btn.classList.toggle('text-red-500', data.liked);
                    btn.classList.toggle('text-gray-400', !data.liked);
                    if (heart) heart.setAttribute('fill', data.liked ? 'currentColor' : 'none');
                    if (count) count.textContent = formatCount(data.likes_base);
                    btn.dataset.base = data.likes_base;

                } catch (e) {
                    // Revert Optimistic UI
                    btn.dataset.liked = liked ? 'true' : 'false';
                    btn.setAttribute('aria-pressed', btn.dataset.liked);
                    btn.classList.toggle('text-red-500', liked);
                    btn.classList.toggle('text-gray-400', !liked);
                    if (heart) heart.setAttribute('fill', liked ? 'currentColor' : 'none');
                    if (count) count.textContent = formatCount(btn.dataset.base);
                    showToast('Gagal menyukai unggahan.');
                }
            });
        });

        scope.querySelectorAll('[data-bookmark-btn]').forEach(btn => {
            if (btn.dataset.bound === 'true') return;
            btn.dataset.bound = 'true';
            btn.addEventListener('click', async () => {
                const article = btn.closest('article[data-post-id]');
                const postId = article?.dataset.postId;
                if (!postId) return;

                const active = btn.getAttribute('aria-pressed') === 'true';
                
                // Optimistic UI
                btn.setAttribute('aria-pressed', !active);
                btn.classList.toggle('text-yellow-500', !active);
                btn.classList.toggle('text-gray-400', active);
                const path = btn.querySelector('path');
                if (path) path.setAttribute('fill', !active ? 'currentColor' : 'none');

                try {
                    const res = await fetch(`/timeline/posts/${postId}/bookmark`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        }
                    });
                    
                    if (!res.ok) throw new Error();
                    const data = await res.json();
                    
                    btn.setAttribute('aria-pressed', data.bookmarked);
                    btn.classList.toggle('text-yellow-500', data.bookmarked);
                    btn.classList.toggle('text-gray-400', !data.bookmarked);
                    if (path) path.setAttribute('fill', data.bookmarked ? 'currentColor' : 'none');
                } catch (e) {
                    // Revert Optimistic UI
                    btn.setAttribute('aria-pressed', active);
                    btn.classList.toggle('text-yellow-500', active);
                    btn.classList.toggle('text-gray-400', !active);
                    if (path) path.setAttribute('fill', active ? 'currentColor' : 'none');
                    showToast('Gagal menyimpan postingan.');
                }
            });
        });

        scope.querySelectorAll('[data-share-btn]').forEach(btn => {
            if (btn.dataset.bound === 'true') return;
            btn.dataset.bound = 'true';
            btn.addEventListener('click', () => {
                if (navigator.share) {
                    navigator.share({ title: 'Alinea', url: location.href });
                } else {
                    navigator.clipboard.writeText(location.href)
                        .then(() => showToast('Tautan disalin ke clipboard!'));
                }
            });
        });

        scope.querySelectorAll('[data-comment-form]').forEach(form => {
            if (form.dataset.bound === 'true') return;
            form.dataset.bound = 'true';
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                await submitComment(form);
            });
        });

        scope.querySelectorAll('[data-comment-input]').forEach(input => {
            if (input.dataset.bound === 'true') return;
            input.dataset.bound = 'true';
            input.addEventListener('input', () => {
                input.style.height = 'auto';
                input.style.height = input.scrollHeight + 'px';
            });
        });

        scope.querySelectorAll('[data-comment-media-input]').forEach(input => {
            if (input.dataset.bound === 'true') return;
            input.dataset.bound = 'true';

            input.addEventListener('change', () => {
                const label = input.closest('form')?.querySelector('[data-comment-media-label]');
                if (label) {
                    const files = input.files ? Array.from(input.files) : [];
                    label.textContent = files.length > 0 ? (files.length + ' file terpilih') : '';
                }
            });
        });

        scope.querySelectorAll('[data-comment-media-trigger]').forEach(btn => {
            if (btn.dataset.bound === 'true') return;
            btn.dataset.bound = 'true';

            btn.addEventListener('click', () => {
                const form = btn.closest('form');
                const input = form?.querySelector('[data-comment-media-input]');
                if (!input) return;

                const mode = btn.dataset.commentMediaTrigger || 'file';
                if (mode === 'image') {
                    input.accept = 'image/*';
                } else if (mode === 'video') {
                    input.accept = 'video/*';
                } else {
                    input.accept = '*/*';
                }

                input.click();
            });
        });

        scope.querySelectorAll('[data-comment-love]').forEach(btn => {
            if (btn.dataset.bound === 'true') return;
            btn.dataset.bound = 'true';

            btn.addEventListener('click', async () => {
                const commentId = btn.dataset.commentId;
                if (!commentId) return;

                const liked = btn.getAttribute('aria-pressed') === 'true';
                const label = btn.querySelector('[data-comment-love-label]');
                const path = btn.querySelector('path');
                
                // Optimistic update
                btn.setAttribute('aria-pressed', liked ? 'false' : 'true');
                btn.classList.toggle('text-red-500', !liked);
                btn.classList.toggle('text-gray-400', liked);
                if (path) path.setAttribute('fill', liked ? 'none' : 'currentColor');
                
                let base = parseInt(btn.dataset.base || '0', 10);
                if (label) {
                    label.textContent = formatCount(base + (liked ? -1 : 1));
                }

                try {
                    let likeUrl = `/timeline_home/comments/${commentId}/like`;
                    // Adjust URL if needed depending on environment, assuming global route works
                    
                    const res = await fetch(likeUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        }
                    });
                    
                    if (!res.ok) throw new Error();
                    const data = await res.json();
                    
                    btn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
                    btn.classList.toggle('text-red-500', data.liked);
                    btn.classList.toggle('text-gray-400', !data.liked);
                    if (path) path.setAttribute('fill', data.liked ? 'currentColor' : 'none');
                    if (label) label.textContent = data.likes_label;
                    btn.dataset.base = data.likes_base;

                } catch (e) {
                    btn.setAttribute('aria-pressed', liked ? 'true' : 'false');
                    btn.classList.toggle('text-red-500', liked);
                    btn.classList.toggle('text-gray-400', !liked);
                    if (path) path.setAttribute('fill', liked ? 'currentColor' : 'none');
                    if (label) label.textContent = formatCount(base);
                    showToast('Gagal menyukai komentar.');
                }
            });
        });
    }

    function createPostElement(post) {
        if (!post) return null;

        const article = document.createElement('article');
        article.className = 'bg-white border-[1.5px] border-[#444] rounded-2xl p-5 hover:bg-gray-50 transition-colors animate-fade-in-down';
        article.dataset.postKlub = post.klub || '';
        if (post.id) {
            article.dataset.postId = String(post.id);
        }

        const bookTag = post.book
            ? '<div class="inline-flex items-center bg-[#FFDDAF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold">' + escapeHtml(post.book) + '</div>'
            : '';

        const klubTag = post.klub
            ? '<div class="inline-flex items-center bg-[#C7E7FF] border-[1.5px] border-[#444] rounded-full px-3.5 py-0.5 text-xs font-bold text-[#444]">👥 ' + escapeHtml(post.klub) + '</div>'
            : '';

        const tagsHtml = (bookTag || klubTag)
            ? '<div class="flex flex-wrap gap-2 mb-3">' + bookTag + klubTag + '</div>'
            : '';

        const attachments = Array.isArray(post.attachments) && post.attachments.length
            ? post.attachments
            : (post.media_url ? [{ url: post.media_url, type: post.media_type, original_name: post.media_original_name }] : []);

        const mediaHtml = buildAttachmentGallery(attachments);

        article.innerHTML =
            '<div class="flex items-center gap-3 mb-3 justify-between">' +
                '<div class="w-11 h-11 rounded-full border-2 border-[#444] flex-shrink-0 overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center">' + renderAvatarHtml(post) + '</div>' +
                '<div class="flex-1">' +
                    '<span class="font-bold text-[15px] leading-tight">' + escapeHtml(post.name || 'Pengguna') + '</span>' +
                    '<div class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400">' +
                        '<span class="whitespace-nowrap">' + escapeHtml(post.handle || '@pengguna') + '</span>' +
                        '<span class="text-gray-200">•</span>' +
                        '<span class="flex items-center gap-1 whitespace-nowrap">' +
                            '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
                                '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>' +
                            '</svg>' +
                            escapeHtml(post.location || 'Online') +
                        '</span>' +
                        '<span class="text-gray-200">•</span>' +
                        '<span class="whitespace-nowrap" title="' + escapeHtml(post.absolute_time || '') + '">' + escapeHtml(post.time || 'Baru saja') + '</span>' +
                    '</div>' +
                '</div>' +
                '<div class="bg-[#fff176] border-2 inline-flex items-center rounded-full border-text px-3.5 py-0.5 text-xs font-bold">' + escapeHtml(post.tag || 'Post') + '</div>' +
            '</div>' +
            '</div>' +

            tagsHtml +

            mediaHtml +
            '<p class="text-sm text-gray-600 leading-relaxed mb-4">' + escapeHtml(post.body || '') + '</p>' +
            '<div class="flex items-center gap-5 pt-3 border-t border-gray-100">' +
                '<button id="comment-btn-' + post.id + '" data-comment-toggle aria-label="Komentar" class="flex items-center gap-1.5 text-gray-400 text-[13px] font-medium hover:text-[#444] transition-colors cursor-pointer">' +
                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>' +
                    '<span data-comment-count>' + escapeHtml(String(post.comments ?? '0')) + '</span>' +
                '</button>' +
                '<button id="like-btn-' + post.id + '" data-like-btn data-base="' + (post.likes_base ?? 0) + '" data-liked="' + (post.liked ? 'true' : 'false') + '" aria-pressed="' + (post.liked ? 'true' : 'false') + '" aria-label="Suka" class="flex items-center gap-1.5 text-[13px] font-medium transition-colors cursor-pointer ' + (post.liked ? 'text-red-500' : 'text-gray-400 hover:text-red-400') + '">' +
                    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>' +
                    '<span data-like-count>' + escapeHtml(String(post.likes_label ?? '0')) + '</span>' +
                '</button>' +
                '<div class="ml-auto flex items-center gap-2">' +
                    '<button id="bookmark-btn-' + post.id + '" data-bookmark-btn aria-pressed="false" aria-label="Simpan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">' +
                        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>' +
                    '</button>' +
                    '<button id="share-btn-' + post.id + '" data-share-btn aria-label="Bagikan" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] transition-colors cursor-pointer">' +
                        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>' +
                    '</button>' +
                '</div>' +
            '</div>';

        article.innerHTML += renderCommentPanel(post);

        return article;
    }

    function renderCommentPanel(post) {
        // Use proper route based on whether it is a global post or club post
        let commentsUrl = '/timeline_home/posts/' + encodeURIComponent(post.id) + '/comments';
        if (location.pathname.includes('timeline_komunitas') && post.klub) {
            commentsUrl = '/timeline_komunitas/posts/' + encodeURIComponent(post.id) + '/comments';
        }
        const storeUrl = commentsUrl;
        const authenticated = document.querySelector('meta[name="user-auth"]')?.content === 'true';

        return '<div data-comments-panel class="hidden mt-4 pt-4 border-t border-gray-100" data-comments-loaded="false" data-comments-url="' + commentsUrl + '" data-comments-store-url="' + storeUrl + '">' +
            '<div class="flex items-center justify-between mb-3">' +
                '<h4 class="text-sm font-bold text-[#444]">Komentar</h4>' +
                '<span class="text-xs text-gray-400">Klik ikon komentar untuk membuka</span>' +
            '</div>' +
            '<div data-comment-list class="space-y-3 mb-4"></div>' +
            (authenticated
                ? '<form data-comment-form class="flex gap-3 items-start">' +
                    '<div class="w-9 h-9 rounded-full border border-[#444] overflow-hidden bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] flex items-center justify-center flex-shrink-0">' +
                        renderAvatarHtml({ avatar_url: currentUserAvatar, name: currentUserName || 'U' }) +
                    '</div>' +
                    '<div class="flex-1">' +
                        '<textarea data-comment-input rows="1" maxlength="500" placeholder="Tulis komentar..." class="w-full border-[1.5px] border-gray-200 rounded-xl px-3 py-2 text-sm placeholder-gray-300 outline-none focus:border-[#444] resize-none transition-colors overflow-hidden"></textarea>' +
                        '<input type="file" data-comment-media-input class="hidden" accept="image/*,video/*,*/*" multiple />' +
                        '<div class="flex flex-wrap items-center justify-between gap-2 mt-2">' +
                            '<div class="flex items-center gap-2">' +
                                '<button type="button" data-comment-media-trigger="image" aria-label="Unggah gambar komentar" title="Unggah gambar" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">' +
                                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>' +
                                '</button>' +
                                '<button type="button" data-comment-media-trigger="video" aria-label="Unggah video komentar" title="Unggah video" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">' +
                                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>' +
                                '</button>' +
                                '<button type="button" data-comment-media-trigger="file" aria-label="Lampirkan file komentar" title="Lampirkan file" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-[#444] hover:bg-gray-100 transition-colors cursor-pointer">' +
                                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>' +
                                '</button>' +
                                '<span data-comment-media-label class="text-xs text-gray-400"></span>' +
                            '</div>' +
                            '<button type="submit" data-comment-submit class="bg-[#FFDDAF] text-[#444] font-bold text-sm px-4 py-2 rounded-full border-[1.5px] border-[#444] hover:bg-[#ffcf90] transition-colors cursor-pointer">Kirim</button>' +
                        '</div>' +
                    '</div>' +
                '</form>'
                : '<div class="text-sm text-gray-400">Silakan login untuk menulis komentar.</div>') +
        '</div>';
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#39;');
    }

    let toastTimeout;
    function showToast(msg) {
        let el = document.getElementById('toast-msg');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toast-msg';
            el.className = 'fixed bottom-24 left-1/2 -translate-x-1/2 z-[9999] bg-[#444] text-white text-sm font-medium px-5 py-3 rounded-full transition-all duration-300 opacity-0 translate-y-2';
            document.body.appendChild(el);
        }
        el.textContent = msg;
        requestAnimationFrame(() => {
            el.classList.remove('opacity-0', 'translate-y-2');
            el.classList.add('opacity-100', 'translate-y-0');
        });
        clearTimeout(toastTimeout);
        toastTimeout = setTimeout(() => {
            el.classList.add('opacity-0', 'translate-y-2');
            el.classList.remove('opacity-100', 'translate-y-0');
        }, 2500);
    }

    // ── Media carousel scroll buttons ──

    document.querySelectorAll('[data-carousel-next]').forEach(btn => {
        btn.addEventListener('click', () => {
            const mediaId = btn.dataset.carouselNext;
            const carousel = document.querySelector(`[data-carousel-scroll-${mediaId}]`);
            if (carousel) {
                carousel.scrollBy({ left: 300, behavior: 'smooth' });
            }
        });
    });

    document.querySelectorAll('[data-carousel-prev]').forEach(btn => {
        btn.addEventListener('click', () => {
            const mediaId = btn.dataset.carouselPrev;
            const carousel = document.querySelector(`[data-carousel-scroll-${mediaId}]`);
            if (carousel) {
                carousel.scrollBy({ left: -300, behavior: 'smooth' });
            }
        });
    });

    // ── Club filters (timeline_komunitas) ──
    const clubFilters = document.querySelectorAll('[data-klub-filter]');
    if (clubFilters.length) {
        clubFilters.forEach(btn => {
            btn.addEventListener('click', () => {
                const isActive = btn.classList.contains('bg-[#FFDDAF]');
                if (isActive) {
                    btn.classList.remove('bg-[#FFDDAF]');
                    btn.classList.add('bg-white');
                } else {
                    btn.classList.add('bg-[#FFDDAF]');
                    btn.classList.remove('bg-white');
                }

                const activeFilters = Array.from(document.querySelectorAll('[data-klub-filter]'))
                                           .filter(b => b.classList.contains('bg-[#FFDDAF]'))
                                           .map(b => b.dataset.klubFilter);
                
                const posts = document.querySelectorAll('article[data-post-klub]');
                posts.forEach(post => {
                    const postKlub = post.dataset.postKlub;
                    if (activeFilters.length === 0 || activeFilters.includes(postKlub)) {
                        post.style.display = 'block';
                    } else {
                        post.style.display = 'none';
                    }
                });
            });
        });
    }

    const sidebarSearchInput = document.getElementById('sidebar-search-input');
    const sidebarSearchDropdown = document.getElementById('sidebar-search-dropdown');
    let sidebarSearchTimeout;

    const mobileSearchDropdown = document.getElementById('mobile-search-dropdown');

    document.addEventListener('click', (e) => {
        if (sidebarSearchDropdown && !e.target.closest('#sidebar-search-input') && !e.target.closest('#sidebar-search-dropdown')) {
            sidebarSearchDropdown.classList.add('hidden');
        }
        if (mobileSearchDropdown && !e.target.closest('#mobile-search-input') && !e.target.closest('#mobile-search-dropdown') && !e.target.closest('#mobile-search-overlay') && !e.target.closest('#mobile-search-trending')) {
            mobileSearchDropdown.classList.add('hidden');
        }
    });

    if (sidebarSearchInput) {
        sidebarSearchInput.addEventListener('input', () => {
            const query = sidebarSearchInput.value.trim();
            filterTimelinePosts(query);
            debouncedUserSearch(query);
        });

        sidebarSearchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                sidebarSearchDropdown?.classList.add('hidden');
                sidebarSearchInput.blur();
            }
        });
    }

    function filterTimelinePosts(query) {
        if (!feedPanel) return;

        const posts = feedPanel.querySelectorAll('article[data-post-id]');
        let visibleCount = 0;

        posts.forEach(post => {
            const match = !query || post.textContent.toLowerCase().includes(query.toLowerCase());
            post.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        let noResults = document.getElementById('search-no-results');
        if (query && visibleCount === 0 && posts.length > 0) {
            if (!noResults) {
                noResults = document.createElement('div');
                noResults.id = 'search-no-results';
                noResults.className = 'text-center py-10 text-gray-400';
                noResults.innerHTML = '<p>Tidak ada postingan yang cocok.</p>';
                feedPanel.appendChild(noResults);
            }
            noResults.classList.remove('hidden');
        } else if (noResults) {
            noResults.classList.add('hidden');
        }
    }

    function debouncedUserSearch(query) {
        clearTimeout(sidebarSearchTimeout);
        const isMobileSearch = mobileSearchOverlay && !mobileSearchOverlay.classList.contains('hidden');
        const dropdown = isMobileSearch ? mobileSearchDropdown : sidebarSearchDropdown;
        if (query.length < 2) {
            dropdown?.classList.add('hidden');
            return;
        }
        sidebarSearchTimeout = setTimeout(() => fetchUsers(query), 300);
    }

    function fetchUsers(query) {
        const isMobileSearch = mobileSearchOverlay && !mobileSearchOverlay.classList.contains('hidden');
        const dropdown = isMobileSearch ? document.getElementById('mobile-search-dropdown') : sidebarSearchDropdown;

        fetch(`/api/search?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                if (!data.users || data.users.length === 0) {
                    dropdown?.classList.add('hidden');
                    return;
                }

                const html = data.users.map(u => {
                    const avatarHtml = u.avatar_url ? `<img src="${u.avatar_url}" class="w-9 h-9 rounded-full border-2 border-[#444] object-cover flex-shrink-0" alt="${u.name}">`
                        : `<div class="w-9 h-9 rounded-full border-2 border-[#444] flex-shrink-0 flex items-center justify-center bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] text-xs font-bold">${u.initial}</div>`;

                    return `<a href="/u/${u.username}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors no-underline text-[#444]">
                        ${avatarHtml}
                        <div class="min-w-0">
                            <div class="text-sm font-bold truncate">${u.name}</div>
                            <div class="text-xs text-gray-400 truncate">@${u.username}</div>
                        </div>
                    </a>`;
                }).join('<div class="border-t border-gray-100 last:hidden"></div>');

                dropdown.innerHTML = html;
                dropdown.classList.remove('hidden');
            })
            .catch(() => {
                dropdown?.classList.add('hidden');
            })
    }

    // ── Mobile bottom nav: hide on scroll down, show on scroll up ──
    const bottomNav = document.getElementById('mobile-bottom-nav');
    if (bottomNav) {
        let lastScrollY = window.scrollY;
        let ticking2 = false;

        window.addEventListener('scroll', () => {
            if (ticking2) return;
            ticking2 = true;
            requestAnimationFrame(() => {
                const currentScrollY = window.scrollY;
                if (currentScrollY > lastScrollY && currentScrollY > 100) {
                    bottomNav.classList.add('hidden-nav');
                } else {
                    bottomNav.classList.remove('hidden-nav');
                }
                lastScrollY = currentScrollY;
                ticking2 = false;
            });
        }, { passive: true });
    }

    // ── Mobile search trigger ──
    const mobileSearchTrigger = document.getElementById('mobile-search-trigger');
    const mobileSearchOverlay = document.getElementById('mobile-search-overlay');
    const mobileSearchClose = document.getElementById('mobile-search-close');
    const mobileSearchBack = document.getElementById('mobile-search-back');
    const mobileSearchInput = document.getElementById('mobile-search-input');
    const mobileSearchTrending = document.getElementById('mobile-search-trending');
    let mobileSearchQuery = '';

    function closeMobileSearch() {
        mobileSearchOverlay.classList.add('hidden');
        if (mobileSearchDropdown) mobileSearchDropdown.classList.add('hidden');
        showMobileTrending(false);
        if (mobileSearchInput) mobileSearchInput.blur();
    }

    function showMobileTrending(show) {
        if (mobileSearchTrending) mobileSearchTrending.classList.toggle('hidden', !show);
    }

    if (mobileSearchTrigger && mobileSearchOverlay) {
        mobileSearchTrigger.addEventListener('click', () => {
            if (mobileSearchInput) {
                mobileSearchInput.value = mobileSearchQuery;
                setTimeout(() => mobileSearchInput.focus(), 100);
            }
            showMobileTrending(!mobileSearchQuery);
            mobileSearchOverlay.classList.remove('hidden');
        });

        if (mobileSearchClose) {
            mobileSearchClose.addEventListener('click', closeMobileSearch);
        }

        if (mobileSearchBack) {
            mobileSearchBack.addEventListener('click', closeMobileSearch);
        }

        if (mobileSearchInput) {
            mobileSearchInput.addEventListener('input', () => {
                mobileSearchQuery = mobileSearchInput.value.trim();
                filterTimelinePosts(mobileSearchQuery);
                debouncedUserSearch(mobileSearchQuery);
                showMobileTrending(!mobileSearchQuery);
                if (mobileSearchQuery && mobileSearchDropdown) mobileSearchDropdown.classList.add('hidden');
            });

            mobileSearchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === 'Escape') {
                    closeMobileSearch();
                }
            });
        }
    }
});

// ── Lightbox Logic ──
let timelineGallery = [];
let timelineGalleryIndex = 0;

const lightboxHtml = `
<div id="timelineLightbox" class="fixed inset-0 z-[100] flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-200" style="background: rgba(0,0,0,0.85); backdrop-filter: blur(6px)">
    <button id="timelineLightboxClose" class="absolute top-4 right-4 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition text-white" title="Tutup">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
    <button id="timelineLightboxPrev" class="absolute top-1/2 left-4 -translate-y-1/2 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition text-white hidden">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <button id="timelineLightboxNext" class="absolute top-1/2 right-4 -translate-y-1/2 z-10 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition text-white hidden">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
    </button>
    <div id="timelineLightboxContent" class="relative max-w-5xl max-h-[90vh] w-full flex items-center justify-center transition-transform duration-200 scale-95"></div>
    <p id="timelineLightboxCounter" class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/60 text-sm font-semibold tracking-wide"></p>
</div>`;

if (!document.getElementById('timelineLightbox')) {
    document.body.insertAdjacentHTML('beforeend', lightboxHtml);
}

const lightbox = document.getElementById('timelineLightbox');
const lightboxClose = document.getElementById('timelineLightboxClose');
const lightboxPrev = document.getElementById('timelineLightboxPrev');
const lightboxNext = document.getElementById('timelineLightboxNext');
const lightboxContent = document.getElementById('timelineLightboxContent');
const lightboxCounter = document.getElementById('timelineLightboxCounter');

function openLightbox(gallery, startIndex) {
    timelineGallery = gallery;
    timelineGalleryIndex = startIndex;
    
    lightbox.classList.remove('hidden');
    // trigger reflow
    void lightbox.offsetWidth;
    lightbox.classList.remove('opacity-0');
    lightboxContent.classList.remove('scale-95');
    lightboxContent.classList.add('scale-100');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling

    updateLightbox();
}

function closeLightbox() {
    lightbox.classList.add('opacity-0');
    lightboxContent.classList.remove('scale-100');
    lightboxContent.classList.add('scale-95');
    document.body.style.overflow = '';
    setTimeout(() => {
        lightbox.classList.add('hidden');
        lightboxContent.innerHTML = '';
    }, 200);
}

function updateLightbox() {
    if (timelineGallery.length === 0) return;
    const item = timelineGallery[timelineGalleryIndex];
    
    if (item.type === 'video') {
        lightboxContent.innerHTML = `<video src="${item.url}" controls autoplay class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain bg-black"></video>`;
    } else {
        lightboxContent.innerHTML = `<img src="${item.url}" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain" />`;
    }

    if (timelineGallery.length > 1) {
        lightboxPrev.classList.remove('hidden');
        lightboxNext.classList.remove('hidden');
        lightboxCounter.textContent = `${timelineGalleryIndex + 1} / ${timelineGallery.length}`;
    } else {
        lightboxPrev.classList.add('hidden');
        lightboxNext.classList.add('hidden');
        lightboxCounter.textContent = '';
    }
}

lightboxClose?.addEventListener('click', closeLightbox);
lightboxPrev?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (timelineGalleryIndex > 0) {
        timelineGalleryIndex--;
    } else {
        timelineGalleryIndex = timelineGallery.length - 1;
    }
    updateLightbox();
});
lightboxNext?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (timelineGalleryIndex < timelineGallery.length - 1) {
        timelineGalleryIndex++;
    } else {
        timelineGalleryIndex = 0;
    }
    updateLightbox();
});
lightbox?.addEventListener('click', (e) => {
    if (e.target === lightbox || e.target === lightboxContent) closeLightbox();
});

document.addEventListener('keydown', (e) => {
    if (lightbox.classList.contains('hidden')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') lightboxPrev.click();
    if (e.key === 'ArrowRight') lightboxNext.click();
});

// Event delegation for opening lightbox
document.addEventListener('click', (e) => {
    const mediaEl = e.target.closest('[data-media-url]');
    if (mediaEl) {
        e.preventDefault();
        const article = mediaEl.closest('article[data-post-id]') || mediaEl.closest('.grid') || mediaEl.closest('div');
        if (!article) return;
        
        // Find all sibling media in the same post container to allow next/prev
        let siblings = Array.from(article.querySelectorAll('[data-media-url]'));
        if(siblings.length === 0) siblings = [mediaEl]; // Fallback to single item
        
        const gallery = siblings.map(el => ({
            url: el.dataset.mediaUrl,
            type: el.dataset.mediaType
        }));
        
        const startIndex = siblings.indexOf(mediaEl) > -1 ? siblings.indexOf(mediaEl) : 0;
        openLightbox(gallery, startIndex);
    }
});
