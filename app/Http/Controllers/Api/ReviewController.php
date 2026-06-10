<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookReview;
use App\Models\FeaturedBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReviewController extends Controller
{
    public function index(string $bookIdentifier) {
        $userId = Auth::id(); // null if not logged in

        $reviews = BookReview::with('user:id,name,foto_profil')
            ->where('book_identifier', $bookIdentifier)
            ->latest()
            ->get()
            ->map(fn($r) => $this->formatReview($r, $userId));
        
        $distribution = BookReview::where('book_identifier', $bookIdentifier)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();
        
        $ratingAvg = $reviews->avg('rating') ?? 0;
        $ratingCount = $reviews->count();

        $myReview = $userId ? BookReview::where('book_identifier', $bookIdentifier)
            ->where('user_id', $userId)
            ->first()
            : null;

        return response()->json([
            'reviews'       => $reviews,
            'rating_avg'    => round($ratingAvg, 1),
            'rating_count'  => $ratingCount,
            'rating_distribution'   => $distribution,
            'has_reviewed'  => (bool) $myReview,
            'my_review_id'  => $myReview?->id,
        ]);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'book_identifier'       => 'required|string|max:255',
            'book_identifier_type'  => 'required|in:db,google',
            'rating'                => 'required|integer|min:1|max:5',
            'ulasan'                => 'required|string|min:5|max:2000',
        ]);

        $existing = BookReview::withTrashed()
                ->where('book_identifier', $validated['book_identifier'])
                ->where('user_id', Auth::id())
                ->first();
        
        if ($existing) {
            if ($existing->trashed()) {
                return response()->json(['message' => 'Ulasan Anda sebelumnya telah disembunyikan oleh admin karena melanggar panduan komunitas.'], 422);
            }
            return response()->json(['message' => 'Kamu sudah pernah menulis ulasan untuk buku ini.'], 422);
        }

        $review = BookReview::create([
            ...$validated,
            'user_id' => Auth::id(),
        ]);

        $review->load('user:id,name,foto_profil');
        $this->syncFeaturedBookRating($validated['book_identifier']);

        return response()->json([
            'message'   => 'Ulasan berhasil disimpan.',
            'review'    => $this->formatReview($review),
        ], 201);
    }

    public function helpful(int $id) {
        $review = BookReview::findOrFail($id);
        $userId = Auth::id();

        $exists = DB::table('review_helpful_votes')
            ->where('review_id', $id)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            // Unvote
            DB::table('review_helpful_votes')
                ->where('review_id', $id)
                ->where('user_id', $userId)
                ->delete();
            $voted = false;
        } else {
            // Vote
            DB::table('review_helpful_votes')->insert([
                'review_id'  => $id,
                'user_id'    => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $voted = true;
        }

        // Sync the count from the votes table (always accurate)
        $count = DB::table('review_helpful_votes')->where('review_id', $id)->count();
        $review->update(['helpful' => $count]);

        return response()->json(['helpful' => $count, 'voted' => $voted]);
    }

    public function update(Request $request, int $id) {
        $review = BookReview::findOrFail($id);

        if ($review->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'ulasan' => 'required|string|min:5|max:2000',
        ]);

        $review->update($validated);
        $review->load('user:id,name,foto_profil');
        $this->syncFeaturedBookRating($review->book_identifier);

        return response()->json([
            'message' => 'Ulasan berhasil diperbarui.',
            'review' => $this->formatReview($review),
        ]);
    }

    public function destroy(int $id) {
        $review = BookReview::findOrFail($id);

        if ($review->user_id !== Auth::id()) {
            return response()->json(['message' => 'Tidak diizinkan.'], 403);
        }

        $bookIdentifier = $review->book_identifier;

        $review->delete();

        $this->syncFeaturedBookRating($bookIdentifier);

        return response()->json(['message' => 'Ulasan berhasil dihapus.']);
    }

    private function formatReview(BookReview $r, ?int $userId = null): array {
        $name = $r->user?->name ?? 'Anonim';
        $initial = strtoupper(mb_substr($name, 0, 1));

        $myVote = $userId
            ? DB::table('review_helpful_votes')
                ->where('review_id', $r->id)
                ->where('user_id', $userId)
                ->exists()
            : false;

        return [
            'id'         => $r->id,
            'user_id'    => $r->user_id,
            'name'       => $name,
            'initial'    => $initial,
            'avatar_url' => $r->user?->avatar_url,
            'rating'     => $r->rating,
            'date'       => $r->created_at->locale('id')->diffForHumans(),
            'text'       => $r->ulasan,
            'helpful'    => (int) ($r->helpful ?? 0),
            'my_vote'    => $myVote,
        ];
    }

    private function syncFeaturedBookRating(string $bookIdentifier): void {
        $featured = FeaturedBook::find((int) $bookIdentifier);
        if ($featured) {
            $featured->syncRatings();
        }
    }

    public function stats(Request $request) {
        $ids = $request->input('ids', []);

        $stats = BookReview::whereIn('book_identifier', $ids)
            ->selectRaw('book_identifier, ROUND(AVG(rating), 1) as rating_avg, COUNT(*) as rating_count')
            ->groupBy('book_identifier')
            ->get()
            ->keyBy('book_identifier');

            $result = [];
            foreach ($ids as $id) {
                $result[$id] = [
                    'rating_avg'        => (float) ($stats[$id]->rating_avg ?? 0),
                    'rating_count'      => (int) ($stats[$id]->rating_count ?? 0),
                ];
            }

            return response()->json(['stats' => $result]);
    }
}
