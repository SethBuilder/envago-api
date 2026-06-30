@extends('layouts.dashboard')
@section('title', 'Lara')
@section('page-title', 'Lara')

@push('styles')
<style>
  .content { padding: 0 !important; overflow: hidden; }

  .lara-admin-wrap {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 60px);
  }

  .lara-admin-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 24px;
    background: #fff;
    border-bottom: 1px solid #ece9e4;
    flex-shrink: 0;
  }
  .lara-admin-bar-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #111;
  }
  .lara-admin-bar-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #22c55e;
    animation: pulse 2s infinite;
  }
  @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.4;} }

  .lara-admin-messages {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    background: #f5f5f3;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .lara-msg { display: flex; flex-direction: column; gap: 3px; }
  .lara-msg.user { align-items: flex-end; }
  .lara-msg.assistant { align-items: flex-start; }

  .lara-bubble {
    max-width: 75%;
    padding: 11px 15px;
    border-radius: 14px;
    font-size: 14px;
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
  }
  .lara-msg.user .lara-bubble {
    background: #111;
    color: #fff;
    border-bottom-right-radius: 4px;
  }
  .lara-msg.assistant .lara-bubble {
    background: #fff;
    color: #111;
    border: 1px solid #ece9e4;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
  }
  .lara-ts { font-size: 11px; color: #bbb; padding: 0 4px; }

  .lara-typing-row { display: flex; }
  .lara-typing {
    background: #fff; border: 1px solid #ece9e4;
    border-radius: 14px; border-bottom-left-radius: 4px;
    padding: 12px 16px;
    display: flex; gap: 5px; align-items: center;
  }
  .lara-dot { width: 7px; height: 7px; border-radius: 50%; background: #ccc; }
  .lara-dot:nth-child(1) { animation: lDot .9s 0s infinite; }
  .lara-dot:nth-child(2) { animation: lDot .9s .15s infinite; }
  .lara-dot:nth-child(3) { animation: lDot .9s .3s infinite; }
  @keyframes lDot { 0%,60%,100%{transform:translateY(0);opacity:.4;} 30%{transform:translateY(-4px);opacity:1;} }

  .lara-admin-form {
    padding: 14px 24px;
    background: #fff;
    border-top: 1px solid #ece9e4;
    display: flex;
    gap: 10px;
    align-items: flex-end;
    flex-shrink: 0;
  }
  #lara-input {
    flex: 1;
    resize: none;
    border: 1.5px solid #e5e5e5;
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    line-height: 1.5;
    max-height: 120px;
    overflow-y: auto;
    transition: border-color .15s;
  }
  #lara-input:focus { border-color: #111; }
  #lara-send {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: #111;
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    transition: background .15s;
  }
  #lara-send:hover { background: #333; }
  #lara-send:disabled { background: #ccc; cursor: default; }

  .lara-empty {
    flex: 1; display: flex; align-items: center; justify-content: center;
    flex-direction: column; gap: 10px; color: #bbb;
  }
  .lara-empty-icon { font-size: 36px; }
  .lara-empty p { font-size: 14px; }
</style>
@endpush

@section('content')
<div class="lara-admin-wrap">

  <div class="lara-admin-bar">
    <div class="lara-admin-bar-title">
      <div class="lara-admin-bar-dot"></div>
      Lara · Admin
    </div>
  </div>

  <div class="lara-admin-messages" id="messages">
    @if($history->isEmpty())
    <div class="lara-empty" id="lara-empty">
      <div class="lara-empty-icon">🤖</div>
      <p>Ask Lara anything about your business.</p>
    </div>
    @else
    @foreach($history as $msg)
    <div class="lara-msg {{ $msg->role }}">
      <div class="lara-bubble">{{ $msg->message }}</div>
      <div class="lara-ts">{{ \Carbon\Carbon::parse($msg->created_at)->format('d/m H:i') }}</div>
    </div>
    @endforeach
    @endif
  </div>

  <div class="lara-admin-form">
    <textarea id="lara-input" placeholder="Ask Lara…" rows="1" onkeydown="handleKey(event)"></textarea>
    <button id="lara-send" onclick="sendMessage()">
      <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24">
        <line x1="22" y1="2" x2="11" y2="13"/>
        <polygon points="22 2 15 22 11 13 2 9 22 2"/>
      </svg>
    </button>
  </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
  var thinking = false;

  var msgs = document.getElementById('messages');
  if (msgs) msgs.scrollTop = msgs.scrollHeight;

  window.handleKey = function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
  };

  window.sendMessage = function () {
    var input = document.getElementById('lara-input');
    var text  = input.value.trim();
    if (!text || thinking) return;

    var empty = document.getElementById('lara-empty');
    if (empty) empty.remove();

    input.value = '';
    input.style.height = '';
    thinking = true;
    document.getElementById('lara-send').disabled = true;

    appendMsg('user', text);
    var typingEl = appendTyping();

    fetch('{{ route('admin.lara.message') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
      },
      body: JSON.stringify({ message: text }),
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
      typingEl.remove();
      appendMsg('assistant', data.reply || 'Algo deu errado.');
    })
    .catch(function () {
      typingEl.remove();
      appendMsg('assistant', 'Erro de conexão. Tente novamente.');
    })
    .finally(function () {
      thinking = false;
      document.getElementById('lara-send').disabled = false;
    });
  };

  function appendMsg(role, text) {
    var msgs = document.getElementById('messages');
    var row  = document.createElement('div');
    row.className = 'lara-msg ' + role;
    var bubble = document.createElement('div');
    bubble.className = 'lara-bubble';
    bubble.textContent = text;
    row.appendChild(bubble);
    msgs.appendChild(row);
    msgs.scrollTop = msgs.scrollHeight;
    return row;
  }

  function appendTyping() {
    var msgs = document.getElementById('messages');
    var row  = document.createElement('div');
    row.className = 'lara-typing-row';
    row.innerHTML = '<div class="lara-typing"><span class="lara-dot"></span><span class="lara-dot"></span><span class="lara-dot"></span></div>';
    msgs.appendChild(row);
    msgs.scrollTop = msgs.scrollHeight;
    return row;
  }

  // Auto-resize textarea
  document.getElementById('lara-input').addEventListener('input', function () {
    this.style.height = '';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
  });
})();
</script>
@endpush
