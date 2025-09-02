import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

console.log('Echo: Starting initialization...');

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
    // Use session-based authentication with cookies (Laravel Sanctum)
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
    },
    // Enable credentials for session cookies
    authEndpoint: '/broadcasting/auth',
    // Use cookies for authentication
    withCredentials: true,
});

// Add basic connection debugging
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Echo: Connected to Soketi!');
});

window.Echo.connector.pusher.connection.bind('error', (error) => {
    console.error('Echo: Connection error:', error);
});

console.log('Echo: Initialization completed');
