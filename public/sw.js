// Service worker Slipin Coffee (v1)
// - Syarat agar Chrome/Edge menampilkan tombol "Install app"
// - Halaman TIDAK di-cache (data selalu fresh dari server); kalau offline tampil offline.html
// - Aset statis (ikon) di-cache
const VERSION = 'slipin-v2';
const PRECACHE = ['/offline.html', '/icons/icon-192.png', '/icons/icon-512.png', '/icons/logo-mark.png', '/icons/logo-full.png'];

self.addEventListener('install', (e) => {
    e.waitUntil(caches.open(VERSION).then((c) => c.addAll(PRECACHE)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== VERSION).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (e) => {
    const req = e.request;
    if (req.method !== 'GET') return;

    if (req.mode === 'navigate') {
        e.respondWith(fetch(req).catch(() => caches.match('/offline.html')));
        return;
    }

    const url = new URL(req.url);
    if (url.origin === location.origin && url.pathname.startsWith('/icons/')) {
        e.respondWith(caches.match(req).then((hit) => hit || fetch(req)));
    }
});
