@extends('layouts.dashboard')
@section('title', 'Conversas Lara')
@section('page-title', 'Conversas Lara')

@section('content')

<div class="section">
  <div class="section-header">
    <span class="section-title">Sessões de onboarding</span>
    <span style="font-size:13px;color:#aaa;">{{ $sessions->total() }} sessões</span>
  </div>
  @if($sessions->isEmpty())
    <div class="empty"><div class="empty-icon">💬</div><p>Nenhuma conversa ainda.</p></div>
  @else
  <table>
    <thead>
      <tr>
        <th>Sessão</th>
        <th>Primeira mensagem</th>
        <th>Msgs</th>
        <th>Data</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($sessions as $session)
      <tr>
        <td style="font-family:monospace;font-size:12px;color:#aaa;">{{ substr($session->session_id, 0, 12) }}…</td>
        <td style="max-width:360px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;">
          {{ $session->first_user_message ? Str::limit($session->first_user_message, 80) : '—' }}
        </td>
        <td style="color:#888;font-size:13px;">{{ $session->message_count }}</td>
        <td style="color:#aaa;font-size:13px;">{{ \Carbon\Carbon::parse($session->last_message_at)->format('d/m/Y H:i') }}</td>
        <td>
          <a href="{{ route('admin.lara.conversations.show', $session->session_id) }}" class="btn btn-ghost" style="font-size:12px;padding:5px 12px;">Ver</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @if($sessions->hasPages())
  <div style="padding:16px 24px;">{{ $sessions->links() }}</div>
  @endif
  @endif
</div>

@endsection
