<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Configure seus recebimentos — Envago</title>
<style>
  body { margin: 0; padding: 0; background: #f5f5f3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
  .wrapper { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
  .header { background: #111; padding: 28px 40px; text-align: center; }
  .header-wordmark { color: #fff; font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
  .header-wordmark span { color: #FF2924; }
  .body { padding: 40px; }
  .greeting { font-size: 20px; font-weight: 600; color: #111; margin-bottom: 12px; }
  .text { font-size: 15px; line-height: 1.7; color: #555; margin-bottom: 24px; }
  .steps { background: #fafaf8; border-radius: 10px; padding: 20px 24px; margin-bottom: 28px; }
  .step { display: flex; gap: 14px; align-items: flex-start; margin-bottom: 14px; }
  .step:last-child { margin-bottom: 0; }
  .step-num { width: 24px; height: 24px; background: #FF2924; color: #fff; border-radius: 50%; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
  .step-text { font-size: 14px; color: #444; line-height: 1.5; }
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
    <p class="greeting">Olá, {{ $provider->user->name }}.</p>
    <p class="text">
      Seu perfil no Envago foi aprovado. Para começar a receber os pagamentos das suas reservas, configure sua conta bancária — leva menos de 5 minutos.
    </p>

    <div class="steps">
      <div class="step">
        <div class="step-num">1</div>
        <div class="step-text">Acesse seu painel e vá em Configurações</div>
      </div>
      <div class="step">
        <div class="step-num">2</div>
        <div class="step-text">Preencha seus dados bancários: nome do titular, banco, agência, conta e SWIFT/BIC</div>
      </div>
      <div class="step">
        <div class="step-num">3</div>
        <div class="step-text">Pronto — o repasse de 90% é feito via transferência bancária em até 1 dia útil após cada reserva confirmada</div>
      </div>
    </div>

    <a href="{{ route('provider.settings') }}" class="btn">Cadastrar dados bancários</a>

    <div class="divider"></div>
    <p class="small">
      Você também pode configurar a qualquer momento acessando seu painel em <a href="{{ route('provider.dashboard') }}" style="color:#FF2924;">envago.live</a>.<br><br>
      Dúvidas? Responda este e-mail ou escreva para <a href="mailto:reservas@envago.live" style="color:#FF2924;">reservas@envago.live</a>.
    </p>
  </div>
  <div class="footer">
    © {{ date('Y') }} Envago · envago.live
  </div>
</div>
</body>
</html>
