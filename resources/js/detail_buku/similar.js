const CARD_GRADIENTS = [
  'from-[#C7E7FF] to-[#D4F6FF]',
  'from-[#FFDDAF] to-[#D4F6FF]',
  'from-[#FFDDAF] to-[#C7E7FF]',
  'from-[#D4F6FF] to-[#FFDDAF]',
  'from-[#C7E7FF] to-[#FFDDAF]',
];

export async function loadSimilarBooks() {
  const book = window.__BOOK_DATA__;
  const bookId = String(book?.id ?? '');
  const kategori = encodeURIComponent(book?.kategori ?? '');
  const grid = document.getElementById('similarGrid');

  if (!bookId || !grid) return;

  grid.innerHTML = Array(5).fill(0).map(() => `
    <div class="animate-pulse w-[140px] shrink-0 sm:w-auto sm:shrink">
      <div class="w-full aspect-[2/3] rounded-xl bg-[#eee] mb-2.5"></div>
      <div class="h-3 bg-[#eee] rounded-full w-3/4 mb-1.5"></div>
      <div class="h-2.5 bg-[#eee] rounded-full w-1/2"></div>
    </div>
  `).join('');

  try {
    const res = await fetch(`/api/books/${bookId}/similar?kategori=${kategori}`);
    const data = await res.json();
    const books = data.books ?? [];

    if (books.length === 0) {
      document.getElementById('similarBooks')?.style.setProperty('display', 'none');
      return;
    }

    grid.innerHTML = books.map((b, i) => {
      const gradient = CARD_GRADIENTS[i % CARD_GRADIENTS.length];
      const initial = (b.judul?.[0] ?? '?').toUpperCase();
      const rating = b.rating_avg > 0 ? `★ ${b.rating_avg.toFixed(1)}` : '';

      const coverHtml = b.cover_url ? `<img src="${b.cover_url}" alt="${b.judul}" class="w-full h-full object-cover" />` : `<span class="text-2xl font-black text-[#444444]/20">${initial}</span>`;

      return `
        <a href="${b.url}" class="block cursor-pointer transition-transform duration-200 hover:-translate-y-1 w-[140px] shrink-0 sm:w-auto sm:shrink">
          <div class="w-full aspect-[2/3] rounded-xl flex items-center justify-center mb-2.5 overflow-hidden bg-gradient-to-br ${gradient}">
            ${coverHtml}
          </div>
          <h4 class="text-[0.82rem] font-bold text-[#444444] mb-0.5 overflow-hidden text-ellipsis whitespace-nowrap">${b.judul}</h4>
          <p class="text-[0.72rem] text-[#444444]/50">${b.penulis}</p>
          ${rating ? `<p class="text-[0.72rem] text-[#F5C518] mt-1">${rating}</p>` : ''}
        </a>
      `;
    }).join('');

  } catch (err) {
    console.error('Gagal memuat buku serupa: ', err);
    document.getElementById('similarBooks')?.style.setProperty('display', 'none');
  }
}
