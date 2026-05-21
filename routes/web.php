<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\AuthController;

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

Route::get('/klub', function () {
    return view('klub');
})->name('klub');

Route::get('/timeline_profile', function () {
    return view('timeline_profile');
})->name('timeline_profile');

Route::get('/chat', function () {
    return view('chat');
})->name('chat');

Route::get('/ulasan', function() {
    return view('ulasan');
})->name('ulasan');

Route::get('/ulasan_detail', function() {
    return view('ulasan_detail');
})->name('ulasan_detail');

Route::get('/dashboard', function() {
    return view('dashboard', ['user' => Auth::user()]);
})->middleware('auth')->name('dashboard');

Route::get('/daftar', function() {
    return view('daftar_akun');
})->name('register');

Route::post('/login', [AuthController::class, 'loginWeb']);
Route::post('/daftar', [AuthController::class, 'registerWeb']);
Route::post('/logout', [AuthController::class, 'logoutWeb'])->middleware('auth');