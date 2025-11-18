function main() {
    const permission = document.getElementById('push-permission');
    // If there's no container or the browser doesn't support required APIs, don't proceed
    if (!permission || !('Notification' in window) || !('serviceWorker' in navigator)) {
        return;
    }
    console.debug('[push] init - container found, Notification API and ServiceWorker supported');
    // Show popup only for authenticated users (meta set in layout)
    const currentUserMeta = document.querySelector('meta[name="current-user-id"]');
    if (!currentUserMeta) {
        // Not logged in — do not prompt for push here
        return;
    }

    // If the user has explicitly denied notifications, show instructions
    if (Notification.permission === 'denied') {
        showPermissionDeniedUI(permission);
        return;
    }

    // If permission is default (not yet asked), show a dynamic popup/button
    if (Notification.permission === 'default') {
        // Respect a user 'snooze' (don't show again for a while)
        const snoozeKey = 'push_permission_snooze_until';
        const snoozeUntil = localStorage.getItem(snoozeKey);
        if (snoozeUntil && Date.now() < Number(snoozeUntil)) {
            return; // user asked to be reminded later
        }

        showPermissionPopup(permission, snoozeKey);
        return;
    }

    // If permission already granted, ensure subscription exists
    if (Notification.permission === 'granted') {
        ensureSubscribed();
    }
}

function showPermissionDeniedUI(container) {
    console.debug('[push] permission denied - showing instructions');
    const wrap = document.createElement('div');
    wrap.className = 'alert alert-warning p-2 m-0';
    wrap.style.maxWidth = '320px';
    wrap.innerHTML = `
        <strong>Notifications bloquées</strong>
        <div style="font-size:0.9rem;margin-top:6px">Vous avez refusé les notifications pour ce site. Pour les réactiver :</div>
        <ol style="font-size:0.85rem;margin:6px 0 0 18px;padding:0">
            <li>Ouvrez les paramètres de votre navigateur pour ce site.</li>
            <li>Allez dans <em>Notifications</em> et choisissez <strong>Autoriser</strong>.</li>
            <li>Rechargez la page et cliquez sur <em>Activer les notifications</em>.</li>
        </ol>
        <div style="margin-top:8px;display:flex;gap:6px">
            <a href="javascript:void(0)" id="push-open-settings" class="btn btn-sm btn-outline-secondary">Voir comment</a>
        </div>
    `;

    container.appendChild(wrap);

    // Provide more specific help when possible
    const help = wrap.querySelector('#push-open-settings');
    help.addEventListener('click', function () {
        // Open browser settings pages when possible (works in Chrome/Edge)
        try {
            // Try Chrome settings page
            window.open('chrome://settings/content/notifications');
        } catch (e) {
            // Fallback: show an alert with manual steps
            alert('Ouvrez les paramètres du site (icône cadenas à gauche de l\'URL) → Paramètres du site → Notifications → Autoriser.');
        }
    });
}

