<?php

namespace App\Http\Controllers;

use App\Models\TimelinePost;
use App\Models\TimelineComment;
use App\Models\TimelineLike;
use App\Models\TimelineCommentLike;
use App\Models\PostBookmark;
use Illuminate\Support\Facades\Auth;

class TimelineInteractionController extends Controller
{
    public function toggleLike(TimelinePost $post)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $like = TimelineLike::where('id_post', $post->id)->where('id_user', $currentUser->id)->first();
        if ($like) {
            $like->delete();
            $liked = false;
            if ($post->id_user !== $currentUser->id) {
                $author = \App\Models\User::find($post->id_user);
                if ($author) {
                    $author->notifications()
                        ->where('type', \App\Notifications\PostLiked::class)
                        ->where('data->user_id', $currentUser->id)
                        ->where('data->post_id', $post->id)
                        ->delete();
                }
            }
        } else {
            TimelineLike::create(['id_post' => $post->id, 'id_user' => $currentUser->id]);
            $liked = true;
            if ($post->id_user !== $currentUser->id) {
                $author = \App\Models\User::find($post->id_user);
                if ($author) {
                    $author->notify(new \App\Notifications\PostLiked($currentUser, $post));
                }
            }
        }

        $likesCount = TimelineLike::where('id_post', $post->id)->count();
        $likes_label = $likesCount >= 1000 ? round($likesCount/1000, 1) . 'K' : (string) $likesCount;

        return response()->json([
            'liked' => $liked,
            'likes_base' => $likesCount,
            'likes_label' => $likes_label,
        ]);
    }

    public function toggleCommentLike(TimelineComment $comment)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $like = TimelineCommentLike::where('id_comment', $comment->id)->where('id_user', $currentUser->id)->first();
        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            TimelineCommentLike::create(['id_comment' => $comment->id, 'id_user' => $currentUser->id]);
            $liked = true;
        }

        $likesCount = TimelineCommentLike::where('id_comment', $comment->id)->count();
        $likes_label = $likesCount >= 1000 ? round($likesCount/1000, 1) . 'K' : (string) $likesCount;

        return response()->json([
            'liked' => $liked,
            'likes_base' => $likesCount,
            'likes_label' => $likes_label,
        ]);
    }

    public function toggleBookmark(TimelinePost $post)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $bookmark = PostBookmark::where('id_post', $post->id)->where('id_user', $currentUser->id)->first();
        if ($bookmark) {
            $bookmark->delete();
            $bookmarked = false;
        } else {
            PostBookmark::create(['id_post' => $post->id, 'id_user' => $currentUser->id]);
            $bookmarked = true;
        }

        return response()->json([
            'bookmarked' => $bookmarked,
        ]);
    }
}
