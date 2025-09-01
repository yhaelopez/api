# 🚨 In-App Notifications Setup Guide

Your notification system is created but not properly configured. Here's what needs to be fixed:

## 🔧 Backend Configuration Issues

### 1. Fix Broadcasting Driver (.env)
**Current:** `BROADCAST_CONNECTION=log`
**Change to:** `BROADCAST_CONNECTION=pusher`

### 2. Complete Pusher Configuration (.env)
Add these missing Pusher environment variables:
```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

**OR** if you want to use Reverb (Laravel's broadcasting server):
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

## 🎯 Frontend Configuration Issues

### 3. Fix Echo Configuration
Your `resources/js/echo.js` is configured for Reverb but your .env has Pusher settings.

**Option A: Use Pusher** (Update echo.js):
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST || `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
        },
    },
});
```

**Option B: Use Reverb** (Add to .env):
```env
BROADCAST_CONNECTION=reverb
VITE_REVERB_APP_KEY=your-app-key
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

### 4. Import Echo in Main App
Update `resources/js/app.ts` to import Echo:
```typescript
import '../css/app.css';
import './echo.js'; // Add this line

import { createInertiaApp } from '@inertiajs/vue3';
// ... rest of your imports
```

### 5. Initialize Broadcast Service
Update `resources/js/app.ts` to initialize the broadcast service:
```typescript
import '../css/app.css';
import './echo.js';
import { broadcastService } from './services/BroadcastService'; // Add this

// ... existing imports ...

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);
        
        // Initialize broadcast service after Echo is ready
        setTimeout(() => {
            if (window.Echo) {
                broadcastService.init(window.Echo);
            }
        }, 100);
        
        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
```

### 6. Add NotificationContainer to Your Layout
Add the NotificationContainer component to your main layout file. Find your main layout (probably in `resources/js/layouts/` or similar) and add:

```vue
<template>
  <div>
    <!-- Your existing layout content -->
    
    <!-- Add this at the end, before closing div -->
    <NotificationContainer />
  </div>
</template>

<script setup>
// Add this import
import NotificationContainer from '@/components/NotificationContainer.vue'

// ... your existing script
</script>
```

### 7. Set Up User Authentication for Broadcasting
Make sure your authentication token is available. In your login success handler, store the user data:
```javascript
// After successful login
localStorage.setItem('auth_token', response.token);
localStorage.setItem('auth_user', JSON.stringify(response.user));

// Or set it globally
window.authUser = response.user;
```

## 🧪 Testing the Setup

### 1. Start Broadcasting Server
If using Reverb:
```bash
./vendor/bin/sail artisan reverb:start
```

If using Pusher, make sure you have valid Pusher credentials.

### 2. Test Notifications
Try creating, updating, or deleting a user. You should see notifications appear in the top-right corner.

### 3. Debug Broadcasting
Check if events are being broadcast:
```bash
# Check Laravel logs
./vendor/bin/sail logs

# Or check broadcast logs specifically
tail -f storage/logs/laravel.log | grep -i broadcast
```

## 🔍 Troubleshooting

### No Notifications Appearing?
1. **Check browser console** for JavaScript errors
2. **Verify Echo connection** in browser dev tools Network tab
3. **Check authentication** - ensure user token is being sent
4. **Verify channel authorization** - check if `user.{id}` channel is authorized

### Broadcasting Not Working?
1. **Check .env configuration** - ensure `BROADCAST_CONNECTION` is not `log`
2. **Verify Pusher/Reverb credentials** are correct
3. **Check if queue is running** - broadcasts might be queued
4. **Test with simple broadcast** to verify setup

### Frontend Not Receiving Events?
1. **Check if Echo is initialized** - `console.log(window.Echo)` in browser
2. **Verify channel name** - should be `user.{userId}`
3. **Check event name** - should be `in_app_notification`
4. **Ensure user ID is available** in frontend

## 🚀 Quick Test
Once configured, test by running this in Laravel tinker:
```php
./vendor/bin/sail artisan tinker

$user = App\Models\User::first();
$service = app(App\Services\InAppNotificationService::class);
Auth::login($user);
$service->success('Test Notification', 'This is a test message');
```

You should see the notification appear in your frontend!

