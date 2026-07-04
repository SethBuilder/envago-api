<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Configure seus recebimentos — Envago</title>
<style>
  body { margin: 0; padding: 0; background: #f5f5f3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
  .wrapper { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,.08); }
  .header { background: #fff; padding: 32px 40px 24px; text-align: center; border-bottom: 1px solid #f0ede8; }
  .body { padding: 40px; }
  .greeting { font-size: 20px; font-weight: 600; color: #111; margin: 0 0 12px; }
  .text { font-size: 15px; line-height: 1.75; color: #555; margin: 0 0 28px; }
  .steps { background: #fafaf8; border-radius: 12px; padding: 24px 28px; margin-bottom: 32px; }
  .step-row { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
  .step-row:last-child { margin-bottom: 0; }
  .step-num { width: 28px; height: 28px; background: #FF2924; color: #fff; border-radius: 50%; font-size: 12px; font-weight: 700; text-align: center; line-height: 28px; vertical-align: top; padding-top: 2px; }
  .step-gap { width: 14px; }
  .step-text { font-size: 14px; color: #444; line-height: 1.6; vertical-align: top; padding-top: 5px; }
  .btn-wrap { margin-bottom: 32px; }
  .btn { display: inline-block; background: #FF2924; color: #fff !important; text-decoration: none; padding: 15px 36px; border-radius: 9px; font-size: 15px; font-weight: 600; letter-spacing: -0.1px; }
  .divider { height: 1px; background: #f0ede8; margin: 0 0 28px; }
  .small { font-size: 13px; color: #999; line-height: 1.7; margin: 0; }
  .small a { color: #FF2924; text-decoration: none; }
  .footer { background: #fafaf8; padding: 22px 40px; text-align: center; font-size: 12px; color: #bbb; border-top: 1px solid #f0ede8; }
</style>
</head>
<body>
<div class="wrapper">

  <div class="header">
    <img src="https://envago.live/logo.svg" alt="Envago" height="36" style="display:inline-block;">
  </div>

  <div class="body">
    <p class="greeting">Olá, {{ $provider->user->name }}.</p>
    <p class="text">
      Seu perfil no Envago foi aprovado. Para começar a receber os pagamentos das suas reservas, configure seus dados bancários — leva menos de 5 minutos.
    </p>

    <div class="steps">
      <table class="step-row" cellpadding="0" cellspacing="0">
        <tr>
          <td class="step-num">1</td>
          <td class="step-gap"></td>
          <td class="step-text">Acesse seu painel e vá em <strong>Configurações</strong></td>
        </tr>
      </table>
      <table class="step-row" cellpadding="0" cellspacing="0">
        <tr>
          <td class="step-num">2</td>
          <td class="step-gap"></td>
          <td class="step-text">Preencha seus dados bancários: nome do titular, banco, agência, conta e SWIFT/BIC</td>
        </tr>
      </table>
      <table class="step-row" cellpadding="0" cellspacing="0">
        <tr>
          <td class="step-num">3</td>
          <td class="step-gap"></td>
          <td class="step-text">Pronto — o repasse de 90% é feito por transferência bancária em até 1 dia útil após cada reserva confirmada</td>
        </tr>
      </table>
    </div>

    <div class="btn-wrap">
      <a href="{{ route('provider.settings') }}" class="btn">Cadastrar dados bancários</a>
    </div>

    <div class="divider"></div>
    <p class="small">
      Você também pode configurar a qualquer momento acessando seu painel em <a href="{{ route('provider.dashboard') }}">envago.live</a>.<br><br>
      Dúvidas? Responda este e-mail ou escreva para <a href="mailto:reservas@envago.live">reservas@envago.live</a>.
    </p>
  </div>

  <div class="footer">
    © {{ date('Y') }} Envago · envago.live
  </div>

</div>
</body>
</html>
