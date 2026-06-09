<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FeaturedBook;
use Illuminate\Support\Facades\Auth;

class ExploreController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Buku Sedang Populer
        $popularBooks = FeaturedBook::orderBy('rating_count', 'desc')
            ->orderBy('rating_avg', 'desc')
            ->take(15)
            ->get();
            
        // Rekomendasi berdasarkan genre
        $genreRecommendations = [];
        $preferredGenres = $user->preferred_genres ?? [];
        
        if (is_string($preferredGenres)) {
            $preferredGenres = json_decode($preferredGenres, true) ?? [];
        }
        
        // Pastikan maksimal 5 genre yang diambil
        $genresToFetch = array_slice($preferredGenres, 0, 5);
        
        foreach ($genresToFetch as $genre) {
            $books = FeaturedBook::whereJsonContains('genres', $genre)
                ->inRandomOrder() // Acak agar lebih variatif seperti Netflix
                ->take(15)
                ->get();
                
            if ($books->isNotEmpty()) {
                $genreRecommendations[$genre] = $books;
            }
        }
        
        // Buku Terbaru
        $newestBooks = FeaturedBook::orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        return view('explore', compact('popularBooks', 'genreRecommendations', 'newestBooks'));
    }
}
