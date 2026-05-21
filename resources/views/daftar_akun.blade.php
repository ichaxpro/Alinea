<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<script src="https://cdn.tailwindcss.com"></script>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Alinea — Daftar Akun</title>
  <meta name="description" content="Daftar akun Alinea dan mulai petualangan membaca Anda." />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800;900&display=swap" rel="stylesheet" />
  
  <style>
    /* Custom checkbox styling for genres */
    .genre-checkbox:checked + label {
      background-color: #F8DBB5;
      border-color: #353337;
      color: #353337;
    }
    .genre-checkbox:disabled:not(:checked) + label {
      opacity: 0.5;
      cursor: not-allowed;
    }
  </style>
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
      <form method="POST" action="/daftar" id="register-form" class="w-full max-w-[450px]">
        @csrf

        <div class="mb-[30px] flex items-center gap-1.5">
          <img src="{{ asset('images/alinealogo.svg') }}" alt="Alinea Logo" class="brand-logo h-8">
        </div>

        <h1 class="mb-3 text-[26px] font-extrabold tracking-[-1px] text-[#444] lg:text-[32px]">Daftar Akun</h1>
        <p class="mb-8 text-[14px] font-medium text-[#808080]">
          Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-[#353337] no-underline hover:underline">Log in!</a>
        </p>

        <!-- Step Indicators -->
        <div class="mb-8 flex items-center justify-between gap-2">
            <div id="indicator-1" class="h-2 w-full rounded-full bg-[#3B82F6]"></div>
            <div id="indicator-2" class="h-2 w-full rounded-full bg-gray-200"></div>
            <div id="indicator-3" class="h-2 w-full rounded-full bg-gray-200"></div>
        </div>

        <!-- STEP 1: Account Details -->
        <div id="step-1" class="step-content">
          <div class="mb-5">
            <label for="email" class="mb-2 block text-[13px] font-bold text-[#555358]">Alamat Email</label>
            <input type="email" id="email" name="email" required placeholder="email@contoh.com" class="w-full rounded-xl border-[1.5px] border-[#4D4B50] bg-white px-4 py-[12px] font-['Plus_Jakarta_Sans',_sans-serif] text-[15px] text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6] focus:px-[15px] focus:py-[11px]" />
          </div>

          <div class="mb-5">
            <label for="password" class="mb-2 block text-[13px] font-bold text-[#555358]">Kata Sandi</label>
            <input type="password" id="password" name="password" required minlength="8" placeholder="Minimal 8 karakter" class="w-full rounded-xl border-[1.5px] border-[#4D4B50] bg-white px-4 py-[12px] font-['Plus_Jakarta_Sans',_sans-serif] text-[15px] text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6] focus:px-[15px] focus:py-[11px]" />
          </div>

          <div class="mb-6">
            <label for="password_confirmation" class="mb-2 block text-[13px] font-bold text-[#555358]">Konfirmasi Kata Sandi</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" placeholder="Ulangi kata sandi" class="w-full rounded-xl border-[1.5px] border-[#4D4B50] bg-white px-4 py-[12px] font-['Plus_Jakarta_Sans',_sans-serif] text-[15px] text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6] focus:px-[15px] focus:py-[11px]" />
            <p id="password-error" class="mt-2 hidden text-xs font-semibold text-red-500">Kata sandi tidak cocok.</p>
          </div>

          <button type="button" onclick="nextStep(2)" class="w-full cursor-pointer rounded-[20px] border-2 border-[#353337] bg-[#F8DBB5] p-3.5 font-['Plus_Jakarta_Sans',_sans-serif] text-[16px] font-extrabold text-[#353337] shadow-[4px_4px_0px_#353337] transition-all duration-100 hover:bg-[#F0D0A5] active:translate-x-[2px] active:translate-y-[2px] active:shadow-[2px_2px_0px_#353337]">
            Lanjut
          </button>
        </div>

        <!-- STEP 2: Personal Info -->
        <div id="step-2" class="step-content hidden">
          <div class="mb-5">
            <label for="username" class="mb-2 block text-[13px] font-bold text-[#555358]">Username <span class="text-red-500">*tidak dapat diubah nanti</span></label>
            <input type="text" id="username" name="username" required placeholder="Pilih username unik" class="w-full rounded-xl border-[1.5px] border-[#4D4B50] bg-white px-4 py-[12px] font-['Plus_Jakarta_Sans',_sans-serif] text-[15px] text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6] focus:px-[15px] focus:py-[11px]" />
          </div>

          <div class="mb-5">
            <label for="name" class="mb-2 block text-[13px] font-bold text-[#555358]">Nama Lengkap</label>
            <input type="text" id="name" name="name" required placeholder="Nama Anda" class="w-full rounded-xl border-[1.5px] border-[#4D4B50] bg-white px-4 py-[12px] font-['Plus_Jakarta_Sans',_sans-serif] text-[15px] text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6] focus:px-[15px] focus:py-[11px]" />
          </div>

          <div class="mb-5 flex gap-4">
            <div class="w-1/2">
                <label for="city" class="mb-2 block text-[13px] font-bold text-[#555358]">Kota</label>
                <input type="text" id="city" name="kota" required placeholder="Kota domisili" class="w-full rounded-xl border-[1.5px] border-[#4D4B50] bg-white px-4 py-[12px] font-['Plus_Jakarta_Sans',_sans-serif] text-[15px] text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6] focus:px-[15px] focus:py-[11px]" />
            </div>
            <div class="w-1/2">
                <label for="phone" class="mb-2 block text-[13px] font-bold text-[#555358]">Nomor Telepon</label>
                <input type="tel" id="phone" name="no_telp" required placeholder="08..." class="w-full rounded-xl border-[1.5px] border-[#4D4B50] bg-white px-4 py-[12px] font-['Plus_Jakarta_Sans',_sans-serif] text-[15px] text-[#353337] outline-none transition-all duration-200 focus:border-2 focus:border-[#3B82F6] focus:px-[15px] focus:py-[11px]" />
            </div>
          </div>

          <div class="flex gap-4">
            <button type="button" onclick="prevStep(1)" class="w-1/3 cursor-pointer rounded-[20px] border-2 border-[#353337] bg-white p-3.5 font-['Plus_Jakarta_Sans',_sans-serif] text-[16px] font-bold text-[#353337] shadow-[4px_4px_0px_#353337] transition-all duration-100 hover:bg-gray-50 active:translate-x-[2px] active:translate-y-[2px] active:shadow-[2px_2px_0px_#353337]">
                Kembali
            </button>
            <button type="button" onclick="nextStep(3)" class="w-2/3 cursor-pointer rounded-[20px] border-2 border-[#353337] bg-[#F8DBB5] p-3.5 font-['Plus_Jakarta_Sans',_sans-serif] text-[16px] font-extrabold text-[#353337] shadow-[4px_4px_0px_#353337] transition-all duration-100 hover:bg-[#F0D0A5] active:translate-x-[2px] active:translate-y-[2px] active:shadow-[2px_2px_0px_#353337]">
                Lanjut
            </button>
          </div>
        </div>

        <!-- STEP 3: Genres -->
        <div id="step-3" class="step-content hidden">
          <div class="mb-6">
            <label class="mb-2 block text-[14px] font-bold text-[#555358]">Pilih Genre Kesukaanmu</label>
            <p class="mb-4 text-[13px] text-[#808080]">Pilih maksimal 5 genre yang paling kamu minati.</p>
            
            <div id="genre-container" class="flex flex-wrap gap-2">
                @php
                    $genres = ['Fiksi', 'Non-Fiksi', 'Thriller', 'Misteri', 'Romansa', 'Sci-Fi', 'Fantasi', 'Horror', 'Biografi', 'Sejarah', 'Pengembangan Diri', 'Bisnis', 'Puisi', 'Komik'];
                @endphp
                
                @foreach($genres as $index => $genre)
                <div>
                    <input type="checkbox" id="genre-{{ $index }}" name="genres[]" value="{{ $genre }}" class="genre-checkbox hidden" onchange="checkGenreLimit()" />
                    <label for="genre-{{ $index }}" class="inline-block cursor-pointer rounded-full border-[1.5px] border-[#4D4B50] bg-white px-4 py-2 text-[13px] font-semibold text-[#555358] transition-all hover:bg-gray-50">
                        {{ $genre }}
                    </label>
                </div>
                @endforeach
            </div>
          </div>

          <div class="flex gap-4">
            <button type="button" onclick="prevStep(2)" class="w-1/3 cursor-pointer rounded-[20px] border-2 border-[#353337] bg-white p-3.5 font-['Plus_Jakarta_Sans',_sans-serif] text-[16px] font-bold text-[#353337] shadow-[4px_4px_0px_#353337] transition-all duration-100 hover:bg-gray-50 active:translate-x-[2px] active:translate-y-[2px] active:shadow-[2px_2px_0px_#353337]">
                Kembali
            </button>
            <button type="submit" class="w-2/3 cursor-pointer rounded-[20px] border-2 border-[#353337] bg-[#F8DBB5] p-3.5 font-['Plus_Jakarta_Sans',_sans-serif] text-[16px] font-extrabold text-[#353337] shadow-[4px_4px_0px_#353337] transition-all duration-100 hover:bg-[#F0D0A5] active:translate-x-[2px] active:translate-y-[2px] active:shadow-[2px_2px_0px_#353337]">
                Daftar
            </button>
          </div>
        </div>

      </form>
    </div>

  </div>

  <script>
    function nextStep(step) {
        if (step === 2) {
            // Validate step 1
            const email = document.getElementById('email');
            const password = document.getElementById('password');
            const confirm = document.getElementById('password_confirmation');
            const error = document.getElementById('password-error');

            if (!email.checkValidity() || !password.checkValidity() || !confirm.checkValidity()) {
                document.getElementById('register-form').reportValidity();
                return;
            }

            if (password.value !== confirm.value) {
                error.classList.remove('hidden');
                return;
            } else {
                error.classList.add('hidden');
            }
        }

        if (step === 3) {
            // Validate step 2
            const username = document.getElementById('username');
            const name = document.getElementById('name');
            const city = document.getElementById('city');
            const phone = document.getElementById('phone');

            if (!username.checkValidity() || !name.checkValidity() || !city.checkValidity() || !phone.checkValidity()) {
                document.getElementById('register-form').reportValidity();
                return;
            }
        }

        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('step-' + step).classList.remove('hidden');

        // Update indicators
        for (let i = 1; i <= 3; i++) {
            const ind = document.getElementById('indicator-' + i);
            if (i <= step) {
                ind.classList.remove('bg-gray-200');
                ind.classList.add('bg-[#3B82F6]');
            } else {
                ind.classList.remove('bg-[#3B82F6]');
                ind.classList.add('bg-gray-200');
            }
        }
    }

    function prevStep(step) {
        document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('step-' + step).classList.remove('hidden');

        // Update indicators
        for (let i = 1; i <= 3; i++) {
            const ind = document.getElementById('indicator-' + i);
            if (i <= step) {
                ind.classList.remove('bg-gray-200');
                ind.classList.add('bg-[#3B82F6]');
            } else {
                ind.classList.remove('bg-[#3B82F6]');
                ind.classList.add('bg-gray-200');
            }
        }
    }

    function checkGenreLimit() {
        const checkboxes = document.querySelectorAll('.genre-checkbox');
        const checkedCount = document.querySelectorAll('.genre-checkbox:checked').length;
        
        checkboxes.forEach(cb => {
            if (!cb.checked) {
                cb.disabled = checkedCount >= 5;
            }
        });
    }

    // async function submitForm() {
    //   const res = await fetch('/api/register', {
    //     method: 'POST',
    //     headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
    //     body: JSON.stringify({
    //       name: document.getElementById('name').value,
    //       username: document.getElementById('username').value,
    //       email: document.getElementById('email').value,
    //       password: document.getElementById('password').value,
    //       password_confirmation: document.getElementById('password_confirmation').value,
    //       kota: document.getElementById('city').value,
    //       no_telp: document.getElementById('phone').value,
    //     }),
    //   });

    //   const data = await res.json();

    //   if(!res.ok) {
    //     alert(data.message || 'Pendaftaran gagal');
    //     return;
    //   }

    //   localStorage.setItem('token', data.token);
    //   window.location.href = '/dashboard';
    // }
  </script>

</body>

</html>
