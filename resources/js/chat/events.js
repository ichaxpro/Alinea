import { state } from './state.js';
import { closeActiveConversation, openConversation, handleReportUser, handleBlockUser, handleDeleteConversation } from './conversation.js';
import { filterChats } from './sidebar.js';
import { closeMediaModal, handleMediaSelect, clearPendingMedia } from './media.js';
import { openUserDetailPanel, closeUserDetailPanel } from './user-detail.js';
import { toggleEmoji } from './emoji.js';
import { openNewChatModal, closeNewChatModal, searchUsers } from './new-chat.js';
import { sendMessage, emitTyping } from './messages.js';

export function bindEvents() {
    document.getElementById('backBtn')?.addEventListener('click', () => {
        window.location.href = '/timeline_home';
    });

    document.getElementById('closeChatBtn')?.addEventListener('click', () => {
        if (window.history.state && window.history.state.conversationId) {
            window.history.back();
        } else {
            closeActiveConversation(true);
        }
    });

    window.addEventListener('popstate', (e) => {
        const stateUrl = e.state;
        if (stateUrl && stateUrl.conversationId) {
            openConversation(stateUrl.conversationId, false);
        } else {
            closeActiveConversation(false);
        }
    });

    document.getElementById('sendBtn')?.addEventListener('click', sendMessage);

    document.getElementById('mediaModalClose')?.addEventListener('click', closeMediaModal);

    document.getElementById('mediaModal')?.addEventListener('click', (e) => {
        if (e.target === document.getElementById('mediaModal')) closeMediaModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (state.userDetailOpen) closeUserDetailPanel();
            else closeMediaModal();
        }
    });

    document.getElementById('userDetailTrigger')?.addEventListener('click', openUserDetailPanel);
    document.getElementById('userDetailClose')?.addEventListener('click', closeUserDetailPanel);
    document.getElementById('userDetailOverlay')?.addEventListener('click', closeUserDetailPanel);
    document.getElementById('udReportBtn')?.addEventListener('click', handleReportUser);
    document.getElementById('udBlockBtn')?.addEventListener('click', handleBlockUser);
    document.getElementById('udDeleteChatBtn')?.addEventListener('click', handleDeleteConversation);

    document.getElementById('attachBtn')?.addEventListener('click', () => {
        document.getElementById('mediaFileInput')?.click();
    });

    document.getElementById('mediaFileInput')?.addEventListener('change', function () {
        if (this.files?.[0]) handleMediaSelect(this.files[0]);
    });

    document.getElementById('mediaCancelBtn')?.addEventListener('click', clearPendingMedia);

    document.getElementById('searchInput')?.addEventListener('input', function () {
        filterChats(this.value);
    });

    const msgInput = document.getElementById('messageInput');
    if (msgInput) {
        msgInput.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
        });
        const autoResize = () => {
            msgInput.style.height = 'auto';
            msgInput.style.height = msgInput.scrollHeight + 'px';
            msgInput.style.overflowY = msgInput.scrollHeight > msgInput.clientHeight ? 'auto' : 'hidden';
            msgInput.style.overflowX = 'hidden';
        };
        msgInput.addEventListener('input', () => {
            autoResize();
            clearTimeout(state.typingTimeout);
            emitTyping(true);
            state.typingTimeout = setTimeout(() => emitTyping(false), 2000);
        });
    }

    const chatBox = document.getElementById('chatBox');
    if (chatBox) {
        chatBox.addEventListener('dragover', e => { e.preventDefault(); chatBox.style.opacity = '0.7'; });
        chatBox.addEventListener('dragleave', () => { chatBox.style.opacity = ''; });
        chatBox.addEventListener('drop', e => {
            e.preventDefault();
            chatBox.style.opacity = '';
            const file = e.dataTransfer?.files?.[0];
            if (file && state.currentConversationId) handleMediaSelect(file);
        });
    }

    document.getElementById('emojiToggle')?.addEventListener('click', toggleEmoji);
    document.getElementById('newChatBtn')?.addEventListener('click', openNewChatModal);
    document.getElementById('closeNewChat')?.addEventListener('click', closeNewChatModal);

    const newChatSearch = document.getElementById('newChatSearch');
    if (newChatSearch) {
        newChatSearch.addEventListener('input', function () {
            clearTimeout(state.searchTimeout);
            state.searchTimeout = setTimeout(() => searchUsers(this.value.trim()), 300);
        });
        newChatSearch.addEventListener('keydown', e => {
            if (e.key === 'Escape') closeNewChatModal();
        });
    }

    document.addEventListener('click', e => {
        const toggle = document.getElementById('emojiToggle');
        const picker = document.getElementById('emojiPicker');
        if (toggle && picker && !toggle.contains(e.target) && !picker.contains(e.target)) {
            picker.classList.remove('open');
        }
        const modal = document.getElementById('newChatModal');
        if (modal && e.target === modal) closeNewChatModal();
    });
}
