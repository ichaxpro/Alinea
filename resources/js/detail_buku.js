import "./custom-select";

/* ═══════════════════════════════════════════
   detail_buku.js — Alinea Book Detail Page
   Data-driven: semua data dari JS, siap integrasi DB
   
   Untuk integrasi database nanti, cukup ganti BOOK_DATA, REVIEWS,
   dan SIMILAR_BOOKS dengan data dari controller via:
   window.__BOOK_DATA__ = {!! json_encode($book) !!};
═══════════════════════════════════════════ */

import { renderBookDetail, renderRatingBreakdown } from "./detail_buku/ui";
import { loadReviews, initReviewsEvents } from "./detail_buku/reviews";
import { loadSimilarBooks } from "./detail_buku/similar";
import { initOwnersEvents } from "./detail_buku/owners";
import { initBookmarkState, initBookmarkEvents } from "./detail_buku/bookmark";

// ══════════════════════════════════════
// INIT — render semua dari data
// ══════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
  // Kalau nanti data dari DB, cukup ganti: const book = window.__BOOK_DATA__ || BOOK_DATA;
  const book = window.__BOOK_DATA__;

  if (book) {
    renderBookDetail(book);
    renderRatingBreakdown(book);
  }
  
  loadReviews();
  loadSimilarBooks();
  initBookmarkState();
  
  initReviewsEvents();
  initOwnersEvents();
  initBookmarkEvents();
});