self.addEventListener('install', (event) => {
    console.log('Service Worker: Installed');
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activated');
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function(event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    let data = {};
    try {
        if (event.data) {
            data = event.data.json();
        }
    } catch (e) {
        console.error('Error parsing push data', e);
    }

    const title = data.title || "Loker Baru Tersedia!";
    const options = {
        body: data.body || "Ada informasi lowongan terbaru sesuai kriteria Anda.",
        icon: data.icon || '/favicon.svg',
        badge: data.badge || '/favicon.svg',
        data: {
            url: data.action_url || '/'
        }
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    
    event.waitUntil(clients.matchAll({
        type: "window"
    }).then(function(clientList) {
        for (let i = 0; i < clientList.length; i++) {
            let client = clientList[i];
            if (client.url === event.notification.data.url && 'focus' in client) {
                return client.focus();
            }
        }
        if (clients.openWindow) {
            return clients.openWindow(event.notification.data.url);
        }
    }));
});
