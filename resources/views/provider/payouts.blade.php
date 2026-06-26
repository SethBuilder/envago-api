@extends('layouts.dashboard')
@section('title', 'Meus Pagamentos')
@section('page-title', 'Meus Pagamentos')

@section('content')

<div class="section">
  <div class="section-header">
    <span class="section-title">Histórico de repasses</span>
    <span style="font-size:12px; color:#aaa;">{{ $payouts->where('status','paid')->count() }} pago(s) · {{ $payouts->where('status','pending')->count() }} pendente(s)</span>
  </div>

  @if($payouts->isEmpty())
    <div class="empty"><p>Nenhum repasse ainda. As reservas confirmadas aparecem aqui assim que processadas.</p></div>
  @else
    <table>
      <thead>
        <tr>
          <th>Passeio</th>
          <th>Data do passeio</th>
          <th>Reservas</th>
          <th>Valor</th>
          <th>Status</th>
          <th>Pago em</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($payouts as $payout)
        @php $activity = $payout->schedule?->activity; @endphp
        <tr>
          <td style="font-weight:500;">{{ $activity?->name ?? '—' }}</td>
          <td style="font-size:13px; color:#888;">
            {{ $payout->schedule?->date?->format('d/m/Y') ?? '—' }}
            @if($payout->schedule?->time)
              {{ substr($payout->schedule->time, 0, 5) }}
            @endif
          </td>
          <td style="font-size:13px; color:#666;">{{ $payout->booking_count ?? '—' }}</td>
          <td style="font-weight:600;">R$ {{ number_format($payout->amount, 0, ',', '.') }}</td>
          <td>
            @if($payout->isPaid())
              <span class="badge badge-confirmed">✓ Pago</span>
            @else
              <span class="badge badge-pending">Aguardando</span>
            @endif
          </td>
          <td style="font-size:13px; color:#888;">
            {{ $payout->paid_at ? $payout->paid_at->format('d/m/Y') : '—' }}
          </td>
          <td>
            @if($payout->confirmation_path)
              <a href="{{ route('provider.payouts.download', $payout) }}" class="btn btn-ghost" style="font-size:12px;" target="_blank">Comprovante</a>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>

@endsection
