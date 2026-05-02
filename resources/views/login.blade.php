
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<script src="https://cdn.tailwindcss.com"></script>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Alinea — Masuk</title>
  <meta name="description" content="Masuk ke akun Alinea Anda dan lanjutkan petualangan membaca Anda." />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet" />
  
  </head>

<body class="h-full bg-white font-['Poppins',_sans-serif] overflow-x-hidden m-0 p-0 box-border">

  <div class="flex flex-col min-h-screen w-screen lg:flex-row lg:h-screen lg:min-h-0">

    <div class="relative flex flex-1 flex-col justify-center overflow-hidden bg-[#D1EAFA] px-[30px] py-[50px] min-h-[400px] lg:pl-[80px] lg:pr-0 lg:py-0 lg:min-h-0">
      <div class="relative z-10 -mt-[60px] lg:-mt-[300px]">
        <h1 class="mb-6 text-[36px] font-extrabold leading-[1.15] tracking-[-1.5px] text-[#38556D] sm:text-[44px] lg:text-[58px]">
          <span>Dimana</span>
          <span class="relative inline-block whitespace-nowrap after:absolute after:-bottom-1 after:-left-0.5 after:-right-0.5 after:-z-10 after:h-2 after:bg-[#F8D3A8]">Ceritamu</span><br>
          <span>Dimulai</span>
        </h1>
        <p class="max-w-[480px] text-[20px] font-medium leading-relaxed text-[#8AA4BC]">
          Temukan buku favoritmu, simpan catatan,<br>
          dan bagikan kisahmu bersama Alinea.
        </p>
      </div>

      <img src="{{ asset('images/Bookshelf4.svg') }}" alt="Bookshelf" class="pointer-events-none absolute bottom-0 left-0 z-[2] h-[180px] w-full max-w-[90%] lg:left-[20px] lg:h-auto lg:w-[600px]">
    </div>

    <div class="flex flex-1 items-center justify-center bg-white px-6 py-[60px] lg:px-[40px] lg:py-0">
      <form class="w-full max-w-[400px]" onsubmit="event.preventDefault();">

        <div class="mb-[44px] flex items-center gap-1.5">
          <img src="{{ asset('images/alinealogo.svg') }}" alt="Alinea Logo" class="brand-logo">
        </div>

        <h1 class="mb-3 text-[26px] font-extrabold tracking-[-1px] text-[#444] lg:text-[32px]">Selamat Datang!</h1>
        <p class="mb-9 text-[14px] font-medium text-[#808080]">
          Belum punya akun? <a href="#" class="font-bold text-[#353337] no-underline hover:underline">Daftar!</a>
        </p>

        <div class="mb-6">
          <label for="email" class="mb-2 block text-[13px] font-bold text-[#555358]">Alamat Email</label>
          <input type="email" id="email" name="email" autocomplete="email" autofocus placeholder="" class="w-full rounded-xl border-[1.5px] border-[#4D4B50] bg-white px-4 py-[14px] font-['Plus_Jakarta_Sans',_sans-serif] text-[15px] text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6] focus:px-[15px] focus:py-[13px]" />
        </div>

        <div class="mb-6">
          <div class="mb-2 flex items-center justify-between">
            <label for="password" class="mb-0 block text-[13px] font-bold text-[#555358]">Kata Sandi</label>
            <a href="#" class="text-[12px] font-semibold text-[#A0A0A0] no-underline transition-colors duration-200 hover:text-[#353337]">Lupa Kata Sandi?</a>
          </div>
          <input type="password" id="password" name="password" autocomplete="current-password" class="w-full rounded-xl border-[1.5px] border-[#4D4B50] bg-white px-4 py-[14px] font-['Plus_Jakarta_Sans',_sans-serif] text-[15px] text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6] focus:px-[15px] focus:py-[13px]" />
        </div>

        <button type="submit" id="login-submit-btn" class="mt-2.5 w-full cursor-pointer rounded-[20px] border-2 border-[#353337] bg-[#F8DBB5] p-4 font-['Plus_Jakarta_Sans',_sans-serif] text-[16px] font-extrabold text-[#353337] shadow-[4px_4px_0px_#353337] transition-all duration-100 hover:bg-[#F0D0A5] active:translate-x-[2px] active:translate-y-[2px] active:shadow-[2px_2px_0px_#353337]">
          Log In
        </button>

      </form>
    </div>

  </div>

</body>

</html>