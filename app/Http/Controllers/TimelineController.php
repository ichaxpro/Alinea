<?php

namespace App\Http\Controllers;

use App\Models\TimelinePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\TrendingService;
use App\Http\Resources\TimelinePostResource;

class TimelineController extends Controller
{
    public function __construct(
        protected TrendingService $trendingService
    ) {}

    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $tab = $request->query('tab', 'untukmu');
        
        $query = TimelinePost::with(['author', 'attachments', 'likes'])
            ->withCount('comments')
            ->whereNull('id_klub');
        
        $activeBook = $request->query('book');
        $activeTag = $request->query('tag_filter');

        if ($activeBook) {
            $query->where(DB::raw('LOWER(TRIM(judul_buku_dibahas))'), strtolower(trim($activeBook)));
        }

        if ($activeTag) {
            $query->where('tag', $activeTag);
        }

        $query->orderByDesc('created_at');

        if ($tab === 'mengikuti' && $currentUser) {
            $followingIds = $currentUser->following()->pluck('following_id');
            $query->whereIn('id_user', $followingIds);
        }

        $posts = TimelinePostResource::collection($query->get())->resolve();

        $trendingItems = collect($this->trendingService->getWeeklyTrending())
            ->map(fn ($item) => [
                $item['judul'],
                $item['count'] . ' postingan',
                $activeBook && strtolower(trim($activeBook)) === strtolower(trim($item['judul']))
                    ? route('timeline_home')
                    : route('timeline_home', ['book' => $item['judul']]),
            ])
            ->all();

        return view('timeline_home', compact('posts', 'trendingItems', 'activeBook', 'activeTag'));
    }

    public function show(TimelinePost $post)
    {
        $currentUser = Auth::user();
        
        $post->load(['author', 'attachments', 'likes', 'club']);
        $post->loadCount('comments');

        $formattedPost = (new TimelinePostResource($post))->resolve();

        $trendingItems = collect($this->trendingService->getWeeklyTrending())
            ->map(fn ($item) => [
                $item['judul'],
                $item['count'] . ' postingan',
                route('timeline_home', ['book' => $item['judul']]),
            ])
            ->all();

        return view('timeline_post', [
            'post' => $formattedPost,
            'trendingItems' => $trendingItems,
            'isOwnProfile' => $currentUser && $currentUser->id === $post->id_user
        ]);
    }

    public function simpanan(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return redirect()->route('login');
        }

        $query = TimelinePost::with(['author', 'attachments', 'likes'])
            ->withCount('comments')
            ->whereHas('bookmarkedBy', function ($q) use ($currentUser) {
                $q->where('users.id', $currentUser->id);
            })
            ->orderByDesc('created_at');

        $posts = TimelinePostResource::collection($query->get())->resolve();

        $trendingItems = collect($this->trendingService->getWeeklyTrending())
            ->map(fn ($item) => [
                $item['judul'],
                $item['count'] . ' postingan',
                route('timeline_home', ['book' => $item['judul']]),
            ])
            ->all();

        return view('timeline_simpanan', compact('posts', 'trendingItems'));
    }
}
