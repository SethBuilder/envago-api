<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Definir senha · Envago</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; background: #f5f5f3; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
  .card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 2px 16px rgba(0,0,0,.07); }
  .logo { text-align: center; margin-bottom: 32px; }
  h1 { font-size: 20px; font-weight: 600; color: #111; margin-bottom: 6px; }
  .subtitle { font-size: 14px; color: #888; margin-bottom: 28px; }
  label { display: block; font-size: 13px; font-weight: 500; color: #444; margin-bottom: 6px; }
  input[type=email], input[type=password] { width: 100%; padding: 11px 14px; border: 1.5px solid #e5e5e5; border-radius: 8px; font-size: 15px; font-family: inherit; outline: none; transition: border-color .15s; }
  input[type=password]:focus { border-color: #FF2924; }
  input[readonly] { background: #f9f7f4; color: #999; cursor: default; }
  .field { margin-bottom: 18px; }
  .error { font-size: 12px; color: #FF2924; margin-top: 5px; }
  .btn { width: 100%; padding: 12px; background: #111; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background .15s; }
  .btn:hover { background: #222; }
</style>
</head>
<body>
<div class="card">
  <div class="logo"><img src="/logo.svg" alt="Envago" style="height:40px; width:auto;"></div>
  <h1>Defina sua senha</h1>
  <p class="subtitle">Escolha uma senha para acessar sua conta no Envago.</p>

  <form method="POST" action="{{ route('auth.activate.submit', $token) }}">
    @csrf
    <div class="field">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" value="{{ $email }}" readonly autocomplete="username" tabindex="-1">
    </div>
    <div class="field">
      <label for="password">Nova senha</label>
      <input type="password" id="password" name="password" autocomplete="new-password" autofocus required minlength="8">
      @error('password')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
      <label for="password_confirmation">Confirmar senha</label>
      <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
    </div>
    <button type="submit" class="btn">Ativar conta</button>
  </form>
</div>
</body>
</html>
