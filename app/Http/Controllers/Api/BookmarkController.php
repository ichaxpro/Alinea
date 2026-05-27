<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function index() {
        $bookmarks = Auth::user()
            ->bookmarks()
            ->latest()
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'book_identifier' => $b->book_identifier,
                'identifier_type' => $b->identifier_type,
                'judul' => $b->judul,
                'penulis' => $b->penulis,
                'foto_sampul' => $b->foto_sampul,
                'kategori' => $b->kategori,
                'created_at' => $b->created_at->toDateString(),
            ]);
            return response()->json(['data' => $bookmarks]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'book_identifier' => 'required|string',
            'identifier_type' => 'required|in:db,google',
            'judul' => 'required|string|max:550',
            'penulis' => 'nullable|string|max:255',
            'foto_sampul' => 'nullable|string|max:1000',
            'kategori' => 'nullable|string|max:100',
        ]);

        $userId = Auth::id();

        $existing = Bookmark::where('user_id', $userId)
            ->where('book_identifier', $validated['book_identifier'])
            ->where('identifier_type', $validated['identifier_type'])
            ->first();
        if ($existing) {
            $existing->delete();
            return response()->json(['bookmarked' => false, 'message' => 'Bookmark dihapus']);
        }

        Bookmark::create([
            'user_id' => $userId,
            'book_identifier' => $validated['book_identifier'],
            'identifier_type' => $validated['identifier_type'],
            'judul' => $validated['judul'],
            'penulis' => $validated['penulis'] ?? '',
            'foto_sampul' => $validated['foto_sampul'] ?? null,
            'kategori' => $validated['kategori'] ?? '',
        ]);

        return response()->json(['bookmarked' => true, 'message' => 'Buku disimpan']);
    }

    public function check(Request $request) {
        $exists = Bookmark::where('user_id', Auth::id())
            ->where('book_identifier', $request->book_identifier)
            ->where('identifier_type', $request->identifier_type)
            ->exists();
        
        return response()->json(['bookmarked' => $exists]);
    }
}
