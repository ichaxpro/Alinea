<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\KlubController;
use App\Http\Controllers\BookController;
use App\Models\FeaturedBook;
use App\Http\Controllers\Api\PersonalBookController;
use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\ProfileController;
use App\Models\User;
use App\Models\PersonalBook;

Route::get('/', function () {
    return view('welcome');
})->name('beranda');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/daftar_akun', function () {
    return view('daftar_akun');
})->name('daftar');

// Route::get('/pinjam', function () {
//     return view('pinjam');
// })->name('pinjam');

Route::get('/pinjam', function () {
    // Ambil semua buku yang tersedia, berserta data pemiliknya
    $books = PersonalBook::with('user')
                ->where('is_available', true)
                ->where('status', 'tersedia')
                ->latest()
                ->get();
    return view('pinjam', compact('books'));
})->name('pinjam');

use App\Http\Controllers\TimelineController;

Route::get('/timeline_home', [TimelineController::class, 'index'])->name('timeline_home');
Route::get('/timeline_simpanan', [TimelineController::class, 'simpanan'])->name('timeline_simpanan');
Route::post('/timeline_home/posts', [TimelineController::class, 'store'])->name('timeline_home.store');
Route::post('/timeline/posts/{post}/bookmark', [TimelineController::class, 'toggleBookmark'])->name('timeline.bookmark');
Route::post('/timeline_home/posts/{post}/like', [TimelineController::class, 'toggleLike'])->name('timeline_home.like');
Route::get('/timeline_home/posts/{post}/comments', [TimelineController::class, 'comments'])->name('timeline_home.comments');
Route::post('/timeline_home/posts/{post}/comments', [TimelineController::class, 'storeComment'])->name('timeline_home.comments.store');
Route::post('/timeline_home/comments/{comment}/like', [TimelineController::class, 'toggleCommentLike'])->name('timeline_home.comments.like');


Route::get('/timeline_komunitas', [KlubController::class, 'timelineKomunitas'])->name('timeline_komunitas');
Route::post('/timeline_komunitas/posts', [KlubController::class, 'storeTimelinePost'])->name('timeline_posts.store');

Route::get('/klub', [KlubController::class, 'index'])->name('klub');

// Create club endpoint used by the klub page (AJAX)
Route::post('/klub', [KlubController::class, 'store']);
Route::patch('/klub/{club}', [KlubController::class, 'update'])->name('klub.update');
Route::delete('/klub/{club}', [KlubController::class, 'destroy'])->name('klub.destroy');
Route::post('/klub/{club}/join', [KlubController::class, 'join'])->name('klub.join');
Route::post('/klub/{club}/leave', [KlubController::class, 'leave'])->name('klub.leave');
Route::delete('/klub/{club}/members/{userId}', [KlubController::class, 'kickMember']);
Route::patch('/klub/{club}/members/{userId}/role', [KlubController::class, 'updateMemberRole'])->name('klub.members.role');
Route::get('/klub/{club}/payload', [KlubController::class, 'payload'])->name('klub.payload');

Route::get('/timeline_profile', [ProfileController::class, 'show'])->name('timeline_profile');
Route::get('/u/{username}', [ProfileController::class, 'show'])->name('profile.by_username');

Route::get('/u/{user}/followers', [ProfileController::class, 'followersList'])->name('profile.followers');
Route::get('/u/{user}/following', [ProfileController::class, 'followingList'])->name('profile.following');



Route::get('/chat', function () {
    return view('chat');
})->name('chat');

Route::get('/katalog', function() {
    return view('katalog', ['featuredBooks' => FeaturedBook::all()]);
})->name('katalog');

Route::get('/detail-buku/{param}', [BookController::class, 'detail'])->name('detail_buku');

Route::get('/user/{id}', function ($id) {
    $user = User::findOrFail($id);
    return view('user_profile', compact('user'));
})->name('user_profile');

Route::get('/dashboard', function() {
    return view('dashboard', [
        'user' => Auth::user(),
        'featuredBooks' => FeaturedBook::all(),
    ]);
})->middleware('auth')->name('dashboard');

Route::get('/daftar', function() {
    return view('daftar_akun');
})->name('register');

Route::get('/timeline_notifikasi', function () {
    return view('timeline_notifikasi');
})->name('timeline_notifikasi');

Route::get('/lupa_akun', function () {
    return view('lupa_akun');
})->name('lupa_akun');

Route::post('/login', [AuthController::class, 'loginWeb']);
Route::post('/daftar', [AuthController::class, 'registerWeb']);
Route::post('/logout', [AuthController::class, 'logoutWeb'])->middleware('auth');

use App\Http\Controllers\Api\TransactionController;

Route::middleware('auth')->group(function () {
    Route::get('/personal-books', [PersonalBookController::class, 'index']);
    Route::post('/personal-books', [PersonalBookController::class, 'store']);
    Route::patch('/personal-books/{book}', [PersonalBookController::class, 'update']);
    Route::delete('/personal-books/{book}', [PersonalBookController::class, 'destroy']);
    Route::post('/upload-avatar', [AvatarController::class, 'upload']);
    
    // Transactions
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions/incoming', [TransactionController::class, 'incomingRequests']);
    Route::get('/transactions/outgoing', [TransactionController::class, 'outgoingRequests']);
    Route::patch('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus']);
    Route::patch('/transactions/{transaction}/request-return', [TransactionController::class, 'requestReturn']);
    Route::patch('/transactions/{transaction}/accept-return', [TransactionController::class, 'acceptReturn']);
    Route::get('/timeline_komunitas/posts/{post}/comments', [KlubController::class, 'timelineComments'])->name('timeline_posts.comments.index');
    Route::post('/timeline_komunitas/posts/{post}/comments', [KlubController::class, 'storeTimelineComment'])->name('timeline_posts.comments.store');

    // Profile edit
    Route::get('/timeline_profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/timeline_profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/timeline_profile/foto', [ProfileController::class, 'updateFoto'])->name('profile.update_foto');
    Route::post('/u/{user}/follow', [ProfileController::class, 'toggleFollow'])->name('profile.follow');

    Route::post('/profile/reading-books', [ProfileController::class, 'storeReadingBook'])->name('profile.reading-books.store');
    Route::put('/profile/reading-books/{book}', [ProfileController::class, 'updateReadingBook'])->name('profile.reading-books.update');
    Route::delete('/profile/reading-books/{book}', [ProfileController::class, 'destroyReadingBook'])->name('profile.reading-books.destroy');
    
    Route::get('/api/books/autocomplete', [App\Http\Controllers\BookController::class, 'searchAutocomplete'])->name('api.books.autocomplete');
});