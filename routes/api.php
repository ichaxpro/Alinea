<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\BookController;
use App\Models\User;
use App\Models\BookClub;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\FeaturedBook;

/*
|--------------------------------------------------------------------------
| Existing routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login',    [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/logout',   [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/upload-avatar', [AvatarController::class, 'upload'])->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Chat — all routes require web session auth (not Sanctum token)
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth'])->group(function () {

    Route::get('/search', function(Request $request) {
        $q = $request->input('q', '');

        if (strlen(trim($q)) < 2) {
            return response()->json(['users' => [], 'clubs' => [], 'books' => []]);
        }

        $users = User::where(function($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('username', 'like', "%{$q}%");
        })
        ->where('is_banned', false)
        ->where('id', '!=', Auth::id())
        ->select('id', 'name', 'username', 'foto_profil')
        ->limit(5)
        ->get()
        ->map(fn($u) => [
            'id'            => $u->id,
            'name'          => $u->name,
            'username'      => $u->username ?? '',
            'avatar_url'    => $u->avatar_url,
            'initial'       => strtoupper(substr($u->name, 0, 1)),
        ]);

        $clubs = BookClub::where('nama_klub', 'like', "%{$q}%")
            ->limit(5)
            ->get()
            ->map(function($c) {
                $memberCount = 0;
                if (Schema::hasTable('klub_member')) {
                    $memberCount = DB::table('klub_member')
                        ->where('id_klub', $c->id)
                        ->count();
                }
                return [
                    'id'                => $c->id,
                    'nama_klub'         => $c->nama_klub,
                    'kategori'          => $c->kategori,
                    'foto_klub'         => $c->foto_klub ? asset('storage/' . $c->foto_klub) : null,
                    'gradient_from'     => $c->gradient_from ?? '#FFDDAF',
                    'gradient_to'       => $c->gradient_to ?? '#C7E7FF',
                    'member_count'      => $memberCount,
                ];
            });

        $books = FeaturedBook::where(function ($query) use ($q) {
                $query->where('judul', 'like', "%{$q}%")
                    ->orWhere('penulis', 'like', "%{$q}%");
        })
        ->select('id', 'judul', 'penulis', 'cover_url', 'isbn', 'gradient_from', 'gradient_to')
        ->limit(5)
        ->get()
        ->map(fn($b) => [
            'id'            => $b->id,
            'judul'         => $b->judul,
            'penulis'       => $b->penulis,
            'cover_url'     => $b->cover_url,
            'isbn'          => $b->isbn ?? '',
            'gradient_from' => $b->gradient_from ?? '#C7E7FF',
            'gradient_to'   => $b->gradient_to ?? '#FFDDAF',
        ]);

        return response()->json([
            'users' => $users,
            'clubs' => $clubs,
            'books' => $books,
        ]);
    });

    // Conversations
    Route::get('/chat/conversations',           [ChatController::class, 'conversations']);
    Route::post('/chat/conversations',          [ChatController::class, 'startConversation']);

    // Messages within a conversation
    Route::get('/chat/conversations/{id}/messages',            [ChatController::class, 'messages']);
    Route::post('/chat/conversations/{id}/messages',           [ChatController::class, 'sendMessage']);
    Route::delete('/chat/conversations/{id}/messages/{msgId}', [ChatController::class, 'deleteMessage']);

    // Utility
    Route::post('/chat/conversations/{id}/read',    [ChatController::class, 'markAsRead']);
    Route::post('/chat/conversations/{id}/typing',  [ChatController::class, 'typing']);

    // ── User Detail Panel ────────────────────────────────────────────
    Route::get('/chat/conversations/{id}/media',    [ChatController::class, 'conversationMedia']);
    Route::delete('/chat/conversations/{id}',        [ChatController::class, 'deleteConversation']);
    Route::post('/users/{userId}/report',            [ChatController::class, 'reportUser']);
    Route::post('/users/{userId}/block',             [ChatController::class, 'blockUser']);

    // User search (for "New Chat" modal)
    Route::get('/users', function (Request $request) {
        $q = $request->get('search', '');

        return User::where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('username', 'like', "%{$q}%");
            })
            ->where('is_banned', false)
            ->excludeBlocked()
            ->where('id', '!=', Auth::id())
            ->select('id', 'name', 'username', 'foto_profil')
            ->limit(10)
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'username'   => $u->username ?? '',
                'avatar_url' => $u->avatar_url,
                'initial'    => strtoupper(substr($u->name, 0, 1)),
            ]);
    });

    Route::get('/reviews/stats', [ReviewController::class, 'stats']);
    Route::get('/reviews/{bookIdentifier}', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/reviews/{id}/helpful', [ReviewController::class, 'helpful']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/bookmarks', [BookmarkController::class, 'store']);
    Route::get('/bookmarks/check', [BookmarkController::class, 'check']);

    Route::get('/books/{param}/similar', [BookController::class, 'similarBooks']);
});
