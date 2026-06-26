@extends('layouts.dashboard')
@section('title', 'Reservas')
@section('page-title', 'Reservas')

@section('content')

<div class="stat-grid" style="margin-bottom:24px;">
  <div class="stat-card">
    <div class="stat-label">Total confirmadas</div>
    <div class="stat-value">{{ $bookings->where('status','confirmed')->count() }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Canceladas</div>
    <div class="stat-value">{{ $bookings->where('status','cancelled')->count() }}</div>
  </div>
  <div class="stat-card">
    <div class="stat-label">Receita total</div>
    <div class="stat-value">R$ {{ number_format($bookings->where('status','confirmed')->sum('total_price'), 0, ',', '.') }}</div>
  </div>
</div>

<div class="section">
  <div class="section-header">
    <span class="section-title">Todas as reservas</span>
    <span style="font-size:12px; color:#aaa;">{{ $bookings->count() }} no total</span>
  </div>

  @if($bookings->isEmpty())
    <div class="empty"><p>Nenhuma reserva ainda.</p></div>
  @else
    <table>
      <thead>
        <tr>
          <th>Passeio</th>
          <th>Data</th>
          <th>Turista</th>
          <th>Pessoas</th>
          <th>Valor</th>
          <th>Status</th>
          <th>Reservado em</th>
        </tr>
      </thead>
      <tbody>
        @foreach($bookings as $booking)
        <tr>
          <td style="font-weight:500;">{{ $booking->schedule->activity->name }}</td>
          <td style="font-size:13px; color:#888; white-space:nowrap;">
            {{ $booking->schedule->date->format('d/m/Y') }}
            {{ substr($booking->schedule->time, 0, 5) }}
          </td>
          <td style="font-size:13px;">
            <div style="font-weight:500;">{{ $booking->guest_name }}</div>
            <div style="color:#aaa; font-size:12px;">{{ $booking->guest_email }}</div>
          </td>
          <td style="font-size:13px; color:#555;">{{ $booking->seats }}</td>
          <td style="font-weight:600;">R$ {{ number_format($booking->total_price, 0, ',', '.') }}</td>
          <td>
            @if($booking->status === 'confirmed')
              <span class="badge badge-confirmed">Confirmada</span>
            @elseif($booking->status === 'cancelled')
              <span class="badge badge-cancelled">Cancelada</span>
            @else
              <span class="badge badge-pending">{{ ucfirst($booking->status) }}</span>
            @endif
          </td>
          <td style="font-size:12px; color:#aaa; white-space:nowrap;">
            {{ $booking->created_at->format('d/m/Y H:i') }}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>

@endsection
