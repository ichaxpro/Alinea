<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            [
                'key' => 'pionir_literasi',
                'title' => 'Pionir Literasi',
                'description' => 'Berhasil melakukan peminjaman buku pertama kamu di Alinea!',
                'icon' => 'badge_(2).png',
                'criteria_type' => 'borrow_count',
                'criteria_value' => 1,
            ],
            [
                'key' => 'kritikus_andal',
                'title' => 'Kritikus Andal',
                'description' => 'Menyelesaikan ulasan buku pertama!',
                'icon' => 'badge_(2).png',
                'criteria_type' => 'review_count',
                'criteria_value' => 1,
            ],
            [
                'key' => 'sang_kolektor',
                'title' => 'Sang Kolektor',
                'description' => 'Berhasil menambahkan 5 buku pribadi ke dalam katalog.',
                'icon' => 'badge_(2).png',
                'criteria_type' => 'personal_book_count',
                'criteria_value' => 5,
            ],
            [
                'key' => 'rajin_membaca',
                'title' => 'Rajin Membaca',
                'description' => 'Menambahkan 10 buku ke dalam riwayat bacaan pribadi.',
                'icon' => 'badge_(2).png',
                'criteria_type' => 'reading_history_count',
                'criteria_value' => 10,
            ],
            [
                'key' => 'aktif_berbagi',
                'title' => 'Aktif Berbagi',
                'description' => 'Membuat 5 postingan di timeline komunitas.',
                'icon' => 'badge_(2).png',
                'criteria_type' => 'post_count',
                'criteria_value' => 5,
            ],
        ];

        foreach ($achievements as $data) {
            Achievement::firstOrCreate(['key' => $data['key']], $data);
        }
    }
}
