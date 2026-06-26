<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lara — Envago</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #ece5dd;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .chat-wrapper {
            width: 100%;
            max-width: 420px;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            background: #ece5dd;
        }

        /* Header */
        .chat-header {
            background: #075e54;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            flex-shrink: 0;
        }
        .chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #25d366;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .chat-header-info { flex: 1; }
        .chat-header-name { font-size: 16px; font-weight: 600; }
        .chat-header-sub  { font-size: 12px; color: rgba(255,255,255,.7); }
        .chat-reset {
            background: none;
            border: none;
            color: rgba(255,255,255,.7);
            font-size: 12px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .chat-reset:hover { color: #fff; }

        /* Messages */
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 12px 8px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .msg {
            max-width: 78%;
            padding: 7px 10px 6px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
            word-break: break-word;
            white-space: pre-wrap;
        }
        .msg-time {
            font-size: 10px;
            color: rgba(0,0,0,.45);
            float: right;
            margin-left: 8px;
            margin-top: 2px;
        }
        .msg.lara {
            background: #fff;
            align-self: flex-start;
            border-top-left-radius: 0;
        }
        .msg.user {
            background: #dcf8c6;
            align-self: flex-end;
            border-top-right-radius: 0;
        }
        .msg.typing {
            background: #fff;
            align-self: flex-start;
            border-top-left-radius: 0;
            color: #888;
            font-style: italic;
        }

        /* Date divider */
        .date-divider {
            text-align: center;
            margin: 8px 0;
        }
        .date-divider span {
            background: #d9fdd3;
            color: #555;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 8px;
        }

        /* Input */
        .chat-input-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            padding: 8px;
            flex-shrink: 0;
        }
        .chat-input {
            flex: 1;
            background: #fff;
            border: none;
            border-radius: 24px;
            padding: 10px 16px;
            font-size: 15px;
            font-family: inherit;
            resize: none;
            outline: none;
            max-height: 120px;
            line-height: 1.4;
        }
        .chat-send {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #075e54;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background .15s;
        }
        .chat-send:hover { background: #128c7e; }
        .chat-send svg { fill: #fff; }
        .chat-send:disabled { background: #aaa; cursor: default; }
    </style>
</head>
<body>
<div class="chat-wrapper">

    <div class="chat-header">
        <div class="chat-avatar">🌊</div>
        <div class="chat-header-info">
            <div class="chat-header-name">Lara</div>
            <div class="chat-header-sub">Envago · assistente de cadastro</div>
        </div>
        <button class="chat-reset" onclick="resetChat()">Nova conversa</button>
    </div>

    <div class="chat-messages" id="messages">
        <div class="date-divider"><span>Hoje</span></div>

        @forelse($history as $msg)
            <div class="msg {{ $msg->role === 'user' ? 'user' : 'lara' }}">{{ $msg->message }}</div>
        @empty
            {{-- Lara opens the conversation --}}
        @endforelse
    </div>

    <div class="chat-input-row">
        <textarea
            id="input"
            class="chat-input"
            placeholder="Digite uma mensagem"
            rows="1"
            onkeydown="handleKey(event)"
            oninput="autoResize(this)"
        ></textarea>
        <button class="chat-send" id="sendBtn" onclick="sendMessage()">
            <svg width="24" height="24" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
    </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const messagesEl = document.getElementById('messages');
const inputEl    = document.getElementById('input');
const sendBtn    = document.getElementById('sendBtn');

function scrollBottom() {
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function appendMsg(role, text) {
    const div = document.createElement('div');
    div.className = 'msg ' + (role === 'user' ? 'user' : 'lara');
    div.textContent = text;
    messagesEl.appendChild(div);
    scrollBottom();
    return div;
}

function showTyping() {
    const div = document.createElement('div');
    div.className = 'msg typing';
    div.id = 'typing';
    div.textContent = 'Lara está digitando...';
    messagesEl.appendChild(div);
    scrollBottom();
}

function removeTyping() {
    const el = document.getElementById('typing');
    if (el) el.remove();
}

async function sendMessage() {
    const text = inputEl.value.trim();
    if (!text) return;

    inputEl.value = '';
    autoResize(inputEl);
    sendBtn.disabled = true;
    appendMsg('user', text);
    showTyping();

    try {
        const res = await fetch('{{ route($messageRoute) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ message: text }),
        });
        const data = await res.json();
        // Typing delay: ~40ms per character, min 1s, max 4s
        const delay = Math.min(Math.max(data.reply.length * 40, 1000), 4000);
        await new Promise(r => setTimeout(r, delay));
        removeTyping();
        appendMsg('lara', data.reply);
    } catch (e) {
        removeTyping();
    } finally {
        sendBtn.disabled = false;
        inputEl.focus();
    }
}

async function resetChat() {
    if (!confirm('Apagar conversa e começar de novo?')) return;
    await fetch('{{ route($resetRoute) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf },
    });
    location.reload();
}

function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
}

function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// Auto-open with Lara's greeting if no history
@if($history->isEmpty())
window.addEventListener('load', async () => {
    showTyping();
    sendBtn.disabled = true;
    try {
        const res = await fetch('{{ route($messageRoute) }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ message: '__init__' }),
        });
        const data = await res.json();
        const delay = Math.min(Math.max(data.reply.length * 40, 1000), 4000);
        await new Promise(r => setTimeout(r, delay));
        removeTyping();
        appendMsg('lara', data.reply);
    } finally {
        sendBtn.disabled = false;
    }
});
@endif

scrollBottom();
</script>
</body>
</html>
