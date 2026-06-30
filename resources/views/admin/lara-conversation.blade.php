@extends('layouts.dashboard')
@section('title', 'Conversa')
@section('page-title', 'Conversa')

@push('styles')
<style>
  .conv-thread { display:flex; flex-direction:column; gap:12px; max-width:760px; }
  .conv-msg { display:flex; flex-direction:column; gap:3px; }
  .conv-msg.user { align-items:flex-end; }
  .conv-msg.assistant { align-items:flex-start; }
  .conv-bubble {
    max-width:85%;padding:10px 14px;border-radius:14px;
    font-size:13.5px;line-height:1.6;white-space:pre-wrap;word-break:break-word;
  }
  .conv-msg.user .conv-bubble { background:#111;color:#fff;border-bottom-right-radius:4px; }
  .conv-msg.assistant .conv-bubble { background:#fff;color:#111;border:1px solid #ece9e4;border-bottom-left-radius:4px; }
  .conv-ts { font-size:11px;color:#bbb;padding:0 4px; }
</style>
@endpush

@section('content')

<div style="margin-bottom:20px;">
  <a href="{{ route('admin.lara.conversations') }}" style="font-size:13px;color:#888;text-decoration:none;">← Voltar</a>
  <span style="font-size:12px;color:#ccc;margin-left:10px;font-family:monospace;">{{ $sessionId }}</span>
</div>

<div class="conv-thread">
  @foreach($messages as $msg)
  <div class="conv-msg {{ $msg->role }}">
    <div class="conv-bubble">{{ preg_replace('/\s*\[PHOTO_FOR:[^\]]+\]/', '', $msg->message) }}</div>
    <div class="conv-ts">{{ \Carbon\Carbon::parse($msg->created_at)->format('d/m H:i') }}</div>
  </div>
  @endforeach
</div>

@endsection
