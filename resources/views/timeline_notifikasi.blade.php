<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — Notifikasi Pinjam Buku</title>
    <meta name="description" content="Lihat aktivitas peminjaman buku antar member di Alinea." />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/timeline.js'])
</head>

<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">

    {{-- ========== NAVBAR ========== --}}
    <x-navbar></x-navbar>

    {{-- ========== PAGE LAYOUT ========== --}}
    <div class="min-h-screen pt-14">
        <div class="max-w-2xl mx-auto px-4 py-6">

            <main class="bg-white border-[1.5px] border-[#444] rounded-2xl overflow-hidden flex flex-col">
                
                {{-- Header Halaman Notifikasi --}}
                <div class="border-b-[1.5px] border-[#444] bg-[#FFDDAF] px-5 py-4 flex items-center justify-between">
                    <h1 class="text-lg font-bold text-[#444]">Notifikasi Pinjam Buku</h1>
                </div>

                {{-- LIST NOTIFIKASI CONTAINER --}}
                <div class="divide-y-[1.5px] divide-gray-200">
                    
                    @php
                    // Data simulasi interaksi pinjam buku peer-to-peer (ketemuan & batas waktu)
                    $notifications = [
                        [
                            'type' => 'request',
                            'avatar_from' => '#C7E7FF', 'avatar_to' => '#FFDDAF',
                            'user' => 'Dina Rahmawati',
                            'meta' => 'Mengajukan Pinjaman',
                            'body' => 'ingin meminjam buku kamu "The Midnight Library". Dia mengajak ketemuan di Perpustakaan Kota Malang besok jam 15.00 WIB. Ketuk untuk merespons.',
                            'time' => 'Baru saja'
                        ],
                        [
                            'type' => 'cod',
                            'avatar_from' => '#FFDDAF', 'avatar_to' => '#D4F6FF',
                            'user' => 'Budi Ashcroft',
                            'meta' => 'Konfirmasi Ketemuan',
                            'body' => 'Permintaan pinjam buku "Harry Potter" disetujui! Segera chat Budi untuk menentukan lokasi ketemuan. Batas waktu pinjam adalah 14 hari setelah buku diserahkan.',
                            'time' => '12 Menit Lalu'
                        ],
                        [
                            'type' => 'warning',
                            'avatar_from' => '#FCE4EC', 'avatar_to' => '#F8BBD0',
                            'user' => 'Pengingat Sistem',
                            'meta' => 'Batas Waktu Pinjam',
                            'body' => 'Masa pinjam buku "Bumi Manusia" dari Ahmad Fauzan tinggal 2 hari lagi. Yuk, segera hubungi Ahmad via chat untuk janjian ketemuan dan mengembalikan bukunya.',
                            'time' => '2 Jam Lalu'
                        ],
                        [
                            'type' => 'success',
                            'avatar_from' => '#D4F6FF', 'avatar_to' => '#C7E7FF',
                            'user' => 'Ahmad Fauzan',
                            'meta' => 'Pengembalian Selesai',
                            'body' => 'telah mengonfirmasi pengembalian buku "Kata". Terima kasih sudah menjaga buku dengan baik dan mengembalikannya tepat waktu!',
                            'time' => '30 Apr'
                        ]
                    ];
                    @endphp

                    @foreach ($notifications as $notif)
                    <div class="p-4 hover:bg-gray-50 transition-colors flex gap-4 items-start">
                        
                        {{-- Indikator Tipe (Icon Kiri) --}}
                        <div class="pt-1 flex-shrink-0 w-0 flex justify-center text-center">
                            @if($notif['type'] === 'request')
                                <span class="text-blue-500 font-bold text-lg"></span>
                            @elseif($notif['type'] === 'cod')
                                <span class="text-purple-500 font-bold text-lg"></span>
                            @elseif($notif['type'] === 'warning')
                                <span class="text-amber-500 font-bold text-lg"></span>
                            @else
                                <span class="text-green-500 font-bold text-lg"></span>
                            @endif
                        </div>

                        {{-- Isi Konten --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                {{-- Avatar User / Sistem --}}
                                <div class="w-7 h-7 rounded-full border border-[#444] flex-shrink-0"
                                     style="background: linear-gradient(135deg, {{ $notif['avatar_from'] }}, {{ $notif['avatar_to'] }})">
                                </div>
                                {{-- Info Pengirim --}}
                                <span class="font-bold text-sm text-[#444]">{{ $notif['user'] }}</span>
                                @if($notif['meta'])
                                    <span class="text-xs text-gray-400 truncate">• {{ $notif['meta'] }}</span>
                                @endif
                                <span class="text-xs text-gray-300 ml-auto flex-shrink-0">{{ $notif['time'] }}</span>
                            </div>
                            
                            {{-- Teks Notifikasi --}}
                            <p class="text-xs text-gray-600 leading-relaxed pl-9 break-words">
                                <strong>{{ $notif['user'] }}</strong> {{ $notif['body'] }}
                            </p>
                        </div>
                    </div>
                    @endforeach

                </div>
            </main>

        </div>
    </div>

</body>
</html>