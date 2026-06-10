@props([
    'user',
    'isOwnProfile',
    'hasBlockedMe',
    'isBlockedByMe',
    'followingCount',
    'followersCount',
    'isFollowing'
])

<div class="flex flex-col sm:flex-row gap-6 items-center sm:items-start text-center sm:text-left">
    <div class="w-28 h-28 rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF] border-2 border-[#444] flex-shrink-0 overflow-hidden flex items-center justify-center">
        @if($user->foto_profil)
        <img src="{{ Storage::disk('public')->url($user->foto_profil) }}" alt="Avatar" class="w-full h-full object-cover">
        @else
        <span class="text-4xl font-black text-text/60">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
        @endif
    </div>

    <div class="flex-1 w-full">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
            <div class="text-center sm:text-left">
                <h2 class="text-2xl font-bold text-[#222]">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->username ? '@' . $user->username : 'tanpa_username' }}</p>
            </div>

            @auth
                @if($isOwnProfile)
                    <a href="{{ route('profile.edit') }}"
                       class="sm:ml-auto px-4 py-2 bg-[#FFDDAF] border-2 border-[#444] rounded-full text-sm font-bold hover:bg-[#ffcf90] transition-colors whitespace-nowrap">
                        Edit Profil
                    </a>
                @else
                    @if($hasBlockedMe)
                        <div class="sm:ml-auto px-4 py-2 bg-red-50 text-red-600 rounded-full text-sm font-bold border border-red-200">
                            Anda diblokir oleh pengguna ini
                        </div>
                    @elseif($isBlockedByMe)
                        <div class="sm:ml-auto flex items-center gap-2">
                            <button type="button" onclick="handleBlockUserProfile({{ $user->id }}, '{{ addslashes($user->name) }}')" class="px-5 py-2 rounded-full text-sm font-bold border-2 border-red-500 bg-red-500 text-white hover:bg-red-600 transition-colors whitespace-nowrap">
                                Buka Blokir
                            </button>
                        </div>
                    @else
                        <div class="sm:ml-auto flex items-center gap-2">
                            {{-- DM Button --}}
                            <a href="{{ route('chat') }}?user_id={{ $user->id }}" class="w-10 h-10 flex items-center justify-center rounded-full border-2 border-[#444] text-[#444] hover:bg-gray-100 transition-colors cursor-pointer bg-white" title="Kirim Pesan">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                            </a>
                            
                            {{-- Follow Button --}}
                            <button id="follow-btn"
                                    data-follow-url="{{ route('profile.follow', $user) }}"
                                    data-following="{{ $isFollowing ? 'true' : 'false' }}"
                                    data-following-count="{{ $followingCount }}"
                                    class="px-5 py-2 rounded-full text-sm font-bold border-2 border-[#444] transition-colors cursor-pointer whitespace-nowrap
                                           {{ $isFollowing ? 'bg-[#444] text-white' : 'bg-[#FFDDAF] hover:bg-[#ffcf90]' }}">
                                {{ $isFollowing ? 'Mengikuti' : 'Ikuti' }}
                            </button>
                            
                            {{-- More Menu (Report & Block) --}}
                            <div class="relative" data-post-menu>
                                <button type="button" class="w-10 h-10 flex items-center justify-center rounded-full border-2 border-[#444] text-[#444] hover:bg-gray-100 transition-colors cursor-pointer bg-white" data-post-menu-trigger title="Lainnya">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                                <div class="absolute right-0 mt-2 min-w-max bg-white rounded-xl border-2 border-[#444] py-1 hidden z-50 transform origin-top-right transition-all" data-post-menu-dropdown>
                                    <button type="button" onclick="handleReportUserProfile({{ $user->id }}, '{{ addslashes($user->name) }}')" class="w-full text-left px-4 py-2.5 text-sm text-red-500 font-bold hover:bg-red-50 transition-colors flex items-center gap-2 whitespace-nowrap">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                                        Laporkan Pengguna
                                    </button>
                                    <button type="button" onclick="handleBlockUserProfile({{ $user->id }}, '{{ addslashes($user->name) }}')" class="w-full text-left px-4 py-2.5 text-sm text-[#444] font-bold hover:bg-gray-100 transition-colors flex items-center gap-2 whitespace-nowrap">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                        Blokir Pengguna
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endauth
        </div>

        @if($user->deskripsi)
            <p class="mt-4 text-base text-[#333] text-center sm:text-left">{{ $user->deskripsi }}</p>
        @endif

        <p class="text-sm text-gray-500 mt-2 text-center sm:text-left">
            <button type="button" id="profile-following-trigger" data-user-id="{{ $user->id }}" class="hover:underline cursor-pointer">
                <span class="font-bold text-[#222]">{{ $followingCount }}</span> Mengikuti
            </button>
            <span class="mx-2">|</span>
            <button type="button" id="profile-followers-trigger" data-user-id="{{ $user->id }}" class="hover:underline cursor-pointer">
                <span class="font-bold text-[#222]">{{ $followersCount }}</span> Pengikut
            </button>
        </p>
    </div>
</div>
