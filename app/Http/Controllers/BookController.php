<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeaturedBook;
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
            'owners' => [],
            'genres' => $book->genres ?? [],
            'rating_avg' => (float) ($book->rating_avg ?? 0),
            'rating_count' => (int) ($book->rating_count ?? 0),
            'rating_distribution' => (object) [],
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
            'owners' => [],
            'genres' => $info['categories'] ?? ['Fiksi'],
            'rating_avg' => (float) ($info['averageRating'] ?? 0),
            'rating_count' => (int) ($info['ratingCount'] ?? 0),
            'rating_distribution' => (object) [],
        ];
    }
}
