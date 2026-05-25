<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Pesan — Alinea</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/chat.js'])

{{-- Pass the authenticated user to the JS layer --}}
<script>
window.authUser = {
    id:         {{ auth()->id() ?? 'null' }},
    name:       '{{ auth()->check() ? addslashes(auth()->user()->name) : "Saya" }}',
    initial:    '{{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : "S" }}',
    avatar_url: '{{ auth()->check() && auth()->user()->foto_profil ? Storage::disk("public")->url(auth()->user()->foto_profil) : "" }}',
};
</script>

<style>
    /* ── Chat list active state ───────────────────────── */
    .chat-item.active { background-color: #FFDDAF40; }
    .chat-item.active .chat-name { font-weight: 600; }

    /* ── Unread badge ─────────────────────────────────── */
    .unread-badge {
        min-width: 18px; height: 18px;
        font-size: 10px; line-height: 18px; text-align: center;
        padding: 0 5px; border-radius: 999px;
        background: #444; color: #fff; font-weight: 600;
    }

    /* ── Bubble animation ─────────────────────────────── */
    @keyframes bubbleIn {
        from { opacity: 0; transform: translateY(8px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0)   scale(1);    }
    }
    .bubble-in { animation: bubbleIn 0.22s cubic-bezier(0.16,1,0.3,1) both; }

    /* ── Typing indicator ─────────────────────────────── */
    @keyframes typingDot {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30%            { transform: translateY(-5px); opacity: 1; }
    }
    .typing-dot {
        width: 6px; height: 6px; background: #aaa;
        border-radius: 50%; display: inline-block;
        animation: typingDot 1.2s infinite;
    }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }

    /* ── Scrollbars ───────────────────────────────────── */
    #chatBox::-webkit-scrollbar,
    #chatList::-webkit-scrollbar { width: 4px; }
    #chatBox::-webkit-scrollbar-thumb,
    #chatList::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

    /* ── Emoji picker ─────────────────────────────────── */
    #emojiPicker {
        display: none; position: absolute;
        bottom: calc(100% + 8px); left: 0;
        background: white; border: 1px solid #e5e7eb;
        border-radius: 16px; padding: 10px;
        box-shadow: 4px 4px 0 1px #44444420;
        z-index: 50; gap: 6px; flex-wrap: wrap; width: 220px;
    }
    #emojiPicker.open { display: flex; }
    .emoji-btn {
        font-size: 20px; cursor: pointer; padding: 4px;
        border-radius: 8px; transition: background 0.15s; line-height: 1;
    }
    .emoji-btn:hover { background: #f3f4f6; }

    /* ── Input focus ──────────────────────────────────── */
    #messageInput:focus { box-shadow: 0 0 0 2px #FFDDAF; background: white; }
    #messageInput { transition: box-shadow 0.2s, background 0.2s; }
    #messageInput::-webkit-scrollbar { width: 4px; }
    #messageInput::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }
    .search-input:focus { box-shadow: 0 0 0 2px #FFDDAF; background: white; }
    .search-input { transition: box-shadow 0.2s, background 0.2s; }

    /* ── Message meta ─────────────────────────────────── */
    .msg-time   { font-size: 10px; color: #aaa; margin-top: 2px; padding: 0 2px; }
    .msg-status { display: inline-flex; align-items: center; margin-left: 2px; }

    /* ── New Chat Modal ───────────────────────────────── */
    #newChatModal {
        background: rgba(0,0,0,0.25);
        backdrop-filter: blur(4px);
    }

    /* ── Clock spin (pending) ─────────────────────────── */
    @keyframes clockSpin {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }
    .status-clock { animation: clockSpin 1.5s linear infinite; }

    /* ── Delete button on hover ───────────────────────── */
    .msg-delete-btn {
        opacity: 0; pointer-events: none;
        transition: opacity 0.15s;
        background: none; border: none; cursor: pointer;
        padding: 3px 5px; border-radius: 6px;
        color: #bbb; flex-shrink: 0; line-height: 1;
        align-self: center;
    }
    .msg-delete-btn:hover { color: #e05a5a; background: #fee2e2; }
    .bubble-wrapper:hover .msg-delete-btn { opacity: 1; pointer-events: auto; }

    /* ── Deleted message ──────────────────────────────── */
    .bubble-deleted {
        font-size: 12px; color: #aaa; font-style: italic;
        padding: 8px 14px;
        border: 1px dashed #ddd !important;
        background: #fafafa !important;
    }

    /* ── Media preview strip ──────────────────────────────── */
    #mediaPreviewStrip {
        display: none;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-top: 1px solid #f0f0f0;
        background: white;
        animation: bubbleIn 0.18s ease both;
    }
    #mediaPreviewStrip.open { display: flex; }
    #mediaPreviewThumb {
        width: 56px; height: 56px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
    }
    #mediaPreviewIcon {
        width: 56px; height: 56px;
        border-radius: 10px;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
    }
    #mediaCancelBtn {
        width: 22px; height: 22px;
        border-radius: 50%;
        background: #444; color: white;
        border: none; cursor: pointer;
        font-size: 12px; line-height: 1;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    #mediaPreviewName {
        flex: 1; font-size: 12px; color: #555;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    #mediaPreviewSize { font-size: 11px; color: #aaa; flex-shrink: 0; }

    /* ── Upload progress bar ──────────────────────────────── */
    #uploadProgressBar {
        position: absolute; bottom: 0; left: 0; right: 0;
        height: 3px; background: #FFDDAF;
        transform-origin: left;
        transform: scaleX(0);
        transition: transform 0.3s ease;
        border-radius: 0 0 0 0;
    }

    /* ── Media bubbles ────────────────────────────────────── */
    .media-bubble-img {
        max-width: 240px; max-height: 240px;
        border-radius: 14px; object-fit: cover;
        cursor: pointer; display: block;
        transition: opacity 0.2s;
    }
    .media-bubble-img:hover { opacity: 0.92; }
    .media-bubble-audio {
        max-width: 240px; width: 100%;
        border-radius: 8px; accent-color: #FFDDAF;
    }
    .media-bubble-video {
        max-width: 280px; max-height: 200px;
        border-radius: 14px; display: block;
    }
    .media-caption {
        font-size: 13px; margin-top: 4px;
    }
