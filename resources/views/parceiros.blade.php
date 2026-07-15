<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Liste seus passeios — Envago</title>
    @include('partials._gtag')
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #F7F5F2;
            --surface:  #FFFFFF;
            --dark:     #111111;
            --red:      #ff2924;
            --muted:    #888888;
            --border:   #E8E4DF;
            --lara-bg:  #FFFFFF;
            --user-bg:  #111111;
            --user-fg:  #FFFFFF;
            --radius:   18px;
        }

        html, body {
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--dark);
            -webkit-font-smoothing: antialiased;
        }

        /* ── Layout ─────────────────────────────────────────── */
        .shell {
            display: flex;
            flex-direction: column;
            height: 100dvh;
            max-width: 560px;
            margin: 0 auto;
        }

        /* ── Header ─────────────────────────────────────────── */
        .header {
            flex-shrink: 0;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .header-logo {
            height: 32px;
            width: auto;
        }
        .header-divider {
            width: 1px;
            height: 22px;
            background: var(--border);
        }
        .header-lara {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .lara-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            flex-shrink: 0;
        }
        .header-lara-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
        }
        .header-lara-sub {
            font-size: 12px;
            color: var(--muted);
        }

        /* ── Messages ───────────────────────────────────────── */
        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 16px 8px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            scroll-behavior: smooth;
        }
        .messages::-webkit-scrollbar { width: 0; }

        /* ── Bubble ─────────────────────────────────────────── */
        .bubble-wrap {
            display: flex;
            flex-direction: column;
            max-width: 82%;
            animation: slideUp .2s ease;
        }
        .bubble-wrap.lara  { align-self: flex-start; }
        .bubble-wrap.user  { align-self: flex-end; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .bubble {
            padding: 11px 15px;
            border-radius: var(--radius);
            font-size: 15px;
            line-height: 1.55;
            word-break: break-word;
            white-space: pre-wrap;
        }
        .bubble.lara {
            background: var(--lara-bg);
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
            color: var(--dark);
        }
        .bubble.user {
            background: var(--user-bg);
            border-bottom-right-radius: 4px;
            color: var(--user-fg);
        }

        /* File bubble */
        .file-bubble {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: var(--radius);
            border-bottom-right-radius: 4px;
            background: var(--user-bg);
            color: var(--user-fg);
            font-size: 14px;
        }
        .file-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(255,255,255,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }
        .file-name { font-size: 13px; font-weight: 500; word-break: break-all; }
        .file-size { font-size: 11px; opacity: .65; margin-top: 1px; }

        .bubble-time {
            font-size: 11px;
            color: var(--muted);
            margin-top: 3px;
            padding: 0 2px;
        }
        .bubble-wrap.user .bubble-time { text-align: right; }

        /* ── Typing ─────────────────────────────────────────── */
        .typing-bubble {
            align-self: flex-start;
            background: var(--lara-bg);
            border-radius: var(--radius);
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
            padding: 14px 18px;
            display: flex;
            gap: 5px;
            animation: slideUp .2s ease;
        }
        .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #bbb;
            animation: bounce 1.2s infinite ease-in-out;
        }
        .dot:nth-child(2) { animation-delay: .2s; }
        .dot:nth-child(3) { animation-delay: .4s; }
        @keyframes bounce {
            0%,80%,100% { transform: translateY(0); }
            40%         { transform: translateY(-6px); }
        }

        /* ── Date divider ───────────────────────────────────── */
        .date-divider {
            text-align: center;
            margin: 6px 0;
        }
        .date-divider span {
            font-size: 11px;
            color: var(--muted);
            background: var(--bg);
            padding: 3px 12px;
            border-radius: 20px;
            border: 1px solid var(--border);
        }

        /* ── Input area ─────────────────────────────────────── */
        .input-area {
            flex-shrink: 0;
            padding: 10px 12px 14px;
            background: var(--bg);
        }

        /* File preview chip */
        .file-preview {
            display: none;
            align-items: center;
            gap: 8px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 8px 12px;
            margin-bottom: 8px;
            font-size: 13px;
            color: var(--dark);
            animation: slideUp .15s ease;
        }
        .file-preview.show { display: flex; }
        .file-preview-name { flex: 1; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file-preview-remove {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            padding: 2px;
            display: flex;
            align-items: center;
            border-radius: 4px;
            transition: color .15s;
        }
        .file-preview-remove:hover { color: var(--red); }

        .input-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            background: var(--surface);
            border-radius: 28px;
            border: 1px solid var(--border);
            padding: 6px 6px 6px 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            transition: border-color .15s, box-shadow .15s;
        }
        .input-row:focus-within {
            border-color: #ccc;
            box-shadow: 0 2px 12px rgba(0,0,0,.1);
        }

        .attach-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            padding: 6px;
            display: flex;
            align-items: center;
            border-radius: 50%;
            transition: color .15s, background .15s;
            flex-shrink: 0;
        }
        .attach-btn:hover { color: var(--dark); background: var(--bg); }

        .msg-input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 15px;
            font-family: inherit;
            background: transparent;
            resize: none;
            line-height: 1.5;
            max-height: 120px;
            padding: 5px 0;
            color: var(--dark);
        }
        .msg-input::placeholder { color: #bbb; }

        .send-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--red);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background .15s, transform .1s;
        }
        .send-btn:hover:not(:disabled) { background: #d41f1b; transform: scale(1.05); }
        .send-btn:disabled { background: #ddd; cursor: default; transform: none; }
        .send-btn svg { fill: #fff; }

        /* ── Responsive ─────────────────────────────────────── */
        @media (min-width: 560px) {
            .shell {
                border-left: 1px solid var(--border);
                border-right: 1px solid var(--border);
            }
        }
    </style>
</head>
<body>

<div class="shell">

    <header class="header">
        <img src="/logo.svg" alt="Envago" class="header-logo">
        <div class="header-divider"></div>
        <div class="header-lara">
            <div class="lara-dot"></div>
            <div>
                <div class="header-lara-name">Lara</div>
                <div class="header-lara-sub">{{ ($claimMode ?? false) ? 'Reivindicar página' : 'Assistente de cadastro' }}</div>
            </div>
        </div>
    </header>

    <div class="messages" id="messages">
        <div class="date-divider"><span>Hoje</span></div>
    </div>

    <div class="input-area">
        <div class="file-preview" id="file-preview">
            <span id="file-preview-icon">📎</span>
            <span class="file-preview-name" id="file-preview-name"></span>
            <button class="file-preview-remove" onclick="clearFile()" title="Remover">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="input-row">
            <button class="attach-btn" onclick="document.getElementById('file-input').click()" title="Enviar arquivo">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                </svg>
            </button>
            <input type="file" id="file-input" accept=".xlsx,.xls,.csv,.ods,.docx,.pdf,image/*" style="display:none" multiple onchange="onFileSelected(this)">
            <textarea
                id="msg-input"
                class="msg-input"
                placeholder="Digite uma mensagem..."
                rows="1"
                onkeydown="handleKey(event)"
                oninput="autoResize(this)"
            ></textarea>
            <button class="send-btn" id="send-btn" onclick="send()" disabled>
                <svg width="18" height="18" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </div>
    </div>

</div>

<script>
// ── Session UUID ──────────────────────────────────────────
const UUID_KEY = 'envago_parceiros_uuid';
function getUuid() {
    let id = localStorage.getItem(UUID_KEY);
    if (!id) {
        id = (crypto.randomUUID ? crypto.randomUUID() : ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g,c=>(c^crypto.getRandomValues(new Uint8Array(1))[0]&15>>c/4).toString(16)));
        localStorage.setItem(UUID_KEY, id);
    }
    return id;
}
const sessionUuid = getUuid();

// ── State ─────────────────────────────────────────────────
const csrf     = document.querySelector('meta[name="csrf-token"]').content;
const msgEl    = document.getElementById('messages');
const inputEl  = document.getElementById('msg-input');
const sendBtn  = document.getElementById('send-btn');
let   pendingFiles = [];

// ── Enable/disable send button ────────────────────────────
function updateSendBtn() {
    sendBtn.disabled = !inputEl.value.trim() && !pendingFiles.length;
}
inputEl.addEventListener('input', updateSendBtn);

// ── Auto-resize textarea ──────────────────────────────────
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

// ── Scroll to bottom ──────────────────────────────────────
function scrollBottom() {
    msgEl.scrollTo({ top: msgEl.scrollHeight, behavior: 'smooth' });
}

// ── Time stamp ────────────────────────────────────────────
function now() {
    return new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}

// ── Append message bubble ─────────────────────────────────
function linkify(text) {
    const escaped = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    const linked  = escaped.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" rel="noopener" style="color:inherit;text-decoration:underline;word-break:break-all;">$1</a>');
    return linked.replace(/\n/g, '<br>');
}

function appendBubble(role, text) {
    const wrap = document.createElement('div');
    wrap.className = 'bubble-wrap ' + role;

    const bub = document.createElement('div');
    bub.className = 'bubble ' + role;
    bub.innerHTML = linkify(text);

    const time = document.createElement('div');
    time.className = 'bubble-time';
    time.textContent = now();

    wrap.appendChild(bub);
    wrap.appendChild(time);
    msgEl.appendChild(wrap);
    scrollBottom();
    return wrap;
}

// ── Append file bubble ────────────────────────────────────
function appendFileBubble(file) {
    const wrap = document.createElement('div');
    wrap.className = 'bubble-wrap user';

    const size = file.size < 1024*1024
        ? (file.size/1024).toFixed(0) + ' KB'
        : (file.size/1024/1024).toFixed(1) + ' MB';

    if (file.type.startsWith('image/')) {
        const objectUrl = URL.createObjectURL(file);
        const img = document.createElement('img');
        img.src = objectUrl;
        img.alt = file.name;
        img.style.cssText = 'max-width:220px;max-height:180px;border-radius:8px;display:block;';
        img.onload = () => URL.revokeObjectURL(objectUrl);
        const sizeEl = document.createElement('div');
        sizeEl.className = 'file-size';
        sizeEl.style.cssText = 'margin-top:4px;text-align:right';
        sizeEl.textContent = size;
        const bubble = document.createElement('div');
        bubble.className = 'file-bubble';
        bubble.style.cssText = 'padding:6px;';
        bubble.appendChild(img);
        bubble.appendChild(sizeEl);
        const time = document.createElement('div');
        time.className = 'bubble-time';
        time.style.cssText = 'text-align:right';
        time.textContent = now();
        wrap.appendChild(bubble);
        wrap.appendChild(time);
    } else {
        const icon = file.name.match(/\.pdf$/i) ? '📄' : '📊';
        wrap.innerHTML = `
            <div class="file-bubble">
                <div class="file-icon">${icon}</div>
                <div>
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${size}</div>
                </div>
            </div>
            <div class="bubble-time" style="text-align:right">${now()}</div>`;
    }
    msgEl.appendChild(wrap);
    scrollBottom();
}

// ── Typing indicator ──────────────────────────────────────
function showTyping() {
    const t = document.createElement('div');
    t.id = 'typing';
    t.className = 'typing-bubble';
    t.innerHTML = '<div class="dot"></div><div class="dot"></div><div class="dot"></div>';
    msgEl.appendChild(t);
    scrollBottom();
}
function removeTyping() {
    document.getElementById('typing')?.remove();
}

// ── File picker ───────────────────────────────────────────
function onFileSelected(input) {
    const files = Array.from(input.files);
    if (!files.length) return;
    pendingFiles = pendingFiles.concat(files);

    const names = pendingFiles.map(f => f.name).join(', ');
    const icon  = pendingFiles.every(f => f.type.startsWith('image/')) ? '🖼' : '📎';
    document.getElementById('file-preview-icon').textContent = icon;
    document.getElementById('file-preview-name').textContent = pendingFiles.length === 1 ? pendingFiles[0].name : `${pendingFiles.length} arquivos`;
    document.getElementById('file-preview').classList.add('show');
    input.value = '';
    updateSendBtn();
    inputEl.focus();
}

function clearFile() {
    pendingFiles = [];
    document.getElementById('file-preview').classList.remove('show');
    updateSendBtn();
}

// ── Conversion tracking ───────────────────────────────────
let _leadFired = false;
function fireLeadConversion() {
    if (_leadFired || typeof gtag === 'undefined') return;
    _leadFired = true;
    try { gtag('event', 'conversion', {'send_to': 'AW-18282842253/p3rbCPeP48scEI2R-I1E', 'value': 1.0, 'currency': 'EUR'}); } catch {}
}
function firePublishedConversion() {
    if (typeof gtag === 'undefined') return;
    try { gtag('event', 'conversion', {'send_to': 'AW-18282842253/GdOGCJn348scEI2R-I1E', 'value': 1.0, 'currency': 'EUR'}); } catch {}
}

// ── Send ──────────────────────────────────────────────────
async function send() {
    const text = inputEl.value.trim();
    if (!text && !pendingFiles.length) return;

    const filesToSend = pendingFiles.slice();
    const textToSend  = text;

    inputEl.value = '';
    autoResize(inputEl);
    clearFile();
    sendBtn.disabled = true;

    filesToSend.forEach(f => appendFileBubble(f));
    if (textToSend) appendBubble('user', textToSend);
    fireLeadConversion();

    showTyping();

    try {
        const formData = new FormData();
        formData.append('session_id', sessionUuid);
        formData.append('website', ''); // honeypot — bots fill this, humans don't
        if (textToSend) formData.append('message', textToSend);
        filesToSend.forEach(f => formData.append('files[]', f));

        const res = await fetch('/parceiros/message', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: formData,
        });
        const data = await res.json();

        removeTyping();
        if (data.reply) appendBubble('lara', data.reply);
        if (data.published) firePublishedConversion();
    } catch {
        removeTyping();
        appendBubble('lara', 'Algo deu errado. Tente novamente.');
    } finally {
        sendBtn.disabled = false;
        inputEl.focus();
        updateSendBtn();
    }
}

function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); if (!sendBtn.disabled) send(); }
}

// ── Init: Lara opens the conversation ─────────────────────
const CLAIM_SLUG = @json(($claimMode ?? false) && isset($claimProvider) ? $claimProvider->slug : null);

window.addEventListener('load', async () => {
    sendBtn.disabled = true;
    showTyping();
    try {
        const formData = new FormData();
        formData.append('session_id', sessionUuid);
        formData.append('message', '__init__');
        if (CLAIM_SLUG) formData.append('claim_slug', CLAIM_SLUG);
        const res = await fetch('/parceiros/message', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: formData,
        });
        const data = await res.json();
        removeTyping();
        if (data.reply) appendBubble('lara', data.reply);
    } catch {
        removeTyping();
    } finally {
        updateSendBtn();
    }
});
</script>

</body>
</html>
