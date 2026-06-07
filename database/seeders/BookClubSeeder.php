<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BookClub;
use App\Models\User;
use Faker\Factory as Faker;

class BookClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Pastikan ada setidaknya satu user untuk dijadikan owner
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin User',
                'username' => 'admin_user',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        $categories = [
            'Fiksi', 'Non-Fiksi', 'Sastra', 'Misteri', 'Romansa',
            'Sci-Fi', 'Fantasi', 'Sejarah', 'Biografi', 'Self-Help'
        ];

        $gradients = [
            ['from' => '#FFDDAF', 'to' => '#C7E7FF'],
            ['from' => '#C7E7FF', 'to' => '#D4F6FF'],
            ['from' => '#D4F6FF', 'to' => '#FFDDAF'],
            ['from' => '#FFDDAF', 'to' => '#D4F6FF'],
            ['from' => '#C7E7FF', 'to' => '#FFDDAF'],
        ];

        for ($i = 0; $i < 10; $i++) {
            $gradient = $faker->randomElement($gradients);
            
            BookClub::create([
                'nama_klub' => ucwords($faker->words(3, true)) . ' Club',
                'kategori' => $faker->randomElement($categories),
                'deskripsi' => $faker->paragraph(3),
                'foto_klub' => null, // Biarkan null, frontend akan menampilkan gradient
                'id_owner' => $user->id,
                'gradient_from' => $gradient['from'],
                'gradient_to' => $gradient['to'],
            ]);
        }
    }
}
