import { state } from './state.js';

export function apiHeaders(extra = {}) {
    return {
        'Accept':       'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': state.csrfToken,
        'X-Socket-ID':  window.Echo?.socketId() || '',
        ...extra,
    };
}

export function apiHeadersMultipart() {
    return {
        'Accept':       'application/json',
        'X-CSRF-TOKEN': state.csrfToken,
        'X-Socket-ID':  window.Echo?.socketId() || '',
    };
}
