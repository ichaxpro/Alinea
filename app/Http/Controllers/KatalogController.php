<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeaturedBook;
use App\Models\BookReview;
use App\Services\BookApiService;
use Illuminate\Support\Facades\DB;

class KatalogController extends Controller
{
    public function index(Request $request, BookApiService $bookApiService)
    {
        $query = $request->query('q');
        $genre = $request->query('genre', []);
        if (is_string($genre)) {
            $genre = explode(',', $genre);
        }
        $minRating = $request->query('rating');
        $sort = $request->query('sort', 'rating-desc');
        $page = (int) $request->query('page', 1);
        $perPage = 16;

        $featuredBooksQuery = FeaturedBook::query();
        
        if (!empty($query)) {
            $featuredBooksQuery->where(function ($q) use ($query) {
                $q->where('judul', 'like', '%' . $query . '%')
                  ->orWhere('penulis', 'like', '%' . $query . '%');
            });
        }

        $featuredBooks = $featuredBooksQuery->get()->map(function($b) {
            return [
                'id' => $b->id,
                'google_id' => null,
                'judul' => $b->judul,
                'penulis' => $b->penulis,
                'tahun' => $b->tahun,
                'rating_avg' => (float) ($b->rating_avg ?? 0),
                'rating_count' => (int) ($b->rating_count ?? 0),
                'sinopsis' => $b->sinopsis,
                'genres' => $b->genres ?? [],
                'cover' => $b->cover_url ? (str_starts_with($b->cover_url, 'http') ? $b->cover_url : asset('storage/' . $b->cover_url)) : null,
                'gradient_from' => $b->gradient_from,
                'gradient_to' => $b->gradient_to,
            ];
        })->toArray();

        $books = $featuredBooks;

        // If user searched, fetch from APIs and merge
        if (!empty($query) && strlen($query) >= 3) {
            $apiBooks = $bookApiService->search($query);
            
            $seen = [];
            foreach ($featuredBooks as $fb) {
                $seen[strtolower($fb['judul'])] = true;
            }

            foreach ($apiBooks as $apiBook) {
                if (!isset($seen[strtolower($apiBook['judul'])])) {
                    $seen[strtolower($apiBook['judul'])] = true;
                    $books[] = $apiBook;
                }
            }
        }

        // Fetch live rating stats from reviews
        $bookIds = collect($books)->map(fn($b) => $b['google_id'] ?: (string) $b['id'])->filter()->unique()->toArray();
        $stats = BookReview::select('book_identifier', DB::raw('AVG(rating) as rating_avg'), DB::raw('COUNT(*) as rating_count'))
            ->whereIn('book_identifier', $bookIds)
            ->groupBy('book_identifier')
            ->get()
            ->keyBy('book_identifier');

        // Apply stats to books
        foreach ($books as &$book) {
            $key = $book['google_id'] ?: (string) $book['id'];
            if ($stats->has($key)) {
                $book['rating_avg'] = (float) $stats[$key]->rating_avg;
                $book['rating_count'] = (int) $stats[$key]->rating_count;
            }
        }
        unset($book); // Unset reference

        // Filters
        if (!empty($genre)) {
            $genre = collect($genre)->flatten()->map(fn($v) => strtolower((string)$v))->filter()->toArray();
            $books = array_filter($books, function($b) use ($genre) {
                $bookGenres = collect($b['genres'])->flatten()->map(fn($v) => strtolower((string)$v))->toArray();
                return !empty(array_intersect($bookGenres, $genre));
            });
        }

        if (!empty($minRating)) {
            $books = array_filter($books, function($b) use ($minRating) {
                return $b['rating_avg'] >= (float) $minRating;
            });
        }

        // Sorting
        usort($books, function($a, $b) use ($sort) {
            switch ($sort) {
                case 'rating-asc':
                    return $a['rating_avg'] <=> $b['rating_avg'];
                case 'reviews-desc':
                    return $b['rating_count'] <=> $a['rating_count'];
                case 'title-asc':
                    return strcasecmp($a['judul'], $b['judul']);
                case 'title-desc':
                    return strcasecmp($b['judul'], $a['judul']);
                case 'newest':
                    return ($b['tahun'] ?? 0) <=> ($a['tahun'] ?? 0);
                case 'rating-desc':
                default:
                    return $b['rating_avg'] <=> $a['rating_avg'];
            }
        });

        // Hardcoded genres as requested by user
        $availableGenres = [
            'Biografi', 'Bisnis', 'Distopia', 'Edukasi', 'Fantasi', 'Fiksi',
            'Filosofi', 'Horor', 'Horror', 'Inspirasi', 'Klasik', 'Komik',
            'Misteri', 'Non-Fiksi', 'Pengembangan Diri', 'Petualangan',
            'Psikologi', 'Puisi', 'Religi', 'Romansa', 'Sains & Teknologi',
            'Sci-Fi', 'Sejarah', 'Teenlit', 'Thriller'
        ];
        sort($availableGenres);

        // Pagination
        $total = count($books);
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        
        $offset = ($page - 1) * $perPage;
        $paginatedBooks = array_slice($books, $offset, $perPage);

        // If AJAX request, return partial view
        if ($request->ajax() || $request->wantsJson() || $request->header('X-PJAX')) {
            $html = '';
            foreach ($paginatedBooks as $book) {
                $html .= view('components.katalog.book-card', ['book' => $book])->render();
            }

            return response()->json([
                'html' => $html,
                'total' => $total,
                'page' => $page,
                'totalPages' => $totalPages,
                'genres' => $availableGenres,
            ]);
        }

        return view('katalog', [
            'books' => $paginatedBooks,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'availableGenres' => $availableGenres,
            'query' => $query,
            'activeGenres' => $genre,
            'minRating' => $minRating,
            'sort' => $sort,
        ]);
    }
}
