@extends('layouts.dashboard')
@section('title', 'Operadores')
@section('page-title', 'Operadores')

@section('content')

<div class="section">
  <div class="section-header">
    <span class="section-title">Todos os operadores</span>
  </div>
  @if($providers->isEmpty())
    <div class="empty">
      <div class="empty-icon">👥</div>
      <p>Nenhum operador cadastrado ainda.</p>
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>Nome</th>
          <th>Negócio</th>
          <th>E-mail</th>
          <th>Cadastro</th>
          <th>Lara</th>
          <th>1º Login</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($providers as $provider)
        @php $deleted = $provider->trashed(); @endphp
        <tr style="{{ $deleted ? 'opacity:0.4;' : '' }}">
          <td style="font-weight:500;">{{ $provider->user->name }}{{ $deleted ? ' (arquivado)' : '' }}</td>
          <td>{{ $provider->business_name }}</td>
          <td style="font-size:13px; color:#888;">{{ $provider->user->email }}</td>
          <td style="font-size:13px; color:#aaa;">{{ $provider->created_at->format('d/m/Y') }}</td>
          <td style="font-size:13px; text-align:center;">
            @if($provider->lara_onboarded_at)
              <span style="color:#22c55e;" title="{{ $provider->lara_onboarded_at->format('d/m/Y H:i') }}">✓</span>
            @else
              <span style="color:#ddd;">—</span>
            @endif
          </td>
          <td style="font-size:13px; color:#aaa;">
            @if($provider->first_login_at)
              <span title="{{ $provider->first_login_at->format('d/m/Y H:i') }}">{{ $provider->first_login_at->format('d/m/Y') }}</span>
            @else
              <span style="color:#ddd;">—</span>
            @endif
          </td>
          <td><span class="badge badge-{{ $provider->status }}">
            {{ match($provider->status) { 'pending' => 'Pendente', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado', default => $provider->status } }}
          </span></td>
          <td style="display:flex; gap:8px; flex-wrap:wrap;">
            @if(!$deleted)
              @if($provider->isPending())
                <form method="POST" action="{{ route('admin.providers.approve', $provider) }}">
                  @csrf
                  <button type="submit" class="btn btn-primary" style="font-size:12px; padding:5px 14px;">Aprovar</button>
                </form>
                <form method="POST" action="{{ route('admin.providers.reject', $provider) }}">
                  @csrf
                  <button type="submit" class="btn btn-ghost" style="font-size:12px; padding:5px 14px;">Rejeitar</button>
                </form>
              @endif
              <a href="{{ route('admin.providers.show', $provider) }}" class="btn btn-ghost" style="font-size:12px; padding:5px 14px;">Ver detalhes</a>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div style="padding:16px 24px;">{{ $providers->links() }}</div>
  @endif
</div>

@if(session('success'))
<div style="position:fixed; bottom:24px; right:24px; background:#111; color:#fff; padding:14px 20px; border-radius:10px; font-size:14px; box-shadow:0 4px 16px rgba(0,0,0,.2);">
  {{ session('success') }}
</div>
@endif

@endsection
