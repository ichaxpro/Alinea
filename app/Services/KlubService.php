<?php

namespace App\Services;

use App\Models\BookClub;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KlubService
{
    public function avatarUrl(?string $username, ?string $name = null): ?string
    {
        $seed = $username ?: $name;

        if (!$seed) {
            return null;
        }

        return 'https://api.dicebear.com/7.x/thumbs/svg?seed=' . urlencode($seed);
    }

    public function memberPayload($user, string $role): array
    {
        $name = $user?->name ?? $user?->nama ?? 'Member';
        $username = $user?->username ?? null;

        return [
            'id' => $user?->id,
            'name' => $name,
            'username' => $username,
            'avatar' => $this->avatarUrl($username, $name),
            'role' => $role,
        ];
    }

    public function clubMemberRows(BookClub $club)
    {
        if (!Schema::hasTable('klub_member')) {
            return collect();
        }

        return DB::table('klub_member')
            ->leftJoin('users', 'klub_member.id_user', '=', 'users.id')
            ->where('klub_member.id_klub', $club->id)
            ->orderByRaw("CASE klub_member.role_di_klub WHEN 'owner' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END")
            ->select([
                'users.id as user_id',
                'users.foto_profil',
                'users.name',
                'users.username',
                'klub_member.role_di_klub as role',
            ])
            ->get();
    }

    public function clubPayload(BookClub $club, ?int $currentUserId = null): array
    {
        $club->loadMissing(['owner']);

        $ownerUser = $club->owner;

        $ownerName = $ownerUser?->name ?? 'Admin';
        $ownerUsername = $ownerUser?->username ?? null;
        // Prefer uploaded avatar on the User model (avatar_url) when available
        $ownerAvatar = $ownerUser?->avatar_url ?? $this->avatarUrl($ownerUsername, $ownerName);

        $membersData = $this->clubMemberRows($club)->map(function ($member) use ($club) {
            $name = $member->name ?: 'Member';
            $username = $member->username ?: null;
            $role = $member->role ?? 'member';

            if ((int) ($club->id_owner ?? 0) === (int) ($member->user_id ?? 0)) {
                $role = 'owner';
            } elseif ((($member->role ?? '') === 'admin')) {
                $role = 'admin';
            }

            // Prefer user's uploaded profile photo if present
            if (!empty($member->foto_profil)) {
                $avatar = asset('storage/' . $member->foto_profil);
            } else {
                $avatar = $this->avatarUrl($username, $name);
            }

            return [
                'id' => $member->user_id,
                'name' => $name,
                'username' => $username,
                'avatar' => $avatar,
                'role' => $role,
            ];
        })->values();

        if ($membersData->isEmpty() && $ownerUser) {
            // Ensure owner appears in members list with their real avatar when no klub_member rows exist
            $ownerPayload = $this->memberPayload($ownerUser, 'owner');
            // If owner has uploaded foto_profil, override avatar
            if (!empty($ownerUser?->foto_profil)) {
                $ownerPayload['avatar'] = asset('storage/' . $ownerUser->foto_profil);
            } elseif (!empty($ownerUser?->avatar_url)) {
                $ownerPayload['avatar'] = $ownerUser->avatar_url;
            }

            $membersData->push($ownerPayload);
        }

        // Determine admin from klub_member (if any); fallback to owner. Prefer uploaded avatar when available.
        $adminMember = $membersData->firstWhere('role', 'admin');
        $adminName = $adminMember['name'] ?? $ownerName;
        $adminUsername = $adminMember['username'] ?? $ownerUsername;
        $adminAvatar = $adminMember['avatar'] ?? $ownerUser?->avatar_url ?? $this->avatarUrl($adminUsername, $adminName);

        // Use klub_member rows as the single source of truth for counts
        $membersCount = $membersData->count();

        $joined = $currentUserId
            ? $membersData->contains(fn ($member) => (int) ($member['id'] ?? 0) === $currentUserId)
            : false;

        return [
            'id' => $club->id,
            'name' => $club->nama_klub,
            'category' => $club->kategori,
            'members' => $membersCount,
            'founded' => $club->created_at ? $club->created_at->locale('id')->translatedFormat('d F Y') : null,
            'description' => (strlen($club->deskripsi) > 160 ? Str::limit($club->deskripsi, 160) : $club->deskripsi),
            'full_description' => $club->deskripsi,
            'admin' => $adminName,
            'admin_username' => $adminUsername,
            'admin_avatar' => $adminAvatar,
            'owner' => [
                'id' => $club->id_owner,
                'name' => $ownerName,
                'username' => $ownerUsername,
                'avatar' => $ownerAvatar,
                'role' => 'owner',
            ],
            'joined' => $joined,
            'foto_klub' => $club->foto_klub ? asset('storage/' . $club->foto_klub) : null,
            'members_list' => $membersData->pluck('name')->values()->all(),
            'members_data' => $membersData->toArray(),
            'recent_books' => [],
            'schedule' => $club->jadwal ?? null,
            'gradient_from' => $club->gradient_from ?? '#FFDDAF',
            'gradient_to' => $club->gradient_to ?? '#C7E7FF',
        ];
    }
}
