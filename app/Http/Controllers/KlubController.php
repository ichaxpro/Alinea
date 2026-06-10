<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BookClub;
use App\Models\FeaturedBook;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\KlubService;

class KlubController extends Controller
{
    public function __construct(
        protected KlubService $klubService
    ) {}

    /**
     * Store a newly created book club and return JSON for immediate UI update.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Allowed categories come from katalog (FeaturedBook.genres)
        $allowed = FeaturedBook::all()->pluck('genres')->flatten()->unique()->values()->all();
        if (empty($allowed)) {
            // Fallback to existing klub categories if katalog is empty
            $allowed = BookClub::distinct()->pluck('kategori')->all();
        }

        $data = $request->validate([
            'nama_klub' => 'required|string|max:100',
            'kategori' => ['required', 'string', 'max:100', Rule::in($allowed)],
            'deskripsi' => 'required|string|max:500',
            'gradient_from' => 'nullable|string|max:25',
            'gradient_to' => 'nullable|string|max:25',
            'foto_klub' => 'nullable|image|max:2048',
        ]);

        $club = BookClub::create([
            'nama_klub' => $data['nama_klub'],
            'kategori' => $data['kategori'],
            'deskripsi' => $data['deskripsi'],
            'gradient_from' => $data['gradient_from'] ?? '#FFDDAF',
            'gradient_to' => $data['gradient_to'] ?? '#C7E7FF',
            'id_owner' => $user ? $user->id : null,
            'member_count' => $user ? 1 : 0,
        ]);

        if ($user && Schema::hasTable('klub_member')) {
            DB::table('klub_member')->insert([
                'id_klub' => $club->id,
                'id_user' => $user->id,
                'role_di_klub' => 'owner',
                'joined_at' => now(),
            ]);
        }

        if ($request->hasFile('foto_klub')) {
            $path = $request->file('foto_klub')->store('klubs', 'public');
            $club->foto_klub = $path;
            $club->save();
        }

        return response()->json($this->klubService->clubPayload($club->fresh(), $user?->id), 201);
    }

    public function join(Request $request, BookClub $club)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (Schema::hasTable('klub_member')) {
            $alreadyJoined = DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $user->id)
                ->exists();

            if (!$alreadyJoined) {
                DB::table('klub_member')->insert([
                    'id_klub' => $club->id,
                    'id_user' => $user->id,
                    'role_di_klub' => 'member',
                    'joined_at' => now(),
                ]);
                $club->increment('member_count');
            }
        }

        return response()->json($this->klubService->clubPayload($club->fresh(), $user->id));
    }

    public function leave(Request $request, BookClub $club)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ((int) ($club->id_owner ?? 0) === (int) $user->id) {
            return response()->json(['message' => 'Owner tidak bisa keluar dari klubnya sendiri.'], 422);
        }

        if (Schema::hasTable('klub_member')) {
            $deleted = DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $user->id)
                ->delete();
            
            if ($deleted) {
                $club->decrement('member_count');
            }
        }

        return response()->json($this->klubService->clubPayload($club->fresh(), $user->id));
    }

    public function update(Request $request, BookClub $club)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ((int) ($club->id_owner ?? 0) !== (int) $user->id) {
            return response()->json(['message' => 'Hanya owner yang bisa mengubah klub.'], 403);
        }

        $allowed = FeaturedBook::all()->pluck('genres')->flatten()->unique()->values()->all();
        if (empty($allowed)) {
            $allowed = BookClub::distinct()->pluck('kategori')->all();
        }

        $data = $request->validate([
            'nama_klub' => 'sometimes|nullable|string|max:100',
            'kategori' => ['sometimes','nullable', 'string', 'max:100', Rule::in($allowed)],
            'deskripsi' => 'sometimes|nullable|string|max:500',
            'gradient_from' => 'nullable|string|max:25',
            'gradient_to' => 'nullable|string|max:25',
            'foto_klub' => 'nullable|image|max:2048',
        ]);

        $club->update([
            'nama_klub' => $request->filled('nama_klub') ? $data['nama_klub'] : $club->nama_klub,
            'kategori' => $request->filled('kategori') ? $data['kategori'] : $club->kategori,
            'deskripsi' => $request->filled('deskripsi') ? $data['deskripsi'] : $club->deskripsi,
            'gradient_from' => $data['gradient_from'] ?? $club->gradient_from ?? '#FFDDAF',
            'gradient_to' => $data['gradient_to'] ?? $club->gradient_to ?? '#C7E7FF',
        ]);

        if ($request->hasFile('foto_klub')) {
            $path = $request->file('foto_klub')->store('klubs', 'public');
            $club->foto_klub = $path;
            $club->save();
        }

        return response()->json($this->klubService->clubPayload($club->fresh(), $user->id));
    }

    public function destroy(Request $request, BookClub $club)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ((int) ($club->id_owner ?? 0) !== (int) $user->id) {
            return response()->json(['message' => 'Hanya owner yang bisa menghapus klub.'], 403);
        }

        if (Schema::hasTable('klub_member')) {
            DB::table('klub_member')->where('id_klub', $club->id)->delete();
        }

        $club->delete();

        return response()->json(['message' => 'Klub berhasil dihapus.']);
    }

    /**
     * Show klub page with clubs loaded from database.
     */
    public function index()
    {
        $currentUser = Auth::user();
        $clubs = BookClub::with('owner')->get()->map(fn ($club) => $this->klubService->clubPayload($club, $currentUser?->id));

        // Categories should align with katalog genres (FeaturedBook.genres)
        $categories = FeaturedBook::all()->pluck('genres')->flatten()->unique()->sort()->values();

        return view('klub', compact('clubs', 'categories', 'currentUser'));
    }

