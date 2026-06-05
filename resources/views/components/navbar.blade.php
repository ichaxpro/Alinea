<!-- =================== NAVBAR =================== -->
<meta name="user-auth" content="{{ Auth::check() ? 'true' : 'false' }}">
<meta name="google-books-key" content="{{ config('services.google_books.key') }}">
        <nav id="main-navbar" class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm transition-transform duration-300 ease-in-out">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <a href="{{ route('beranda') }}" class="flex items-center gap-2 group py-2">
                        <div class="flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <img fill="none" src="/img/alinealogo.svg" class="h-7">
                        </div>
                    </a>

                    <!-- Nav Links (Desktop) -->
                    <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
                        <a href="{{ route('beranda') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Beranda</a>
                        <a href="{{ route('timeline_home') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Lini Masa</a>
                        <a href="{{ route('klub') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Klub</a>
                        <a href="{{ route('katalog') }}" class="nav-link relative hover:text-gray-900 transition-colors duration-200">Katalog</a>
                    </div>

                    <!-- Right Actions Group (CTA + Mobile Menu) -->
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3">
                            <button id="navbar-search-btn" aria-label="Cari" class="cursor-pointer w-9 h-9 rounded-full border-2 border-text flex items-center justify-center text-text shadow-pop hover:shadow-none hover:translate-x-[4px] hover:translate-y-[2px] hover:bg-[#C7E7FF] transition-all duration-200">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                            </button>
                           @auth
                                @php
                                    $unreadCount = auth()->user()->unreadNotifications->count();
                                    $latestNotifications = auth()->user()->notifications()->take(3)->get();
                                @endphp
                                
                                <div class="relative" id="notification-dropdown">
                                    <button onclick="toggleNotificationDropdown()" class="cursor-pointer w-9 h-9 rounded-full border-2 border-text flex items-center justify-center text-text shadow-pop hover:shadow-none hover:translate-x-[4px] hover:translate-y-[2px] hover:bg-[#C7E7FF] transition-all duration-200 relative">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                        </svg>
                                        @if($unreadCount > 0)
                                            <span id="navbar-notif-badge" class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full"></span>
                                        @endif
                                    </button>

                                    <div id="notif-dropdown-menu" class="hidden absolute right-0 top-full mt-2 w-80 bg-white border-2 border-[#444] rounded-2xl shadow-xl z-50 overflow-hidden flex flex-col">
                                        <div class="px-4 py-3 border-b-[1.5px] border-gray-100 flex justify-between items-center">
                                            <span class="font-bold text-[#444]">Notifikasi</span>
                                            @if($unreadCount > 0)
                                                <div class="flex items-center gap-2" id="notif-header-actions">
                                                    <button onclick="markNotificationsAsRead()" class="text-[10px] font-bold text-gray-400 hover:text-blue-500 transition-colors cursor-pointer">
                                                        Tandai sudah dibaca
                                                    </button>
                                                    <span class="text-xs font-bold bg-[#FFDDAF] text-[#444] px-2 py-0.5 rounded-full">{{ $unreadCount }} Baru</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="max-h-80 overflow-y-auto flex flex-col divide-y-[1.5px] divide-gray-100">
                                            @forelse($latestNotifications as $notification)
                                                @php
                                                    $notif = $notification->data;
                                                    $time = \Carbon\Carbon::parse($notification->created_at)->locale('id')->diffForHumans();
                                                @endphp
                                                <div class="p-3 hover:bg-gray-50 transition-colors flex gap-3 items-start {{ $notification->read_at ? '' : 'bg-blue-50/30 notif-item-unread' }}">
                                                    <div class="flex-shrink-0 pt-0.5">
                                                        @if(isset($notif['type']) && $notif['type'] === 'like')
                                                            <div class="w-2 h-2 rounded-full bg-red-500 mt-1.5"></div>
                                                        @elseif(isset($notif['type']) && $notif['type'] === 'comment')
                                                            <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5"></div>
                                                        @elseif(isset($notif['type']) && $notif['type'] === 'follow')
                                                            <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5"></div>
                                                        @elseif(isset($notif['type']) && $notif['type'] === 'borrow')
                                                            <div class="w-2 h-2 rounded-full bg-purple-500 mt-1.5"></div>
                                                        @elseif(isset($notif['type']) && $notif['type'] === 'return')
                                                            <div class="w-2 h-2 rounded-full bg-teal-500 mt-1.5"></div>
                                                        @else
                                                            <div class="w-2 h-2 rounded-full bg-amber-500 mt-1.5"></div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[13px] text-gray-600 leading-relaxed line-clamp-2">
                                                            <strong class="text-[#444]">{{ $notif['user_name'] ?? 'Sistem' }}</strong> {{ $notif['body'] ?? '' }}
                                                        </p>
                                                        <span class="text-[11px] text-gray-400 mt-1 block">{{ $time }}</span>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="p-4 text-center text-[13px] text-gray-500">
                                                    Belum ada notifikasi.
                                                </div>
                                            @endforelse
                                        </div>
                                        <div class="border-t-[1.5px] border-gray-100 p-2">
                                            <a href="{{ route('timeline_notifikasi') }}" class="block w-full text-center py-2 text-[13px] font-bold text-gray-600 hover:text-[#444] hover:bg-gray-50 rounded-xl transition-colors">
                                                Lihat semua notifikasi
                                            </a>
                                        </div>
                                    </div>
                                </div>

                               <div class="relative" id="profile-dropdown">
                                <button onclick="toggleDropdown()" class="w-9 h-9 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex items-center justify-center text-sm font-black text-[#444] hover:shadow-md transition-shadow cursor-pointer overflow-hidden">
                                    <img id="navbar-avatar-img" src="{{ Auth::user()->foto_profil ? Storage::disk('public')->url(Auth::user()->foto_profil) : '' }}" alt="Avatar" class="w-full h-full object-cover {{ Auth::user()->foto_profil ? '' : 'hidden' }}">
                                    <span id="navbar-avatar-initial" class="{{ Auth::user()->foto_profil ? 'hidden' : '' }}">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </button>

                                <div id="dropdown-menu" class="hidden absolute right-0 top-full mt-2 w-48 bg-white border-2 border-[#444] rounded-2xl shadow-xl py-2 z-50">
                                    <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-[#FFDDAF]/30 transition-colors">Dashboard</a>
                                    <form method="POST" action="/logout">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-[#FFDDAF]/30 transition-colors cursor-pointer">Logout</button>
                                    </form>
                                </div>
                               </div>
                           @else
                               <a href="{{ route('login') }}" class="text-sm bg-accent px-5 py-2 outline-2 hover:bg-amber-500 outline-text shadow-pop2 rounded-full font-bold text-text hover:text-gray-900 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">Masuk</a>
                           @endauth
                        </div>

                        <!-- Mobile menu button -->
                        <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors" id="mobile-menu-btn" aria-label="Menu">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M3 6h14M3 10h14M3 14h14"/>
                            </svg>
                        </button>
                    </div>

                    @auth
                       <script>
                        function toggleDropdown() {
                            document.getElementById('dropdown-menu').classList.toggle('hidden');
                            document.getElementById('notif-dropdown-menu')?.classList.add('hidden');
                        }
                        
                        function toggleNotificationDropdown() {
                            document.getElementById('notif-dropdown-menu').classList.toggle('hidden');
                            document.getElementById('dropdown-menu')?.classList.add('hidden');
                        }

                        document.addEventListener('click', function(e) {
                            const profileDd = document.getElementById('profile-dropdown');
                            const notifDd = document.getElementById('notification-dropdown');
                            
                            if (profileDd && !profileDd.contains(e.target)) {
                                document.getElementById('dropdown-menu')?.classList.add('hidden');
                            }
                            if (notifDd && !notifDd.contains(e.target)) {
                                document.getElementById('notif-dropdown-menu')?.classList.add('hidden');
                            }
                        });

                        function markNotificationsAsRead() {
                            fetch('{{ route('notifications.mark_read') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            }).then(res => res.json()).then(data => {
                                if(data.success) {
                                    const iconBadge = document.getElementById('navbar-notif-badge');
                                    if(iconBadge) iconBadge.remove();
                                    
                                    const headerActions = document.getElementById('notif-header-actions');
                                    if(headerActions) headerActions.remove();
                                    
                                    document.querySelectorAll('.notif-item-unread').forEach(el => {
                                        el.classList.remove('bg-blue-50/30', 'notif-item-unread');
                                    });
                                }
                            }).catch(err => console.error(err));
                        }
                       </script>
                    @endauth
                </div>
            </div>
        </nav>
        @vite(['resources/js/global-search.js'])
         <div id="mobile-menu" class="hidden-menu fixed inset-0 z-40 bg-white/95 backdrop-blur-lg flex flex-col items-center justify-center text-center">
            <button id="close-mobile-menu" class="absolute top-5 right-5 p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Tutup Menu">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M4 4l12 12M16 4L4 16"/>
                </svg>
            </button>
            <nav class="flex flex-col gap-8 text-3xl font-black text-gray-800">
                <a href="{{ route('beranda') }}" class="hover:text-amber-500 hover:translate-x-2 transition-all duration-300" onclick="closeMobileMenu()">Beranda</a>
                <a href="{{ route('timeline_home') }}" class="hover:text-amber-500 hover:translate-x-2 transition-all duration-300" onclick="closeMobileMenu()">Komunitas</a>
                <a href="{{ route('klub') }}" class="hover:text-amber-500 hover:translate-x-2 transition-all duration-300" onclick="closeMobileMenu()">Klub</a>
                <a href="{{ route('katalog') }}" class="hover:text-amber-500 hover:translate-x-2 transition-all duration-300" onclick="closeMobileMenu()">Katalog</a>
            </nav>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const mobileMenuBtn = document.getElementById('mobile-menu-btn');
                const mobileMenu = document.getElementById('mobile-menu');
                const closeBtn = document.getElementById('close-mobile-menu');

                if (mobileMenuBtn && mobileMenu) {
                    mobileMenuBtn.addEventListener('click', () => {
                        mobileMenu.classList.remove('hidden-menu');
                        mobileMenu.classList.add('visible-menu');
                    });
                }

                window.closeMobileMenu = () => {
                    if (mobileMenu) {
                        mobileMenu.classList.remove('visible-menu');
                        mobileMenu.classList.add('hidden-menu');
                    }
                };

                closeBtn?.addEventListener('click', window.closeMobileMenu);
            });
        </script>