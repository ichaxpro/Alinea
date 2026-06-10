<div class="mt-8 border-b border-gray-200">
    <div class="overflow-x-auto [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <div class="flex w-full">
            @foreach ([
                ['label' => 'Unggahan', 'active' => true],
                ['label' => 'Penghargaan', 'active' => false],
                ['label' => 'Riwayat', 'active' => false],
                ['label' => 'Media', 'active' => false],
            ] as $tab)
            <button type="button"
                    data-profile-tab
                    data-profile-tab-target="{{ strtolower($tab['label']) }}"
                    class="flex-shrink-0 relative flex-1 px-3 pb-4 text-sm font-semibold transition-colors cursor-pointer text-center {{ $tab['active'] ? 'text-[#111]' : 'text-gray-400 hover:text-gray-600' }}"
                    aria-selected="{{ $tab['active'] ? 'true' : 'false' }}">
                {{ $tab['label'] }}
                <span data-profile-tab-indicator
                      class="absolute left-1/2 -translate-x-1/2 -bottom-[1px] h-1 w-24 rounded-full bg-[#5DA9FF] {{ $tab['active'] ? '' : 'hidden' }}"></span>
            </button>
            @endforeach
        </div>
    </div>
</div>
