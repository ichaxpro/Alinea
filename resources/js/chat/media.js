import { state } from './state.js';
import { formatFileSize, compressImage } from './utils.js';

export async function handleMediaSelect(file) {
    if (!file) return;

    const maxBytes = { image: 20 * 1024 * 1024, audio: 16 * 1024 * 1024, video: 100 * 1024 * 1024 };
    let mediaType = null;
    if (file.type.startsWith('image/'))      mediaType = 'image';
    else if (file.type.startsWith('audio/')) mediaType = 'audio';
    else if (file.type.startsWith('video/')) mediaType = 'video';
    else { alert('Tipe file tidak didukung.'); return; }

    if (file.size > maxBytes[mediaType]) {
        alert(`Ukuran file terlalu besar. Maksimal ${formatFileSize(maxBytes[mediaType])} untuk ${mediaType}.`);
        return;
    }

    let blob = file;
    if (mediaType === 'image') blob = await compressImage(file);

    state.pendingMediaBlob = blob;
    state.pendingMediaType = mediaType;
    state.pendingMediaName = file.name;

    const strip  = document.getElementById('mediaPreviewStrip');
    const thumb  = document.getElementById('mediaPreviewThumb');
    const icon   = document.getElementById('mediaPreviewIcon');
    const nameEl = document.getElementById('mediaPreviewName');
    const sizeEl = document.getElementById('mediaPreviewSize');

    nameEl.textContent = file.name;
    sizeEl.textContent = `${formatFileSize(file.size)}${mediaType === 'image' ? ` → ${formatFileSize(blob.size)} (terkompresi)` : ''}`;

    if (mediaType === 'image') {
        const objUrl = URL.createObjectURL(blob);
        thumb.src = objUrl;
        thumb.onload = () => URL.revokeObjectURL(objUrl);
        thumb.style.display = '';
        icon.style.display  = 'none';
    } else {
        icon.textContent    = mediaType === 'audio' ? '🎵' : '🎬';
        icon.style.display  = '';
        thumb.style.display = 'none';
    }

    strip.classList.add('open');
}

export function clearPendingMedia() {
    state.pendingMediaBlob = null;
    state.pendingMediaType = null;
    state.pendingMediaName = null;
    document.getElementById('mediaPreviewStrip')?.classList.remove('open');
    const thumb = document.getElementById('mediaPreviewThumb');
    if (thumb) thumb.src = '';
    const fileInput = document.getElementById('mediaFileInput');
    if (fileInput) fileInput.value = '';
}

export function setProgress(fraction) {
    const bar = document.getElementById('uploadProgressBar');
    if (!bar) return;
    bar.style.transform = `scaleX(${fraction})`;
    if (fraction >= 1) setTimeout(() => { bar.style.transform = 'scaleX(0)'; }, 400);
}

export function openMediaModal(url, type, name = '') {
    const modal = document.getElementById('mediaModal');
    const content = document.getElementById('mediaModalContent');
    const caption = document.getElementById('mediaModalCaption');
    const dlBtn = document.getElementById('mediaModalDownload');

    content.innerHTML = '';

    if (type === 'image') {
        const img = document.createElement('img');
        img.src = url;
        img.alt = name || 'Gambar';
        content.appendChild(img);
    } else if (type === 'video') {
        const video = document.createElement('video');
        video.src = url;
        video.controls = true;
        video.autoplay = true;
        content.appendChild(video);
    }

    caption.textContent = name || '';
    dlBtn.href = url;
    dlBtn.download = name || 'media';

    modal.classList.remove('hidden');
    requestAnimationFrame(() => modal.classList.add('open'));

    document.body.style.overflow = 'hidden';
}

export function closeMediaModal() {
    const modal   = document.getElementById('mediaModal');
    const content = document.getElementById('mediaModalContent');

    modal.classList.remove('open');
    content.innerHTML = ''; // hentikan video yang sedang play

    document.body.style.overflow = '';
}
