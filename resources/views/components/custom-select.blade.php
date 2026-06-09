@props([
    'id' => '',
    'name' => '',
    'options' => [],
    'multiple' => false,
    'placeholder' => 'Pilih opsi',
    'class' => '',
    'title' => '', // For the header inside the dropdown, like "FILTER STATUS"
    'columns' => 1,
    'align' => 'left',
    'direction' => 'down',
])

<div class="relative custom-select-container inline-block w-full sm:w-auto" id="{{ $id }}-container" data-multiple="{{ $multiple ? 'true' : 'false' }}">
    {{-- Trigger Button with Grid Trick for Intrinsic Width --}}
    @php
        $longest = $placeholder;
        foreach($options as $label) {
            if(strlen($label) > strlen($longest)) {
                $longest = $label;
            }
        }
    @endphp
    <button type="button" class="custom-select-trigger grid items-center bg-white border-[1.5px] border-[#444] rounded-lg pl-4 pr-3 py-2.5 text-sm font-medium text-[#444] outline-none cursor-pointer hover:bg-gray-50 focus:border-[#FFDDAF] transition-colors w-full {{ $class }}" style="grid-template-columns: 1fr auto; gap: 0.5rem;">
        <div class="relative flex items-center justify-start overflow-hidden w-full text-left">
            <span class="custom-select-label absolute left-0 truncate w-full text-left">{{ $placeholder }}</span>
            <span class="invisible whitespace-nowrap" aria-hidden="true">{{ $longest }}</span>
        </div>
        <svg class="custom-select-icon flex-shrink-0 text-[#444] transition-transform duration-200 justify-self-end" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    {{-- Dropdown Menu --}}
    @php
        $gridColsClass = '';
        if ($columns == 2) $gridColsClass = 'grid-cols-2';
        elseif ($columns == 3) $gridColsClass = 'grid-cols-2 sm:grid-cols-3';
        elseif ($columns >= 4) $gridColsClass = 'grid-cols-2 sm:grid-cols-4';
        
        $dropdownWidth = $columns > 1 ? 'w-full sm:w-max sm:min-w-[400px] pr-2 max-w-[90vw]' : 'w-full sm:w-56';
        if ($align === 'right') {
            $alignClasses = 'left-0 right-0 sm:left-auto sm:right-0';
        } elseif ($align === 'center') {
            $alignClasses = 'left-0 right-0 sm:left-1/2 sm:right-auto sm:-translate-x-1/2';
        } else {
            $alignClasses = 'left-0 right-0 sm:left-0 sm:right-auto';
        }
        
        if ($direction === 'up') {
            $positionClasses = 'bottom-full mb-2';
        } else {
            $positionClasses = 'top-full mt-2';
        }
    @endphp
    <div class="custom-select-dropdown opacity-0 invisible -translate-y-2 pointer-events-none transition-all duration-200 ease-out absolute {{ $alignClasses }} {{ $positionClasses }} {{ $dropdownWidth }} max-h-60 sm:max-h-80 overflow-y-auto bg-white border-[1.5px] border-[#444] rounded-xl shadow-lg z-[100]">
        @if($title)
        <div class="px-4 py-2 border-b border-gray-100 font-bold text-[10px] text-gray-400 uppercase tracking-wider bg-gray-50 sticky top-0 z-10">
            {{ $title }}
        </div>
        @endif
        
        <div class="py-2 custom-select-options {{ $columns > 1 ? 'grid ' . $gridColsClass . ' gap-x-2 sm:gap-x-6 gap-y-2 sm:gap-y-1 px-3' : 'flex flex-col' }}">
            @if(!$multiple && $placeholder)
                <label class="custom-select-option px-4 py-2 text-sm hover:bg-gray-50 transition-colors cursor-pointer flex items-center gap-2">
                    <input type="radio" name="{{ $name ?: $id }}_radio" value="" class="hidden" checked data-label="{{ $placeholder }}">
                    <span class="flex-1 {{ empty($options) ? 'font-bold text-[#444]' : 'text-gray-600' }}">{{ $placeholder }}</span>
                </label>
            @endif

            @foreach($options as $value => $label)
                <label class="custom-select-option px-4 py-2 text-sm hover:bg-gray-50 transition-colors cursor-pointer flex items-start gap-2">
                    @if($multiple)
                        <div class="mt-0.5 relative flex items-center justify-center w-4 h-4 border-2 border-gray-300 rounded focus-within:border-[#444] bg-white transition-colors">
                            <input type="checkbox" name="{{ $name ?: $id }}[]" value="{{ $value }}" data-label="{{ $label }}" class="peer absolute inset-0 opacity-0 cursor-pointer w-full h-full m-0">
                            <svg class="peer-checked:opacity-100 opacity-0 pointer-events-none text-[#444]" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <span class="flex-1 text-gray-600 peer-checked:font-bold peer-checked:text-[#444] leading-tight select-none">{{ $label }}</span>
                    @else
                        <input type="radio" name="{{ $name ?: $id }}_radio" value="{{ $value }}" data-label="{{ $label }}" class="hidden">
                        <span class="flex-1 text-gray-600">{{ $label }}</span>
                    @endif
                </label>
            @endforeach
        </div>
    </div>

    {{-- Hidden Select for JS Compatibility --}}
    <select id="{{ $id }}" name="{{ $name ?: $id }}" {!! $multiple ? 'multiple' : '' !!} class="hidden">
        @if(!$multiple && $placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
