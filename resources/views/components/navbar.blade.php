<meta name="user-auth" content="{{ Auth::check() ? 'true' : 'false' }}">
<meta name="google-books-key" content="{{ config('services.google_books.key') }}">
        <nav id="main-navbar" class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm transition-transform duration-300 ease-in-out">
            <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8">
                <div class="flex items-center justify-between h-14 md:h-16">
                    <!-- Logo -->
                    <a href="{{ route('beranda') }}" class="flex items-center gap-2 group py-2">
                        <div class="flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <img fill="none" src="/img/alinealogo.svg" class="h-7">
                        </div>
                    </a>

                    <!-- Nav Links (Desktop) -->
                    <div class="hidden md:flex items-center gap-8 text-sm font-medium">
                        <a href="{{ route('beranda') }}" class="relative transition-colors duration-200 {{ request()->routeIs('beranda*') ? 'text-[#444] font-bold after:absolute after:bottom-[-6px] after:left-1/2 after:-translate-x-1/2 after:w-1 after:h-1 after:bg-amber-500 after:rounded-full' : 'nav-link text-gray-600 hover:text-gray-900' }}">Beranda</a>
                        <a href="{{ route('explore') }}" class="relative transition-colors duration-200 {{ request()->routeIs('explore*') ? 'text-[#444] font-bold after:absolute after:bottom-[-6px] after:left-1/2 after:-translate-x-1/2 after:w-1 after:h-1 after:bg-amber-500 after:rounded-full' : 'nav-link text-gray-600 hover:text-gray-900' }}">Jelajah</a>
                        <a href="{{ route('timeline_home') }}" class="relative transition-colors duration-200 {{ request()->is('timeline*') ? 'text-[#444] font-bold after:absolute after:bottom-[-6px] after:left-1/2 after:-translate-x-1/2 after:w-1 after:h-1 after:bg-amber-500 after:rounded-full' : 'nav-link text-gray-600 hover:text-gray-900' }}">Lini Masa</a>
                        <a href="{{ route('klub') }}" class="relative transition-colors duration-200 {{ request()->routeIs('klub*') ? 'text-[#444] font-bold after:absolute after:bottom-[-6px] after:left-1/2 after:-translate-x-1/2 after:w-1 after:h-1 after:bg-amber-500 after:rounded-full' : 'nav-link text-gray-600 hover:text-gray-900' }}">Klub</a>
                        <a href="{{ route('katalog') }}" class="relative transition-colors duration-200 {{ request()->routeIs('katalog*') ? 'text-[#444] font-bold after:absolute after:bottom-[-6px] after:left-1/2 after:-translate-x-1/2 after:w-1 after:h-1 after:bg-amber-500 after:rounded-full' : 'nav-link text-gray-600 hover:text-gray-900' }}">Katalog</a>
                    </div>

                    <!-- Right Actions Group (CTA + Mobile Menu) -->
                    <div class="flex items-center gap-2 md:gap-4">
                        <div class="hidden md:flex items-center gap-3">
                            <button id="navbar-search-btn" aria-label="Cari" class="cursor-pointer w-8 h-8 md:w-9 md:h-9 rounded-full border-2 border-text flex items-center justify-center text-text shadow-pop hover:shadow-none hover:translate-x-[4px] hover:translate-y-[2px] hover:bg-[#C7E7FF] transition-all duration-200">
                                <svg width="14" height="14" class="md:w-[16px] md:h-[16px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                            </button>
                           @auth
                                @php
                                    $unreadCount = auth()->user()->unreadNotifications->count();
                                    $latestNotifications = auth()->user()->notifications()->take(3)->get();
                                @endphp
                                
                                <div class="relative" id="notification-dropdown">
                                    <button onclick="toggleNotificationDropdown()" class="cursor-pointer w-8 h-8 md:w-9 md:h-9 rounded-full border-2 border-text flex items-center justify-center text-text shadow-pop hover:shadow-none hover:translate-x-[4px] hover:translate-y-[2px] hover:bg-[#C7E7FF] transition-all duration-200 relative">
                                        <svg width="16" height="16" class="md:w-[18px] md:h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                        </svg>
                                        @if($unreadCount > 0)
                                            <span id="navbar-notif-badge" class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 border-2 border-white rounded-full"></span>
                                        @endif
                                    </button>

                                    <div id="notif-dropdown-menu" class="opacity-0 scale-95 pointer-events-none translate-y-[-10px] transition-all duration-200 origin-top-right absolute right-0 top-full mt-2 w-80 bg-white border-2 border-[#444] rounded-2xl shadow-xl z-50 overflow-hidden flex flex-col">
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
                                                <div class="p-3 hover:bg-gray-50 transition-colors flex gap-3 items-start {{ (isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended'])) ? 'bg-red-50' : ($notification->read_at ? '' : 'bg-blue-50/30 notif-item-unread') }}">
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
                                                        @elseif(isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended']))
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-red-600 mt-0.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                                        @else
                                                            <div class="w-2 h-2 rounded-full bg-amber-500 mt-1.5"></div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[13px] leading-relaxed line-clamp-2 {{ (isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended'])) ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                                                            @if(isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended']))
                                                                <strong class="text-red-700">Peringatan Admin</strong> {{ $notif['message'] }}
                                                            @else
                                                                <strong class="text-[#444]">{{ $notif['user_name'] ?? 'Sistem' }}</strong> {{ $notif['body'] ?? '' }}
                                                            @endif
                                                        </p>
                                                        <span class="text-[11px] {{ (isset($notif['type']) && in_array($notif['type'], ['user_warning', 'post_hidden', 'klub_hidden', 'review_hidden', 'content_hidden', 'post_suspended'])) ? 'text-red-400' : 'text-gray-400' }} mt-1 block">{{ $time }}</span>
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
                                <button onclick="toggleDropdown()" class="w-8 h-8 md:w-9 md:h-9 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex items-center justify-center text-xs md:text-sm font-black text-[#444] hover:shadow-md transition-shadow cursor-pointer overflow-hidden">
                                    <img id="navbar-avatar-img" src="{{ Auth::user()->foto_profil ? Storage::disk('public')->url(Auth::user()->foto_profil) : '' }}" alt="Avatar" class="w-full h-full object-cover {{ Auth::user()->foto_profil ? '' : 'hidden' }}">
                                    <span id="navbar-avatar-initial" class="{{ Auth::user()->foto_profil ? 'hidden' : '' }}">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </button>

                                <div id="dropdown-menu" class="opacity-0 scale-95 pointer-events-none translate-y-[-10px] transition-all duration-200 origin-top-right absolute right-0 top-full mt-2 w-48 bg-white border-2 border-[#444] rounded-2xl shadow-xl py-2 z-50">
                                    <a href="{{ route('dashboard') }}" class="block px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-[#FFDDAF]/30 transition-colors">Dasbor</a>
                                    <form method="POST" action="/logout">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2.5 text-sm font-medium text-red-500 hover:text-red-600 hover:bg-red-50 transition-colors cursor-pointer">Keluar</button>
                                    </form>
                                </div>
                               </div>
                           @else
                               <a href="{{ route('login') }}" class="text-sm bg-accent px-5 py-2 outline-2 hover:bg-amber-500 outline-text shadow-pop2 rounded-full font-bold text-text hover:text-gray-900 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">Masuk</a>
                           @endauth
                        </div>

                        <!-- Mobile search button -->
                        <button id="mobile-top-search-btn" class="md:hidden p-1.5 rounded-lg hover:bg-gray-100 transition-colors text-gray-600 cursor-pointer" aria-label="Cari">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </button>

                        <!-- Mobile menu button -->
                        <button class="md:hidden p-1.5 rounded-lg hover:bg-gray-100 transition-colors" id="mobile-menu-btn" aria-label="Menu">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <path d="M3 6h14M3 10h14M3 14h14"/>
                            </svg>
                        </button>
                    </div>

                    @auth
                       <script>
                        function toggleDropdown() {
                            const menu = document.getElementById('dropdown-menu');
                            const notifMenu = document.getElementById('notif-dropdown-menu');
                            
                            menu.classList.toggle('opacity-0');
                            menu.classList.toggle('scale-95');
                            menu.classList.toggle('pointer-events-none');
                            menu.classList.toggle('translate-y-[-10px]');
                            
                            menu.classList.toggle('opacity-100');
                            menu.classList.toggle('scale-100');
                            menu.classList.toggle('pointer-events-auto');
                            menu.classList.toggle('translate-y-0');

                            if (notifMenu && notifMenu.classList.contains('opacity-100')) {
                                notifMenu.classList.add('opacity-0', 'scale-95', 'pointer-events-none', 'translate-y-[-10px]');
                                notifMenu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto', 'translate-y-0');
                            }
                        }
                        
                        function toggleNotificationDropdown() {
                            const notifMenu = document.getElementById('notif-dropdown-menu');
                            const menu = document.getElementById('dropdown-menu');
                            
                            notifMenu.classList.toggle('opacity-0');
                            notifMenu.classList.toggle('scale-95');
                            notifMenu.classList.toggle('pointer-events-none');
                            notifMenu.classList.toggle('translate-y-[-10px]');
                            
                            notifMenu.classList.toggle('opacity-100');
                            notifMenu.classList.toggle('scale-100');
                            notifMenu.classList.toggle('pointer-events-auto');
                            notifMenu.classList.toggle('translate-y-0');

                            if (menu && menu.classList.contains('opacity-100')) {
                                menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none', 'translate-y-[-10px]');
                                menu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto', 'translate-y-0');
                            }
                        }

                        document.addEventListener('click', function(e) {
                            const profileDd = document.getElementById('profile-dropdown');
                            const notifDd = document.getElementById('notification-dropdown');
                            const menu = document.getElementById('dropdown-menu');
                            const notifMenu = document.getElementById('notif-dropdown-menu');
                            
                            if (profileDd && !profileDd.contains(e.target) && menu && menu.classList.contains('opacity-100')) {
                                menu.classList.add('opacity-0', 'scale-95', 'pointer-events-none', 'translate-y-[-10px]');
                                menu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto', 'translate-y-0');
                            }
                            if (notifDd && !notifDd.contains(e.target) && notifMenu && notifMenu.classList.contains('opacity-100')) {
                                notifMenu.classList.add('opacity-0', 'scale-95', 'pointer-events-none', 'translate-y-[-10px]');
                                notifMenu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto', 'translate-y-0');
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
            
            @auth
                @if(auth()->user()->is_banned)
                    <div id="banned-banner" class="bg-red-600 text-white text-center py-2 px-4 text-[13px] md:text-sm font-medium shadow-sm w-full border-t border-red-700">
                        Akun Anda telah ditangguhkan. Silakan hubungi admin di <strong>support@breeze-alinea.cloud</strong> untuk mengajukan banding.
                    </div>
                @endif
            @endauth
        </nav>
        
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const navbar = document.getElementById('main-navbar');
                const banner = document.getElementById('banned-banner');
                
                if (navbar && banner) {
                    const adjustPadding = () => {
                        const height = navbar.offsetHeight;
                        document.querySelectorAll('.pt-14, .pt-16, .min-h-screen.pt-16').forEach(el => {
                            if (el.tagName === 'MAIN' || el.tagName === 'DIV') {
                                el.style.paddingTop = height + 'px';
                            }
                        });
                    };
                    adjustPadding();
                    window.addEventListener('resize', adjustPadding);
                }
            });
        </script>
        @vite(['resources/js/global-search.js'])
        <!-- Mobile Menu Overlay -->
        <div id="mobile-menu-overlay" class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300 ease-in-out"></div>

        <!-- Mobile Menu Sidebar -->
        <div id="mobile-menu" class="fixed top-0 right-0 h-full w-72 bg-white z-50 shadow-2xl transform translate-x-full transition-transform duration-500 ease-out flex flex-col">
            <div class="flex justify-between items-center p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    @auth
                        <button class="w-10 h-10 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex items-center justify-center text-sm font-black text-[#444] overflow-hidden shadow-sm">
                            <img src="{{ Auth::user()->foto_profil ? Storage::disk('public')->url(Auth::user()->foto_profil) : '' }}" alt="Avatar" class="w-full h-full object-cover {{ Auth::user()->foto_profil ? '' : 'hidden' }}">
                            <span class="{{ Auth::user()->foto_profil ? 'hidden' : '' }}">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </button>
                        <div class="flex flex-col">
                            <span class="font-bold text-gray-800 text-sm leading-tight">{{ Auth::user()->name }}</span>
                            <span class="text-xs text-gray-500">{{ '@' . Auth::user()->username }}</span>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm bg-accent px-5 py-2 outline-2 hover:bg-amber-500 outline-text shadow-pop2 rounded-full font-bold text-text hover:text-gray-900 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">Masuk</a>
                    @endauth
                </div>
                <button id="close-mobile-menu" class="p-2 -mr-2 rounded-lg hover:bg-gray-100 text-gray-600 transition-colors" aria-label="Tutup Menu">
                    <svg width="24" height="24" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M4 4l12 12M16 4L4 16"/>
                    </svg>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-8">
                <!-- Action Buttons -->
                <div class="flex flex-col gap-4">
                    <div class="flex gap-4">
                        <button id="mobile-navbar-search-btn" class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 border-[#444] shadow-pop text-[#444] font-bold text-sm bg-white hover:bg-[#C7E7FF] hover:shadow-none hover:translate-x-[2px] hover:translate-y-[2px] transition-all cursor-pointer">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                            Cari
                        </button>
                        @auth
                            @php
                                $unreadCount = auth()->user()->unreadNotifications->count();
                            @endphp
                            <a href="{{ route('timeline_notifikasi') }}" class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 border-[#444] shadow-pop text-[#444] font-bold text-sm bg-white hover:bg-[#C7E7FF] hover:shadow-none hover:translate-x-[2px] hover:translate-y-[2px] transition-all relative cursor-pointer">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                                Notif
                                @if($unreadCount > 0)
                                    <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 border-2 border-white rounded-full flex items-center justify-center text-[10px] text-white font-bold">{{ $unreadCount }}</span>
                                @endif
                            </a>
                        @endauth
                    </div>

                </div>

                <nav class="flex flex-col gap-5 text-sm font-medium">
                    <a href="{{ route('beranda') }}" class="transition-colors {{ request()->routeIs('beranda*') ? 'text-amber-500 font-bold' : 'text-gray-600 hover:text-amber-500' }}" onclick="closeMobileMenu()">Beranda</a>
                    <a href="{{ route('explore') }}" class="transition-colors {{ request()->routeIs('explore*') ? 'text-amber-500 font-bold' : 'text-gray-600 hover:text-amber-500' }}" onclick="closeMobileMenu()">Jelajah</a>
                    <a href="{{ route('timeline_home') }}" class="transition-colors {{ request()->is('timeline*') ? 'text-amber-500 font-bold' : 'text-gray-600 hover:text-amber-500' }}" onclick="closeMobileMenu()">Lini Masa</a>
                    <a href="{{ route('klub') }}" class="transition-colors {{ request()->routeIs('klub*') ? 'text-amber-500 font-bold' : 'text-gray-600 hover:text-amber-500' }}" onclick="closeMobileMenu()">Klub</a>
                    <a href="{{ route('katalog') }}" class="transition-colors {{ request()->routeIs('katalog*') ? 'text-amber-500 font-bold' : 'text-gray-600 hover:text-amber-500' }}" onclick="closeMobileMenu()">Katalog</a>
                </nav>

                @auth
                <div class="mt-auto pt-6 border-t border-gray-100 flex flex-col gap-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-sm text-gray-600 hover:text-gray-900 font-medium" onclick="closeMobileMenu()">
                        <div class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-200 flex items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        </div>
                        Dasbor
                    </a>
                    <form method="POST" action="/logout" class="w-full">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 text-sm text-red-500 hover:text-red-600 font-medium w-full text-left">
                            <div class="w-8 h-8 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            </div>
                            Keluar
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const mobileMenuBtn = document.getElementById('mobile-menu-btn');
                const mobileMenu = document.getElementById('mobile-menu');
                const overlay = document.getElementById('mobile-menu-overlay');
                const closeBtn = document.getElementById('close-mobile-menu');
                const mobileSearchBtn = document.getElementById('mobile-navbar-search-btn');

                window.openMobileMenu = () => {
                    if (mobileMenu && overlay) {
                        mobileMenu.classList.remove('translate-x-full');
                        mobileMenu.classList.add('translate-x-0');
                        overlay.classList.remove('opacity-0', 'pointer-events-none');
                        overlay.classList.add('opacity-100', 'pointer-events-auto');
                        document.body.style.overflow = 'hidden';
                    }
                };

                window.closeMobileMenu = () => {
                    if (mobileMenu && overlay) {
                        mobileMenu.classList.remove('translate-x-0');
                        mobileMenu.classList.add('translate-x-full');
                        overlay.classList.remove('opacity-100', 'pointer-events-auto');
                        overlay.classList.add('opacity-0', 'pointer-events-none');
                        document.body.style.overflow = '';
                    }
                };

                if (mobileMenuBtn) {
                    mobileMenuBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (mobileMenu.classList.contains('translate-x-full')) {
                            openMobileMenu();
                        } else {
                            closeMobileMenu();
                        }
                    });
                }

                if (mobileSearchBtn) {
                    mobileSearchBtn.addEventListener('click', () => {
                        closeMobileMenu();
                        const desktopSearchBtn = document.getElementById('navbar-search-btn');
                        if (desktopSearchBtn) {
                            setTimeout(() => {
                                desktopSearchBtn.click();
                            }, 300); // Wait for menu to start closing
                        }
                    });
                }
                
                const mobileTopSearchBtn = document.getElementById('mobile-top-search-btn');
                if (mobileTopSearchBtn) {
                    mobileTopSearchBtn.addEventListener('click', (e) => {
                        if (document.getElementById('mobile-search-overlay')) {
                            // Timeline page: search.js will handle showing the overlay, so we do nothing here
                            return;
                        }
                        // Non-timeline page: fallback to global search
                        const desktopSearchBtn = document.getElementById('navbar-search-btn');
                        if (desktopSearchBtn) desktopSearchBtn.click();
                    });
                }

                closeBtn?.addEventListener('click', window.closeMobileMenu);
                overlay?.addEventListener('click', window.closeMobileMenu);
            });
        </script>