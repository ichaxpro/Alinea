<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PersonalBook;

class PersonalBookController extends Controller
{
    public function index(Request $request) {
        return response()->json(
            $request->user()->personalBooks()->orderBy('created_at', 'desc')->get()
        );
    }

    public function store(Request $request) {
        $data = $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'tahun_terbit' => 'nullable|integer|min:1900|max:2030',
            'kategori' => 'nullable|string|max:50',
            'cover_url' => 'nullable|string|max:500',
            'jumlah_halaman' => 'nullable|integer|min:1',
        ]);

        $data['kategori'] ??= 'Fiksi';
        $data['status'] = 'tersedia';
        $data['is_available'] = true;
        $book = $request->user()->personalBooks()->create($data);

        // Also add to FeaturedBook if not exists so it appears in the catalog page
        $featuredBookQuery = \App\Models\FeaturedBook::query();
        if (!empty($data['isbn'])) {
            $featuredBookQuery->where('isbn', $data['isbn']);
        } else {
            $featuredBookQuery->where('judul', $data['judul'])->where('penulis', $data['penulis']);
        }
        
        if (!$featuredBookQuery->exists()) {
            $gradients = [
                ['from' => '#FFDDAF', 'to' => '#C7E7FF'],
                ['from' => '#C7E7FF', 'to' => '#D4F6FF'],
                ['from' => '#FFDDAF', 'to' => '#D4F6FF'],
                ['from' => '#D4F6FF', 'to' => '#FFDDAF'],
            ];
            $gradient = $gradients[array_rand($gradients)];

            \App\Models\FeaturedBook::create([
                'judul' => $data['judul'],
                'penulis' => $data['penulis'],
                'tahun' => $data['tahun_terbit'] ?? null,
                'isbn' => $data['isbn'] ?? null,
                'kategori' => $data['kategori'],
                'cover_url' => $data['cover_url'] ?? null,
                'jumlah_halaman' => $data['jumlah_halaman'] ?? null,
                'status' => 'tersedia',
                'gradient_from' => $gradient['from'],
                'gradient_to' => $gradient['to'],
                'genres' => [$data['kategori']],
                'sinopsis' => 'Belum ada sinopsis.',
                'bahasa' => 'Indonesia',
                'rating_avg' => 0,
                'rating_count' => 0,
            ]);
        }

        return response()->json($book, 201);
    }

    public function update(Request $request, PersonalBook $book) {
        if ($book->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'is_available' => 'boolean',
            'status' => 'nullable|string|in:tersedia,dipinjam',
        ]);

        $book -> update($data);

        return response()->json($book);
    }
    
    public function destroy(Request $request, PersonalBook $book) {
        if ($book->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($book->status == 'dipinjam') {
            return response()->json(['message' => 'Buku sedang dipinjam, tidak bisa dihapus'], 422);
        }

        $book->delete();

        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}
