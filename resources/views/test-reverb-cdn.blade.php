<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Reverb - CDN</title>
</head>
<body>
    <h1>Reverb Test (CDN)</h1>
    <div id="status">Connecting...</div>
    <button onclick="testBroadcast()">Send Test</button>
    <div id="log"></div>

    <!-- Load from CDN -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env("REVERB_APP_KEY") }}',
            wsHost: '{{ env("REVERB_HOST") }}',
            wsPort: {{ env("REVERB_PORT") }},
            wssPort: {{ env("REVERB_PORT") }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });

        Echo.channel('test-channel').listen('.test-event', (e) => {
            document.getElementById('log').innerHTML += '<br>Received: ' + JSON.stringify(e);
        });

        Echo.connector.pusher.connection.bind('connected', () => {
            document.getElementById('status').innerHTML = '✅ Connected!';
        });

        async function testBroadcast() {
            const res = await fetch('/test-broadcast', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            console.log(await res.json());
        }
    </script>
</body>
</html>
