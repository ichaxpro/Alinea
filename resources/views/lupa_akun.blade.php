<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Alinea — Pemulihan Akun</title>
  <meta name="description" content="Pulihkan akun Alinea Anda dengan mudah." />

  <script src="https://cdn.tailwindcss.com"></script>
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
      <div class="w-full max-w-[400px]">
        
        <div class="mb-[44px] flex items-center gap-1.5">
          <img src="{{ asset('images/alinealogo.svg') }}" alt="Alinea Logo" class="brand-logo">
        </div>

        <div id="step-email">
        <h1 class="mb-3 text-[26px] font-extrabold tracking-[-1px] text-[#444] lg:text-[32px]">Pemulihan Akun</h1>
        <p class="mb-9 text-[14px] font-medium text-[#808080]">
          Untuk membantu menjaga akun Anda tetap aman, masukkan email yang terdaftar untuk menerima kode verifikasi.</a>
        </p>

          <div class="mb-8">
            <label for="email" class="mb-2 block text-[13px] font-bold text-[#555358]">Alamat Email</label>
            <input type="email" id="email" placeholder="Masukkan alamat email pemulihan" 
              class="w-full rounded-xl border-[1.5px] border-[#4D4B50] bg-[#EDF3FC] px-4 py-[14px] text-[15px] text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6]" />
          </div>

          <div class="flex items-center justify-between gap-4">
            <a href="{{ route('login') }}" class="text-sm font-bold text-[#353337] no-underline hover:underline">Kembali Login</a>
            <button type="button" onclick="goToVerify()" class="px-8 cursor-pointer rounded-[20px] border-2 border-[#353337] bg-[#F8DBB5] p-3.5 text-[15px] font-extrabold text-[#353337] shadow-[4px_4px_0px_#353337] transition-all duration-100 hover:bg-[#F0D0A5] active:translate-x-[2px] active:translate-y-[2px] active:shadow-[2px_2px_0px_#353337]">
              Kirim Kode
            </button>
          </div>
        </div>

        <div id="step-verify" class="hidden">
          <h1 class="mb-2 text-[26px] font-extrabold tracking-[-1px] text-[#353337] lg:text-[32px]">Verifikasi Kode</h1>
          <p class="mb-8 text-[14px] font-medium text-[#808080] leading-relaxed">
            Kode verifikasi telah dikirim ke <strong id="display-email" class="text-[#353337]"></strong>. Silakan periksa kotak masuk Anda.
          </p>

          <div class="mb-8">
            <label for="code" class="mb-2 block text-[13px] font-bold text-[#555358]">Masukkan Kode Verifikasi</label>
            <input type="text" id="code" maxlength="6" placeholder="******" 
              class="w-full text-center tracking-[0.5em] font-mono rounded-xl border-[1.5px] border-[#4D4B50] bg-[#EDF3FC] px-4 py-[14px] text-[20px] font-bold text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6]" />
          </div>

          <div class="flex items-center justify-between gap-4">
            <button type="button" onclick="goToEmail()" class="text-sm font-bold text-[#808080] no-underline hover:text-[#353337]">Ganti Email</button>
            <button type="button" onclick="submitRecovery()" class="px-8 cursor-pointer rounded-[20px] border-2 border-[#353337] bg-[#F8DBB5] p-3.5 text-[15px] font-extrabold text-[#353337] shadow-[4px_4px_0px_#353337] transition-all duration-100 hover:bg-[#F0D0A5] active:translate-x-[2px] active:translate-y-[2px] active:shadow-[2px_2px_0px_#353337]">
              Verifikasi
            </button>
          </div>
        </div>

      </div>
    </div>

  </div>

  <script>
    function goToVerify() {
      const emailInput = document.getElementById('email').value;
      if(emailInput.trim() === "") {
        alert("Silakan masukkan email Anda terlebih dahulu.");
        return;
      }
      document.getElementById('display-email').innerText = emailInput;
      document.getElementById('step-email').classList.add('hidden');
      document.getElementById('step-verify').classList.remove('hidden');
    }

    function goToEmail() {
      document.getElementById('step-verify').classList.add('hidden');
      document.getElementById('step-email').classList.remove('hidden');
    }

    function submitRecovery() {
      const codeInput = document.getElementById('code').value;
      if(codeInput.length < 6) {
        alert("Masukkan 6 digit kode verifikasi yang valid.");
        return;
      }
      alert("Kode berhasil diverifikasi! Mengalihkan ke halaman pembuatan password baru.");
    }
  </script>
</body>
</html>