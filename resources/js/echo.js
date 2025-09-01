import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? 'localhost',
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 6001,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 6001,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'http') === 'https',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
        },
    },
});
