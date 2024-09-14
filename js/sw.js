var CACHE_NAME = 'training-v1';

self.addEventListener('install', function (event) {
	console.log(event);
	var offlineRequest = new Request('sites/offline.html');
	event.waitUntil(
		fetch(offlineRequest).then(function (response) {
			return caches.open(CACHE_NAME).then(function (cache) {
				console.log('[oninstall] Cached offline page', response.url);
				return cache.put(offlineRequest, response);
			});
		})
	);
});

self.addEventListener('activate', function (event) {
	console.log(event);
});

self.addEventListener('fetch', function (event) {
	console.log(event);
	var request = event.request;
	if (request.method === 'GET') {
		event.respondWith(
			fetch(request).catch(function (error) {
				console.warn('[offline] load fallback page');
				return caches.open(CACHE_NAME).then(function (cache) {
					return cache.match('sites/offline.html');
				});
			})
		);
	}
});