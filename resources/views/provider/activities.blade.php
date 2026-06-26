@extends('layouts.dashboard')
@section('title', 'Meus Passeios')
@section('page-title', 'Meus Passeios')

@section('content')

@if(session('success'))
<div id="success-toast" style="position:fixed;bottom:28px;right:28px;z-index:9999;background:#111;color:#fff;padding:14px 20px;border-radius:10px;font-size:14px;font-weight:500;box-shadow:0 8px 32px rgba(0,0,0,.18);display:flex;align-items:center;gap:10px;max-width:380px;">
  <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="11" fill="#22c55e"/><path d="M7 12.5l3.5 3.5 6.5-7" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
  {{ session('success') }}
</div>
<script>setTimeout(()=>{const t=document.getElementById('success-toast');if(t){t.style.transition='opacity .4s';t.style.opacity='0';setTimeout(()=>t.remove(),400);}},5000);</script>
@endif

<div class="section">
  <div class="section-header">
    <span class="section-title">Seus passeios</span>
    <a href="{{ route('provider.activities.create') }}" class="btn btn-primary" style="font-size:13px;">
      <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Novo passeio
    </a>
  </div>

  @if($activities->isEmpty())
    <div class="empty"><p>Nenhum passeio cadastrado ainda.</p></div>
  @else
    <table>
      <thead>
        <tr>
          <th>Nome</th>
          <th>Categoria</th>
          <th>Preço</th>
          <th>Duração</th>
          <th>Vagas</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($activities as $activity)
        <tr>
          <td style="font-weight:500;">{{ $activity->name }}</td>
          <td style="font-size:13px; color:#888;">{{ $activity->category?->name ?? '—' }}</td>
          <td style="font-weight:600;">R$ {{ number_format($activity->price, 0, ',', '.') }}</td>
          <td style="font-size:13px; color:#666;">
            @if($activity->duration_minutes)
              {{ $activity->duration_minutes }} min
            @else
              —
            @endif
          </td>
          <td style="font-size:13px; color:#666;">{{ $activity->capacity ?? '—' }}</td>
          <td>
            @if($activity->is_active)
              <span class="badge badge-confirmed">Ativo</span>
            @else
              <span class="badge badge-cancelled">Inativo</span>
            @endif
          </td>
          <td>
            <a href="{{ config('app.frontend_url') }}/passeio/{{ $activity->slug }}"
               target="_blank" class="btn btn-ghost" style="font-size:12px;">Ver página</a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>

@endsection
