export function getCsrf() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

export function getBookId() {
  return String(window.__BOOK_DATA__?.id ?? '');
}

export function getBookIdType() {
  return window.__BOOK_DATA__?.book_identifier_type ?? 'db';
}

export function starsHtml(rating, size = 'text-base') {
  return [1,2,3,4,5].map(s =>
    `<span class="${size} ${s <= Math.round(rating) ? 'text-[#F5C518]' : 'text-[#ddd]'}">★</span>`
  ).join('');
}

export function setTextById(id, text) {
  const el = document.getElementById(id);
  if (el) el.textContent = text;
}

export function setHtmlById(id, html) {
  const el = document.getElementById(id);
  if (el) el.innerHTML = html;
}

export function showToast(msg, type = 'default') {
  const t = document.createElement('div');
  t.className = 'toast bg-[#444444] text-white px-6 py-3.5 rounded-xl text-[0.85rem] font-semibold shadow-[0_8px_24px_rgba(0,0,0,0.15)]';
  // Optional: you can style based on `type` if you want
  t.textContent = msg;
  document.getElementById('toastContainer').appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3200);
}
