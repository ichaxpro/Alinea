<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Alinea — Platform Buku Komunitasmu</title>
        <meta name="description" content="Alinea adalah platform komunitas buku: pinjam, baca, ulas, dan pamerkan bacaanmu bersama ribuan pembaca lain di kotamu.">
        <meta name="user-auth" content="{{ Auth::check() ? 'true' : 'false' }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/css/welcome.css', 'resources/js/app.js', 'resources/js/welcome.js'])
    </head>
    <body class="bg-white text-gray-900 overflow-x-hidden">
        <!-- Scroll Progress Bar -->
        <div id="scroll-progress"></div>

        <x-navbar></x-navbar>

        <x-welcome.hero />
        <x-welcome.features />
        <x-welcome.community />
        <x-welcome.reviews />

        <x-footer/>
    </body>
</html>