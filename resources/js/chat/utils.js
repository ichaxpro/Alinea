import browserImageCompression from 'browser-image-compression';

export function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

export function formatTime(isoString) {
    if (!isoString) return '';
    const d = new Date(isoString);
    return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
}

export function avatarHTML(user, size, border = true) {
    const borderClass = border ? 'border-2 border-[#444]' : '';
    if (user.avatar_url) {
        return `<img src="${user.avatar_url}" alt="avatar"
                     class="${size} rounded-full ${borderClass} object-cover flex-shrink-0">`;
    }
    const initial = user.initial || user.name?.charAt(0)?.toUpperCase() || '?';
    return `<div class="${size} rounded-full bg-gradient-to-br from-[#FFDDAF] to-[#C7E7FF]
                         ${borderClass} flex items-center justify-center
                         text-[#444] font-bold text-xs flex-shrink-0">
                ${escapeHtml(initial)}
            </div>`;
}

export function formatFileSize(bytes) {
    if (bytes < 1024)        return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

export function svgSingleTick() {
    return `<svg width="14" height="10" viewBox="0 0 14 10" fill="none">
                <polyline points="1,5 5,9 13,1" stroke="#aaa" stroke-width="1.8"
                          stroke-linecap="round" stroke-linejoin="round"/>
            </svg>`;
}

export async function compressImage(file) {
    const options = {
        maxSizeMB: 1,
        maxWidthOrHeight: 1280,
        useWebWorker: true,
        initialQuality: 0.8
    };
    try {
        return await browserImageCompression(file, options);
    } catch (error) {
        console.warn('Image compression failed, returning original file', error);
        return file;
    }
}
