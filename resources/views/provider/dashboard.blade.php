@extends('layouts.dashboard')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- Lara onboarding welcome banner --}}
@if($laraWelcome)
<div style="background:#f0fdf4; border:1.5px solid #22c55e; border-radius:12px; padding:20px 24px; margin-bottom:24px; display:flex; flex-wrap:wrap; align-items:center; gap:16px;">
  <div style="width:40px; height:40px; background:#dcfce7; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:20px;">🎉</div>
  <div style="flex:1; min-width:180px;">
    <div style="font-weight:600; color:#111; margin-bottom:4px;">Sua página foi criada pela Lara.</div>
    <div style="font-size:14px; color:#555;">Revise seus passeios e em breve sua página estará pronta para receber reservas.</div>
  </div>
  <a href="{{ route('provider.activities') }}" style="font-size:13px; font-weight:600; background:#111; color:#fff; padding:8px 16px; border-radius:8px; text-decoration:none; flex-shrink:0;">Ver passeios</a>
</div>
@endif

{{-- Bank details missing banner --}}
@if($provider && $provider->isApproved() && empty($provider->bank_details))
<div style="background:#fff; border:1.5px solid #FF2924; border-radius:12px; padding:20px 24px; margin-bottom:24px; display:flex; flex-wrap:wrap; align-items:center; gap:16px;">
  <div style="width:40px; height:40px; background:#fff1f1; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
    <svg width="20" height="20" fill="none" stroke="#FF2924" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
  </div>
  <div style="flex:1; min-width:180px;">
    <p style="font-size:14px; font-weight:600; color:#111; margin-bottom:2px;">Cadastre seus dados bancários</p>
    <p style="font-size:13px; color:#888;">Adicione seus dados para receber os repasses das reservas confirmadas.</p>
  </div>
  <a href="{{ route('provider.settings') }}" class="btn btn-danger" style="white-space:nowrap; flex-shrink:0;">Configurar agora</a>
</div>
@endif

{{-- Pending approval notice --}}
@if($provider && $provider->isPending())
<div style="background:#fff8e1; border:1px solid #ffe082; border-radius:10px; padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; gap:12px;">
  <span style="font-size:20px;">⏳</span>
  <div>
    <p style="font-size:14px; font-weight:600; color:#92400e;">Perfil em análise</p>
    <p style="font-size:13px; color:#a16207; margin-top:2px;">Seu perfil está sendo revisado pela equipe do Envago. Você receberá um e-mail assim que for aprovado.</p>
  </div>
</div>
@endif

{{-- Stats --}}
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-label">Próximas</div>
    <div class="stat-value">{{ $stats['confirmed'] }}</div>
  </div>
  <div class="stat-card" style="position:relative;">
    <div class="stat-label">Receita</div>
    <div class="stat-value">R$ {{ number_format($stats['revenue'], 0, ',', '.') }}</div>
    @if($provider && !empty($provider->bank_details))
      <div style="margin-top:10px;">
        <span style="font-size:11px; background:#e8f5e9; color:#2e7d32; padding:3px 10px; border-radius:20px; font-weight:600;">✓ Dados bancários cadastrados</span>
      </div>
    @elseif($provider && $provider->isApproved())
      <a href="{{ route('provider.settings') }}" style="display:inline-block; margin-top:10px; font-size:12px; font-weight:600; color:#FF2924; text-decoration:none;">
        Cadastrar dados bancários →
      </a>
    @endif
  </div>
</div>

@if($provider?->slug)
<div style="background:#fafaf8; border:1px solid #ece9e4; border-radius:10px; padding:14px 20px; margin-bottom:24px; display:flex; align-items:center; gap:12px;">
  <svg width="16" height="16" fill="none" stroke="#aaa" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
  <span style="font-size:13px; color:#888;">Sua página:</span>
  <a href="{{ config('app.web_url', 'http://localhost:3000') }}/operadores/{{ $provider->slug }}" target="_blank" style="font-size:13px; color:#FF2924; font-weight:500; text-decoration:none;">
    envago.live/operadores/{{ $provider->slug }}
  </a>
</div>
@endif


{{-- Bookings grouped --}}
@if($grouped->isEmpty())
  <div class="section">
    <div class="empty">
      <div class="empty-icon">📭</div>
      <p>Nenhuma reserva ainda.<br>Suas reservas aparecerão aqui quando os turistas reservarem.</p>
    </div>
  </div>
