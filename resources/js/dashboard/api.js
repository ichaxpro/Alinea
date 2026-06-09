export async function apiCall(method, url, body = null) {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const opts = {
    method,
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrfToken,
    },
  };
  if (body) {
    opts.headers['Content-Type'] = 'application/json';
    opts.body = JSON.stringify(body);
  }
  const resp = await fetch(url, opts);
  if (!resp.ok) {
    const err = await resp.json().catch(() => ({message: resp.statusText}));
    throw new Error(err.message || 'Request failed');
  }
  return resp.json();
}
