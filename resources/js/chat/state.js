export const state = {
    authUser: window.authUser || { id: null, name: 'Saya', initial: 'S', avatar_url: '' },
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
    apiBase: '/api/chat',

    conversations: [],
    currentMessages: [],
    currentConversationId: null,
    currentOtherUser: null,
    cursor: null,
    
    loadingMessages: false,
    typingTimeout: null,
    searchTimeout: null,
    
    activeSubscriptions: [],
    subscribeIds: new Set(),
    
    userDetailOpen: false,

    pendingMediaBlob: null,
    pendingMediaType: null,
    pendingMediaName: null,

    emojis: ['😀','😂','😍','🥰','😎','🤔','😅','🙏','👍','❤️','🔥','🎉','📚','✨','💬','😭','🥺','😊','👀','💪']
};
