import { state } from './state.js';

export function buildEmojiPicker() {
    const picker = document.getElementById('emojiPicker');
    if (!picker) return;
    state.emojis.forEach(e => {
        const btn = document.createElement('span');
        btn.className   = 'emoji-btn';
        btn.textContent = e;
        btn.onclick = () => {
            const input = document.getElementById('messageInput');
            input.value += e;
            input.focus();
            toggleEmoji(false);
        };
        picker.appendChild(btn);
    });
}

export function toggleEmoji(forceClose) {
    const picker = document.getElementById('emojiPicker');
    if (!picker) return;
    if (forceClose === false || picker.classList.contains('open')) {
        picker.classList.remove('open');
    } else {
        picker.classList.add('open');
    }
}
