/**
 * AMRAJ service worker
 * - Caches static assets (CSS, icons) for instant repeat loads.
 * - Network-first for PHP pages, falling back to a cached copy or an
 *   offline page when there's no connection.
 * - Never caches admin/owner/user dashboard or login pages, since they
 *   contain session-specific and sensitive data.
 */

const CACHE_VERSION = 'amraj-cache-v2';
const OFFLINE_URL = 'offline.html';

const STATIC_ASSETS = [
    'assets/css/style.css',
    'assets/icons/icon-192.png',
    'assets/icons/icon-512.png',
    OFFLINE_URL,
];

const NEVER_CACHE_PATTERNS = [
    '/admin/', '/owner/', '/user/', '/login.php', '/register.php', '/backend/',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION).then((cache) => cache.addAll(STATIC_ASSETS)).catch(() => {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

function isSensitivePath(url) {
    return NEVER_CACHE_PATTERNS.some((pattern) => url.pathname.includes(pattern));
}

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    if (event.request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    if (isSensitivePath(url)) {
        // Always go to the network for logged-in/authenticated areas.
        event.respondWith(fetch(event.request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                const copy = response.clone();
                caches.open(CACHE_VERSION).then((cache) => cache.put(event.request, copy)).catch(() => {});
                return response;
            })
            .catch(() =>
                caches.match(event.request).then((cached) => cached || caches.match(OFFLINE_URL))
            )
    );
});
