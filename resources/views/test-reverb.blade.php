<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Reverb Connection</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .status-box {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 18px;
            text-align: center;
        }
        .connected {
            background: #c8e6c9;
            color: #2e7d32;
            border: 2px solid #4caf50;
        }
        .disconnected {
            background: #ffcdd2;
            color: #c62828;
            border: 2px solid #f44336;
        }
        .connecting {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
        }
        button {
            background: #2196f3;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin: 10px 0;
        }
        button:hover {
            background: #1976d2;
        }
        #log {
            background: #263238;
            color: #aed581;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 400px;
            overflow-y: auto;
            margin-top: 20px;
        }
        .log-entry {
            margin: 5px 0;
            padding: 5px 0;
            border-bottom: 1px solid #37474f;
        }
        .log-time {
            color: #64b5f6;
        }
        .log-success {
            color: #81c784;
        }
        .log-error {
            color: #e57373;
        }
    </style>
</head>
<body>
    <h1>🔌 Reverb Connection Test</h1>

    <div id="status" class="status-box connecting">
        Connecting to Reverb...
    </div>

    <div>
        <button onclick="testBroadcast()">📡 Send Test Broadcast</button>
        <button onclick="clearLog()">🗑️ Clear Log</button>
    </div>

    <div id="log"></div>

    <script>
        const statusDiv = document.getElementById('status');
        const logDiv = document.getElementById('log');

        function addLog(message, type = 'info') {
            const time = new Date().toLocaleTimeString();
            const entry = document.createElement('div');
            entry.className = 'log-entry';

            let className = '';
            if (type === 'success') className = 'log-success';
            if (type === 'error') className = 'log-error';

            entry.innerHTML = `<span class="log-time">[${time}]</span> <span class="${className}">${message}</span>`;
            logDiv.appendChild(entry);
            logDiv.scrollTop = logDiv.scrollHeight;
        }

        function clearLog() {
            logDiv.innerHTML = '';
        }

        // Test if Echo is loaded
        if (typeof window.Echo === 'undefined') {
            addLog('❌ ERROR: Laravel Echo not loaded! Check your imports.', 'error');
            statusDiv.textContent = '❌ Echo not loaded';
            statusDiv.className = 'status-box disconnected';
        } else {
            addLog('✓ Laravel Echo loaded successfully', 'success');

            // Listen to connection events
            window.Echo.connector.pusher.connection.bind('connecting', () => {
                addLog('🔄 Attempting to connect to Reverb...');
                statusDiv.textContent = '🔄 Connecting...';
                statusDiv.className = 'status-box connecting';
            });

            window.Echo.connector.pusher.connection.bind('connected', () => {
                addLog('✓ Connected to Reverb server!', 'success');
                statusDiv.textContent = '✅ Connected to Reverb';
                statusDiv.className = 'status-box connected';
            });

            window.Echo.connector.pusher.connection.bind('unavailable', () => {
                addLog('❌ Connection unavailable', 'error');
                statusDiv.textContent = '❌ Connection Unavailable';
                statusDiv.className = 'status-box disconnected';
            });

            window.Echo.connector.pusher.connection.bind('failed', () => {
                addLog('❌ Connection failed!', 'error');
                statusDiv.textContent = '❌ Connection Failed';
                statusDiv.className = 'status-box disconnected';
            });

            window.Echo.connector.pusher.connection.bind('disconnected', () => {
                addLog('⚠ Disconnected from Reverb', 'error');
                statusDiv.textContent = '⚠ Disconnected';
                statusDiv.className = 'status-box disconnected';
            });

            // Listen on test channel
            window.Echo.channel('test-channel')
                .listen('.test-event', (data) => {
                    addLog('📨 Received test event: ' + JSON.stringify(data), 'success');
                });

            addLog('👂 Listening on channel: test-channel');
        }

        async function testBroadcast() {
            addLog('📤 Sending test broadcast...');

            try {
                const response = await fetch('/test-broadcast', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        message: 'Test from browser at ' + new Date().toLocaleTimeString()
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    addLog('✓ Broadcast sent successfully!', 'success');
                    addLog('Server response: ' + JSON.stringify(data));
                } else {
                    addLog('❌ Broadcast failed: ' + response.status, 'error');
                }
            } catch (error) {
                addLog('❌ Error: ' + error.message, 'error');
            }
        }
    </script>
</body>
</html>
