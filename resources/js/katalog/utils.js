export function mapCategory(categories) {
  if (!categories || !categories.length) return 'Fiksi';
  const cat = categories.join(' ').toLowerCase();
  const mapping = [
    { keys: ['thriller','suspense','crime'], val: 'Thriller' },
    { keys: ['mystery','detective'], val: 'Misteri' },
    { keys: ['romance','love'], val: 'Romansa' },
    { keys: ['science fiction','sci-fi'], val: 'Sci-Fi' },
    { keys: ['fantasy','magic','dragon'], val: 'Fantasi' },
    { keys: ['horror','ghost','supernatural'], val: 'Horror' },
    { keys: ['biography','autobiography','memoir'], val: 'Biografi' },
    { keys: ['history','historical'], val: 'Sejarah' },
    { keys: ['self-help','self help','personal development','psychology','motivation'], val: 'Pengembangan Diri' },
    { keys: ['business','economics','finance'], val: 'Bisnis' },
    { keys: ['poetry','poem'], val: 'Puisi' },
    { keys: ['comics','comic','graphic novel','manga'], val: 'Komik' },
    { keys: ['adventure','action'], val: 'Petualangan' },
    { keys: ['dystopia','dystopian'], val: 'Distopia' },
    { keys: ['religion','spirituality','islam'], val: 'Religi' },
    { keys: ['science','technology'], val: 'Sains & Teknologi' },
    { keys: ['education','academic','textbook'], val: 'Edukasi' },
    { keys: ['nonfiction','non-fiction','reference','philosophy','politics','social'], val: 'Non-Fiksi' },
    { keys: ['fiction','novel','literary'], val: 'Fiksi' },
  ];
  for (const m of mapping) {
    if (m.keys.some(k => cat.includes(k))) return m.val;
  }
  return 'Fiksi';
}

export function fixCoverUrl(url) {
  if (!url) return '';
  return url
    .replace(/^http:\/\//i, 'https://')
    .replace('zoom=1', 'zoom=2')
    .replace('&edge=curl', '');
}

export function extractISBN(info) {
  if (!info.industryIdentifiers) return '';
  const isbn13 = info.industryIdentifiers.find(id => id.type === 'ISBN_13');
  const isbn10 = info.industryIdentifiers.find(id => id.type === 'ISBN_10');
  return isbn13?.identifier || isbn10?.identifier || '';
}

export function starsHtml(rating) {
  return [1,2,3,4,5].map(s =>
    `<span class="text-[0.82rem] ${s <= Math.round(rating) ? 'text-[#F5C518]' : 'text-[#ddd]'}">★</span>`
  ).join('');
}

export function showToast(msg) {
  const t = document.createElement('div');
  t.className = 'toast bg-[#444444] text-white px-6 py-3.5 rounded-xl text-[0.85rem] font-semibold shadow-[0_8px_24px_rgba(0,0,0,0.15)]';
  t.textContent = msg;
  document.getElementById('toastContainer').appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3200);
}

export function truncateSynopsis(text, maxLength = 150) {
  if (!text) return '';
  const stripped = text.replace(/<[^>]*>/g, '');
  if (stripped.length <= maxLength) return stripped;
  return stripped.substring(0, maxLength).trimEnd() + '…';
}
