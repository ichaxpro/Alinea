<?php

namespace Database\Seeders;

use App\Models\PersonalBook;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data deterministik khusus E2E (Playwright).
 *
 * Pakai: php artisan migrate:fresh --seed --seeder=E2ESeeder
 * JANGAN dijalankan di production.
 */
class E2ESeeder extends Seeder
{
    public const BOOK_TITLE = 'Laskar Pelangi E2E';

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('E2ESeeder tidak boleh dijalankan di production.');
        }

        $password = env('E2E_PASSWORD', 'e2e-Pass-2026');

        $accounts = [
            ['name' => 'E2E Admin',    'username' => 'e2e_admin',    'email' => 'admin@alinea.test',    'role' => User::ROLE_ADMIN],
            ['name' => 'E2E Owner',    'username' => 'e2e_owner',    'email' => 'owner@alinea.test',    'role' => User::ROLE_USER],
            ['name' => 'E2E Borrower', 'username' => 'e2e_borrower', 'email' => 'borrower@alinea.test', 'role' => User::ROLE_USER],
        ];

        $users = [];
        foreach ($accounts as $a) {
            $users[$a['username']] = User::updateOrCreate(
                ['email' => $a['email']],
                $a + ['password' => $password, 'kota' => 'Malang', 'no_telp' => '08123456789', 'preferred_genres' => ['Fiksi']],
            );
        }

        // Satu buku milik "owner" yang tersedia untuk dipinjam "borrower".
        PersonalBook::updateOrCreate(
            ['user_id' => $users['e2e_owner']->id, 'judul' => self::BOOK_TITLE],
            [
                'penulis' => 'Andrea Hirata',
                'kategori' => 'Fiksi',
                'is_available' => true,
                'status' => 'tersedia',
            ],
        );
    }
}
