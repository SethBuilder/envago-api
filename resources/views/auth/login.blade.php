<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Entrar · Envago</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; background: #f5f5f3; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; }
  .card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 400px; box-shadow: 0 2px 16px rgba(0,0,0,.07); }
  .logo { text-align: center; margin-bottom: 32px; }
  h1 { font-size: 20px; font-weight: 600; color: #111; margin-bottom: 6px; }
  .subtitle { font-size: 14px; color: #888; margin-bottom: 28px; }
  label { display: block; font-size: 13px; font-weight: 500; color: #444; margin-bottom: 6px; }
  input[type=email], input[type=password] { width: 100%; padding: 11px 14px; border: 1.5px solid #e5e5e5; border-radius: 8px; font-size: 15px; font-family: inherit; outline: none; transition: border-color .15s; }
  input:focus { border-color: #FF2924; }
  .field { margin-bottom: 18px; }
  .error { font-size: 12px; color: #FF2924; margin-top: 5px; }
  .btn { width: 100%; padding: 12px; background: #111; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 4px; transition: background .15s, color .15s; position: relative; overflow: hidden; }
  .btn:hover { background: #222; }
  .btn.loading { pointer-events: none; color: transparent !important; }
  .btn.loading::after { content: ''; position: absolute; width: 18px; height: 18px; top: 50%; left: 50%; margin: -9px 0 0 -9px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: btn-spin .65s linear infinite; }
  @keyframes btn-spin { to { transform: rotate(360deg); } }
  .divider { display: flex; align-items: center; gap: 12px; margin: 24px 0; }
  .divider hr { flex: 1; border: none; border-top: 1px solid #eee; }
  .divider span { font-size: 12px; color: #bbb; }
  .link-row { text-align: center; font-size: 13px; color: #888; }
  .link-row a { color: #FF2924; text-decoration: none; font-weight: 500; }
</style>
</head>
<body>
<div class="card">
  <div class="logo"><img src="/logo.svg" alt="Envago" style="height:40px; width:auto;"></div>
  <h1>Bem-vindo de volta</h1>
  <p class="subtitle">Entre na sua conta para continuar.</p>

  <form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="text" name="website" value="" autocomplete="off" tabindex="-1" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;" aria-hidden="true">
    <div class="field">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus required>
      @error('email')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
      <label for="password">Senha</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
      @error('password')<p class="error">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="btn">Entrar</button>
    <p style="text-align:right; margin-top:12px; font-size:13px;"><a href="{{ route('password.request') }}" style="color:#888; text-decoration:none;">Esqueceu sua senha?</a></p>
  </form>

  <div class="divider"><hr><span>ou</span><hr></div>
  <p class="link-row">Ainda não tem conta? <a href="{{ route('register') }}">Cadastre-se gratuitamente</a></p>
</div>
<script>
document.querySelector('form').addEventListener('submit', function() {
  var btn = this.querySelector('button[type=submit]');
  btn.classList.add('loading');
});
</script>
</body>
</html>
