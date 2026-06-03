<?php

namespace Database\Seeders;

use App\Models\TimelinePost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Filament\Tables\Columns\ImageColumn;

class TimelinePostSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id');

        if ($userIds->isEmpty()) {
            $this->command->warn('Tidak ada user — buat user dulu.');
            return;
        }

        $posts = [
            // Buku yang paling banyak dibahas — biar muncul di trending
            ['judul' => 'Laskar Pelangi',       'tag' => 'Selesai', 'days_ago' => 1],
            ['judul' => 'Laskar Pelangi',       'tag' => 'Dibaca',  'days_ago' => 2],
            ['judul' => 'Laskar Pelangi',       'tag' => 'Kutipan', 'days_ago' => 3],
            ['judul' => 'Laskar Pelangi',       'tag' => 'Dibaca',  'days_ago' => 5],
            ['judul' => 'Laskar Pelangi',       'tag' => 'Selesai', 'days_ago' => 6],

            ['judul' => 'Atomic Habits',        'tag' => 'Selesai', 'days_ago' => 0],
            ['judul' => 'Atomic Habits',        'tag' => 'Kutipan', 'days_ago' => 1],
            ['judul' => 'Atomic Habits',        'tag' => 'Dibaca',  'days_ago' => 2],
            ['judul' => 'Atomic Habits',        'tag' => 'Dibaca',  'days_ago' => 4],

            ['judul' => 'Bumi Manusia',         'tag' => 'Selesai', 'days_ago' => 0],
            ['judul' => 'Bumi Manusia',         'tag' => 'Selesai', 'days_ago' => 1],
            ['judul' => 'Bumi Manusia',         'tag' => 'Dibaca',  'days_ago' => 3],

            ['judul' => 'Filosofi Teras',       'tag' => 'Dibaca',  'days_ago' => 0],
            ['judul' => 'Filosofi Teras',       'tag' => 'Kutipan', 'days_ago' => 2],

            ['judul' => 'Sapiens',              'tag' => 'Selesai', 'days_ago' => 1],

            // Post tanpa judul buku — ikut terpilih juga di feed
            ['judul' => null,                   'tag' => 'Diskusi', 'days_ago' => 0],
            ['judul' => null,                   'tag' => 'Diskusi', 'days_ago' => 1],
            ['judul' => null,                   'tag' => 'Dibaca',  'days_ago' => 3],

            // Post dari minggu lalu — tidak masuk trending minggu ini
            ['judul' => 'Harry Potter',         'tag' => 'Selesai', 'days_ago' => 10],
            ['judul' => 'Harry Potter',         'tag' => 'Dibaca',  'days_ago' => 11],
            ['judul' => 'Toko Kelontong Namiya','tag' => 'Selesai', 'days_ago' => 12],
        ];

        $pesan = [
            'Laskar Pelangi' => 'Buku ini bikin aku nangis, salut sama perjuangan anak-anak Belitung.',
            'Atomic Habits'  => 'Perubahan kecil sehari-hari bisa berdampak besar.',
            'Bumi Manusia'   => 'Minke keren banget, perjuangan pribumi di jaman kolonial.',
            'Filosofi Teras' => 'Stoisisme versi Indonesia, relevan banget buat kehidupan sehari-hari.',
            'Sapiens'        => 'Perjalanan panjang umat manusia yang bikin melek sejarah.',
            'default'        => 'Baru selesai baca buku ini, recommended banget!',
            'Diskusi'        => 'Ada yang udah baca buku bagus rekomendasi?',
            'Dibaca'         => 'Baru mulai baca, semoga bagus.',
        ];

        $now = now();

        foreach ($posts as $i => $p) {
            $judul = $p['judul'];
            $body = $pesan[$judul] ?? $pesan['default'];

            if ($judul === null) {
                $body = $pesan[['Diskusi', 'Diskusi', 'Dibaca'][$i - 15]] ?? $pesan['default'];
            }

            TimelinePost::create([
                'id_user' => $userIds->random(),
                'id_klub' => null,
                'judul_buku_dibahas' => $judul,
                'pesan' => $body,
                'tag' => $p['tag'],
                'created_at' => $now->copy()->subDays($p['days_ago']),
                'updated_at' => $now->copy()->subDays($p['days_ago']),
            ]);
        }

        $this->command->info('Seeder ' . count($posts) . ' postingan berhasil.');
    }
}
