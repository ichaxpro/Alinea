<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FeaturedBook;

class FeaturedBookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            ['judul'=>'Laut Bercerita','penulis'=>'Leila S. Chudori','tahun'=>2017,'sinopsis'=>'Kisah aktivis mahasiswa yang menghilang di era Orde Baru.','genres'=>['Fiksi','Sejarah']],
            ['judul'=>'Cantik Itu Luka','penulis'=>'Eka Kurniawan','tahun'=>2002,'sinopsis'=>'Dewi Ayu bangkit dari kubur dan menyaksikan dunia penuh kekerasan dan kecantikan.','genres'=>['Fiksi']],
            ['judul'=>'Ronggeng Dukuh Paruk','penulis'=>'Ahmad Tohari','tahun'=>1982,'sinopsis'=>'Srintil menjadi ronggeng yang dikagumi, terjebak pusaran tragedi politik.','genres'=>['Fiksi','Klasik']],
            ['judul'=>'Pulang','penulis'=>'Tere Liye','tahun'=>2015,'sinopsis'=>'Bujang mencari arti pulang di dunia bayangan penuh intrik.','genres'=>['Fiksi']],
            ['judul'=>'Bumi','penulis'=>'Tere Liye','tahun'=>2014,'sinopsis'=>'Raib, Ali, dan Seli berpetualang ke klan-klan misterius di dunia paralel.','genres'=>['Fantasi']],
            ['judul'=>'Hujan','penulis'=>'Tere Liye','tahun'=>2016,'sinopsis'=>'Lail dan Esok tumbuh bersama di tengah bencana dan teknologi penghapus ingatan.','genres'=>['Fiksi','Romansa']],
            ['judul'=>'Laskar Pelangi','penulis'=>'Andrea Hirata','tahun'=>2005,'sinopsis'=>'Sepuluh anak dari Belitung berjuang meraih mimpi di tengah keterbatasan.','genres'=>['Fiksi','Inspirasi']],
            ['judul'=>'Negeri 5 Menara','penulis'=>'A. Fuadi','tahun'=>2009,'sinopsis'=>'Enam sahabat di pesantren bermimpi mengunjungi menara dunia.','genres'=>['Fiksi','Inspirasi']],
            ['judul'=>'Perahu Kertas','penulis'=>'Dee Lestari','tahun'=>2009,'sinopsis'=>'Kugy dan Keenan mengejar mimpi sambil tersesat dalam cinta.','genres'=>['Fiksi','Romansa']],
            ['judul'=>'Supernova: Ksatria, Puteri, dan Bintang Jatuh','penulis'=>'Dee Lestari','tahun'=>2001,'sinopsis'=>'Dua sahabat menulis novel yang mengubah kehidupan orang-orang di sekitarnya.','genres'=>['Fiksi']],
            ['judul'=>'Sapiens','penulis'=>'Yuval Noah Harari','tahun'=>2011,'sinopsis'=>'Sejarah umat manusia dari zaman batu hingga revolusi sains.','genres'=>['Non-Fiksi','Sejarah']],
            ['judul'=>'Atomic Habits','penulis'=>'James Clear','tahun'=>2018,'sinopsis'=>'Panduan membangun kebiasaan baik lewat perubahan kecil yang konsisten.','genres'=>['Non-Fiksi','Pengembangan Diri']],
            ['judul'=>'Filosofi Teras','penulis'=>'Henry Manampiring','tahun'=>2018,'sinopsis'=>'Filsafat Stoa untuk menghadapi kecemasan hidup modern.','genres'=>['Non-Fiksi','Filosofi']],
            ['judul'=>'Di Tanah Lada','penulis'=>'Ziggy Zezsyazeoviennazabrizkie','tahun'=>2023,'sinopsis'=>'Kekerasan domestik melalui perspektif anak-anak yang polos namun cerdas.','genres'=>['Fiksi']],
            ['judul'=>'Project Hail Mary','penulis'=>'Andy Weir','tahun'=>2021,'sinopsis'=>'Seorang astronot terbangun di pesawat luar angkasa tanpa ingatan.','genres'=>['Fiksi','Sci-Fi']],
            ['judul'=>'The Psychology of Money','penulis'=>'Morgan Housel','tahun'=>2020,'sinopsis'=>'Pelajaran abadi tentang kekayaan, keserakahan, dan kebahagiaan.','genres'=>['Non-Fiksi','Bisnis']],
            ['judul'=>'Dunia Sophie','penulis'=>'Jostein Gaarder','tahun'=>1991,'sinopsis'=>'Perjalanan seorang gadis mempelajari filsafat dari zaman kuno hingga modern.','genres'=>['Fiksi','Filosofi']],
            ['judul'=>'Keajaiban Toko Kelontong Namiya','penulis'=>'Keigo Higashino','tahun'=>2012,'sinopsis'=>'Toko kelontong tua yang menjadi tempat curhat dan mengubah hidup banyak orang.','genres'=>['Fiksi']],
            ['judul'=>'Hidup Ini Terlalu Banyak Kamu','penulis'=>'Pidi Baiq','tahun'=>2025,'sinopsis'=>'Buku reflektif dengan gaya bahasa sederhana yang menyentuh hati.','genres'=>['Fiksi']],
            ['judul'=>'The Let Them Theory','penulis'=>'Mel Robbins','tahun'=>2024,'sinopsis'=>'Solusi untuk burnout akibat tekanan sosial.','genres'=>['Non-Fiksi','Pengembangan Diri']],
        ];

        foreach ($books as $b) {
            FeaturedBook::create($b);
        }
    }
}
