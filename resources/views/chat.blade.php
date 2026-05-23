<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Pesan — Alinea</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@vite(['resources/css/app.css'])

</head>

<body class="h-screen overflow-hidden flex flex-col">

<x-navbar></x-navbar>

<main class="flex-1 flex pt-16 overflow-hidden">

<!-- SIDEBAR -->
<aside class="w-[340px] bg-white border-r flex flex-col">

    <!-- HEADER SIDEBAR -->
    <div class="h-[64px] flex items-center justify-between px-4 border-b">

        <div class="flex items-center gap-3">

            <!-- BACK BUTTON -->
            <button onclick="history.back()"
                class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-gray-100 transition">
                
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5"
                     stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </button>

            <!-- TITLE -->
            <div class="flex items-center gap-2">
                <h2 class="font-semibold text-sm leading-none">Pesan</h2>
                <span class="text-xs bg-gray-200 px-2 py-0.5 rounded-full">4</span>
            </div>

        </div>

        <!-- ADD BUTTON -->
        <button class="w-9 h-9 bg-[#FFDDAF] border border-[#444] rounded-full">
            +
        </button>

    </div>

    <!-- SEARCH -->
    <div class="px-4 py-3">
        <input type="text" placeholder="Search messages"
            class="w-full px-4 py-2 bg-gray-100 rounded-xl text-sm outline-none">
    </div>

    <!-- LIST -->
    <div id="chatList" class="flex-1 overflow-y-auto px-2 space-y-1">

        <div onclick="openChat('bima')" class="chat-item flex gap-3 px-3 py-2 rounded-xl cursor-pointer hover:bg-gray-100">
            <div class="w-11 h-11 bg-gray-300 rounded-full"></div>
            <div class="flex-1">
                <p class="font-medium text-sm">Bima</p>
                <p class="text-xs text-gray-400">Halo, lagi dimana?</p>
            </div>
            <span class="text-xs text-gray-400">12m</span>
        </div>

        <div onclick="openChat('rizki')" class="chat-item flex gap-3 px-3 py-2 rounded-xl cursor-pointer hover:bg-gray-100">
            <div class="w-11 h-11 bg-gray-300 rounded-full"></div>
            <div class="flex-1">
                <p class="font-medium text-sm">Rizki</p>
                <p class="text-xs text-gray-400">Tugas udah kelar belum?</p>
            </div>
            <span class="text-xs text-gray-400">10m</span>
        </div>

        <div onclick="openChat('icha')" class="chat-item flex gap-3 px-3 py-2 rounded-xl cursor-pointer hover:bg-gray-100">
            <div class="w-11 h-11 bg-gray-300 rounded-full"></div>
            <div class="flex-1">
                <p class="font-medium text-sm">Icha</p>
                <p class="text-xs text-gray-400">Nanti meeting yaa</p>
            </div>
            <span class="text-xs text-gray-400">8m</span>
        </div>

        <div onclick="openChat('zaky')" class="chat-item flex gap-3 px-3 py-2 rounded-xl cursor-pointer hover:bg-gray-100">
            <div class="w-11 h-11 bg-gray-300 rounded-full"></div>
            <div class="flex-1">
                <p class="font-medium text-sm">Zaky</p>
                <p class="text-xs text-gray-400">Ngopi kuy</p>
            </div>
            <span class="text-xs text-gray-400">5m</span>
        </div>

    </div>
</aside>

<!-- CHAT AREA -->
<section class="flex-1 flex flex-col bg-[#fafafa]">

    <!-- HEADER CHAT -->
    <div class="h-[64px] flex items-center gap-3 px-4 border-b bg-white">
        <div class="w-11 h-11 bg-gray-300 rounded-xl"></div>
        <div>
            <p id="chatName" class="font-medium text-sm">Bima</p>
            <p class="text-xs text-gray-400">Online</p>
        </div>
    </div>

    <!-- CHAT CONTENT -->
    <div id="chatBox" class="flex-1 overflow-y-auto p-4 space-y-3"></div>

    <!-- INPUT -->
    <div class="border-t bg-white px-3 py-3 flex items-center gap-2">
        
        <button class="text-gray-400 hover:text-[#444] transition">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.44 11.05l-8.49 8.49a5.5 5.5 0 01-7.78-7.78l8.49-8.49a3.5 3.5 0 114.95 4.95L9.17 17.66a1.5 1.5 0 01-2.12-2.12l8.49-8.49"/>
            </svg>
        </button>

        <input id="messageInput" type="text" placeholder="Type a message"
            class="flex-1 px-4 py-2 bg-gray-100 rounded-full outline-none text-sm">

        <button onclick="sendMessage()" class="w-9 h-9 bg-[#FFDDAF] border border-[#444] rounded-full">
            ➤
        </button>

    </div>

</section>

</main>

<!-- SCRIPT -->
<script>

const chats = {
    bima: [
        { type: 'left', text: 'Halo, lagi dimana?' },
        { type: 'right', text: 'Lagi di kampus' }
    ],
    rizki: [
        { type: 'left', text: 'Tugas udah kelar belum?' },
        { type: 'right', text: 'Belum kii sabarrr yaa' }
    ],
    icha: [
        { type: 'left', text: 'Nanti meeting yaa' },
        { type: 'right', text: 'Insyaallah hehehe' }
    ],
    zaky: [
        { type: 'left', text: 'Ngopi kuy' },
        { type: 'right', text: 'Gas!!' }
    ]
};

let currentChat = 'bima';

function renderChat() {
    const chatBox = document.getElementById('chatBox');
    chatBox.innerHTML = '';

    chats[currentChat].forEach(msg => {
        const div = document.createElement('div');
        div.className = msg.type === 'right' ? 'flex justify-end' : 'flex';

        div.innerHTML = `
            <div class="${msg.type === 'right' ? 'bg-[#FFDDAF] border border-[#444]' : 'bg-white shadow'} px-4 py-2 rounded-xl text-sm">
                ${msg.text}
            </div>
        `;
        chatBox.appendChild(div);
    });

    chatBox.scrollTop = chatBox.scrollHeight;
}

function openChat(name) {
    currentChat = name;
    document.getElementById('chatName').innerText =
        name.charAt(0).toUpperCase() + name.slice(1);
    renderChat();
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    if (!text) return;

    chats[currentChat].push({ type: 'right', text });
    input.value = '';
    renderChat();
}

renderChat();

</script>

</body>
</html>