</style>

</head>

<body class="h-screen overflow-hidden flex flex-col">

<x-navbar></x-navbar>

<main class="flex-1 flex pt-16 overflow-hidden">

{{-- ═══════════════ SIDEBAR ═══════════════ --}}
<aside class="w-[340px] bg-white border-r flex flex-col">

    {{-- Header --}}
    <div class="h-[64px] flex items-center justify-between px-4 border-b">
        <div class="flex items-center gap-3">
            <button id="backBtn"
                class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 transition">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <h1 class="font-semibold text-sm leading-none">Pesan</h1>
                <span id="totalUnread" class="text-xs bg-gray-200 px-2 py-0.5 rounded-full" style="display:none">0</span>
            </div>
        </div>
        {{-- New chat button --}}
        <button id="newChatBtn" title="Pesan Baru"
            class="w-9 h-9 bg-[#FFDDAF] border border-[#444] rounded-full flex items-center justify-center hover:shadow-[2px_2px_0_1px_#444] transition-shadow">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="#444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
        </button>
    </div>

    {{-- Search existing conversations --}}
    <div class="px-4 py-3">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="14" height="14"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input id="searchInput" type="text" placeholder="Cari percakapan…"
                class="search-input w-full pl-9 pr-4 py-2 bg-gray-100 rounded-xl text-sm outline-none">
        </div>
    </div>

    {{-- Conversation list --}}
    <div id="chatList" class="flex-1 overflow-y-auto px-2 space-y-1 pb-2">
        <p class="text-xs text-gray-400 text-center py-6">Memuat percakapan…</p>
    </div>

</aside>

