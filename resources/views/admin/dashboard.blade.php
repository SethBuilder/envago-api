@extends('layouts.dashboard')
@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-label">Operadores</div>
    <div class="stat-value">{{ $stats['providers_total'] }}</div>
    <div class="stat-sub">cadastrados</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Aguardando aprovação</div>
    <div class="stat-value" style="{{ $stats['providers_pending'] > 0 ? 'color:#b45309' : '' }}">{{ $stats['providers_pending'] }}</div>
    <div class="stat-sub">pendentes</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Reservas</div>
    <div class="stat-value">{{ $stats['bookings_total'] }}</div>
    <div class="stat-sub">total</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Receita</div>
    <div class="stat-value">R$ {{ number_format($stats['revenue'], 0, ',', '.') }}</div>
    <div class="stat-sub">confirmadas</div>
  </div>
</div>

{{-- Pending approvals --}}
@if($pendingProviders->isNotEmpty())
<div class="section" style="border-color: #ffe082;">
  <div class="section-header" style="background:#fffde7;">
    <span class="section-title" style="color:#92400e;">⏳ Operadores aguardando aprovação</span>
    <a href="{{ route('admin.providers') }}" style="font-size:13px; color:#FF2924; text-decoration:none; font-weight:500;">Ver todos</a>
  </div>
  <table>
    <thead>
      <tr>
        <th>Nome</th>
        <th>Negócio</th>
        <th>E-mail</th>
        <th>Cadastro</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      @foreach($pendingProviders as $provider)
      <tr>
        <td style="font-weight:500;">{{ $provider->user->name }}</td>
        <td>{{ $provider->business_name }}</td>
        <td style="font-size:13px; color:#888;">{{ $provider->user->email }}</td>
        <td style="font-size:13px; color:#aaa;">{{ $provider->created_at->format('d/m/Y') }}</td>
        <td style="display:flex; gap:8px;">
          <form method="POST" action="{{ route('admin.providers.approve', $provider) }}">
            @csrf
            <button type="submit" class="btn btn-primary" style="font-size:12px; padding:5px 14px;">Aprovar</button>
          </form>
          <form method="POST" action="{{ route('admin.providers.reject', $provider) }}">
            @csrf
            <button type="submit" class="btn btn-ghost" style="font-size:12px; padding:5px 14px;">Rejeitar</button>
          </form>
          <a href="{{ route('admin.providers.show', $provider) }}" class="btn btn-ghost" style="font-size:12px; padding:5px 14px;">Ver</a>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

{{-- Recent bookings --}}
<div class="section">
  <div class="section-header">
    <span class="section-title">Reservas recentes</span>
  </div>
  @if($recentBookings->isEmpty())
    <div class="empty">
      <div class="empty-icon">📭</div>
      <p>Nenhuma reserva ainda.</p>
    </div>
  @else
    <table>
      <thead>
        <tr>
          <th>Código</th>
          <th>Turista</th>
          <th>Passeio</th>
          <th>Operador</th>
          <th>Valor</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($recentBookings as $booking)
        <tr>
          <td style="font-family:monospace; font-size:12px; color:#aaa;">{{ $booking->reference }}</td>
          <td style="font-weight:500;">{{ $booking->guest_name }}</td>
          <td>{{ $booking->schedule->activity->name }}</td>
          <td style="font-size:13px; color:#888;">{{ $booking->schedule->activity->provider->business_name ?? '—' }}</td>
          <td style="font-weight:600;">R$ {{ number_format($booking->total_price, 0, ',', '.') }}</td>
          <td>
            <span class="badge badge-{{ $booking->status }}">
              {{ match($booking->status) { 'confirmed' => 'Confirmada', 'pending' => 'Pendente', 'cancelled' => 'Cancelada', default => $booking->status } }}
            </span>
          </td>
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
