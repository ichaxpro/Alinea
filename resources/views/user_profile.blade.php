<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — {{ $user->name }}</title>
    <meta name="description" content="Profil {{ $user->name }} di Alinea" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">
    <x-navbar></x-navbar>

    <div class="min-h-screen pt-16">
        <div class="max-w-2xl mx-auto px-4 py-8">
            <div class="bg-white border-[1.5px] border-[#444] rounded-2xl p-8">
                <div class="flex items-center gap-6">
                    {{-- Avatar --}}
                    <div class="w-24 h-24 rounded-full border-2 border-[#444] flex-shrink-0 overflow-hidden
                                bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF]
                                flex items-center justify-center">
                        @if($user->foto_profil)
                            <img src="{{ Storage::disk('public')->url($user->foto_profil) }}"
                                 alt="Avatar" class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl font-black text-text/60">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div>
                        <h1 class="text-2xl font-bold text-[#222]">{{ $user->name }}</h1>
                        <p class="text-sm text-gray-500">{{ $user->username ? '@' . $user->username : 'tanpa_username' }}</p>
                        @if($user->kota)
                            <p class="text-sm text-gray-400 mt-1">{{ $user->kota }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>