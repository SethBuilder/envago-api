@extends('layouts.dashboard')
@section('title', $provider->business_name)
@section('page-title', $provider->business_name)

@section('content')

{{-- Header --}}
<div style="display:flex; align-items:center; gap:16px; margin-bottom:28px;">
  <a href="{{ route('admin.providers') }}" style="color:#aaa; text-decoration:none; font-size:13px;">← Operadores</a>
  <span style="color:#ddd;">|</span>
  <span class="badge badge-{{ $provider->status }}">
    {{ match($provider->status) { 'pending' => 'Pendente', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado', default => $provider->status } }}
  </span>
  <div style="margin-left:auto; display:flex; gap:8px;">
    <form method="POST" action="{{ route('admin.providers.impersonate', $provider) }}">
      @csrf
      <button type="submit" class="btn btn-ghost">Ver como operador</button>
    </form>
  </div>
  @if($provider->isPending())
    <form method="POST" action="{{ route('admin.providers.approve', $provider) }}" style="display:flex; gap:8px;">
      @csrf
      <button type="submit" class="btn btn-primary">Aprovar operador</button>
    </form>
    <form method="POST" action="{{ route('admin.providers.reject', $provider) }}">
      @csrf
      <button type="submit" class="btn btn-ghost">Rejeitar</button>
    </form>
  @endif
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
  {{-- Profile info --}}
  <div class="section">
    <div class="section-header"><span class="section-title">Informações</span></div>
    <div style="padding:20px 24px; display:flex; flex-direction:column; gap:14px;">
      <div><div style="font-size:11px;color:#aaa;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;">Nome</div><div style="font-size:15px;font-weight:500;">{{ $provider->user->name }}</div></div>
      <div><div style="font-size:11px;color:#aaa;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;">Negócio</div><div style="font-size:15px;font-weight:500;">{{ $provider->business_name }}</div></div>
      <div><div style="font-size:11px;color:#aaa;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;">E-mail</div><div style="font-size:14px;color:#555;">{{ $provider->user->email }}</div></div>
      <div><div style="font-size:11px;color:#aaa;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;">WhatsApp</div><div style="font-size:14px;color:#555;">{{ $provider->whatsapp_number ?? '—' }}</div></div>
      <div><div style="font-size:11px;color:#aaa;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;">Cadastro</div><div style="font-size:14px;color:#555;">{{ $provider->created_at->format('d/m/Y H:i') }}</div></div>
    </div>
  </div>

  {{-- Stats --}}
  <div style="display:flex; flex-direction:column; gap:16px;">
    <div class="stat-card"><div class="stat-label">Reservas</div><div class="stat-value">{{ $stats['total'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Confirmadas</div><div class="stat-value">{{ $stats['confirmed'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Receita</div><div class="stat-value">R$ {{ number_format($stats['revenue'], 0, ',', '.') }}</div></div>
  </div>
</div>

{{-- Activities --}}
<div class="section" style="margin-bottom:24px;">
  <div class="section-header"><span class="section-title">Passeios ({{ $provider->activities->count() }})</span></div>
  @if($provider->activities->isEmpty())
    <div class="empty"><p>Nenhum passeio cadastrado.</p></div>
  @else
    <table>
      <thead><tr><th>Nome</th><th>Preço</th><th>Duração</th><th>Capacidade</th></tr></thead>
      <tbody>
        @foreach($provider->activities as $activity)
        <tr>
          <td style="font-weight:500;">{{ $activity->name }}</td>
          <td>R$ {{ number_format($activity->price, 0, ',', '.') }}</td>
          <td>{{ $activity->duration_minutes }} min</td>
          <td>{{ $activity->capacity }} pessoas</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>

{{-- Bookings --}}
<div class="section">
  <div class="section-header"><span class="section-title">Reservas recentes</span></div>
  @if($bookings->isEmpty())
    <div class="empty"><p>Nenhuma reserva.</p></div>
  @else
    <table>
      <thead><tr><th>Código</th><th>Turista</th><th>Passeio</th><th>Data</th><th>Valor</th><th>Status</th></tr></thead>
      <tbody>
        @foreach($bookings as $booking)
        <tr>
          <td style="font-family:monospace;font-size:12px;color:#aaa;">{{ $booking->reference }}</td>
          <td style="font-weight:500;">{{ $booking->guest_name }}</td>
          <td>{{ $booking->schedule->activity->name }}</td>
          <td style="font-size:13px;color:#888;">{{ $booking->schedule->date->format('d/m/Y') }}</td>
          <td style="font-weight:600;">R$ {{ number_format($booking->total_price, 0, ',', '.') }}</td>
          <td><span class="badge badge-{{ $booking->status }}">{{ match($booking->status) { 'confirmed' => 'Confirmada', 'pending' => 'Pendente', 'cancelled' => 'Cancelada', default => $booking->status } }}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>

@if(session('success'))
<div style="position:fixed; bottom:24px; right:24px; background:#111; color:#fff; padding:14px 20px; border-radius:10px; font-size:14px; box-shadow:0 4px 16px rgba(0,0,0,.2);">
  {{ session('success') }}
</div>
@endif

@endsection
