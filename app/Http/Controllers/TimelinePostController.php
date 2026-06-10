<?php

namespace App\Http\Controllers;

use App\Models\TimelinePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use App\Services\TimelineFormatterService;

class TimelinePostController extends Controller
{
    public function __construct(
        protected TimelineFormatterService $timelineFormatterService
    ) {}

    public function store(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $validated = $request->validate([
            'judul_buku_dibahas' => ['nullable', 'string', 'max:120'],
            'pesan' => ['required', 'string', 'max:500'],
            'tag' => ['nullable', 'string', 'max:30'],
            'media' => ['nullable', 'array', 'max:4'],
            'media.*' => ['file', 'max:102400'], // max 100MB per file
        ]);

        $files = $request->file('media', []);
        if ($files instanceof \Illuminate\Http\UploadedFile) {
            $files = [$files];
        }

        $attachments = [];
        foreach (array_values($files) as $index => $file) {
            if (!$file) continue;
            
            $path = $file->store('timeline_media', 'public');
            $attachments[] = [
                'path' => $path,
                'type' => $this->timelineFormatterService->detectMediaType($file->getMimeType()),
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'sort_order' => $index,
            ];
        }

        $post = TimelinePost::create([
            'id_user' => $currentUser->id,
            'id_klub' => null, // Global post
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
            'post' => $this->timelineFormatterService->timelinePostPayload($post, $currentUser),
        ], 201);
    }

    public function destroy(TimelinePost $post)
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        // Only allow owner or admin to delete
        if ($post->id_user !== $currentUser->id && $currentUser->role !== 'admin') {
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
