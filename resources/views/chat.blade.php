<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Reverb Chat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .chat-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 800px;
            height: 600px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .chat-header h1 {
            font-size: 24px;
            font-weight: 600;
        }
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 10px #4ade80;
            animation: pulse 2s infinite;
        }
        .status-dot.disconnected {
            background: #ef4444;
            box-shadow: 0 0 10px #ef4444;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8fafc;
        }
        .message {
            margin-bottom: 16px;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .message-bubble {
            background: white;
            padding: 12px 16px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 70%;
            word-wrap: break-word;
        }
        .message-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
        }
        .username {
            font-weight: 600;
            color: #667eea;
            font-size: 14px;
        }
        .timestamp {
            font-size: 11px;
            color: #94a3b8;
        }
        .message-text {
            color: #1e293b;
            font-size: 15px;
            line-height: 1.5;
        }
        .input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e2e8f0;
        }
        .name-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 12px;
        }
        .input-group {
            display: flex;
            gap: 10px;
        }
        input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        button:active {
            transform: translateY(0);
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <h1>💬 Laravel Reverb Chat</h1>
            <div class="status-indicator">
                <div class="status-dot" id="statusDot"></div>
                <span id="statusText">Connecting...</span>
            </div>
        </div>

        <div class="messages-container" id="messagesContainer">
            <div class="empty-state">
                <div class="empty-state-icon">👋</div>
                <p>No messages yet. Be the first to say hi!</p>
            </div>
        </div>

        <div class="input-container">
            <div class="name-input-group">
                <input
                    type="text"
                    id="usernameInput"
                    placeholder="Your name"
                    value="User{{ rand(100, 999) }}"
                    maxlength="30"
                >
            </div>
            <div class="input-group">
                <input
                    type="text"
                    id="messageInput"
                    placeholder="Type your message..."
                    maxlength="500"
                >
                <button onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>

    <!-- Load libraries from CDN -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
        const messagesContainer = document.getElementById('messagesContainer');
        const messageInput = document.getElementById('messageInput');
        const usernameInput = document.getElementById('usernameInput');
        const statusDot = document.getElementById('statusDot');
        const statusText = document.getElementById('statusText');
        let messageCount = 0;

        // Initialize Laravel Echo
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env("REVERB_APP_KEY") }}',
            wsHost: '{{ env("REVERB_HOST") }}',
            wsPort: {{ env("REVERB_PORT") }},
            wssPort: {{ env("REVERB_PORT") }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });

        // Update connection status
        function updateStatus(connected) {
            if (connected) {
                statusDot.classList.remove('disconnected');
                statusText.textContent = 'Connected';
            } else {
                statusDot.classList.add('disconnected');
                statusText.textContent = 'Disconnected';
            }
        }

        // Connection events
        Echo.connector.pusher.connection.bind('connected', () => {
            updateStatus(true);
            console.log('✅ Connected to Reverb');
        });

        Echo.connector.pusher.connection.bind('disconnected', () => {
            updateStatus(false);
            console.log('❌ Disconnected from Reverb');
        });

        // Listen for messages with detailed logging
        const channel = Echo.channel('chat');

        channel.subscribed(() => {
            console.log('✅ Successfully subscribed to chat channel');
        });

        channel.error((error) => {
            console.error('❌ Channel error:', error);
        });

        channel.listen('.message.sent', (data) => {
            console.log('📨 MESSAGE RECEIVED:', data);
            addMessageToUI(data);
        });

        // Listen to ALL events (for debugging)
        Echo.connector.pusher.bind_global((event, data) => {
            console.log('🔔 Global event:', event, data);
        });

        function addMessageToUI(data) {
            // Remove empty state if this is the first message
            if (messageCount === 0) {
                messagesContainer.innerHTML = '';
            }
            messageCount++;

            const messageDiv = document.createElement('div');
            messageDiv.className = 'message';

            const time = new Date(data.timestamp).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });

            messageDiv.innerHTML = `
                <div class="message-bubble">
                    <div class="message-header">
                        <span class="username">${escapeHtml(data.username)}</span>
                        <span class="timestamp">${time}</span>
                    </div>
                    <div class="message-text">${escapeHtml(data.message)}</div>
                </div>
            `;

            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        async function sendMessage() {
            const message = messageInput.value.trim();
            const username = usernameInput.value.trim();

            if (!message) {
                messageInput.focus();
                return;
            }

            if (!username) {
                usernameInput.focus();
                return;
            }

            console.log('Sending message:', { message, username });

            try {
                const response = await fetch('/chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message, username })
                });

                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);

                if (response.ok) {
                    messageInput.value = '';
                    messageInput.focus();
                    console.log('✅ Message sent successfully');
                } else {
                    console.error('❌ Failed to send:', data);
                    alert('Failed to send message: ' + (data.message || response.status));
                }
            } catch (error) {
                console.error('❌ Error sending message:', error);
                alert('Network error: ' + error.message);
            }
        }

        // Send on Enter key
        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Focus message input on load
        window.addEventListener('load', () => {
            messageInput.focus();
        });
    </script>
</body>
</html>
