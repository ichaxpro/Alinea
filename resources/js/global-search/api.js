export const GOOGLE_BOOKS_API = 'https://www.googleapis.com/books/v1/volumes';
export const GOOGLE_BOOKS_KEY = document.querySelector('meta[name="google-books-key"]')?.content || '';

function fixCoverUrl(url) {
    if (!url) {
        return '';
    }
    return url.replace(/^http:\/\//i, 'https://')
              .replace('zoom=1', 'zoom=2')
              .replace('&edge=curl', '');
}

function mapCategory(categories) {
    if (!categories || !categories.length) {
        return 'Fiksi';
    }
    const cat = categories.join(' ').toLowerCase();
    const mapping = [
        {keys: ['thriller','suspense','crime'], val: 'Thriller'},
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
        { keys: ['nonfiction','non-fiction','science','education','reference','philosophy','religion','politics','social'], val: 'Non-Fiksi' },
        { keys: ['fiction','novel','literary'], val: 'Fiksi' },
    ];
    for (const m of mapping) {
        if (m.keys.some(k => cat.includes(k))) {
            return m.val;
        }
    }
    return 'Fiksi';
}

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
        rating_avg: info.averageRating || 0,
        rating_count: info.ratingsCount || 0,
        sinopsis: info.description || '',
        genres: [mapCategory(info.categories)],
        cover: coverUrl || null,
        gradient_from: '#C7E7FF',
        gradient_to: '#FFDDAF',
        google_id: volume.id,
    };
}

export async function fetchGoogleBooks(query, maxResults = 5) {
    const keyParam = GOOGLE_BOOKS_KEY ? `&key=${GOOGLE_BOOKS_KEY}` : '';
    const url = `${GOOGLE_BOOKS_API}?q=${encodeURIComponent(query)}&maxResults=${maxResults}&printType=books&orderBy=relevance${keyParam}`;
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) throw new Error(`API error: ${res.status}`);
    const data = await res.json();
    return (data.items || []).map(v => parseBookVolume(v));
}

export async function fetchLocalSearch(query, abortController) {
    const res = await fetch(`/api/search?q=${encodeURIComponent(query)}`, {
        signal: abortController.signal,
        headers: {'Accept': 'application/json'},
        credentials: 'same-origin',
    });
    if (!res.ok) throw new Error(`API error: ${res.status}`);
    return await res.json();
}
