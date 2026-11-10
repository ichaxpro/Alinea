<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PersonalBook;

class E2eSeeder extends Seeder
{
    /**
     * Seed data khusus untuk E2E testing (Playwright).
     * Credentials ini hanya dipakai di environment CI/testing.
     */
    public function run(): void
    {
        // --- User 1: Owner buku ---
        $owner = User::firstOrCreate(
            ['email' => 'owner@test.com'],
            [
                'name'             => 'Owner Test',
                'username'         => 'owner_test',
                'password'         => Hash::make('password123'),
                'kota'             => 'Jakarta',
                'no_telp'          => '081234567890',
                'preferred_genres' => ['Fiksi', 'Non-Fiksi'],
            ]
        );

        // --- User 2: Borrower ---
        User::firstOrCreate(
            ['email' => 'borrower@test.com'],
            [
                'name'             => 'Borrower Test',
                'username'         => 'borrower_test',
                'password'         => Hash::make('password123'),
                'kota'             => 'Bandung',
                'no_telp'          => '089876543210',
                'preferred_genres' => ['Fiksi'],
            ]
        );

        // --- User 3: User ketiga untuk tes klub ---
        User::firstOrCreate(
            ['email' => 'member@test.com'],
            [
                'name'             => 'Member Test',
                'username'         => 'member_test',
                'password'         => Hash::make('password123'),
                'kota'             => 'Surabaya',
                'no_telp'          => '085555555555',
                'preferred_genres' => ['Horror'],
            ]
        );

        // --- Personal Book milik Owner ---
        PersonalBook::firstOrCreate(
            [
                'user_id' => $owner->id,
                'judul'   => 'Laskar Pelangi',
            ],
            [
                'penulis'      => 'Andrea Hirata',
                'kondisi'      => 'baik',
                'is_available' => true,
                'status'       => 'tersedia',
                'deskripsi'    => 'Novel inspiratif dari Belitung.',
            ]
        );

        $this->command->info('E2eSeeder selesai: 3 user tes + 1 buku created.');
    }
}
