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
