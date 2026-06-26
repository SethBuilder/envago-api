<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Verifique seu e-mail · Envago</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; background: #f5f5f3; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
  .card { background: #fff; border-radius: 16px; padding: 48px 40px; width: 100%; max-width: 420px; box-shadow: 0 2px 16px rgba(0,0,0,.07); text-align: center; }
  .icon { font-size: 48px; margin-bottom: 24px; }
  h1 { font-size: 22px; font-weight: 600; color: #111; margin-bottom: 12px; }
  p { font-size: 15px; color: #666; line-height: 1.7; }
  .email { font-weight: 500; color: #111; }
  .back { display: inline-block; margin-top: 32px; font-size: 13px; color: #FF2924; text-decoration: none; font-weight: 500; }
</style>
</head>
<body>
<div class="card">
  <div class="icon">📬</div>
  <h1>Verifique seu e-mail</h1>
  <p>Enviamos um link de ativação para <span class="email">{{ $email }}</span>. Clique no link para definir sua senha e ativar sua conta.</p>
  <p style="margin-top:16px; font-size:13px; color:#aaa;">O link é válido por 48 horas. Verifique também a pasta de spam.</p>
  <a href="{{ route('login') }}" class="back">Voltar ao login</a>
</div>
</body>
</html>
