import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Import Echo
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make Pusher globally available
window.Pusher = Pusher;

// Initialize Echo only if Pusher is configured
if (typeof window.Pusher !== 'undefined') {
    try {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: import.meta.env.VITE_PUSHER_APP_KEY || 'local',
            cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
            wsHost: import.meta.env.VITE_PUSHER_HOST || window.location.hostname,
            wsPort: import.meta.env.VITE_PUSHER_PORT || 6001,
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
        });
        console.log('✅ Echo initialized successfully');
    } catch (error) {
        console.warn('⚠️ Echo initialization failed:', error);
        window.Echo = null;
    }
} else {
    console.warn('⚠️ Pusher not found, Echo will not be initialized');
    window.Echo = null;
}

// Chat manager will handle the rest
console.log('✅ Bootstrap loaded');
