<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BookApiService
{
    protected string $googleBooksApi = 'https://www.googleapis.com/books/v1/volumes';
    protected string $openLibraryApi = 'https://openlibrary.org/search.json';

    public function search(string $query, int $maxResults = 40): array
    {
        $cacheKey = 'books_search_' . md5($query . $maxResults);

        return Cache::remember($cacheKey, 3600, function () use ($query, $maxResults) {
            $books = $this->fetchGoogleBooks($query, $maxResults);

            if (empty($books)) {
                $books = $this->fetchOpenLibrary($query, $maxResults);
            }

            return $books;
        });
    }

    protected function fetchGoogleBooks(string $query, int $maxResults): array
    {
        try {
            $params = [
                'q' => $query,
                'maxResults' => $maxResults,
                'printType' => 'books',
                'orderBy' => 'relevance',
            ];

            $apiKey = config('services.google_books.key');
            if ($apiKey) {
                $params['key'] = $apiKey;
            }

            $response = Http::get($this->googleBooksApi, $params);

            if (!$response->successful()) {
                return [];
            }

            $items = $response->json('items', []);
            $books = [];

            foreach ($items as $index => $item) {
                $info = $item['volumeInfo'] ?? [];
                
                $judul = $info['title'] ?? '';
                if (!empty($info['subtitle'])) {
                    $judul .= ': ' . $info['subtitle'];
                }

                $coverUrl = $info['imageLinks']['thumbnail'] ?? $info['imageLinks']['smallThumbnail'] ?? null;
                if ($coverUrl) {
                    // Fix Google Books URL to use HTTPS and no edge cache
                    $coverUrl = str_replace('http://', 'https://', $coverUrl);
                    $coverUrl = preg_replace('/&edge=curl/', '', $coverUrl);
                }

                $books[] = [
                    'id' => 'g_' . ($item['id'] ?? uniqid()),
                    'google_id' => $item['id'] ?? null,
                    'judul' => $judul,
                    'penulis' => implode(', ', $info['authors'] ?? []),
                    'tahun' => !empty($info['publishedDate']) ? (int) substr($info['publishedDate'], 0, 4) : null,
                    'rating_avg' => 0,
                    'rating_count' => 0,
                    'sinopsis' => $info['description'] ?? '',
                    'genres' => [!empty($info['categories']) ? $this->mapCategory($info['categories']) : 'Fiksi'],
                    'cover' => $coverUrl,
                    'gradient_from' => '#C7E7FF',
                    'gradient_to' => '#FFDDAF',
                ];
            }

            return $books;
        } catch (\Exception $e) {
            \Log::warning('Google Books API failed: ' . $e->getMessage());
            return [];
        }
    }

    protected function fetchOpenLibrary(string $query, int $limit): array
    {
        try {
            $response = Http::get($this->openLibraryApi, [
                'q' => $query,
                'limit' => $limit,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $docs = $response->json('docs', []);
            $books = [];

            foreach ($docs as $index => $doc) {
                $books[] = [
                    'id' => 'ol_' . ($doc['key'] ?? uniqid()),
                    'google_id' => null,
                    'judul' => $doc['title'] ?? '',
                    'penulis' => implode(', ', (array) ($doc['author_name'] ?? [])),
                    'tahun' => $doc['first_publish_year'] ?? null,
                    'rating_avg' => 0,
                    'rating_count' => 0,
                    'sinopsis' => $doc['description'] ?? $doc['subtitle'] ?? '',
                    'genres' => collect($doc['subject'] ?? [])->take(3)->toArray() ?: ['Fiksi'],
                    'cover' => !empty($doc['cover_i']) ? "https://covers.openlibrary.org/b/id/{$doc['cover_i']}-L.jpg" : null,
                    'gradient_from' => '#C7E7FF',
                    'gradient_to' => '#FFDDAF',
                ];
            }

            return $books;
        } catch (\Exception $e) {
            \Log::warning('Open Library API failed: ' . $e->getMessage());
            return [];
        }
    }

    protected function mapCategory(array $categories): string
    {
        $joined = strtolower(implode(' ', $categories));
        
        $map = [
            'fiction' => 'Fiksi',
            'fantasy' => 'Fantasi',
            'romance' => 'Romantis',
            'thriller' => 'Thriller',
            'mystery' => 'Misteri',
            'horror' => 'Horor',
            'sci-fi' => 'Fiksi Ilmiah',
            'science fiction' => 'Fiksi Ilmiah',
            'history' => 'Sejarah',
            'biography' => 'Biografi',
            'self-help' => 'Pengembangan Diri',
            'business' => 'Bisnis',
            'religion' => 'Agama',
            'philosophy' => 'Filsafat',
            'psychology' => 'Psikologi',
            'poetry' => 'Puisi',
            'comics' => 'Komik',
            'manga' => 'Komik',
        ];

        foreach ($map as $en => $id) {
            if (str_contains($joined, $en)) {
                return $id;
            }
        }

        // Return the first category if no specific mapping matched
        return $categories[0] ?? 'Fiksi';
    }
}
