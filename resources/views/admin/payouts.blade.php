@extends('layouts.dashboard')
@section('title', 'Pagamentos')
@section('page-title', 'Pagamentos')

@section('content')

<div class="section">
  <div class="section-header">
    <span class="section-title">Passeios com reservas confirmadas</span>
    <span style="font-size:12px; color:#aaa;">{{ $schedules->count() }} passeio(s)</span>
  </div>

  @if($schedules->isEmpty())
    <div class="empty"><p>Nenhuma reserva confirmada ainda.</p></div>
  @else
    <table>
      <thead>
        <tr>
          <th>Operador</th>
          <th>Passeio</th>
          <th>Data</th>
          <th>Pendente</th>
          <th>Já pago</th>
          <th>Histórico</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach($schedules as $row)
        @php $schedule = $row['schedule']; $provider = $row['provider']; @endphp
        <tr>
          <td style="font-weight:500;">{{ $provider?->business_name ?? '—' }}</td>
          <td style="font-size:13px;">{{ $schedule->activity->name }}</td>
          <td style="font-size:13px; color:#888; white-space:nowrap;">
            {{ $schedule->date->format('d/m/Y') }}
            {{ substr($schedule->time, 0, 5) }}
          </td>

          {{-- Accumulator --}}
          <td>
            @if($row['pending_amount'] > 0)
              <span style="font-weight:700; color:#111;">R$ {{ number_format($row['pending_amount'], 0, ',', '.') }}</span>
              <span style="font-size:11px; color:#aaa; margin-left:4px;">{{ $row['pending_count'] }} reserva(s)</span>
            @else
              <span style="color:#aaa; font-size:13px;">—</span>
            @endif
          </td>

          {{-- Total paid so far --}}
          <td style="font-size:13px; color:#666;">
            @if($row['history']->isNotEmpty())
              R$ {{ number_format($row['history']->sum('amount'), 0, ',', '.') }}
            @else
              —
            @endif
          </td>

          {{-- History: each payment as a download link --}}
          <td style="font-size:12px;">
            @foreach($row['history'] as $payout)
              <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                <span style="color:#aaa;">{{ $payout->paid_at->format('d/m') }}</span>
                <span>R$ {{ number_format($payout->amount, 0, ',', '.') }}</span>
                @if($payout->confirmation_path)
                  <a href="{{ route('admin.payouts.download', $payout) }}" target="_blank"
                     style="color:#ff2924; text-decoration:none; font-size:11px;">comprovante</a>
                @endif
              </div>
            @endforeach
          </td>

          {{-- Pay action --}}
          <td>
            @if($row['pending_amount'] > 0)
              <form method="POST" action="{{ route('admin.payouts.pay', $schedule) }}"
                    enctype="multipart/form-data" style="display:flex; gap:8px; align-items:center;">
                @csrf
                <input type="file" name="confirmation" accept=".pdf,.jpg,.jpeg,.png" required
                       style="font-size:11px; max-width:150px;">
                <button type="submit" class="btn btn-primary" style="font-size:12px; white-space:nowrap;">
                  Marcar pago
                </button>
              </form>
            @else
              <span style="font-size:12px; color:#aaa;">Pago</span>
            @endif
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
