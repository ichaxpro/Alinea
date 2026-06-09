export const state = {
    CURRENT_USER: window.__CURRENT_USER__ || null,
    CSRF_TOKEN: document.querySelector('meta[name="csrf-token"]')?.content || '',
    CLUBS: [],
    PER_PAGE: 12,
    currentPage: 1
};

export function mapClub(c) {
    return {
        id:              c.id,
        name:            c.name,
        category:        c.category,
        members:         c.members,
        founded:         c.founded,
        description:     c.description,
        fullDescription: c.full_description,
        admin:           c.admin,
        adminAvatar:     c.admin_avatar,
        membersList:     c.members_list,
        recentBooks:     c.recent_books,
        schedule:        c.schedule,
        gradientFrom:    c.gradient_from,
        gradientTo:      c.gradient_to,
        coverUrl:        c.foto_klub || c.cover_url || null,
        adminUsername:   c.admin_username || null,
        membersData:     c.members_data || [],
        joined:          Boolean(c.joined),
        ownerId:         c.owner?.id ?? c.owner_id ?? null,
        isOwner:         Boolean(c.owner && state.CURRENT_USER && Number(c.owner.id) === Number(state.CURRENT_USER.id)),
    };
}

export function syncClubFromResponse(data) {
    const updatedClub = mapClub(data);
    const index = state.CLUBS.findIndex(club => club.id === updatedClub.id);

    if (index >= 0) {
        state.CLUBS[index] = updatedClub;
    } else {
        state.CLUBS.unshift(updatedClub);
    }

    return updatedClub;
}

export function initData() {
    state.CLUBS = (window.__KLUB_DATA__ || []).map(mapClub);
}