function showPermissionPopup(container, snoozeKey) {
    const card = document.createElement('div');
    card.className = 'card p-2';
    card.style.maxWidth = '360px';
    card.innerHTML = `
        <div class="d-flex align-items-start">
            <div style="flex:1">
                <strong>Activer les notifications</strong>
                <div style="font-size:0.9rem;margin-top:6px">Recevez une alerte quand vous avez une nouvelle commande ou quand le stock est faible.</div>
                <div style="margin-top:8px;display:flex;gap:6px">
                    <button id="push-allow" class="btn btn-sm btn-primary">Autoriser</button>
                    <button id="push-later" class="btn btn-sm btn-outline-secondary">Plus tard</button>
                </div>
            </div>
        </div>
    `;

    container.appendChild(card);

    const allow = card.querySelector('#push-allow');
    const later = card.querySelector('#push-later');

    allow.addEventListener('click', async function () {
        try {
            // Quick checks that commonly prevent Chrome from showing the prompt
            if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                card.innerHTML = '<div class="p-2 text-warning">Les notifications nécessitent un contexte sécurisé (HTTPS) ou localhost.</div>';
                console.warn('[push] insecure context - permission prompt blocked by browser');
                return;
            }

            if (window.self !== window.top) {
                card.innerHTML = '<div class="p-2 text-warning">La demande d\'autorisation ne peut pas être affichée depuis un iframe. Ouvrez la page dans un onglet principal.</div>';
                console.warn('[push] in iframe - permission prompt unavailable');
                return;
            }

            // Optional: inspect Permissions API state before asking
            if (navigator.permissions && navigator.permissions.query) {
                try {
                    navigator.permissions.query({ name: 'notifications' }).then(p => console.debug('[push] permissions state before request:', p.state)).catch(() => {});
                } catch (e) {
                    // ignore
                }
            }

            console.debug('[push] calling Notification.requestPermission()');
            const permission = await Notification.requestPermission();
            console.debug('[push] requestPermission result:', permission);

            // Optional: inspect Permissions API state after asking
            if (navigator.permissions && navigator.permissions.query) {
                try {
                    navigator.permissions.query({ name: 'notifications' }).then(p => console.debug('[push] permissions state after request:', p.state)).catch(() => {});
                } catch (e) {
                    // ignore
                }
            }

            if (permission === 'granted') {
                // Replace UI with success message
                card.innerHTML = '<div class="p-2 text-success">Notifications activées ✅</div>';
                await registerServiceWorkerAndSubscribe();
            } else if (permission === 'denied') {
                card.innerHTML = '<div class="p-2 text-warning">Notifications refusées. Vous pouvez les réactiver depuis les paramètres du navigateur.</div>';
            } else {
                // default - user dismissed
                card.innerHTML = '<div class="p-2">Nous vous redemanderons plus tard.</div>';
            }
        } catch (e) {
            console.error('Erreur lors de la demande de permission', e);
            // Show a friendly message plus the error string to help debug in prod
            const safe = (e && e.message) ? String(e.message) : String(e);
            card.innerHTML = `<div class="p-2 text-danger">Erreur lors de l'activation des notifications.<br><small style="word-break:break-word;">${safe}</small></div>`;
        }
    });

    later.addEventListener('click', function () {
        // snooze for 24 hours
        const until = Date.now() + 24 * 60 * 60 * 1000;
        localStorage.setItem(snoozeKey, String(until));
        card.remove();
    });
}

async function ensureSubscribed() {
    try {
        const { registration, subscription } = await registerServiceWorker();
        if (!subscription) {
            await registerServiceWorkerAndSubscribe();
        } else {
            // send existing subscription to server to ensure it's recorded
            await sendSubscriptionToServer(subscription);
        }
    } catch (e) {
        console.error('Erreur lors de l\'enregistrement/service worker:', e);
    }
}

async function askPermission() {
    const permission = await Notification.requestPermission();
    console.log('Permission de notification:', permission);
    if (permission === 'granted') {
        try {
            await registerServiceWorkerAndSubscribe();
            console.log('Service worker registered and subscription sent');
        } catch (err) {
            console.error('Erreur lors de l e2  registration/subscription:', err);
        }
    } else {
        console.warn('Permission not granted for notifications');
    }
}

async function registerServiceWorker() {
    const registration = await navigator.serviceWorker.register('/sw.js');
    const subscription = await registration.pushManager.getSubscription();
    return { registration, subscription };
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

async function registerServiceWorkerAndSubscribe() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        throw new Error('Service Worker or Push not supported');
    }

    const { registration, subscription } = await registerServiceWorker();
    if (subscription) {
        // already subscribed -> send to server
        await sendSubscriptionToServer(subscription);
        return subscription;
    }

    // get VAPID key from meta tag
    const vapidMeta = document.querySelector('meta[name="webpush-public-key"]');
    if (!vapidMeta) throw new Error('VAPID public key meta tag not found');
    let publicKey = vapidMeta.getAttribute('content') || '';
    // client-side sanitize: remove whitespace/newlines that can be introduced by env or templates
    publicKey = publicKey.replace(/\s+/g, '');
    console.debug('[push] vapid meta content (raw):', publicKey);
    const convertedKey = urlBase64ToUint8Array(publicKey);
    console.debug('[push] convertedKey byteLength:', convertedKey && convertedKey.byteLength, 'expected 65');

    const newSub = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: convertedKey
    });

    await sendSubscriptionToServer(newSub);
    return newSub;
}

async function sendSubscriptionToServer(subscription) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const resp = await fetch('/push/subscribe', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: JSON.stringify(subscription)
    });

    if (!resp.ok) {
        const text = await resp.text();
        throw new Error(`Subscription POST failed: ${resp.status} ${text}`);
    }
    return resp.json();
}

main();