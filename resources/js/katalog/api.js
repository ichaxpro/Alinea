import { fixCoverUrl, mapCategory } from './utils';

const GOOGLE_BOOKS_API = 'https://www.googleapis.com/books/v1/volumes';
const GOOGLE_BOOKS_KEY = document.querySelector('meta[name="google-books-key"]')?.content || '';

function parseBookVolume(volume) {
  const info = volume.volumeInfo || {};
  const judul = info.title || '';
  const subtitle = info.subtitle ? `: ${info.subtitle}` : '';
  let coverUrl = '';
  if (info.imageLinks) {
    coverUrl = fixCoverUrl(info.imageLinks.thumbnail || info.imageLinks.smallThumbnail || '');
  }
  return {
    judul: judul + subtitle,
    penulis: info.authors?.join(', ') || '',
    tahun: info.publishedDate ? parseInt(info.publishedDate.substring(0, 4)) : '',
    rating_avg: 0,
    rating_count: 0,
    sinopsis: info.description || '',
    genres: [mapCategory(info.categories)],
    cover: coverUrl || null,
    gradient_from: '#C7E7FF',
    gradient_to: '#FFDDAF',
  };
}

async function fetchGoogleBooks(query, maxResults = 40) {
  const keyParam = GOOGLE_BOOKS_KEY ? `&key=${GOOGLE_BOOKS_KEY}` : '';
  const url = `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&maxResults=${maxResults}&printType=books&orderBy=relevance${keyParam}`;
  const res = await fetch(url, {headers: {'Accept': 'application/json'}});
  if(!res.ok) throw new Error(`API error: ${res.status}`);
  const data = await res.json();
  return (data.items || []).map((v, i) => ({...parseBookVolume(v), id:i + 1, google_id: v.id}));
}

async function fetchOpenLibrary(query, limit = 40) {
  const url = `https://openlibrary.org/search.json?q=${encodeURIComponent(query)}&limit=${limit}`;
  const res = await fetch(url);
  if (!res.ok) throw new Error(`Open Library error: ${res.status}`);
  const data = await res.json();
  return (data.docs || []).map((b, i) => ({
    id: i + 1,
    judul: b.title,
    penulis: b.author_name?.join(', ') || '',
    tahun: b.first_publish_year || '',
    rating_avg: 0,
    rating_count: 0,
    sinopsis: b.description || b.subtitle || '',
    genres: b.subject?.slice(0, 3) || ['Fiksi'],
    cover: b.cover_i ? `https://covers.openlibrary.org/b/id/${b.cover_i}-L.jpg` : null,
    gradient_from: '#C7E7FF',
    gradient_to: '#FFDDAF',
  }));
}

export async function fetchBooks(query) {
  const featured = window.__FEATURED_BOOKS__ || [];
  const seen = new Set(featured.map(b => b.judul.toLowerCase()));
  let apiBooks = [];

  try {
    const books = await fetchGoogleBooks(query);
    if (books.length > 0) apiBooks = books;
  } catch (e) {
    console.warn('Google Books error:', e);
  }

  if (apiBooks.length === 0) {
    console.warn('Google Books returned 0 results, falling back to Open Library');
    try {
      apiBooks = await fetchOpenLibrary(query);
    } catch (e2) {
      console.warn('Open Library also failed');
    }
  }

  const merged = [...featured];
  let nextId = merged.length + 1;
  for (const b of apiBooks) {
    if (!seen.has(b.judul.toLowerCase())) {
      seen.add(b.judul.toLowerCase());
      merged.push({ ...b, id: nextId++ });
    }
  }
  return merged;
}

export async function fetchRatingStats(ids) {
  if (!ids.length) return {};
  try {
    const params = new URLSearchParams();
    ids.forEach(id => params.append('ids[]', id));
    const res = await fetch(`/api/reviews/stats?${params}`);
    const data = await res.json();
    return data.stats ?? {};
  } catch (e) {
    console.warn('Gagal fetch rating stats: ', e);
    return {};
  }
}
