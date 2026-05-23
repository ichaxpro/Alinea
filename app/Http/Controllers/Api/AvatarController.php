<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvatarController extends Controller
{
    public function upload(Request $request) {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,webp,gif|max:5120',
        ]);

        $user = $request->user();
        $image = $request->file('avatar');
        $ext = $image->getClientOriginalExtension() ?: 'png';
        $filename = $user->id . '.' . $ext;
        $path = $image->storeAs('avatars', $filename, 'public');

        $user->foto_profil = $path;
        $user->save();

        return response()->json([
            'avatar_url' => Storage::disk('public')->url($path),
        ]);
    }
}
