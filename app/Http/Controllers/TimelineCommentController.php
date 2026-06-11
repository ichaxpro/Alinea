<?php

namespace App\Http\Controllers;

use App\Models\TimelinePost;
use App\Models\TimelineComment;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTimelineCommentRequest;
use App\Http\Resources\TimelineCommentResource;
use Illuminate\Support\Carbon;

class TimelineCommentController extends Controller
{

    public function index(Request $request, TimelinePost $post)
    {
        $currentUser = Auth::user();
        $limit = $request->query('limit');

        $query = $post->comments()
            ->with(['author:id,name,username,foto_profil', 'attachments', 'likes'])
            ->orderBy('created_at');

        $total = $query->count();

        if ($limit) {
            $query->limit((int) $limit);
        }

        $comments = TimelineCommentResource::collection($query->get());

        return response()->json([
            'comments' => $comments,
            'total' => $total,
        ]);
    }

    public function store(StoreTimelineCommentRequest $request, TimelinePost $post)
    {
        $validated = $request->validated();

        $comment = TimelineComment::create([
            'id_post' => $post->id,
            'id_user' => auth()->id(),
            'isi_komentar' => $validated['isi_komentar'],
        ]);

        $files = $request->file('media', []);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        $attachments = [];
        foreach (array_values($files) as $index => $file) {
            if (!$file) continue;
            
            $path = $file->store('timeline_media', 'public');
            $attachments[] = [
                'path' => $path,
                'type' => \Illuminate\Support\Str::before($file->getMimeType(), '/'),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'sort_order' => $index,
            ];
        }

        if (!empty($attachments)) {
            $comment->attachments()->createMany($attachments);
        }

        $comment->load(['author', 'attachments']);

        if ($post->id_user !== auth()->id()) {
            $author = \App\Models\User::find($post->id_user);
            if ($author) {
                $author->notify(new \App\Notifications\PostCommented(auth()->user(), $post, $comment));
            }
        }

        return response()->json([
            'message' => 'Komentar berhasil dikirim.',
            'comment' => (new TimelineCommentResource($comment))->resolve(),
            'comments_count' => $post->comments()->count(),
        ], 201);
    }

    public function destroy(TimelineComment $comment)
    {
        if ($comment->id_user !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Komentar berhasil dihapus.',
        ]);
    }
}