{{-- ═══════════════ CHAT AREA ═══════════════ --}}
<section class="flex-1 flex flex-col bg-[#fafafa] overflow-hidden">

    {{-- Empty state --}}
    <div id="chatEmpty" class="flex-1 flex flex-col items-center justify-center text-gray-400 gap-3">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
             class="opacity-30">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <p class="text-sm">Pilih percakapan atau mulai yang baru</p>
    </div>

    {{-- Chat header (hidden until a conversation is opened) --}}
    <div id="chatHeader" class="hidden h-[64px] flex items-center gap-3 px-4 border-b bg-white">
        <div id="chatAvatarWrapper" class="shrink-0">
            {{-- Avatar injected by JS --}}
        </div>
        <div class="flex-1">
            <p id="chatName" class="font-semibold text-sm"></p>
        </div>
        <button title="Info" class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 transition text-gray-400">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8"  x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </button>
    </div>

    {{-- Messages --}}
    <div id="chatBox" class="flex-1 overflow-y-auto p-4 space-y-2 hidden"></div>

    {{-- Typing indicator --}}
    <div id="typingIndicator" class="hidden px-6 pb-1">
        <div class="inline-flex items-center gap-1.5 bg-white shadow px-4 py-2 rounded-xl">
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="text-xs text-gray-400 ml-1">sedang mengetik…</span>
        </div>
    </div>

    {{-- Input bar (hidden until a conversation is opened) --}}
    <div id="chatInputArea" class="hidden border-t bg-white flex flex-col relative">

        {{-- Media preview strip --}}
        <div id="mediaPreviewStrip">
            <img id="mediaPreviewThumb" src="" alt="preview" style="display:none">
            <div id="mediaPreviewIcon" style="display:none">🎵</div>
            <div class="flex flex-col flex-1 min-w-0">
                <span id="mediaPreviewName"></span>
                <span id="mediaPreviewSize"></span>
            </div>
            <button id="mediaCancelBtn" title="Batalkan">✕</button>
        </div>

        {{-- Progress bar --}}
        <div id="uploadProgressBar"></div>

        {{-- Buttons + textarea --}}
        <div class="flex items-end gap-2 px-3 py-3">

        {{-- Emoji --}}
        <div class="relative">
            <button id="emojiToggle"
                class="text-gray-400 hover:text-[#444] transition w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100"
                title="Emoji">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                    <line x1="9"  y1="9"  x2="9.01"  y2="9"/>
                    <line x1="15" y1="9"  x2="15.01" y2="9"/>
                </svg>
            </button>
            <div id="emojiPicker"></div>
        </div>

        {{-- Attachment --}}
        <input type="file" id="mediaFileInput" accept="image/*,audio/*,video/*" class="hidden">
        <button id="attachBtn"
            class="text-gray-400 hover:text-[#444] transition w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100"
            title="Lampirkan media">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.44 11.05l-8.49 8.49a5.5 5.5 0 01-7.78-7.78l8.49-8.49a3.5 3.5 0 114.95 4.95L9.17 17.66a1.5 1.5 0 01-2.12-2.12l8.49-8.49"/>
            </svg>
        </button>

        <textarea id="messageInput" rows="1" placeholder="Ketik pesan…"
            class="flex-1 px-4 py-2 bg-gray-100 rounded-2xl outline-none text-sm resize-none overflow-hidden max-h-32"></textarea>

        <button id="sendBtn"
            class="w-9 h-9 bg-[#FFDDAF] border border-[#444] rounded-full flex items-center justify-center hover:shadow-[2px_2px_0_1px_#444] transition-shadow shrink-0"
            title="Kirim">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#444" stroke="none">
                <path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/>
            </svg>
        </button>

        </div>{{-- end buttons+textarea row --}}
    </div>

</section>

</main>

{{-- ═══════════════ NEW CHAT MODAL ═══════════════ --}}
<div id="newChatModal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl border-2 border-[#444] shadow-xl w-full max-w-sm flex flex-col"
         style="max-height: 70vh">

        {{-- Modal header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b">
            <h2 class="font-semibold text-sm">Pesan Baru</h2>
            <button id="closeNewChat"
                class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-gray-100 transition text-gray-400">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6"  y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        {{-- Search input --}}
        <div class="px-4 py-3 border-b">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" width="14" height="14"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input id="newChatSearch" type="text" placeholder="Cari nama atau username…"
                    class="search-input w-full pl-9 pr-4 py-2 bg-gray-100 rounded-xl text-sm outline-none">
            </div>
        </div>

        {{-- Results --}}
        <div id="newChatResults" class="flex-1 overflow-y-auto px-2 py-2 space-y-1"></div>

        {{-- Empty results --}}
        <p id="newChatEmpty" class="hidden text-xs text-gray-400 text-center py-6">
            Pengguna tidak ditemukan
        </p>

    </div>
</div>

</body>
</html>