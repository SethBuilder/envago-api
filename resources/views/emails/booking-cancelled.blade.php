<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sua reserva foi cancelada — Envago</title>
<style>
  body { margin: 0; padding: 0; background: #f5f5f3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
  .wrapper { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
  .header { background: #111; padding: 28px 40px; text-align: center; }
  .header-wordmark { color: #fff; font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
  .header-wordmark span { color: #FF2924; }
  .body { padding: 40px; }
  .greeting { font-size: 20px; font-weight: 600; color: #111; margin-bottom: 12px; }
  .text { font-size: 15px; line-height: 1.7; color: #555; margin-bottom: 28px; }
  .reason-box { background: #fafaf8; border-left: 3px solid #FF2924; border-radius: 0 8px 8px 0; padding: 16px 20px; margin-bottom: 28px; }
  .reason-label { font-size: 11px; font-weight: 600; color: #aaa; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 6px; }
  .reason-text { font-size: 14px; color: #333; line-height: 1.6; }
  .booking-box { background: #f5f5f3; border-radius: 10px; padding: 20px 24px; margin-bottom: 28px; }
  .booking-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #ece9e4; font-size: 14px; }
  .booking-row:last-child { border-bottom: none; }
  .booking-label { color: #999; }
  .booking-value { color: #111; font-weight: 500; }
  .btn { display: inline-block; background: #FF2924; color: #fff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 15px; font-weight: 600; }
  .divider { height: 1px; background: #f0ede8; margin: 32px 0; }
  .small { font-size: 13px; color: #999; line-height: 1.6; }
  .footer { background: #fafaf8; padding: 24px 40px; text-align: center; font-size: 12px; color: #aaa; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="header-wordmark">en<span>v</span>ago</div>
  </div>
  <div class="body">
    <p class="greeting">Olá, {{ $booking->guest_name }}.</p>
    <p class="text">
      Infelizmente, sua reserva foi cancelada pelo operador. Pedimos desculpas pelo transtorno.
    </p>

    <div class="reason-box">
      <div class="reason-label">Motivo do cancelamento</div>
      <div class="reason-text">{{ $booking->cancellation_reason }}</div>
    </div>

    <div class="booking-box">
      <div class="booking-row">
        <span class="booking-label">Passeio</span>
        <span class="booking-value">{{ $booking->schedule->activity->name }}</span>
      </div>
      <div class="booking-row">
        <span class="booking-label">Data</span>
        <span class="booking-value">{{ $booking->schedule->date->format('d/m/Y') }} às {{ substr($booking->schedule->time, 0, 5) }}</span>
      </div>
      <div class="booking-row">
        <span class="booking-label">Reserva</span>
        <span class="booking-value">#{{ $booking->reference }}</span>
      </div>
      <div class="booking-row">
        <span class="booking-label">Valor</span>
        <span class="booking-value">R$ {{ number_format($booking->total_price, 2, ',', '.') }}</span>
      </div>
    </div>

    <div class="reason-box" style="border-left-color: #2e7d32; background: #f1f8f1; margin-bottom: 28px;">
      <div class="reason-label" style="color: #2e7d32;">Reembolso</div>
      <div class="reason-text"><strong>R$ {{ number_format($booking->total_price, 2, ',', '.') }}</strong> será devolvido automaticamente em até 5 dias úteis para o método de pagamento utilizado.</div>
    </div>

    <a href="https://envago.live" class="btn">Ver outros passeios</a>

    <div class="divider"></div>
    <p class="small">
      Dúvidas? Entre em contato pelo e-mail <a href="mailto:reservas@envago.live" style="color:#FF2924;">reservas@envago.live</a>.
    </p>
  </div>
  <div class="footer">
    © {{ date('Y') }} Envago · envago.live
  </div>
</div>
</body>
</html>