@else
  @foreach($grouped as $activityName => $byDate)
    @php $activitySlug = Str::slug($activityName); @endphp
    <div class="section" style="margin-bottom:20px;">

      {{-- Activity header --}}
      <div class="section-header">
        <div style="display:flex; align-items:center; gap:10px;">
          <span class="section-title">{{ $activityName }}</span>
          <span style="font-size:12px; color:#aaa; background:#f5f5f3; padding:2px 10px; border-radius:20px;">
            {{ $byDate->flatten()->count() }} reserva{{ $byDate->flatten()->count() != 1 ? 's' : '' }}
          </span>
        </div>
      </div>

      @foreach($byDate as $dateStr => $dateBookings)
        @php
          $date        = \Carbon\Carbon::parse($dateStr);
          $time        = substr($dateBookings->first()->schedule->time, 0, 5);
          $total       = $dateBookings->sum('total_price');
          $pax         = $dateBookings->sum('seats');
          $groupKey    = $activitySlug . '-' . $dateStr;
        @endphp

        {{-- Date sub-header --}}
        <div style="padding:10px 24px; background:#fafaf8; border-bottom:1px solid #f0ede8; display:flex; align-items:center; gap:12px;">
          <input type="checkbox" class="date-select-all" data-group="{{ $groupKey }}"
            onchange="toggleDateGroup(this)"
            style="width:15px; height:15px; cursor:pointer; accent-color:#FF2924; flex-shrink:0;">
          <div style="flex:1;">
            <span style="font-size:13px; font-weight:600; color:#333;">
              {{ $date->locale('pt_BR')->isoFormat('dddd, D [de] MMMM') }}
            </span>
            <span style="color:#ddd; margin:0 8px;">·</span>
            <span style="font-size:13px; color:#666;">{{ $time }}</span>
          </div>
          <div style="font-size:12px; color:#888;">{{ $pax }} pax</div>
          <div style="font-size:13px; font-weight:600; color:#111;">R$ {{ number_format($total, 0, ',', '.') }}</div>
        </div>

        {{-- Booking rows --}}
        @foreach($dateBookings as $booking)
        @php $cancelled = $booking->status === 'cancelled'; @endphp
        <div id="row-{{ $booking->id }}" style="display:flex; align-items:center; gap:12px; padding:12px 24px 12px 48px; border-bottom:1px solid #f7f5f2; {{ $cancelled ? 'opacity:.45;' : '' }}">
          <input type="checkbox"
            class="booking-check {{ $activitySlug }} {{ $groupKey }}"
            data-id="{{ $booking->id }}"
            data-amount="{{ $booking->total_price }}"
            onchange="updateBulkBar()"
            {{ $cancelled ? 'disabled' : '' }}
            style="width:15px; height:15px; cursor:{{ $cancelled ? 'default' : 'pointer' }}; accent-color:#FF2924; flex-shrink:0;">
          <div style="flex:1; min-width:0;">
            <div style="font-size:14px; font-weight:500; color:#111; {{ $cancelled ? 'text-decoration:line-through;' : '' }}">{{ $booking->guest_name }}</div>
            <div style="font-size:12px; color:#aaa; margin-top:1px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $booking->guest_email }}</div>
          </div>
          <div style="font-size:13px; color:#666; white-space:nowrap;">{{ $booking->seats }} pax</div>
          <div style="font-size:14px; font-weight:600; white-space:nowrap; min-width:70px; text-align:right;">R$ {{ number_format($booking->total_price, 0, ',', '.') }}</div>
          <div style="min-width:90px; text-align:right;">
            <span class="badge badge-{{ $booking->status }}">
              {{ $booking->status === 'confirmed' ? 'Confirmada' : 'Cancelada' }}
            </span>
          </div>
        </div>
        @endforeach

      @endforeach
    </div>
  @endforeach
@endif

{{-- Bulk cancel bar --}}
<div id="bulkBar" style="display:none; position:fixed; bottom:24px; left:50%; transform:translateX(-50%); background:#111; color:#fff; padding:14px 20px; border-radius:12px; box-shadow:0 8px 32px rgba(0,0,0,.25); align-items:center; gap:14px; z-index:200; min-width:400px;">
  <span id="bulkCount" style="font-size:14px; font-weight:500;"></span>
  <span id="bulkAmount" style="font-size:13px; color:#999;"></span>
  <button onclick="openCancelModal()" style="margin-left:auto; background:#FF2924; border:none; color:#fff; padding:8px 20px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Cancelar selecionadas</button>
  <button onclick="clearAll()" style="background:transparent; border:none; color:#666; padding:6px 10px; border-radius:8px; font-size:18px; cursor:pointer; line-height:1;">×</button>
</div>