    /**
     * Return JSON payload for a single club (used by client to re-sync state).
     */
    public function payload(BookClub $club)
    {
        $user = Auth::user();
        return response()->json($this->klubService->clubPayload($club, $user?->id));
    }

    public function kickMember(Request $request, BookClub $club, int $userId) {
        $currentUser = Auth::user();

        if (!$currentUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $myRow = DB::table('klub_member')
            ->where('id_klub', $club->id)
            ->where('id_user', $currentUser->id)
            ->first();
        
        $isOwner = (int) ($club->id_owner ?? 0) === (int) $currentUser->id;
        $isAdmin = $myRow && in_array($myRow->role_di_klub, ['admin', 'moderator']);

        if (!$isOwner && !$isAdmin) {
            return response()->json(['message' => 'Hanya owner atau admin yang bisa kick member.'], 403);
        } 

        if ((int) ($club->id_owner ?? 0) === (int) $userId) {
            return response()->json(['message' => 'Owner tidak bisa di-kick dari klub.'], 422);
        }

        if (!$isOwner && $isAdmin) {
            $targetRow = DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $userId)
                ->first();
            
            if ($targetRow && in_array($targetRow->role_di_klub, ['admin', 'moderator'])) {
                return response()->json(['message' => 'Admin tidak bisa kick admin lain.'], 403);
            }
        }

        if ((int) $userId === (int) $currentUser->id) {
            return response()->json(['message' => 'Gunakan fitur "Keluar Klub" untuk meninggalkan klub.'], 422);
        }

        if (Schema::hasTable('klub_member')) {
            $deleted = DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $userId)
                ->delete();
            
            if ($deleted) {
                $club->decrement('member_count');
            }
        }

        return response()->json($this->klubService->clubPayload($club->fresh(), $currentUser->id));
    }

    public function updateMemberRole(Request $request, BookClub $club, int $userId) {
        $currentUser = Auth::user();

        if (!$currentUser) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $isOwner = (int) ($club->id_owner ?? 0) === (int) $currentUser->id;

        if (!$isOwner) {
            return response()->json(['message' => 'Hanya owner yang bisa mengubah role member.'], 403);
        }

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:member,admin,owner'],
        ]);

        $newRole = $validated['role'];

        if (Schema::hasTable('klub_member')) {
            $targetExists = DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $userId)
                ->exists();
            if (!$targetExists) {
                return response()->json(['message' => 'User bukan anggota klub ini.'], 404);
            }
        }

        if ($newRole === 'owner') {
            $club->id_owner = $userId;
            $club->save();

            DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $currentUser->id)
                ->update(['role_di_klub' => 'admin']);
            
            DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $userId)
                ->update(['role_di_klub' => 'owner']);
        } else {
            DB::table('klub_member')
                ->where('id_klub', $club->id)
                ->where('id_user', $userId)
                ->update(['role_di_klub' => $newRole]);
        }

        return response()->json($this->klubService->clubPayload($club->fresh(), $currentUser->id));
    }
}
