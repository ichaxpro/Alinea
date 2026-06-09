import { initEcho } from './chat/echo.js';
import { bindEvents } from './chat/events.js';
import { buildEmojiPicker } from './chat/emoji.js';
import { fetchConversations } from './chat/sidebar.js';
import { openConversation } from './chat/conversation.js';
import { startNewConversation } from './chat/new-chat.js';

document.addEventListener('DOMContentLoaded', () => {
    initEcho();
    bindEvents();
    buildEmojiPicker();

    fetchConversations().then(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const targetUserId = urlParams.get('user_id');
        const targetConvId = urlParams.get('conv');

        if (targetUserId) {
            startNewConversation({ id: targetUserId });
            window.history.replaceState({}, document.title, window.location.pathname);
        } else if (targetConvId) {
            openConversation(targetConvId, false);
        }
    });
});
