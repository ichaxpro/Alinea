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
use App\Models\User;

Route::get('/', function () {
    return view('welcome');
})->name('beranda');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/daftar_akun', function () {
    return view('daftar_akun');
})->name('daftar');

Route::get('/pinjam', function () {
    return view('pinjam');
})->name('pinjam');

Route::get('/timeline_home', function () {
    return view('timeline_home');
})->name('timeline_home');


Route::get('/timeline_komunitas', [KlubController::class, 'timelineKomunitas'])->name('timeline_komunitas');
Route::post('/timeline_komunitas/posts', [KlubController::class, 'storeTimelinePost'])->name('timeline_posts.store');

Route::get('/klub', [KlubController::class, 'index'])->name('klub');

// Create club endpoint used by the klub page (AJAX)
Route::post('/klub', [KlubController::class, 'store']);
Route::patch('/klub/{club}', [KlubController::class, 'update'])->name('klub.update');
Route::delete('/klub/{club}', [KlubController::class, 'destroy'])->name('klub.destroy');
Route::post('/klub/{club}/join', [KlubController::class, 'join'])->name('klub.join');
Route::post('/klub/{club}/leave', [KlubController::class, 'leave'])->name('klub.leave');
Route::get('/klub/{club}/payload', [KlubController::class, 'payload'])->name('klub.payload');

Route::get('/timeline_profile', function () {
    return view('timeline_profile');
})->name('timeline_profile');

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

Route::middleware('auth')->group(function () {
    Route::get('/personal-books', [PersonalBookController::class, 'index']);
    Route::post('/personal-books', [PersonalBookController::class, 'store']);
    Route::patch('/personal-books/{book}', [PersonalBookController::class, 'update']);
    Route::delete('/personal-books/{book}', [PersonalBookController::class, 'destroy']);
    Route::post('/upload-avatar', [AvatarController::class, 'upload']);
    Route::get('/timeline_komunitas/posts/{post}/comments', [KlubController::class, 'timelineComments'])->name('timeline_posts.comments.index');
    Route::post('/timeline_komunitas/posts/{post}/comments', [KlubController::class, 'storeTimelineComment'])->name('timeline_posts.comments.store');
});