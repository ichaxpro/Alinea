<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeaturedBook;
use App\Models\BookReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    public function detail(string $param) {
        if(is_numeric($param)) {
            $book = FeaturedBook::find((int) $param);
            if($book) {
                return view('detail_buku', [
                    'bookData' => $this->buildFromDb($book),
                ]);
            }
        }

        $apiKey = config('services.google_books.key');
        if(!$apiKey) {
            abort(404);
        }

        $response = Http::get("https://www.googleapis.com/books/v1/volumes/{$param}", [
            'key' => $apiKey,
        ]);

        if($response->failed()) {
            abort(404);
        }

        $volume = $response->json();
        if(!$volume || !isset($volume['volumeInfo'])) {
            abort(404);
        }

        return view('detail_buku', [
            'bookData' => $this->buildFromApi($volume),
        ]);
    }

    private function buildFromDb(FeaturedBook $book): array {
        return [
            'id' => $book->id,
            'judul' => $book->judul,
            'penulis' => $book->penulis,
            'penerbit' => $book->penerbit ?? '',
            'tahun_terbit' => $book->tahun,
            'jumlah_halaman' => $book->jumlah_halaman ?? 0,
            'bahasa' => $book->bahasa ?? 'Indonesia',
            'isbn' => $book->isbn ?? '',
            'kategori' => $book->kategori ?? '',
            'sinopsis' => $book->sinopsis ?? '',
            'foto_sampul' => $book->cover_url,
            'status' => $book->status ?? 'tersedia',
            'owners' => $this->getOwners($book->isbn, $book->judul, $book->penulis),
            'genres' => $book->genres ?? [],
            'rating_avg' => (float) ($book->rating_avg ?? 0),
            'rating_count' => (int) ($book->rating_count ?? 0),
            'rating_distribution' => (object) [],
            'book_identifier_type' => 'db',
        ];
    }

    private function buildFromApi(array $volume): array {
        $info = $volume['volumeInfo'] ?? [];
        $googleId = $volume['id'] ?? '';

        $isbn = '';
        $identifiers = $info['industryIdentifiers'] ?? [];
        foreach($identifiers as $id) {
            if($id['type'] == 'ISBN_13') {
                $isbn = $id['identifier'];
                break;
            }
            if($id['type'] == 'ISBN_10') {
                $isbn = $id['identifier'];
            }
        }

        $coverUrl = null;
        $imageLinks = $info['imageLinks'] ?? [];
        if(!empty($imageLinks['thumbnail'])) {
            $coverUrl = str_replace('http://', 'https://', $imageLinks['thumbnail']);
        } elseif(!empty($imageLinks['smallThumbnail'])) {
            $coverUrl = str_replace('http://', 'https://', $imageLinks['smallThumbnail']);
        }

        if($coverUrl) {
            $coverUrl = preg_replace('/&zoom=\d+/', '', $coverUrl);
        }

        $judul = $info['title'] ?? '';
        if(!empty($info['subtitle'])) {
            $judul .= ': ' . $info['subtitle'];
        }

        $langMap = ['id' => 'Indonesia', 'en' => 'Inggris'];
        $bahasa = $langMap[$info['language'] ?? ''] ?? ($info['language'] ?? 'Indonesia');

        return [
            'id' => $googleId,
            'judul' => $judul,
            'penulis' => isset($info['authors']) ? implode(', ', $info['authors']) : '',
            'penerbit' => $info['publisher'] ?? '',
            'tahun_terbit' => isset($info['publishedDate']) ? (int) substr($info['publishedDate'], 0, 4) : 0,
            'jumlah_halaman' => $info['pageCount'] ?? 0,
            'bahasa' => $bahasa,
            'isbn' => $isbn,
            'kategori' => $info['categories'][0] ?? '',
            'sinopsis' => $info['description'] ?? '',
            'foto_sampul' => $coverUrl,
            'status' => 'tersedia',
            'owners' => $this->getOwners($isbn, $judul, isset($info['authors']) ? implode(', ', $info['authors']) : ''),
            'genres' => $info['categories'] ?? ['Fiksi'],
            // Selalu mulai dari 0 — loadReviews() akan overwrite dengan data ulasan Alinea yang sesungguhnya.
            // Jangan pakai averageRating/ratingCount dari Google karena itu rating Google, bukan Alinea.
            'rating_avg' => 0.0,
            'rating_count' => 0,
            'rating_distribution' => (object) [],
            'book_identifier_type' => 'google',
        ];
    }

    private function getOwners(?string $isbn, string $judul, string $penulis): array {
        $query = \App\Models\PersonalBook::with('user')->where('is_available', true);
        
        $query->where(function($q) use ($isbn, $judul, $penulis) {
            if (!empty($isbn)) {
                $q->where('isbn', $isbn)->orWhere(function($q2) use ($judul, $penulis) {
                    $q2->where('judul', 'like', "%{$judul}%")->where('penulis', 'like', "%{$penulis}%");
                });
            } else {
                $q->where('judul', 'like', "%{$judul}%")->where('penulis', 'like', "%{$penulis}%");
            }
        });

        $owners = [];
        foreach($query->get() as $pb) {
            if ($pb->user) {
                $owners[] = [
                    'id' => $pb->user->id,
                    'name' => $pb->user->name ?? $pb->user->username,
                    'location' => $pb->user->kota ?? 'Indonesia',
                    'avatar_url' => $pb->user->avatar_url,
                    'personal_book_id' => $pb->id,
                ];
            }
        }

        $uniqueOwners = [];
        $seen = [];
        foreach ($owners as $owner) {
            if (!in_array($owner['id'], $seen)) {
                $seen[] = $owner['id'];
                $uniqueOwners[] = $owner;
            }
        }
        
        return $uniqueOwners;
    }

    public function similarBooks(Request $request, string $param): JsonResponse {
        $kategori = $request->input('kategori', '');
        $genres = $request->input('genres', []);
        $exclude = $param;

        $query = FeaturedBook::query()->Limit(10);

        if (!empty($kategori)) {
            $query->where('kategori', 'like', "%{$kategori}%");
        }

        if (is_numeric($exclude)) {
            $query->where('id', '!=', (int) $exclude);
        }

        $books = $query->get();

        // Ambil rating real dari book_reviews (satu query, bukan N+1)
        $bookIds = $books->pluck('id')->map(fn($id) => (string) $id)->toArray();
        $realRatings = BookReview::whereIn('book_identifier', $bookIds)
            ->selectRaw('book_identifier, ROUND(AVG(rating), 1) as avg_rating')
            ->groupBy('book_identifier')
            ->pluck('avg_rating', 'book_identifier');

        $dbBooks = $books->map(fn($b) => [
            'id'                => $b->id,
            'judul'             => $b->judul,
            'penulis'           => $b->penulis,
            'cover_url'         => $b->cover_url,
            'rating_avg'        => (float) ($realRatings[(string) $b->id] ?? 0),
            'kategori'          => $b->kategori ?? '',
            'identifier_type'   => 'db',
            'url'               => route('detail_buku', $b->id),
        ])->values()->toArray();

        $need = 5 - count($dbBooks);

        if ($need > 0 && !empty($kategori)) {
            $apiKey = config('services.google_books.key');
            $subject = urlencode($kategori);
            $response = Http::get(
                "https://www.googleapis.com/books/v1/volumes",
                [
                    'q'         => "subject:{$subject}",
                    'maxResults' => $need + 2,
                    'key'   => $apiKey,
                    'langRestrict'  => 'id',
                ]
            );

            if ($response->ok()) {
                $items = $response->json('items') ?? [];
                foreach ($items as $vol) {
                    if (count($dbBooks) >= 5) break;
                    if ($vol['id'] === $exclude) continue;

                    $info = $vol['volumeInfo'] ?? [];
                    $imgLinks = $info['imageLinks'] ?? [];
                    $cover = $imgLinks['thumbnail'] ?? $imgLinks['smallThumbnail'] ?? null;
                    if ($cover) {
                        $cover = str_replace('http://', 'https://', $cover);
                        $cover = preg_replace('/&zoom=\d+/', '', $cover);
                    }

                    $dbBooks[] = [
                        'id'              => $vol['id'],
                        'judul'           => $info['title'] ?? '',
                        'penulis'         => isset($info['authors']) ? implode(', ', $info['authors']) : '',
                        'cover_url'       => $cover,
                        'rating_avg'      => (float) ($info['averageRating'] ?? 0),
                        'kategori'        => $info['categories'][0] ?? $kategori,
                        'identifier_type' => 'google',
                        'url'             => route('detail_buku', $vol['id']),
                    ];
                }
            }
        }

        return response()->json([
            'books' => array_slice($dbBooks, 0, 5),
        ]);
    }
}
