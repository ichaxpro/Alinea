let toastTimeout;

export function showToast(msg) {
    let el = document.getElementById('toast-msg');
    if (!el) {
        el = document.createElement('div');
        el.id = 'toast-msg';
        el.className = 'fixed bottom-24 left-1/2 -translate-x-1/2 z-[9999] bg-[#444] text-white text-sm font-medium px-5 py-3 rounded-full transition-all duration-300 opacity-0 translate-y-2';
        document.body.appendChild(el);
    }
    el.textContent = msg;
    requestAnimationFrame(() => {
        el.classList.remove('opacity-0', 'translate-y-2');
        el.classList.add('opacity-100', 'translate-y-0');
    });
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        el.classList.add('opacity-0', 'translate-y-2');
        el.classList.remove('opacity-100', 'translate-y-0');
    }, 2500);
}

window.showToast = showToast;
