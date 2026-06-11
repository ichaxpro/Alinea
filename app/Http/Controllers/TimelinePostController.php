<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimelinePostRequest;
use App\Models\TimelinePost;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use App\Http\Resources\TimelinePostResource;

class TimelinePostController extends Controller
{

    public function store(StoreTimelinePostRequest $request)
    {
        $validated = $request->validated();

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

        $post = TimelinePost::create([
            'id_user' => auth()->id(),
            'id_klub' => null,
            'judul_buku_dibahas' => $validated['judul_buku_dibahas'] ?? null,
            'pesan' => $validated['pesan'],
            'tag' => $validated['tag'] ?? 'Post',
            'media' => $attachments[0]['path'] ?? null,
            'media_type' => $attachments[0]['type'] ?? null,
            'media_original_name' => $attachments[0]['original_name'] ?? null,
            'media_size' => $attachments[0]['size'] ?? null,
        ]);

        if (!empty($attachments)) {
            $post->attachments()->createMany($attachments);
        }

        $post->load(['author', 'attachments', 'likes']);

        if (!empty($validated['judul_buku_dibahas'])) {
            $now = Carbon::now();
            $startOfWeek = $now->copy()->startOfWeek();
            Cache::forget("trending_weekly_{$startOfWeek->year}_W{$startOfWeek->weekOfYear}");
        }

        return response()->json([
            'message' => 'Postingan berhasil diunggah.',
            'post' => (new TimelinePostResource($post->load(['club', 'author', 'attachments'])))->resolve(),
        ], 201);
    }

    public function destroy(TimelinePost $post)
    {
        if ($post->id_user !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['message' => 'Anda tidak berhak menghapus unggahan ini.'], 403);
        }

        $post->delete();

        return response()->json([
            'message' => 'Unggahan berhasil dihapus.',
        ]);
    }

    public function reportPost(Request $request, TimelinePost $post)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:8'],
        ]);

        \App\Models\ReportPost::create([
            'reporter_id' => $currentUser->id,
            'post_id' => $post->id,
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Laporan berhasil dikirim dan akan segera kami tinjau.',
        ]);
    }
}
