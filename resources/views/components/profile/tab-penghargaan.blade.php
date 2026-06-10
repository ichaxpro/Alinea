@props(['achievements', 'inProgressAchievements', 'isOwnProfile'])

<div data-profile-panel="penghargaan" class="hidden mt-5 flex flex-col gap-8">
    {{-- Sedang Berjalan (Only visible to owner) --}}
    @if(isset($isOwnProfile) && $isOwnProfile && isset($inProgressAchievements) && $inProgressAchievements->isNotEmpty())
    <section>
        <h2 class="font-bold text-[16px] text-[#444] mb-4">Sedang Berjalan</h2>
        <div class="flex flex-col gap-5">
            @foreach ($inProgressAchievements as $achievement)
            <div class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0 opacity-70">
                <div class="flex items-center gap-5 max-sm:gap-3">
                    <div class="w-24 h-24 max-sm:w-16 max-sm:h-16 grayscale shrink-0" style="background: url('{{ asset('images/' . ($achievement->icon ?? 'badge_(2).png')) }}') no-repeat center center; background-size: cover;"></div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-[15px] max-sm:text-[14px] text-[#444]">{{ $achievement->title }}</h3>
                        <p class="text-sm max-sm:text-xs text-gray-500 mb-2">{{ $achievement->description }}</p>
                        
                        {{-- Progress Bar --}}
                        @php
                            $progressPercent = min(100, max(0, ($achievement->current_progress / max(1, $achievement->criteria_value)) * 100));
                        @endphp
                        <div class="w-full bg-gray-200 rounded-full h-3 max-sm:h-2 mb-1 relative overflow-hidden">
                            <div class="bg-[#FFDDAF] h-full rounded-full border-[1.5px] border-[#444] shadow-[inset_0_1px_2px_rgba(0,0,0,0.1)] transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                        </div>
                        <p class="text-[11px] font-bold text-gray-500 tracking-wide">{{ $achievement->current_progress }} / {{ $achievement->criteria_value }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Diperoleh --}}
    <section>
        @if(isset($isOwnProfile) && $isOwnProfile && isset($inProgressAchievements) && $inProgressAchievements->isNotEmpty())
            <h2 class="font-bold text-[16px] text-[#444] mb-4">Diperoleh</h2>
        @endif
        <div class="flex flex-col gap-5">
            @forelse ($achievements as $achievement)
            <div class="pb-5 border-b border-gray-200 last:border-b-0 last:pb-0">
                <div class="flex items-center gap-5 max-sm:gap-3">
                    <div class="w-24 h-24 max-sm:w-16 max-sm:h-16 shrink-0 drop-shadow-sm" style="background: url('{{ asset('images/' . ($achievement->icon ?? 'badge_(2).png')) }}') no-repeat center center; background-size: cover;"></div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-[15px] max-sm:text-[14px] text-[#444]">{{ $achievement->title }}</h3>
                        <p class="text-sm max-sm:text-xs text-gray-500">{{ $achievement->description }}</p>
                        @if($achievement->pivot?->earned_at)
                            <p class="text-xs max-sm:text-[11px] text-gray-400 mt-1">Diperoleh {{ \Carbon\Carbon::parse($achievement->pivot->earned_at)->locale('id')->diffForHumans() }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <p class="text-center text-gray-400 py-8 text-sm">Belum ada penghargaan.</p>
            @endforelse
        </div>
    </section>
</div>
