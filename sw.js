/**
 * SimpleMenti Service Worker
 * Proporciona funcionalidad offline y cacheo inteligente
 */

const CACHE_VERSION = 'simplementi-v1.0.0';
const CACHE_STATIC = `${CACHE_VERSION}-static`;
const CACHE_DYNAMIC = `${CACHE_VERSION}-dynamic`;
const CACHE_API = `${CACHE_VERSION}-api`;

// Archivos estáticos a cachear en instalación
const STATIC_ASSETS = [
    '/',
    '/control-movil.php',
    '/css/control-movil.css',
    '/manifest.json',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
];

// Instalación del Service Worker
self.addEventListener('install', event => {
    console.log('[SW] Instalando Service Worker...');

    event.waitUntil(
        caches.open(CACHE_STATIC)
            .then(cache => {
                console.log('[SW] Pre-cacheando archivos estáticos');
                return cache.addAll(STATIC_ASSETS);
            })
            .catch(err => {
                console.error('[SW] Error al cachear archivos estáticos:', err);
            })
    );

    // Activar inmediatamente sin esperar
    self.skipWaiting();
});

// Activación del Service Worker
self.addEventListener('activate', event => {
    console.log('[SW] Activando Service Worker...');

    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    // Eliminar cachés antiguas
                    if (cacheName.startsWith('simplementi-') &&
                        cacheName !== CACHE_STATIC &&
                        cacheName !== CACHE_DYNAMIC &&
                        cacheName !== CACHE_API) {
                        console.log('[SW] Eliminando caché antigua:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );

    // Tomar control inmediatamente
    return self.clients.claim();
});

// Interceptar peticiones
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Solo cachear peticiones GET
    if (request.method !== 'GET') {
        return;
    }

    // Estrategia de cacheo según tipo de recurso
    if (isStaticAsset(url)) {
        // Estáticos: Cache First (rápido, offline-ready)
        event.respondWith(cacheFirst(request, CACHE_STATIC));
    } else if (isApiRequest(url)) {
        // APIs: Network First con fallback a cache (datos frescos cuando hay conexión)
        event.respondWith(networkFirst(request, CACHE_API));
    } else {
        // Dinámicos: Network First con cache de corta duración
        event.respondWith(networkFirst(request, CACHE_DYNAMIC));
    }
});

// Determinar si es un recurso estático
function isStaticAsset(url) {
    const staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.svg', '.woff', '.woff2', '.ttf'];
    const isCDN = url.hostname.includes('cdn.jsdelivr') || url.hostname.includes('cdnjs.cloudflare');
    const hasStaticExt = staticExtensions.some(ext => url.pathname.endsWith(ext));

    return isCDN || hasStaticExt;
}

// Determinar si es una petición API
function isApiRequest(url) {
    return url.pathname.includes('/api/');
}

// Estrategia Cache First
async function cacheFirst(request, cacheName) {
    const cache = await caches.open(cacheName);
    const cached = await cache.match(request);

    if (cached) {
        // Actualizar cache en segundo plano (stale-while-revalidate)
        fetch(request).then(response => {
            if (response && response.status === 200) {
                cache.put(request, response.clone());
            }
        }).catch(() => {
            // Ignorar errores de red en background
        });

        return cached;
    }

    try {
        const response = await fetch(request);
        if (response && response.status === 200) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('[SW] Error en cache-first:', error);
        throw error;
    }
}

// Estrategia Network First
async function networkFirst(request, cacheName) {
    const cache = await caches.open(cacheName);

    try {
        const response = await fetch(request);

        // Solo cachear respuestas exitosas
        if (response && response.status === 200) {
            // Para APIs, usar TTL corto (no cachear indefinidamente)
            if (cacheName === CACHE_API) {
                // Agregar header personalizado con timestamp
                const clonedResponse = response.clone();
                cache.put(request, clonedResponse);

                // Limpiar cache antiguo después de 5 minutos
                setTimeout(() => {
                    cache.delete(request);
                }, 5 * 60 * 1000);
            } else {
                cache.put(request, response.clone());
            }
        }

        return response;
    } catch (error) {
        // Si falla la red, intentar con cache
        console.log('[SW] Red no disponible, usando cache:', request.url);
        const cached = await cache.match(request);

        if (cached) {
            return cached;
        }

        // Si no hay cache, devolver error offline personalizado
        if (request.headers.get('accept').includes('text/html')) {
            return new Response(
                '<html><body><h1>Sin conexión</h1><p>No se puede cargar esta página sin conexión a Internet.</p></body></html>',
                { headers: { 'Content-Type': 'text/html' } }
            );
        }

        throw error;
    }
}

// Manejo de mensajes desde el cliente
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => caches.delete(cacheName))
                );
            })
        );
    }
});

// Sincronización en segundo plano (para peticiones fallidas)
self.addEventListener('sync', event => {
    if (event.tag === 'sync-navigation') {
        event.waitUntil(syncNavigationQueue());
    }
});

// Cola de sincronización de navegación
async function syncNavigationQueue() {
    // Aquí se pueden implementar lógicas de sincronización
    // Por ejemplo, reintentar comandos de navegación fallidos
    console.log('[SW] Sincronizando cola de navegación...');
}
