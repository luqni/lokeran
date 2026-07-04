<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#dc2626">
        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="/favicon.svg">

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @auth
                @if(!request()->routeIs('home'))
                    <livewire:layout.navigation />
                @endif
            @endauth

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js').then(function(registration) {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    }, function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
                });
            }

            function subscribeToPushNotifications() {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    alert('Push notifications are not supported by your browser.');
                    return;
                }

                navigator.serviceWorker.ready.then(function(registration) {
                    const vapidPublicKey = "{{ config('webpush.vapid.public_key') }}";
                    if (!vapidPublicKey) {
                        console.error('VAPID_PUBLIC_KEY is missing');
                        return;
                    }

                    const urlBase64ToUint8Array = (base64String) => {
                        const padding = '='.repeat((4 - base64String.length % 4) % 4);
                        const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
                        const rawData = window.atob(base64);
                        const outputArray = new Uint8Array(rawData.length);
                        for (let i = 0; i < rawData.length; ++i) {
                            outputArray[i] = rawData.charCodeAt(i);
                        }
                        return outputArray;
                    };

                    registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
                    }).then(function(subscription) {
                        fetch('/push-subscribe', {
                            method: 'POST',
                            body: JSON.stringify(subscription),
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        }).then(response => {
                            if (response.ok) alert('Notifikasi berhasil diaktifkan!');
                        });
                    }).catch(function(err) {
                        if (err.name === 'InvalidStateError' || (err.message && err.message.includes('applicationServerKey'))) {
                            console.warn('Subscription key mismatch, unsubscribing and retrying...');
                            registration.pushManager.getSubscription().then(function(subscription) {
                                if (subscription) {
                                    subscription.unsubscribe().then(function() {
                                        subscribeToPushNotifications();
                                    });
                                }
                            });
                        } else {
                            console.log('Failed to subscribe the user: ', err);
                        }
                    });
                });
            }
        </script>
        
        <x-pwa-prompt />
    </body>
</html>
