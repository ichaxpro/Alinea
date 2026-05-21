<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'kota' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'kota' => $request->kota,
            'no_telp' => $request->no_telp, 
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau password salah',
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout',
        ]);
    }

    public function loginWeb(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors(['email' => 'Email atau password salah']);
        }

        return redirect()->intended('/dashboard');
    }

    public function registerWeb(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'kota' => 'required|string|max:255',
            'no_telp' => 'required|string|max:20',
            'genres' => 'nullable|array',
        ]);

        $genres = $request->input('genres', []);
        $normalized = array_map(function($g) {
            $map = [
                'Non-fiksi' => 'Non-Fiksi',
                'Sci-fi' => 'Sci-Fi',
                'Horror' => 'Horror',
            ];
            return $map[$g] ?? $g;
        }, $genres);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'kota' => $request->kota,
            'no_telp' => $request->no_telp, 
            'preferred_genres' => $normalized,
        ]);
        
        Auth::login($user);
        return redirect('/dashboard');
    }

    public function logoutWeb(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
