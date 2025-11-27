<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Random Chat - Talk to Strangers</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #0f172a;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            background: #1e293b;
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid #3b82f6;
        }
        .header h1 {
            font-size: 32px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .header p {
            color: #94a3b8;
            margin-top: 8px;
        }
        .container {
            flex: 1;
            display: flex;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 20px;
            gap: 20px;
        }
        .main-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #1e293b;
            border-radius: 16px;
            overflow: hidden;
        }
        .status-bar {
            background: #334155;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #475569;
            flex-wrap: wrap;
            gap: 12px;
        }
        .status-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ef4444;
            animation: pulse 2s infinite;
        }
        .status-dot.waiting {
            background: #f59e0b;
        }
        .status-dot.connected {
            background: #10b981;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .match-info {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .match-badge {
            padding: 4px 10px;
            background: #3b82f6;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .match-badge.interest {
            background: #8b5cf6;
        }
        .match-badge.location {
            background: #10b981;
        }
        .controls {
            display: flex;
            gap: 10px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .btn-secondary {
            background: #475569;
            color: white;
        }
        .btn-secondary:hover {
            background: #334155;
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .message {
            max-width: 70%;
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
        .message.you {
            align-self: flex-end;
        }
        .message.stranger {
            align-self: flex-start;
        }
        .message-bubble {
            padding: 12px 16px;
            border-radius: 16px;
            word-wrap: break-word;
        }
        .message.you .message-bubble {
            background: #3b82f6;
            border-bottom-right-radius: 4px;
        }
        .message.stranger .message-bubble {
            background: #475569;
            border-bottom-left-radius: 4px;
        }
        .message-label {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 4px;
            font-weight: 600;
        }
        .system-message {
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
            padding: 12px;
            background: #1e293b;
            border-radius: 8px;
            align-self: center;
            max-width: 80%;
        }
        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 12px 16px;
            background: #475569;
            border-radius: 16px;
            width: fit-content;
            align-self: flex-start;
        }
        .typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #94a3b8;
            animation: typing 1.4s infinite;
        }
        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }
        .input-area {
            padding: 20px;
            background: #334155;
            border-top: 1px solid #475569;
        }
        .input-group {
            display: flex;
            gap: 12px;
        }
        input[type="text"] {
            flex: 1;
            padding: 14px 16px;
            background: #1e293b;
            border: 2px solid #475569;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus {
            outline: none;
            border-color: #3b82f6;
        }
        input[type="text"]:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .sidebar {
            width: 350px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .sidebar-section {
            background: #1e293b;
            border-radius: 16px;
            padding: 20px;
        }
        .sidebar h3 {
            color: #3b82f6;
            margin-bottom: 16px;
            font-size: 18px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-label {
            color: #94a3b8;
            font-size: 13px;
            margin-bottom: 6px;
            display: block;
            font-weight: 600;
        }
        select {
            width: 100%;
            padding: 10px 12px;
            background: #334155;
            border: 2px solid #475569;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            cursor: pointer;
        }
        select:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .interests-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .interest-tag {
            padding: 8px 12px;
            background: #334155;
            border: 2px solid #475569;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
            user-select: none;
        }
        .interest-tag:hover {
            background: #475569;
        }
        .interest-tag.selected {
            background: #3b82f6;
            border-color: #3b82f6;
            font-weight: 600;
        }
        .info-item {
            background: #334155;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .info-label {
            color: #94a3b8;
            font-size: 12px;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 16px;
            font-weight: 600;
        }
        .welcome-screen {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }
        .welcome-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .welcome-screen h2 {
            color: white;
            margin-bottom: 12px;
        }
        @media (max-width: 1024px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
            }
            .interests-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 640px) {
            .interests-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .controls {
                width: 100%;
                justify-content: stretch;
            }
            .controls button {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>💬 Random Chat</h1>
        <p>Talk to strangers based on location and interests</p>
    </div>

    <div class="container">
        <div class="sidebar">
            <div class="sidebar-section">
                <h3>📍 Your Location</h3>
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <select id="countrySelect">
                        <option value="">Select Country</option>
                        <option value="US">United States</option>
                        <option value="PH">Philippines</option>
                        <option value="UK">United Kingdom</option>
                        <option value="CA">Canada</option>
                        <option value="AU">Australia</option>
                        <option value="IN">India</option>
                        <option value="DE">Germany</option>
                        <option value="FR">France</option>
                        <option value="JP">Japan</option>
                        <option value="BR">Brazil</option>
                        <option value="MX">Mexico</option>
                        <option value="ES">Spain</option>
                        <option value="IT">Italy</option>
                        <option value="NL">Netherlands</option>
                        <option value="SG">Singapore</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">City (Optional)</label>
                    <input type="text" id="cityInput" placeholder="e.g. Manila, New York">
                </div>
            </div>

            <div class="sidebar-section">
                <h3>🎯 Your Interests</h3>
                <div class="interests-grid">
                    <div class="interest-tag" data-interest="gaming">🎮 Gaming</div>
                    <div class="interest-tag" data-interest="music">🎵 Music</div>
                    <div class="interest-tag" data-interest="movies">🎬 Movies</div>
                    <div class="interest-tag" data-interest="sports">⚽ Sports</div>
                    <div class="interest-tag" data-interest="technology">💻 Tech</div>
                    <div class="interest-tag" data-interest="art">🎨 Art</div>
                    <div class="interest-tag" data-interest="cooking">🍳 Cooking</div>
                    <div class="interest-tag" data-interest="travel">✈️ Travel</div>
                    <div class="interest-tag" data-interest="fitness">💪 Fitness</div>
                    <div class="interest-tag" data-interest="reading">📚 Reading</div>
                    <div class="interest-tag" data-interest="photography">📷 Photo</div>
                    <div class="interest-tag" data-interest="anime">🎌 Anime</div>
                </div>
            </div>

            <div class="sidebar-section">
                <h3>📊 Stats</h3>
                <div class="info-item">
                    <div class="info-label">Your ID</div>
                    <div class="info-value" id="yourId">-</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Status</div>
                    <div class="info-value" id="sidebarStatus">Disconnected</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Messages Sent</div>
                    <div class="info-value" id="messageCount">0</div>
                </div>
            </div>
        </div>

        <div class="main-chat">
            <div class="status-bar">
                <div class="status-info">
                    <div class="status-dot" id="statusDot"></div>
                    <span id="statusText">Not connected</span>
                </div>
                <div class="match-info" id="matchInfo"></div>
                <div class="controls">
                    <button class="btn-primary" id="startBtn" onclick="startChat()">Start Chat</button>
                    <button class="btn-secondary" id="skipBtn" onclick="skipChat()" disabled>Skip</button>
                    <button class="btn-danger" id="stopBtn" onclick="stopChat()" disabled>Stop</button>
                </div>
            </div>

            <div class="messages" id="messages">
                <div class="welcome-screen">
                    <div class="welcome-icon">👋</div>
                    <h2>Welcome to Random Chat</h2>
                    <p>Set your location and interests, then click "Start Chat"</p>
                </div>
            </div>

            <div class="input-area">
                <div class="input-group">
                    <input
                        type="text"
                        id="messageInput"
                        placeholder="Type your message..."
                        disabled
                    >
                    <button class="btn-primary" id="sendBtn" onclick="sendMessage()" disabled>Send</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

    <script>
        // Generate unique user ID
        const userId = 'user_' + Math.random().toString(36).substr(2, 9);
        document.getElementById('yourId').textContent = userId.substr(0, 12);

        // State
        let state = {
            status: 'disconnected',
            roomId: null,
            partnerId: null,
            messageCount: 0,
            typingTimeout: null,
            channel: null,
            preferences: {
                country: '',
                city: '',
                interests: []
            }
        };

        // Initialize Echo
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env("REVERB_APP_KEY") }}',
            wsHost: '{{ env("REVERB_HOST") }}',
            wsPort: {{ env("REVERB_PORT") }},
            wssPort: {{ env("REVERB_PORT") }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });

        // UI Elements
        const elements = {
            messages: document.getElementById('messages'),
            messageInput: document.getElementById('messageInput'),
            statusDot: document.getElementById('statusDot'),
            statusText: document.getElementById('statusText'),
            sidebarStatus: document.getElementById('sidebarStatus'),
            messageCount: document.getElementById('messageCount'),
            matchInfo: document.getElementById('matchInfo'),
            startBtn: document.getElementById('startBtn'),
            skipBtn: document.getElementById('skipBtn'),
            stopBtn: document.getElementById('stopBtn'),
            sendBtn: document.getElementById('sendBtn'),
            countrySelect: document.getElementById('countrySelect'),
            cityInput: document.getElementById('cityInput')
        };

        // Interest selection
        document.querySelectorAll('.interest-tag').forEach(tag => {
            tag.addEventListener('click', () => {
                tag.classList.toggle('selected');
                updatePreferences();
            });
        });

        // Location change
        elements.countrySelect.addEventListener('change', updatePreferences);
        elements.cityInput.addEventListener('blur', updatePreferences);

        async function updatePreferences() {
            const selectedInterests = Array.from(document.querySelectorAll('.interest-tag.selected'))
                .map(tag => tag.dataset.interest);

            state.preferences = {
                country: elements.countrySelect.value,
                city: elements.cityInput.value.trim(),
                interests: selectedInterests
            };

            try {
                await fetch('/random-chat/preferences', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        userId,
                        ...state.preferences
                    })
                });
                console.log('✅ Preferences updated');
            } catch (error) {
                console.error('Error updating preferences:', error);
            }
        }

        function updateUI() {
            const { status } = state;

            elements.startBtn.disabled = status !== 'disconnected';
            elements.skipBtn.disabled = status !== 'connected';
            elements.stopBtn.disabled = status === 'disconnected';
            elements.messageInput.disabled = status !== 'connected';
            elements.sendBtn.disabled = status !== 'connected';

            if (status === 'disconnected') {
                elements.statusDot.className = 'status-dot';
                elements.statusText.textContent = 'Not connected';
                elements.sidebarStatus.textContent = 'Disconnected';
                elements.matchInfo.innerHTML = '';
            } else if (status === 'waiting') {
                elements.statusDot.className = 'status-dot waiting';
                elements.statusText.textContent = 'Looking for best match...';
                elements.sidebarStatus.textContent = 'Searching...';
            } else if (status === 'connected') {
                elements.statusDot.className = 'status-dot connected';
                elements.statusText.textContent = 'Connected to stranger';
                elements.sidebarStatus.textContent = 'Chatting';
                elements.messageInput.focus();
            }
        }

        function showMatchInfo(matchInfo) {
            let badges = [];

            if (matchInfo.sameCity) {
                badges.push(`<div class="match-badge location">📍 Same City: ${matchInfo.city}</div>`);
            } else if (matchInfo.sameCountry) {
                badges.push(`<div class="match-badge location">🌍 Same Country</div>`);
            }

            if (matchInfo.commonInterests && matchInfo.commonInterests.length > 0) {
                matchInfo.commonInterests.forEach(interest => {
                    badges.push(`<div class="match-badge interest">🎯 ${interest}</div>`);
                });
            }

            elements.matchInfo.innerHTML = badges.join('');
        }

        function addMessage(sender, text, isSystem = false) {
            if (isSystem) {
                elements.messages.innerHTML += `
                    <div class="system-message">${text}</div>
                `;
            } else {
                elements.messages.innerHTML += `
                    <div class="message ${sender}">
                        <div class="message-label">${sender === 'you' ? 'You' : 'Stranger'}</div>
                        <div class="message-bubble">${escapeHtml(text)}</div>
                    </div>
                `;
            }
            elements.messages.scrollTop = elements.messages.scrollHeight;
        }

        function showTyping() {
            const existing = document.getElementById('typingIndicator');
            if (existing) return;

            const indicator = document.createElement('div');
            indicator.id = 'typingIndicator';
            indicator.className = 'typing-indicator';
            indicator.innerHTML = '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';
            elements.messages.appendChild(indicator);
            elements.messages.scrollTop = elements.messages.scrollHeight;
        }

        function hideTyping() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) indicator.remove();
        }

        async function startChat() {
            state.status = 'waiting';
            updateUI();
            elements.messages.innerHTML = '';
            addMessage(null, '🔍 Finding your perfect match based on location and interests...', true);

            try {
                const response = await fetch('/random-chat/find', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ userId })
                });

                const data = await response.json();

                if (data.status === 'paired') {
                    connectToRoom(data.roomId, data.matchInfo);
                } else {
                    pollForPartner();
                }
            } catch (error) {
                console.error('Error starting chat:', error);
                addMessage(null, '❌ Error connecting. Please try again.', true);
                state.status = 'disconnected';
                updateUI();
            }
        }

        async function pollForPartner() {
            let pollCount = 0;
            const maxPolls = 30; // 60 seconds max

            const interval = setInterval(async () => {
                if (state.status !== 'waiting') {
                    clearInterval(interval);
                    return;
                }

                pollCount++;

                // Timeout after max polls
                if (pollCount >= maxPolls) {
                    clearInterval(interval);
                    addMessage(null, '⏱️ No match found. Please try again.', true);
                    state.status = 'disconnected';
                    updateUI();
                    return;
                }

                try {
                    const response = await fetch('/random-chat/find', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ userId })
                    });

                    const data = await response.json();
                    console.log('Poll response:', data);

                    if (data.status === 'paired') {
                        clearInterval(interval);
                        connectToRoom(data.roomId, data.matchInfo);
                    } else {
                        // Update waiting message with count
                        const lastMessage = elements.messages.lastElementChild;
                        if (lastMessage && lastMessage.classList.contains('system-message')) {
                            const dots = '.'.repeat((pollCount % 3) + 1);
                            lastMessage.innerHTML = `🔍 Finding your perfect match${dots} (${data.waitingCount || 1} waiting)`;
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }
            }, 2000);
        }

        function connectToRoom(roomId, matchInfo) {
            state.roomId = roomId;
            state.status = 'connected';
            state.messageCount = 0;
            updateUI();

            // Clear messages and show connected state
            elements.messages.innerHTML = '';
            addMessage(null, '✅ Connected! Say hi to your new friend!', true);

            if (matchInfo) {
                showMatchInfo(matchInfo);

                if (matchInfo.commonInterests && matchInfo.commonInterests.length > 0) {
                    addMessage(null, `🎯 You both like: ${matchInfo.commonInterests.join(', ')}!`, true);
                }
                if (matchInfo.sameCity) {
                    addMessage(null, `📍 You're both from ${matchInfo.city}!`, true);
                } else if (matchInfo.sameCountry) {
                    addMessage(null, `🌍 You're both from the same country!`, true);
                }
            }

            // Leave previous channel if exists
            if (state.channel) {
                Echo.leave('room.' + state.channel);
            }

            state.channel = roomId;

            console.log('✅ Subscribing to room:', roomId);

            Echo.channel('room.' + roomId)
                .listen('.chat.event', (data) => {
                    console.log('Event received:', data);

                    if (data.userId === userId) return;

                    if (data.action === 'paired') {
                        console.log('✅ Partner also paired');
                    } else if (data.action === 'message') {
                        hideTyping();
                        addMessage('stranger', data.message);
                    } else if (data.action === 'typing') {
                        if (data.message === 'true') {
                            showTyping();
                        } else {
                            hideTyping();
                        }
                    } else if (data.action === 'disconnected') {
                        addMessage(null, '👋 Stranger has disconnected', true);
                        state.status = 'disconnected';
                        state.roomId = null;
                        updateUI();
                    }
                });
        }

        async function sendMessage() {
            const message = elements.messageInput.value.trim();
            if (!message || state.status !== 'connected') return;

            try {
                await fetch('/random-chat/message', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ userId, message })
                });

                addMessage('you', message);
                elements.messageInput.value = '';
                state.messageCount++;
                elements.messageCount.textContent = state.messageCount;

                sendTyping(false);
            } catch (error) {
                console.error('Error sending message:', error);
            }
        }

        async function sendTyping(isTyping) {
            if (state.status !== 'connected') return;

            try {
                await fetch('/random-chat/typing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ userId, isTyping })
                });
            } catch (error) {
                console.error('Error sending typing:', error);
            }
        }

        async function skipChat() {
            await stopChat();
            setTimeout(() => startChat(), 500);
        }

        async function stopChat() {
            try {
                await fetch('/random-chat/disconnect', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ userId })
                });

                if (state.channel) {
                    Echo.leave('room.' + state.channel);
                }

                state.status = 'disconnected';
                state.roomId = null;
                state.messageCount = 0;
                updateUI();

                elements.messages.innerHTML = '<div class="welcome-screen"><div class="welcome-icon">👋</div><h2>Chat ended</h2><p>Click "Start Chat" to find someone new</p></div>';
            } catch (error) {
                console.error('Error stopping chat:', error);
            }
        }

        elements.messageInput.addEventListener('input', () => {
            if (state.status !== 'connected') return;

            sendTyping(true);

            clearTimeout(state.typingTimeout);
            state.typingTimeout = setTimeout(() => {
                sendTyping(false);
            }, 1000);
        });

        elements.messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        window.addEventListener('beforeunload', () => {
            if (state.status !== 'disconnected') {
                stopChat();
            }
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        updateUI();
    </script>
</body>
</html>
