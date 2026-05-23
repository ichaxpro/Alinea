/**
 * Avatar upload with fixed-circle crop.
 * The circle viewport stays still — user drags / zooms the image behind it.
 */
export function initAvatarUpload() {
  const wrapper = document.getElementById('profile-avatar-wrapper');
  if (!wrapper) return;

  let fileInput = null;

  // ─── State ──────────────────────────────────────────────────────
  let imgEl = null;          // the <img> inside the viewport
  let naturalW = 0;
  let naturalH = 0;
  let scale = 1;
  let minScale = 1;
  let posX = 0;
  let posY = 0;

  // Circle crop geometry (matches SVG overlay)
  const VIEWPORT_W = 400;
  const VIEWPORT_H = 320;
  const CIRCLE_R = 130;
  const CIRCLE_CX = VIEWPORT_W / 2;
  const CIRCLE_CY = VIEWPORT_H / 2;

  // Drag state
  let dragging = false;
  let dragStartX = 0;
  let dragStartY = 0;
  let dragStartPosX = 0;
  let dragStartPosY = 0;

  // ─── Helpers ────────────────────────────────────────────────────
  function createFileInput() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/jpeg,image/png,image/webp,image/gif';
    input.style.display = 'none';
    document.body.appendChild(input);
    return input;
  }

  function openFilePicker() {
    if (!fileInput) fileInput = createFileInput();
    fileInput.value = '';
    fileInput.click();
  }

  /** Clamp position so the circle area always covers actual image pixels */
  function clampPosition() {
    const viewport = document.getElementById('avatar-crop-viewport');
    if (!viewport || !imgEl) return;

    const vpRect = viewport.getBoundingClientRect();
    const displayW = naturalW * scale;
    const displayH = naturalH * scale;

    // Convert circle center from SVG coords to pixel coords
    const circlePxCX = (CIRCLE_CX / VIEWPORT_W) * vpRect.width;
    const circlePxCY = (CIRCLE_CY / VIEWPORT_H) * vpRect.height;
    const circlePxR  = (CIRCLE_R  / VIEWPORT_W) * vpRect.width;

    // Image must cover the circle: the circle edges must stay within the image bounds
    const maxX = circlePxCX - circlePxR;             // leftmost image-left can go
    const minX = circlePxCX + circlePxR - displayW;  // rightmost image-left can go
    const maxY = circlePxCY - circlePxR;
    const minY = circlePxCY + circlePxR - displayH;

    posX = Math.min(maxX, Math.max(minX, posX));
    posY = Math.min(maxY, Math.max(minY, posY));
  }

  function applyTransform() {
    if (!imgEl) return;
    imgEl.style.transform = `translate(${posX}px, ${posY}px) scale(${scale})`;
  }

  function centerImage(vpEl) {
    const vpRect = vpEl.getBoundingClientRect();
    const displayW = naturalW * minScale;
    const displayH = naturalH * minScale;

    posX = (vpRect.width  - displayW) / 2;
    posY = (vpRect.height - displayH) / 2;
    scale = minScale;
  }

  // ─── Show / Hide modal ─────────────────────────────────────────
  function showCropModal(file) {
    const viewport = document.getElementById('avatar-crop-viewport');
    if (!viewport) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      const image = new Image();
      image.onload = () => {
        naturalW = image.naturalWidth;
        naturalH = image.naturalHeight;

        const modal = document.getElementById('avatar-crop-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';

        // Set img source
        imgEl = document.getElementById('avatar-crop-image');
        imgEl.src = e.target.result;

        // Wait a frame so the viewport is laid out
        requestAnimationFrame(() => {
          const vpRect = viewport.getBoundingClientRect();
          const circlePxR = (CIRCLE_R / VIEWPORT_W) * vpRect.width;
          const circleDiam = circlePxR * 2;

          // Compute minimum scale so image covers the circle
          minScale = Math.max(circleDiam / naturalW, circleDiam / naturalH);
          scale = minScale;

          // Reset zoom slider
          const slider = document.getElementById('avatar-crop-zoom');
          if (slider) {
            slider.min = Math.round(minScale * 100);
            slider.max = Math.round(minScale * 100 * 4);
            slider.value = Math.round(minScale * 100);
          }

          centerImage(viewport);
          applyTransform();

          // Setup crosshair sizes
          setupCrosshair(vpRect);
        });
      };
      image.src = e.target.result;
    };
    reader.readAsDataURL(file);
  }

  function setupCrosshair(vpRect) {
    const circlePxR = (CIRCLE_R / VIEWPORT_W) * vpRect.width;
    const circlePxCX = (CIRCLE_CX / VIEWPORT_W) * vpRect.width;
    const circlePxCY = (CIRCLE_CY / VIEWPORT_H) * vpRect.height;

    const chH = document.getElementById('crop-ch-h');
    const chV = document.getElementById('crop-ch-v');
    if (chH) {
      chH.style.width = (circlePxR * 2) + 'px';
      chH.style.left = (circlePxCX - circlePxR) + 'px';
      chH.style.top = circlePxCY + 'px';
      chH.style.transform = 'translateY(-0.5px)';
    }
    if (chV) {
      chV.style.height = (circlePxR * 2) + 'px';
      chV.style.left = circlePxCX + 'px';
      chV.style.top = (circlePxCY - circlePxR) + 'px';
      chV.style.transform = 'translateX(-0.5px)';
    }
  }

  function closeCropModal() {
    const modal = document.getElementById('avatar-crop-modal');
    if (modal) {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }
    document.body.style.overflow = '';
    imgEl = null;
  }

  // ─── Drag handlers ─────────────────────────────────────────────
  function onPointerDown(e) {
    if (e.target.closest('input[type="range"]')) return;
    dragging = true;
    dragStartX = e.clientX;
    dragStartY = e.clientY;
    dragStartPosX = posX;
    dragStartPosY = posY;
    e.preventDefault();
  }

  function onPointerMove(e) {
    if (!dragging) return;
    posX = dragStartPosX + (e.clientX - dragStartX);
    posY = dragStartPosY + (e.clientY - dragStartY);
    clampPosition();
    applyTransform();
  }

  function onPointerUp() {
    dragging = false;
  }

  // ─── Wheel zoom ────────────────────────────────────────────────
  function onWheel(e) {
    e.preventDefault();
    const viewport = document.getElementById('avatar-crop-viewport');
    if (!viewport) return;

    const vpRect = viewport.getBoundingClientRect();
    const maxScale = minScale * 4;

    // Zoom towards cursor position
    const mouseX = e.clientX - vpRect.left;
    const mouseY = e.clientY - vpRect.top;

    const oldScale = scale;
    const delta = e.deltaY > 0 ? -0.05 : 0.05;
    scale = Math.min(maxScale, Math.max(minScale, scale + delta * scale));

    // Adjust position so the point under cursor stays put
    posX = mouseX - (mouseX - posX) * (scale / oldScale);
    posY = mouseY - (mouseY - posY) * (scale / oldScale);

    clampPosition();
    applyTransform();

    // Sync slider
    const slider = document.getElementById('avatar-crop-zoom');
    if (slider) slider.value = Math.round(scale * 100);
  }

  // ─── Touch pinch zoom ──────────────────────────────────────────
  let lastPinchDist = 0;
  function getPinchDist(touches) {
    const dx = touches[0].clientX - touches[1].clientX;
    const dy = touches[0].clientY - touches[1].clientY;
    return Math.sqrt(dx * dx + dy * dy);
  }

  function onTouchStart(e) {
    if (e.touches.length === 2) {
      lastPinchDist = getPinchDist(e.touches);
      e.preventDefault();
    }
  }

  function onTouchMove(e) {
    if (e.touches.length === 2) {
      e.preventDefault();
      const dist = getPinchDist(e.touches);
      const maxScale = minScale * 4;
      const factor = dist / lastPinchDist;
      const oldScale = scale;
      scale = Math.min(maxScale, Math.max(minScale, scale * factor));

      // Zoom towards center of the two touches
      const viewport = document.getElementById('avatar-crop-viewport');
      if (viewport) {
        const vpRect = viewport.getBoundingClientRect();
        const cx = ((e.touches[0].clientX + e.touches[1].clientX) / 2) - vpRect.left;
        const cy = ((e.touches[0].clientY + e.touches[1].clientY) / 2) - vpRect.top;
        posX = cx - (cx - posX) * (scale / oldScale);
        posY = cy - (cy - posY) * (scale / oldScale);
      }

      clampPosition();
      applyTransform();
      lastPinchDist = dist;

      const slider = document.getElementById('avatar-crop-zoom');
      if (slider) slider.value = Math.round(scale * 100);
    }
  }

  // ─── Crop & Upload ─────────────────────────────────────────────
  async function cropAndUpload() {
    if (!imgEl) return;

    const viewport = document.getElementById('avatar-crop-viewport');
    if (!viewport) return;
    const vpRect = viewport.getBoundingClientRect();

    // Circle in pixel coords
    const circlePxCX = (CIRCLE_CX / VIEWPORT_W) * vpRect.width;
    const circlePxCY = (CIRCLE_CY / VIEWPORT_H) * vpRect.height;
    const circlePxR  = (CIRCLE_R  / VIEWPORT_W) * vpRect.width;

    // The circle top-left corner relative to the viewport
    const circleLeft = circlePxCX - circlePxR;
    const circleTop  = circlePxCY - circlePxR;
    const circleDiam = circlePxR * 2;

    // Convert to source image coordinates
    const srcX = (circleLeft - posX) / scale;
    const srcY = (circleTop  - posY) / scale;
    const srcW = circleDiam / scale;
    const srcH = circleDiam / scale;

    // Draw to canvas
    const outputSize = 512;
    const canvas = document.createElement('canvas');
    canvas.width  = outputSize;
    canvas.height = outputSize;
    const ctx = canvas.getContext('2d');

    // Clip to circle
    ctx.beginPath();
    ctx.arc(outputSize / 2, outputSize / 2, outputSize / 2, 0, Math.PI * 2);
    ctx.closePath();
    ctx.clip();

    ctx.drawImage(imgEl, srcX, srcY, srcW, srcH, 0, 0, outputSize, outputSize);

    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png', 1));
    return blob;
  }

  async function uploadAvatar(blob) {
    const formData = new FormData();
    formData.append('avatar', blob, 'avatar.png');

    const resp = await fetch('/upload-avatar', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      body: formData,
    });

    if (!resp.ok) {
      const err = await resp.json().catch(() => ({message: 'Upload gagal'}));
      throw new Error(err.message || 'Upload gagal');
    }

    return resp.json();
  }

  function updateAvatarUI(avatarUrl) {
    const img = document.getElementById('profile-avatar-img');
    const initial = document.getElementById('profile-initial');
    if (img) {
      img.src = avatarUrl;
      img.classList.remove('hidden');
    }
    if (initial) initial.classList.add('hidden');
    if (window.CURRENT_USER) {
      window.CURRENT_USER.foto_profil = avatarUrl;
    }
    const navImg = document.getElementById('navbar-avatar-img');
    const navInitial = document.getElementById('navbar-avatar-initial');
    if (navImg) {
      navImg.src = avatarUrl;
      navImg.classList.remove('hidden');
    }
    if (navInitial) navInitial.classList.add('hidden');
  }

  // ─── Bind events ───────────────────────────────────────────────
  wrapper.addEventListener('click', openFilePicker);

  document.addEventListener('change', (e) => {
    if (e.target === fileInput && e.target.files && e.target.files[0]) {
      showCropModal(e.target.files[0]);
    }
  });

  // Viewport drag
  const vp = document.getElementById('avatar-crop-viewport');
  if (vp) {
    vp.addEventListener('pointerdown', onPointerDown);
    window.addEventListener('pointermove', onPointerMove);
    window.addEventListener('pointerup', onPointerUp);
    vp.addEventListener('wheel', onWheel, { passive: false });
    vp.addEventListener('touchstart', onTouchStart, { passive: false });
    vp.addEventListener('touchmove', onTouchMove, { passive: false });
  }

  // Zoom slider
  document.getElementById('avatar-crop-zoom')?.addEventListener('input', (e) => {
    const viewport = document.getElementById('avatar-crop-viewport');
    if (!viewport) return;
    const vpRect = viewport.getBoundingClientRect();

    const oldScale = scale;
    scale = parseInt(e.target.value, 10) / 100;

    // Zoom towards the center of the viewport
    const cx = vpRect.width / 2;
    const cy = vpRect.height / 2;
    posX = cx - (cx - posX) * (scale / oldScale);
    posY = cy - (cy - posY) * (scale / oldScale);

    clampPosition();
    applyTransform();
  });

  // Save
  document.getElementById('avatar-crop-save')?.addEventListener('click', async () => {
    const saveBtn = document.getElementById('avatar-crop-save');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';

    try {
      const blob = await cropAndUpload();
      if (!blob) throw new Error('Gagal memproses foto');
      const result = await uploadAvatar(blob);
      updateAvatarUI(result.avatar_url);
      closeCropModal();
      const toastFn = window.showToast || ((msg) => alert(msg));
      toastFn('Foto profil berhasil diperbarui!');
    } catch (err) {
      const toastFn = window.showToast || ((msg) => alert(msg));
      toastFn(err.message || 'Gagal mengupload foto profil');
    } finally {
      saveBtn.disabled = false;
      saveBtn.textContent = 'Simpan';
    }
  });

  // Cancel
  const cancelBtns = ['avatar-crop-cancel', 'avatar-crop-cancel-btn'];
  cancelBtns.forEach(id => {
    document.getElementById(id)?.addEventListener('click', closeCropModal);
  });

  // Backdrop click
  document.getElementById('avatar-crop-modal')?.addEventListener('click', (e) => {
    if (e.target.id === 'avatar-crop-modal') closeCropModal();
  });
}
