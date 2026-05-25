<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\ChatController;
use App\Models\User;

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

    // User search (for "New Chat" modal)
    Route::get('/users', function (Request $request) {
        $q = $request->get('search', '');

        return User::where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('username', 'like', "%{$q}%");
            })
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
});
