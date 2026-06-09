export function getJoinButtonLabel(club) {
    return club.joined ? 'Sudah Bergabung' : 'Bergabung';
}

export function getPrimaryActionLabel(club) {
    if (club.isOwner) return 'Edit Klub';
    if (club.joined) return 'Keluar Klub';
    return 'Bergabung';
}

export function getPrimaryActionKind(club) {
    if (club.isOwner) return 'edit';
    if (club.joined) return 'leave';
    return 'join';
}

export function getMemberRoleLabel(role) {
    switch (role) {
        case 'owner':
            return 'Pemilik';
        case 'admin':
        case 'moderator':
            return 'Admin';
        default:
            return 'Anggota';
    }
}

export function getUserAvatar(user) {
    const seed = user?.username || user?.name;
    return seed ? `https://api.dicebear.com/7.x/thumbs/svg?seed=${encodeURIComponent(seed)}` : null;
}
