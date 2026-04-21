<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Alinea — Masuk</title>
  <meta name="description" content="Masuk ke akun Alinea Anda dan lanjutkan petualangan membaca Anda." />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
</head>

<body>

  <div class="page">

    <!-- ======= LEFT PANEL ======= -->
    <div class="left-panel">
      <div class="left-content">
        <h1 class="left-title">
          <span>Dimana</span>
          <span class="highlight">Ceritamu</span><br>
          <span>Dimulai</span>
        </h1>
        <p class="left-subtitle">
          Temukan buku favoritmu, simpan catatan,<br>
          dan bagikan kisahmu bersama Alinea.
        </p>
      </div>

      <!-- Books Illustration (inline SVG) -->
      {{-- <svg class="books-illustration" viewBox="0 0 500 220" preserveAspectRatio="xMinYMax meet" aria-hidden="true">
        <!-- Green Vertical -->
        <rect x="150" y="80" width="22" height="140" fill="#1CA14B" />
        <rect x="158" y="90" width="6" height="130" fill="#18823C" />

        <!-- Blue Vertical -->
        <rect x="172" y="30" width="30" height="190" fill="#4B6CA6" />
        <rect x="182" y="40" width="10" height="180" fill="#6B8EC8" />

        <!-- Teal Vertical -->
        <rect x="202" y="45" width="28" height="175" fill="#45B0A1" />
        <rect x="202" y="110" width="28" height="50" fill="#E4F4F1" />

        <!-- Dark Blue Geometric -->
        <rect x="230" y="25" width="35" height="195" fill="#3D38A0" />
        <polygon points="247.5,50 257.5,62 247.5,74 237.5,62" fill="#242168" />
        <polygon points="247.5,85 257.5,97 247.5,109 237.5,97" fill="#242168" />
        <polygon points="247.5,120 257.5,132 247.5,144 237.5,132" fill="#242168" />
        <polygon points="247.5,155 257.5,167 247.5,179 237.5,167" fill="#242168" />

        <!-- Peach Vertical -->
        <rect x="265" y="60" width="24" height="160" fill="#F8CEA1" />
        <circle cx="277" cy="190" r="4" fill="#FFEACB" />

        <!-- Pink Vertical -->
        <rect x="289" y="55" width="46" height="165" fill="#FBB1F4" />
        <line x1="304" y1="55" x2="304" y2="220" stroke="#DD9CD6" stroke-width="2" />
        <line x1="319" y1="55" x2="319" y2="220" stroke="#DD9CD6" stroke-width="2" />

        <!-- Dark Grey Vertical -->
        <rect x="335" y="35" width="34" height="185" fill="#353436" />
        <rect x="349" y="35" width="6" height="185" fill="#883136" />

        <!-- Brown Leaning -->
        <g transform="translate(369, 220) rotate(22)">
          <rect x="0" y="-170" width="45" height="170" fill="#693736" />
          <polygon points="22,-20 34,-40 10,-40" fill="#432221" />
          <polygon points="22,-70 34,-50 10,-50" fill="#432221" />
          <polygon points="22,-110 34,-130 10,-130" fill="#432221" />
        </g>

        <!-- Bottom Tan Horizontal -->
        <path d="M 20,220 L 250,220 L 250,175 L 20,175 C 10,175 10,220 20,220 Z" fill="#E6B995" />
        <path d="M 30,220 L 30,175" stroke="#CDA482" stroke-width="4" />
        <path d="M 50,195 Q 60,180 75,195 T 100,195 T 130,195" fill="none" stroke="#FFFFFF" stroke-width="1.5" />

        <!-- Middle Light Green Horizontal -->
        <path d="M 18,175 L 240,175 L 240,145 L 18,145 C 12,145 12,175 18,175 Z" fill="#C5DF9F" />
        <path d="M 30,175 L 30,145" stroke="#AFD17E" stroke-width="4" />
        <path d="M 110,165 L 115,160 L 120,165" fill="none" stroke="#FFFFFF" stroke-width="1.5" />

        <!-- Top Purple Horizontal -->
        <path d="M 25,145 L 220,145 L 220,123 L 25,123 C 18,123 18,145 25,145 Z" fill="#C0A4DF" />
        <path d="M 35,145 L 35,123" stroke="#A988CC" stroke-width="3" />
        <path d="M 70,135 Q 90,120 105,135 T 145,133" fill="none" stroke="#75529C" stroke-width="1.5" />
      </svg> --}}
      <img src="{{ asset('images/Bookshelf4.svg') }}" alt="Bookshelf" class="books-illustration">
    </div>

    <!-- ======= RIGHT PANEL ======= -->
    <div class="right-panel">
      <form class="form-box" onsubmit="event.preventDefault();">

        <!-- Logo -->
        <div class="brand">
          {{-- <svg width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 1L14.5 7L21 7.5L16.2 12.5L18 19L12 15.5L6 19L7.8 12.5L3 7.5L9.5 7L12 1Z" fill="#D1EAFA"
              stroke="#BAE0F7" stroke-width="1" />
          </svg>
          <span class="brand-text">Alinea</span> --}}
          <img src="{{ asset('images/alinealogo.svg') }}" alt="Alinea Logo" class="brand-logo">
        </div>

        <h1 class="form-title">Selamat Datang!</h1>
        <p class="form-subtitle">
          Belum punya akun? <a href="#">Daftar!</a>
        </p>

        <!-- Email Field -->
        <div class="input-group">
          <label for="email">Alamat Email</label>
          <input type="email" id="email" name="email" autocomplete="email" autofocus placeholder="" />
        </div>

        <!-- Password Field -->
        <div class="input-group">
          <div class="label-row">
            <label for="password">Kata Sandi</label>
            <a href="#" class="forgot-link">Lupa Kata Sandi?</a>
          </div>
          <input type="password" id="password" name="password" autocomplete="current-password" />
        </div>

        <!-- Submit Button -->
        <button type="submit" class="login-btn" id="login-submit-btn">
          Log In
        </button>

      </form>
    </div>

  </div>

</body>

</html>