<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Alinea — {{ $user->name }}</title>
    <meta name="description" content="Profil {{ $user->name }} di Alinea" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-auth" content="{{ Auth::check() ? 'true' : 'false' }}">
    <meta name="user-name" content="{{ Auth::user()?->name ?? '' }}">
    <meta name="user-avatar-url" content="{{ Auth::user()?->avatar_url ?? '' }}" />
    <meta name="user-id" content="{{ Auth::id() ?? '' }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/timeline.js'])
</head>

<body class="bg-gray-100 text-[#444] font-[Poppins,sans-serif] min-h-screen antialiased">

    {{-- ========== NAVBAR (fixed, hides when scrolled away from top) ========== --}}
    <x-navbar></x-navbar>

    {{-- ========== PAGE LAYOUT (3-column: left | center | right) ========== --}}
    <div class="min-h-screen pt-16">
        <div class="flex items-start gap-6 max-w-[1200px] mx-auto px-4 py-6 max-md:pb-24">

            {{-- ===== LEFT SIDEBAR — floating sticky card ===== --}}
            <x-timeline-sidebar />

            {{-- ===== CENTER — FEED COLUMN ===== --}}
            <main class="flex-1 min-w-0 flex flex-col gap-4">

                            {{-- Profile Header & Tabs --}}
                <article class="bg-white border-[1.5px] border-[#444] rounded-2xl p-6">
                    
                    <x-profile.header 
                        :user="$user" 
                        :isOwnProfile="$isOwnProfile" 
                        :hasBlockedMe="$hasBlockedMe" 
                        :isBlockedByMe="$isBlockedByMe" 
                        :followingCount="$followingCount" 
                        :followersCount="$followersCount" 
                        :isFollowing="$isFollowing" 
                    />

                    <x-profile.tabs-nav />

                    <x-profile.tab-unggahan 
                        :posts="$posts" 
                        :user="$user" 
                        :hasBlockedMe="$hasBlockedMe" 
                        :isBlockedByMe="$isBlockedByMe" 
                        :isOwnProfile="$isOwnProfile" 
                    />

                    <x-profile.tab-penghargaan 
                        :achievements="$achievements" 
                        :inProgressAchievements="$inProgressAchievements" 
                        :isOwnProfile="$isOwnProfile" 
                    />

                    <x-profile.tab-riwayat 
                        :readingNow="$readingNow" 
                        :finishedBooks="$finishedBooks" 
                        :wantToRead="$wantToRead" 
                    />

                    <x-profile.tab-media 
                        :mediaPosts="$mediaPosts" 
                        :user="$user" 
                        :hasBlockedMe="$hasBlockedMe" 
                        :isBlockedByMe="$isBlockedByMe" 
                        :isOwnProfile="$isOwnProfile" 
                    />

                </article>
            </main>

            {{-- ===== RIGHT SIDEBAR — floating sticky card ===== --}}
            <x-timeline-sidebar-right
                searchPlaceholder="Cari buku atau pengguna..."
                trendingTitle="Populer Minggu Ini"
                :trendingItems="$trendingItems"
            />

        </div>
    </div>

    <div id="follow-modal-overlay" class="fixed inset-0 z-999 bg-black/50 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-200">
        <div id="follow-modal" class="bg-white rounded-2xl border-[1.5px] border-text w-full max-w-md mx-4 max-h-[80vh] flex flex-col shadow-xl opacity-0 translate-y-4 transition-all duration-200">
            <div class="flex items-center justify-between px-5 pt-5 pb-3 border-b border-gray-100">
                <h3 class="text-lg font-bold text-[#222]">Mengikuti &amp; Pengikut</h3>
                <button type="button" id="follow-modal-close" class="w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:text-text hover:bg-gray-100 transition-colors cursor-pointer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <div class="flex border-b border-gray-100">
                <button type="button" id="follow-tab-following" data-follow-tab="following" class="flex-1 pb-3 pt-3 text-sm font-semibold text-center transition-colors cursor-pointer text-[#111]">
                    Mengikuti
                    <span class="block mx-auto mt-1 h-1 w-16 rounded-full bg-[#5DA9FF]"></span>
                </button>
                <button type="button" id="follow-tab-followers" data-follow-tab="followers" class="flex-1 pb-3 pt-3 text-sm font-semibold text-center transition-colors cursor-pointer text-gray-400 hover:text-gray-600">
                    Pengikut
                    <span class="block mx-auto mt-1 h-1 w-16 rounded-full bg-transparent"></span>
                </button>
            </div>

            <div id="follow-modal-body" class="flex-1 overflow-y-auto p-5 min-h-50">
                <div class="flex items-center justify-center h-32">
                    <div class="w-6 h-6 border-2 border-text border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MOBILE SEARCH (full-page, like Twitter) ===== --}}
    <div id="mobile-search-overlay" class="hidden fixed inset-0 z-50 bg-white flex-col md:hidden">
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 flex-shrink-0">
            <button id="mobile-search-back" class="w-8 h-8 flex items-center justify-center -ml-1 cursor-pointer text-[#444] hover:bg-gray-100 rounded-full transition-colors flex-shrink-0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </button>
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="search" id="mobile-search-input" placeholder="Cari postingan atau pengguna..."
                       class="w-full border-[1.5px] border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm outline-none focus:border-[#444] transition-colors"
                       autocomplete="off" />
            </div>
            <button id="mobile-search-close" class="text-sm font-semibold text-gray-500 hover:text-[#444] cursor-pointer flex-shrink-0">Batal</button>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-4">
            <div id="mobile-search-dropdown" class="hidden"></div>
            <div id="mobile-search-trending">
                <h3 class="font-bold text-[13px] text-gray-400 uppercase tracking-wider mb-3">Populer Minggu ini</h3>
                <div class="flex flex-col gap-3">
                    @forelse ($trendingItems as $rank => $item)
                    <a href="{{ $item[2] ?? route('timeline_home', ['book' => $item[0]]) }}"
                       class="flex items-center gap-3 cursor-pointer hover:opacity-70 transition-opacity" tabindex="0">
                        <span class="text-[13px] font-bold text-gray-300 w-4 text-center flex-shrink-0">{{ $rank + 1 }}</span>
                        <div>
                            <span class="font-bold text-[13px] leading-tight block">{{ $item[0] }}</span>
                            <span class="text-[11px] text-gray-400">{{ $item[1] }}</span>
                        </div>
                    </a>
                    @empty
                    <p class="text-[13px] text-gray-400">Belum ada trending minggu ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MOBILE BOTTOM NAV ===== --}}
    <x-timeline-bottom-nav active="profil" />

    {{-- ========== BACK TO TOP ========== --}}
    <button id="back-to-top" aria-label="Kembali ke atas"
            class="fixed bottom-7 max-sm:bottom-20 right-7 z-50 w-12 h-12 rounded-full bg-[#444] text-white
                   flex items-center justify-center border-2 border-[#FFDDAF]
                   opacity-0 pointer-events-none translate-y-4
                   transition-all duration-300
                   hover:bg-[#FFDDAF] hover:text-[#444] hover:border-[#444] cursor-pointer">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15"/>
        </svg>
    </button>

    <script>
        async function handleReportUserProfile(userId, name) {
            const reason = prompt(`Laporkan ${name}?\nTuliskan alasanmu (opsional):`);
            if (reason === null) return;

            try {
                const res = await fetch(`/api/users/${userId}/report`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ reason })
                });
                alert(res.ok ? 'Laporan telah dikirim. Terima kasih.' : 'Gagal mengirim laporan. Coba lagi.');
            } catch {
                alert('Terjadi kesalahan. Coba lagi.');
            }
        }

        async function handleBlockUserProfile(userId, name) {
            if (!confirm(`Yakin ingin mengubah status blokir untuk ${name}?`)) return;

            try {
                const res = await fetch(`/api/users/${userId}/block`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.action === 'blocked') {
                        alert(`${name} telah diblokir.`);
                    } else {
                        alert(`Blokir untuk ${name} telah dibuka.`);
                    }
                    window.location.reload(); // Reload to reflect changes (hide/show posts and buttons)
                } else {
                    alert('Gagal memproses permintaan.');
                }
            } catch {
                alert('Terjadi kesalahan. Coba lagi.');
            }
        }
    </script>
</body>
</html>