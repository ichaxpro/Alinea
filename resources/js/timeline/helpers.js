export function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

export function formatCount(n) {
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M';
    if (n >= 1_000)     return (n / 1_000).toFixed(0) + 'K';
    return String(n);
}

export function renderAvatarHtml(data) {
    const initial = escapeHtml(String(data?.name || 'U').charAt(0).toUpperCase() || 'U');
    if (data?.avatar_url) {
        return '<img src="' + data.avatar_url + '" alt="Avatar ' + escapeHtml(data?.name || 'Pengguna') + '" class="w-full h-full object-cover" />';
    }

    return '<span class="text-xs font-bold text-[#444]">' + initial + '</span>';
}