{{-- Cancel modal --}}
<div id="cancelModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:300; align-items:center; justify-content:center;">
  <div style="background:#fff; border-radius:16px; padding:32px; width:100%; max-width:440px; margin:16px;">
    <h3 style="font-size:17px; font-weight:600; margin-bottom:6px;">Cancelar reservas</h3>
    <p id="modalSummary" style="font-size:13px; color:#888; margin-bottom:12px;"></p>
    <div id="refundNotice" style="background:#f1f8f1; border:1px solid #c8e6c9; border-radius:8px; padding:12px 16px; margin-bottom:20px; font-size:13px; color:#2e7d32; font-weight:500; line-height:1.5;"></div>
    <p style="font-size:13px; color:#666; margin-bottom:12px;">Informe o motivo. Os clientes serão notificados por e-mail.</p>
    <textarea id="cancelReason" placeholder="Ex: Passeio cancelado por condições climáticas." style="width:100%; padding:12px; border:1.5px solid #e5e5e5; border-radius:8px; font-size:14px; font-family:inherit; resize:vertical; min-height:90px; outline:none;"></textarea>
    <div style="display:flex; gap:10px; margin-top:16px; justify-content:flex-end;">
      <button onclick="closeCancelModal()" class="btn btn-ghost">Voltar</button>
      <button id="confirmCancelBtn" onclick="submitCancel()" class="btn btn-danger">Confirmar cancelamento</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function getChecked() {
  return [...document.querySelectorAll('.booking-check:checked')];
}

function updateBulkBar() {
  const checked = getChecked();
  const bar = document.getElementById('bulkBar');
  if (checked.length === 0) { bar.style.display = 'none'; return; }

  const total = checked.reduce((s, el) => s + parseFloat(el.dataset.amount), 0);
  const n = checked.length;
  document.getElementById('bulkCount').textContent = n + ' reserva' + (n !== 1 ? 's' : '') + ' selecionada' + (n !== 1 ? 's' : '');
  document.getElementById('bulkAmount').textContent = '· R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 0});
  bar.style.display = 'flex';
}

function toggleDateGroup(master) {
  document.querySelectorAll('.' + master.dataset.group).forEach(cb => { cb.checked = master.checked; });
  updateBulkBar();
}

function clearAll() {
  document.querySelectorAll('.booking-check, .date-select-all').forEach(cb => { cb.checked = false; });
  updateBulkBar();
}

function openCancelModal() {
  const checked = getChecked();
  if (!checked.length) return;
  const total = checked.reduce((s, el) => s + parseFloat(el.dataset.amount), 0);
  const n = checked.length;
  const fmt = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2});

  document.getElementById('modalSummary').textContent = n + ' reserva' + (n !== 1 ? 's' : '') + ' será' + (n !== 1 ? 'o' : '') + ' cancelada' + (n !== 1 ? 's' : '') + '.';
  document.getElementById('refundNotice').innerHTML = '💳 <strong>Reembolso total: ' + fmt + '</strong><br>Cada cliente receberá o valor integral automaticamente em até 5 dias úteis.';
  document.getElementById('cancelModal').style.display = 'flex';
  setTimeout(() => document.getElementById('cancelReason').focus(), 100);
}

const originalModalHTML = document.querySelector('#cancelModal > div').innerHTML;

function closeCancelModal() {
  document.getElementById('cancelModal').style.display = 'none';
  document.querySelector('#cancelModal > div').innerHTML = originalModalHTML;
}

async function submitCancel() {
  const reason = document.getElementById('cancelReason').value.trim();
  if (!reason) { document.getElementById('cancelReason').style.borderColor = '#FF2924'; return; }

  const ids = getChecked().map(el => parseInt(el.dataset.id));
  const btn = document.getElementById('confirmCancelBtn');
  btn.textContent = 'Cancelando...';
  btn.disabled = true;

  const res = await fetch('{{ route("provider.bookings.cancel") }}', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
    body: JSON.stringify({ booking_ids: ids, reason }),
  });

  if (res.ok) {
    // Mark rows as cancelled in-place
    ids.forEach(id => {
      const row = document.getElementById('row-' + id);
      if (!row) return;
      row.style.opacity = '.45';
      const cb = row.querySelector('input[type=checkbox]');
      cb.checked = false; cb.disabled = true; cb.style.cursor = 'default';
      const name = row.querySelector('div > div:first-child');
      if (name) name.style.textDecoration = 'line-through';
      const badge = row.querySelector('.badge');
      if (badge) { badge.className = 'badge badge-cancelled'; badge.textContent = 'Cancelada'; }
    });

    clearAll();

    // Show confirmation in modal
    const modalInner = document.querySelector('#cancelModal > div');
    modalInner.innerHTML = `
      <div style="text-align:center; padding:16px 0;">
        <div style="font-size:40px; margin-bottom:16px;">✓</div>
        <h3 style="font-size:17px; font-weight:600; margin-bottom:8px;">Cancelamento realizado</h3>
        <p style="font-size:14px; color:#666; line-height:1.6;">Os clientes foram notificados por e-mail e receberão o reembolso automaticamente em até 5 dias úteis.</p>
        <button onclick="closeCancelModal()" class="btn btn-primary" style="margin-top:24px;">Fechar</button>
      </div>`;
  } else {
    btn.textContent = 'Confirmar cancelamento';
    btn.disabled = false;
    alert('Erro ao cancelar. Tente novamente.');
  }
}
</script>
@endpush
