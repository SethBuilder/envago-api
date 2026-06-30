<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Redefinir senha · Envago</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; background: #f5f5f3; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; }
  .card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 2px 16px rgba(0,0,0,.07); }
  .logo { text-align: center; margin-bottom: 32px; }
  h1 { font-size: 20px; font-weight: 600; color: #111; margin-bottom: 6px; }
  .subtitle { font-size: 14px; color: #888; margin-bottom: 28px; }
  label { display: block; font-size: 13px; font-weight: 500; color: #444; margin-bottom: 6px; }
  input[type=email] { width: 100%; padding: 11px 14px; border: 1.5px solid #e5e5e5; border-radius: 8px; font-size: 15px; font-family: inherit; outline: none; transition: border-color .15s; }
  input:focus { border-color: #FF2924; }
  .field { margin-bottom: 18px; }
  .error { font-size: 12px; color: #FF2924; margin-top: 5px; }
  .status { font-size: 13px; color: #22c55e; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; margin-bottom: 20px; }
  .btn { width: 100%; padding: 12px; background: #111; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 4px; transition: background .15s; }
  .btn:hover { background: #222; }
  .link-row { text-align: center; font-size: 13px; color: #888; margin-top: 20px; }
  .link-row a { color: #FF2924; text-decoration: none; font-weight: 500; }
</style>
</head>
<body>
<div class="card">
  <div class="logo"><img src="/logo.svg" alt="Envago" style="height:40px; width:auto;"></div>
  <h1>Esqueceu sua senha?</h1>
  <p class="subtitle">Informe seu e-mail e enviaremos um link para redefinir sua senha.</p>

  @if(session('status'))
    <div class="status">{{ session('status') }}</div>
  @endif

  <form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="field">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" autofocus required>
      @error('email')<p class="error">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="btn">Enviar link</button>
  </form>

  <p class="link-row"><a href="{{ route('login') }}">Voltar para o login</a></p>
</div>
</body>
</html>
