self.addEventListener('push', function(event) {
  // Debug: log that we received a push event (visible in SW console)
  try {
    console.log('[sw] push received', event);
  } catch (e) {
    // some browsers may not allow console in SW, ignore
  }

  let payload = {};
  if (event.data) {
    try {
      payload = event.data.json();
    } catch (e) {
      try {
        payload = { title: 'Notification', body: event.data.text() };
      } catch (ee) {
        payload = { title: 'Notification', body: '' };
      }
    }
  }

  const title = payload.title || 'Notification';
  const options = {
    body: payload.body || '',
    icon: payload.icon || '/images/default.png',
    badge: payload.badge || '/images/default.png',
    // Ensure top-level url is preserved on data so notificationclick can use it
    data: Object.assign({}, payload.data || {}, { url: payload.url || (payload.data && payload.data.url) || '/' })
  };

  // Ensure we always show a notification (useful for debugging)
  event.waitUntil(
    (async () => {
      try {
        await self.registration.showNotification(title, options);
        try { console.log('[sw] showNotification called', title, options); } catch (_) {}
      } catch (err) {
        try { console.error('[sw] showNotification error', err); } catch (_) {}
        // Nothing more to do
      }
    })()
  );
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  const url = event.notification.data && event.notification.data.url ? event.notification.data.url : '/';

  // Normalize a URL for comparison (origin + pathname)
  function normalize(u) {
    try {
      const parsed = new URL(u, self.location.origin);
      return parsed.origin + parsed.pathname.replace(/\/$/, '');
    } catch (e) {
      return u;
    }
  }

  const targetNormalized = normalize(url);

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
      // Try to focus or navigate an existing matching client
      for (const client of clientList) {
        try {
          const clientNormalized = normalize(client.url);
          if (clientNormalized === targetNormalized) {
            if ('focus' in client) {
              client.focus();
              // Try to navigate the client to the full URL (keeps query/hash)
              if (client.url !== url && 'navigate' in client) {
                return client.navigate(url);
              }
              return;
            }
          }
        } catch (e) {
          // ignore parsing errors and continue
        }
      }

      // Otherwise open a new window/tab to the url
      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});
