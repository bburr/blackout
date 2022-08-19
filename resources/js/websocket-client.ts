import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export class EchoClient {
    private static instance: Echo;

    public static getInstance(): Echo {
        if (! EchoClient.instance) {
            const options = {
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_CLUSTER,
                wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
                wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
                forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
                enabledTransports: ['ws', 'wss'],
            };

            EchoClient.instance = new Echo({
                ...options,
                client: new Pusher(options.key, options),
            });
        }

        return EchoClient.instance;
    }
}
