// Service Worker for Zero-Install AI Studio
// PWA 오프라인 지원 및 캐싱

const CACHE_NAME = 'zero-install-ai-studio-v1';
const urlsToCache = [
  '/',
  '/studio',
  '/pro-shorts',
  '/editor',
  '/gallery',
  '/manifest.json',
  '/icon-192x192.png',
  '/icon-512x512.png'
];

// 설치 이벤트: 캐시 초기화
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[Service Worker] Caching app shell');
        return cache.addAll(urlsToCache);
      })
      .catch((error) => {
        console.error('[Service Worker] Cache failed:', error);
      })
  );
  
  // 새 서비스 워커를 즉시 활성화
  self.skipWaiting();
});

// 활성화 이벤트: 오래된 캐시 제거
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  
  // 즉시 제어권 획득
  return self.clients.claim();
});

// Fetch 이벤트: 네트워크 우선 전략
self.addEventListener('fetch', (event) => {
  // POST 요청은 캐시하지 않음
  if (event.request.method !== 'GET') {
    return;
  }
  
  // API 요청은 항상 네트워크
  if (event.request.url.includes('/api/')) {
    event.respondWith(fetch(event.request));
    return;
  }
  
  // 외부 리소스 (AI 모델 등)는 캐시 우선
  if (event.request.url.includes('huggingface') || 
      event.request.url.includes('replicate') ||
      event.request.url.includes('onnxruntime')) {
    event.respondWith(
      caches.match(event.request)
        .then((cachedResponse) => {
          if (cachedResponse) {
            console.log('[Service Worker] Serving from cache:', event.request.url);
            return cachedResponse;
          }
          
          return fetch(event.request).then((response) => {
            // 성공적인 응답만 캐시
            if (!response || response.status !== 200 || response.type === 'opaque') {
              return response;
            }
            
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
            
            return response;
          });
        })
    );
    return;
  }
  
  // 나머지는 네트워크 우선, 실패 시 캐시
  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // 응답을 캐시에 저장
        const responseToCache = response.clone();
        caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, responseToCache);
        });
        
        return response;
      })
      .catch(() => {
        // 네트워크 실패 시 캐시에서 가져오기
        return caches.match(event.request);
      })
  );
});

// 백그라운드 동기화
self.addEventListener('sync', (event) => {
  console.log('[Service Worker] Background sync:', event.tag);
  
  if (event.tag === 'sync-generations') {
    event.waitUntil(syncGenerations());
  }
});

// 푸시 알림
self.addEventListener('push', (event) => {
  console.log('[Service Worker] Push notification received');
  
  const options = {
    body: event.data ? event.data.text() : '새로운 AI 쇼츠가 준비되었습니다!',
    icon: '/icon-192x192.png',
    badge: '/icon-192x192.png',
    vibrate: [200, 100, 200],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'explore',
        title: '보러가기',
        icon: '/icon-192x192.png'
      },
      {
        action: 'close',
        title: '닫기'
      }
    ]
  };
  
  event.waitUntil(
    self.registration.showNotification('Zero-Install AI Studio', options)
  );
});

// 알림 클릭
self.addEventListener('notificationclick', (event) => {
  console.log('[Service Worker] Notification click:', event.action);
  
  event.notification.close();
  
  if (event.action === 'explore') {
    event.waitUntil(
      clients.openWindow('/gallery')
    );
  }
});

// 메시지 이벤트
self.addEventListener('message', (event) => {
  console.log('[Service Worker] Message received:', event.data);
  
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    event.waitUntil(
      caches.keys().then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => caches.delete(cacheName))
        );
      })
    );
  }
});

// 헬퍼 함수: 생성 동기화
async function syncGenerations() {
  // IndexedDB에서 대기 중인 생성 작업 가져오기
  // 실제 구현은 프로젝트에 따라 다름
  console.log('[Service Worker] Syncing pending generations...');
  
  try {
    // 예시: 대기 중인 작업 처리
    const db = await openDatabase();
    const pending = await getPendingGenerations(db);
    
    for (const generation of pending) {
      await processGeneration(generation);
    }
    
    console.log('[Service Worker] Sync completed');
  } catch (error) {
    console.error('[Service Worker] Sync failed:', error);
  }
}

// IndexedDB 헬퍼 (간소화)
function openDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('AIStudioDB', 1);
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

function getPendingGenerations(db) {
  return new Promise((resolve) => {
    // 실제 구현 필요
    resolve([]);
  });
}

function processGeneration(generation) {
  return new Promise((resolve) => {
    // 실제 구현 필요
    setTimeout(() => resolve(), 1000);
  });
}
