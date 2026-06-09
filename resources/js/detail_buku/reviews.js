import { state } from './state';
import { starsHtml, setHtmlById, showToast, getBookId, getBookIdType, getCsrf } from './utils';
import { updateUlasanButton, refreshRatingStats, renderRatingBreakdown } from './ui';

export function renderReviews(reset = false) {
  const list = document.getElementById('reviewsList');
  if (reset) { list.innerHTML = ''; state.currentReviewPage = 0; }

  const start = state.currentReviewPage * state.REVIEWS_PER_PAGE;
  const end = Math.min(start + state.REVIEWS_PER_PAGE, state.REVIEWS.length);

  state.REVIEWS.slice(start, end).forEach((r, i) => {
    const voted = state.helpfulVotes.has(r.id);
    const card = document.createElement('div');
    card.className = 'bg-white rounded-[20px] p-7 md:p-8 mb-4 animate-fade-in-up relative';
    card.style.animationDelay = `${i * 0.08}s`;

    card.innerHTML = `
      ${window.__USER__ && r.user_id == window.__USER__.id ? `
        <div class="absolute top-6 right-6 md:top-8 md:right-8 z-[10]">
          <div class="relative">
            <button class="btn-review-menu flex items-center justify-center w-8 h-8 text-[#444444]/40 hover:text-[#444444] hover:bg-[#f5f5f5] rounded-full transition-colors cursor-pointer" data-id="${r.id}">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1.5"></circle><circle cx="12" cy="5" r="1.5"></circle><circle cx="12" cy="19" r="1.5"></circle></svg>
            </button>
            <div class="review-dropdown absolute right-0 top-full mt-1 w-36 bg-white rounded-xl shadow-[0_4px_24px_rgba(0,0,0,0.12)] border border-[#eee] py-1.5 opacity-0 invisible transition-all duration-200 origin-top-right transform scale-95" id="reviewMenu-${r.id}">
              <button class="btn-edit-review w-full text-left px-4 py-2 text-[0.8rem] font-semibold text-[#444444] hover:bg-[#F5F5F5] transition-colors" data-id="${r.id}">Edit Ulasan</button>
              <button class="btn-delete-review w-full text-left px-4 py-2 text-[0.8rem] font-semibold text-rose-500 hover:bg-rose-50 transition-colors" data-id="${r.id}">Hapus Ulasan</button>
            </div>
          </div>
        </div>
      ` : ''}
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-0 mb-3.5 pr-10">
        <div class="flex items-center gap-3">
          ${r.avatar_url
            ? `<img src="${r.avatar_url}" alt="${r.name}" class="w-10 h-10 rounded-full object-cover shrink-0" />`
            : `<div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#D4F6FF] flex items-center justify-center text-[0.85rem] font-bold text-[#444444] shrink-0">${r.initial}</div>`
          }
          <div class="flex flex-col">
            <span class="text-[0.9rem] font-bold text-[#444444] leading-tight">
              ${r.name} 
              ${window.__USER__ && r.user_id == window.__USER__.id ? '<span class="font-normal text-[#444444]/60">(Anda)</span>' : ''}
            </span>
            <span class="text-[0.75rem] text-[#444444]/40 mt-0.5">${r.date}</span>
          </div>
        </div>
        <div class="flex items-center gap-2 ml-[52px] sm:ml-0 shrink-0">
          <div class="flex gap-0.5 text-[0.85rem]">${starsHtml(r.rating, 'text-[0.85rem]')}</div>
        </div>
      </div>
      <p class="text-[0.88rem] leading-[1.75] text-[#444444]/80 mb-4">${r.text}</p>
      <div class="flex items-center justify-between">
        <span class="text-[0.78rem] text-[#444444]/35 italic">Apakah ulasan ini membantu?</span>
        <button class="btn-helpful inline-flex items-center gap-1.5 px-4 py-1.5 text-[0.78rem] font-semibold text-[#444444] border ${voted ? 'border-[#FFDDAF] bg-[#FFDDAF]' : 'border-[#ddd] bg-white'} rounded-full transition-all duration-200 hover:border-[#444444] hover:bg-[#FBFBFB]" data-id="${r.id}">
          Membantu (${r.helpful})
        </button>
      </div>`;
    list.appendChild(card);
  });

  state.currentReviewPage++;
  const btn = document.getElementById('loadMoreReviews');
  if (btn) btn.style.display = end >= state.REVIEWS.length ? 'none' : 'inline-block';
}

export async function loadReviews() {
  const bookId = getBookId();
  if (!bookId) return;

  try {
    const res  = await fetch(`/api/reviews/${bookId}`);
    const data = await res.json();

    state.REVIEWS = data.reviews ?? [];

    state.helpfulVotes.clear();
    state.REVIEWS.forEach(r => { if (r.my_vote) state.helpfulVotes.add(r.id); });

    updateUlasanButton(data.has_reviewed, data.my_review_id);

    const book = window.__BOOK_DATA__;
    book.rating_avg          = data.rating_avg;
    book.rating_count        = data.rating_count;
    book.rating_distribution = data.rating_distribution;

    setHtmlById('bookRating', `
      <div class="flex gap-0.5">${starsHtml(book.rating_avg)}</div>
      <span class="text-[0.85rem] text-text/60">
        <strong class="text-text font-bold">${book.rating_avg}</strong> (${book.rating_count} Ulasan)
      </span>
    `);

    renderRatingBreakdown(book);
    applySort();
    renderReviews(true);
  } catch (err) {
    console.error('Gagal memuat ulasan: ', err);
  }
}

// Review modal functionality
export function openModal(reviewToEdit = null) { 
  const modalOverlay = document.getElementById('reviewModalOverlay');
  const user = window.__USER__;
  if (user) {
    const avatarEl = document.getElementById('modalUserAvatar');
    if (user.avatar_url) {
      avatarEl.innerHTML = `<img src="${user.avatar_url}" alt="${user.name}" class="w-full h-full object-cover rounded-full" />`;
    } else {
      avatarEl.textContent = user.name[0].toUpperCase();
    }
    document.getElementById('modalUserName').textContent = user.name;
  }

  const starPicker = document.getElementById('starPicker');
  if (reviewToEdit) {
    state.editingReviewId = reviewToEdit.id;
    document.querySelector('#reviewModal h3').textContent = 'Edit Ulasan';
    document.getElementById('submitReviewBtn').textContent = 'Simpan Perubahan';
    document.getElementById('reviewText').value = reviewToEdit.text;

    state.pickedRating = reviewToEdit.rating;
    if (starPicker) {
      starPicker.querySelectorAll('.pick-star').forEach(s => s.classList.toggle('active', Number(s.dataset.val) <= state.pickedRating));
    }
  } else {
    state.editingReviewId = null;
    document.querySelector('#reviewModal h3').textContent = 'Tulis Ulasan';
    document.getElementById('submitReviewBtn').textContent = 'Kirim Ulasan';
    document.getElementById('reviewText').value = '';
    state.pickedRating = 0;
    if (starPicker) {
      starPicker.querySelectorAll('.pick-star').forEach(s => {
        s.classList.remove('active');
        s.style.color = '#ddd';
      });
    }
  }
  if (modalOverlay) {
    modalOverlay.classList.add('active'); 
    document.body.style.overflow = 'hidden'; 
  }
}

export function closeModal() { 
  const modalOverlay = document.getElementById('reviewModalOverlay');
  if (modalOverlay) modalOverlay.classList.remove('active'); 
  document.body.style.overflow = ''; 
  state.editingReviewId = null;
}

export function applySort() {
  const sorters = {
    newest: (a,b) => b.id - a.id, oldest: (a,b) => a.id - b.id,
    highest: (a,b) => b.rating - a.rating, lowest: (a,b) => a.rating - b.rating,
    helpful: (a,b) => b.helpful - a.helpful,
  };
  
  state.REVIEWS.sort((a, b) => {
    // Prioritize current user's review
    const aIsMe = window.__USER__ && a.user_id == window.__USER__.id;
    const bIsMe = window.__USER__ && b.user_id == window.__USER__.id;
    
    if (aIsMe && !bIsMe) return -1;
    if (!aIsMe && bIsMe) return 1;
    
    // Fallback to selected sorter
    const sorter = sorters[state.currentSort] || sorters.newest;
    return sorter(a, b);
  });
}

export function initReviewsEvents() {
  const reviewsList = document.getElementById('reviewsList');
  if (!reviewsList) return;

  // Toggle review menu
  document.addEventListener('click', (e) => {
    // Close all open menus first if clicking outside
    if (!e.target.closest('.btn-review-menu') && !e.target.closest('.review-dropdown')) {
      document.querySelectorAll('.review-dropdown').forEach(m => {
        m.classList.remove('opacity-100', 'visible', 'scale-100');
        m.classList.add('opacity-0', 'invisible', 'scale-95');
      });
    }

    const btn = e.target.closest('.btn-review-menu');
    if (!btn) return;
    
    const id = btn.dataset.id;
    const menu = document.getElementById(`reviewMenu-${id}`);
    
    // Close other menus
    document.querySelectorAll('.review-dropdown').forEach(m => {
      if (m !== menu) {
        m.classList.remove('opacity-100', 'visible', 'scale-100');
        m.classList.add('opacity-0', 'invisible', 'scale-95');
      }
    });

    if (menu) {
      const isOpen = menu.classList.contains('opacity-100');
      if (isOpen) {
        menu.classList.remove('opacity-100', 'visible', 'scale-100');
        menu.classList.add('opacity-0', 'invisible', 'scale-95');
      } else {
        menu.classList.remove('opacity-0', 'invisible', 'scale-95');
        menu.classList.add('opacity-100', 'visible', 'scale-100');
      }
    }
  });

  // Edit review
  reviewsList.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-edit-review');
    if (!btn) return;
    const id = Number(btn.dataset.id);
    const review = state.REVIEWS.find(r => r.id === id);
    if (!review) return;
    openModal(review);
  });

  // Delete review
  reviewsList.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-delete-review');
    if (!btn) return;
    const id = Number(btn.dataset.id);
    
    if (!confirm('Hapus ulasan ini? Tindakan ini tidak bisa dibatalkan.')) return;
    btn.disabled    = true;
    btn.textContent = 'Menghapus...';
    try {
      const res = await fetch(`/api/reviews/${id}`, {
        method:  'DELETE',
        headers: { 'X-CSRF-TOKEN': getCsrf() },
      });
      const data = await res.json();
      if (!res.ok) {
        showToast(data.message ?? 'Gagal menghapus ulasan.');
        btn.disabled    = false;
        btn.textContent = 'Hapus';
        return;
      }

      state.REVIEWS = state.REVIEWS.filter(r => r.id !== id);
      updateUlasanButton(false, null);
      refreshRatingStats();
      renderReviews(true);
      showToast('Ulasan berhasil dihapus.');
    } catch (err) {
      showToast('Terjadi kesalahan jaringan.');
      console.error(err);
      btn.disabled    = false;
      btn.textContent = 'Hapus';
    }
  });

  // Helpful vote toggle
  const helpfulPending = new Set();
  reviewsList.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-helpful');
    if (!btn) return;

    const id = Number(btn.dataset.id);
    if (helpfulPending.has(id)) return;

    const review = state.REVIEWS.find(r => r.id === id);
    if (!review) return;

    const alreadyVoted = state.helpfulVotes.has(id);

    if (alreadyVoted) {
      state.helpfulVotes.delete(id);
      review.helpful--;
      btn.classList.remove('border-[#FFDDAF]', 'bg-[#FFDDAF]');
      btn.classList.add('border-[#ddd]', 'bg-white');
    } else {
      state.helpfulVotes.add(id);
      review.helpful++;
      btn.classList.remove('border-[#ddd]', 'bg-white');
      btn.classList.add('border-[#FFDDAF]', 'bg-[#FFDDAF]');
    }
    btn.textContent = `Membantu (${review.helpful})`;

    helpfulPending.add(id);
    try {
      const res  = await fetch(`/api/reviews/${id}/helpful`, {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': getCsrf() },
      });
      const data = await res.json();

      review.helpful   = data.helpful;
      review.my_vote   = data.voted;
      if (data.voted) { state.helpfulVotes.add(id); } else { state.helpfulVotes.delete(id); }
      btn.textContent = `Membantu (${data.helpful})`;
    } catch (err) {
      if (alreadyVoted) {
        state.helpfulVotes.add(id); review.helpful++;
        btn.classList.remove('border-[#ddd]', 'bg-white');
        btn.classList.add('border-[#FFDDAF]', 'bg-[#FFDDAF]');
      } else {
        state.helpfulVotes.delete(id); review.helpful--;
        btn.classList.remove('border-[#FFDDAF]', 'bg-[#FFDDAF]');
        btn.classList.add('border-[#ddd]', 'bg-white');
      }
      btn.textContent = `Membantu (${review.helpful})`;
      console.error('Gagal vote helpful', err);
    } finally {
      helpfulPending.delete(id);
    }
  });

  document.getElementById('loadMoreReviews')?.addEventListener('click', () => renderReviews());

  // Tulis ulasan btn
  document.getElementById('tulisUlasanBtn')?.addEventListener('click', () => {
    if (!window.__AUTH__) {
      showToast('Kamu harus login untuk menulis ulasan.');
      setTimeout(() => window.location.href = '/login', 1500);
      return;
    }

    const btn = document.getElementById('tulisUlasanBtn');
    const myReviewId = Number(btn.dataset.myReviewId);

    if (myReviewId) {
      const myReview = state.REVIEWS.find(r => r.id === myReviewId);
      if (myReview) {
        openModal(myReview);
      } else {
        openModal();
      }
    } else {
      openModal();
    }
  });

  const modalOverlay = document.getElementById('reviewModalOverlay');
  document.getElementById('reviewModalClose')?.addEventListener('click', closeModal);
  if (modalOverlay) modalOverlay.addEventListener('click', e => { if (e.target === modalOverlay) closeModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

  // Star picker
  const starPicker = document.getElementById('starPicker');
  if (starPicker) {
    starPicker.querySelectorAll('.pick-star').forEach(star => {
      star.addEventListener('click', () => {
        state.pickedRating = Number(star.dataset.val);
        starPicker.querySelectorAll('.pick-star').forEach(s =>
          s.classList.toggle('active', Number(s.dataset.val) <= state.pickedRating)
        );
      });
      star.addEventListener('mouseenter', () => {
        const val = Number(star.dataset.val);
        starPicker.querySelectorAll('.pick-star').forEach(s =>
          s.style.color = Number(s.dataset.val) <= val ? '#F5C518' : '#ddd'
        );
      });
    });
    starPicker.addEventListener('mouseleave', () => {
      starPicker.querySelectorAll('.pick-star').forEach(s =>
        s.style.color = s.classList.contains('active') ? '#F5C518' : '#ddd'
      );
    });
  }

  // Submit review
  document.getElementById('submitReviewBtn')?.addEventListener('click', async () => {
    const text = document.getElementById('reviewText').value.trim();
    if (!text || state.pickedRating === 0) { showToast('Lengkapi semua field dan pilih rating'); return; }

    const btn = document.getElementById('submitReviewBtn');
    btn.disabled = true;
    btn.textContent = 'Mengirim...';
    
    try {
      let res, data;

      if (state.editingReviewId) {
        res = await fetch(`/api/reviews/${state.editingReviewId}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrf(),
          },
          body: JSON.stringify({rating: state.pickedRating, ulasan: text}),
        });
      } else {
         res = await fetch('/api/reviews', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrf(),
          },
          body: JSON.stringify({
            book_identifier: getBookId(),
            book_identifier_type: getBookIdType(),
            rating: state.pickedRating,
            ulasan: text,
          }),
        });
      }

      data = await res.json();

      if (!res.ok) {
        showToast(`${data.message ?? 'Gagal mengirim ulasan.'}`);
        return;
      }

      if (state.editingReviewId) {
        const idx = state.REVIEWS.findIndex(r => r.id === state.editingReviewId);
        if (idx !== -1) state.REVIEWS[idx] = data.review;
        showToast('Ulasan berhasil diperbarui');
      } else {
        state.REVIEWS.unshift(data.review);
        updateUlasanButton(true, data.review.id);
        showToast('Ulasan berhasil dikirim');
      }

      refreshRatingStats();
      renderReviews(true);

      closeModal();
      document.getElementById('reviewText').value = '';
      state.pickedRating = 0;
      if (starPicker) starPicker.querySelectorAll('.pick-star').forEach(s => s.classList.remove('active'));
      document.getElementById('reviewsSection').scrollIntoView({ behavior: 'smooth' });
    } catch (err) {
      showToast('Terjadi kesalahan jaringan.');
      console.error(err);
    } finally {
      btn.disabled = false;
      btn.textContent = state.editingReviewId ? 'Simpan Perubahan' : 'Kirim Ulasan';
    }
  });

  // Sort select
  document.getElementById('sortSelect')?.addEventListener('change', function() {
    state.currentSort = this.value;
    applySort();
    renderReviews(true);
  });
}
