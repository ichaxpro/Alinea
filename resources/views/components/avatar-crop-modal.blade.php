<div id="avatar-crop-modal" class="hidden fixed inset-0 z-300 bg-black/50 backdrop-blur-sm items-center justify-center p-4">
    <style>
        .crop-viewport {
            position: relative;
            width: 100%;
            aspect-ratio: 5 / 4;
            overflow: hidden;
            background: #111;
            border-radius: 12px;
            cursor: grab;
            touch-action: none;
            user-select: none;
            isolation: isolate;
            transform: translateZ(0);
            -webkit-mask-image: -webkit-radial-gradient(white, black);
        }
        .crop-viewport:active { cursor: grabbing; }
        .crop-viewport img {
            position: absolute;
            transform-origin: 0 0;
            will-change: transform;
            pointer-events: none;
            max-width: none;
        }
        .crop-circle-overlay {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 2;
        }
        .crop-circle-overlay svg {
            width: 100%;
            height: 100%;
        }
        .crop-crosshair {
            position: absolute;
            z-index: 3;
            pointer-events: none;
        }
        .crop-crosshair-h, .crop-crosshair-v {
            position: absolute;
            background: rgba(255,255,255,0.25);
        }
        .crop-crosshair-h {
            height: 1px;
            left: 50%;
            top: 50%;
            transform: translateY(-0.5px);
        }
        .crop-crosshair-v {
            width: 1px;
            left: 50%;
            top: 50%;
            transform: translateX(-0.5px);
        }
        .crop-zoom-slider {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            outline: none;
        }
        .crop-zoom-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #444;
            border: 2px solid white;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        }
        .crop-zoom-slider::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #444;
            border: 2px solid white;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        }
    </style>
    <div class="bg-white rounded-2xl border-[1.5px] border-text w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-sm">Ubah Foto Profil</h3>
            <button type="button" id="avatar-crop-cancel" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center transition-colors cursor-pointer">
                <svg width="16" height="16" viewBox="0 0 20 20" fill="none" stroke="#444" stroke-width="2.5" stroke-linecap="round"><path d="M4 4l12 12M16 4L4 16"/></svg>
            </button>
        </div>
        <div class="p-4">
            <div class="crop-viewport" id="avatar-crop-viewport">
                <img id="avatar-crop-image" src="" alt="Crop preview" />
                <!-- Circle mask overlay using SVG -->
                <svg class="crop-circle-overlay" viewBox="0 0 400 320" preserveAspectRatio="none">
                    <defs>
                        <mask id="circle-mask">
                            <rect width="400" height="320" fill="white"/>
                            <circle cx="200" cy="160" r="130" fill="black"/>
                        </mask>
                    </defs>
                    <rect width="400" height="320" fill="rgba(0,0,0,0.55)" mask="url(#circle-mask)"/>
                    <circle cx="200" cy="160" r="130" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2"/>
                </svg>
                <!-- Crosshair -->
                <div class="crop-crosshair" style="inset: 0;">
                    <div class="crop-crosshair-h" id="crop-ch-h"></div>
                    <div class="crop-crosshair-v" id="crop-ch-v"></div>
                </div>
            </div>
            <!-- Zoom slider -->
            <div class="flex items-center gap-3 mt-3 px-1">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                <input type="range" id="avatar-crop-zoom" class="crop-zoom-slider flex-1" min="100" max="400" value="100" step="1" />
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            </div>
        </div>
        <div class="p-4 border-t border-gray-100 flex justify-end gap-3">
            <button type="button" id="avatar-crop-cancel-btn" class="px-5 py-2.5 text-sm font-medium text-gray-400 hover:text-text transition-colors cursor-pointer rounded-full border border-gray-200">
                Batal
            </button>
            <button type="button" id="avatar-crop-save" class="px-6 py-2.5 bg-accent text-text font-bold text-sm rounded-full border-[1.5px] border-text hover:bg-[#ffcf90] transition-colors cursor-pointer">
                Simpan
            </button>
        </div>
    </div>
</div>