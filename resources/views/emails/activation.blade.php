<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ative sua conta no Envago</title>
<style>
  body { margin: 0; padding: 0; background: #f5f5f3; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
  .wrapper { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.07); }
  .header { background: #111; padding: 32px 40px; text-align: center; }
  .header img { height: 36px; }
  .header-wordmark { color: #fff; font-size: 22px; font-weight: 700; letter-spacing: -0.5px; }
  .header-wordmark span { color: #FF2924; }
  .body { padding: 40px; }
  .greeting { font-size: 20px; font-weight: 600; color: #111; margin-bottom: 16px; }
  .text { font-size: 15px; line-height: 1.7; color: #555; margin-bottom: 24px; }
  .btn { display: inline-block; background: #FF2924; color: #fff; text-decoration: none; padding: 14px 32px; border-radius: 8px; font-size: 15px; font-weight: 600; letter-spacing: 0.2px; }
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
    <p class="greeting">Olá, {{ $user->name }}.</p>
    <p class="text">
      Recebemos o seu cadastro no Envago. Para ativar sua conta e definir sua senha, clique no botão abaixo.
    </p>
    <a href="{{ $activationUrl }}" class="btn">Ativar minha conta</a>
    <div class="divider"></div>
    <p class="small">
      Este link é válido por 48 horas. Se você não solicitou este cadastro, ignore este e-mail.<br><br>
      Caso o botão não funcione, copie e cole este endereço no seu navegador:<br>
      <a href="{{ $activationUrl }}" style="color: #FF2924; word-break: break-all;">{{ $activationUrl }}</a>
    </p>
  </div>
  <div class="footer">
    © {{ date('Y') }} Envago · envago.live
  </div>
</div>
</body>
</html>
