<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cadastro · Envago</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; background: #f5f5f3; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; }
  .card { background: #fff; border-radius: 16px; padding: 40px; width: 100%; max-width: 440px; box-shadow: 0 2px 16px rgba(0,0,0,.07); }
  .logo { text-align: center; margin-bottom: 32px; }
  h1 { font-size: 20px; font-weight: 600; color: #111; margin-bottom: 6px; }
  .subtitle { font-size: 14px; color: #888; margin-bottom: 28px; line-height: 1.6; }
  label { display: block; font-size: 13px; font-weight: 500; color: #444; margin-bottom: 6px; }
  input[type=text], input[type=email], input[type=tel] { width: 100%; padding: 11px 14px; border: 1.5px solid #e5e5e5; border-radius: 8px; font-size: 15px; font-family: inherit; outline: none; transition: border-color .15s; }
  input:focus { border-color: #FF2924; }
  .field { margin-bottom: 18px; }
  .error { font-size: 12px; color: #FF2924; margin-top: 5px; }
  .hint { font-size: 12px; color: #aaa; margin-top: 4px; }
  .btn { width: 100%; padding: 12px; background: #111; color: #fff; border: none; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; margin-top: 4px; transition: background .15s, color .15s; position: relative; overflow: hidden; }
  .btn:hover { background: #222; }
  .btn.loading { pointer-events: none; color: transparent !important; }
  .btn.loading::after { content: ''; position: absolute; width: 18px; height: 18px; top: 50%; left: 50%; margin: -9px 0 0 -9px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: btn-spin .65s linear infinite; }
  @keyframes btn-spin { to { transform: rotate(360deg); } }
  .terms { font-size: 12px; color: #aaa; text-align: center; margin-top: 16px; line-height: 1.6; }
  .terms a { color: #888; }
  .link-row { text-align: center; font-size: 13px; color: #888; margin-top: 20px; }
  .link-row a { color: #FF2924; text-decoration: none; font-weight: 500; }
</style>
</head>
<body>
<div class="card">
  <div class="logo"><img src="/logo.svg" alt="Envago" style="height:40px; width:auto;"></div>
  <h1>Cadastre seus passeios</h1>
  <p class="subtitle">Gratuito para cadastrar. Apenas 10% sobre reservas realizadas pelo Envago.</p>

  <form method="POST" action="{{ route('register') }}">
    @csrf
    <div class="field">
      <label for="name">Seu nome</label>
      <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" autofocus required>
      @error('name')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
      <label for="business_name">Nome do seu negócio ou passeio</label>
      <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}" required>
      <p class="hint">Ex: Buggys do João, Passeios Jericoacoara</p>
      @error('business_name')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
      <label for="email">E-mail</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
      <p class="hint">Você receberá um link para ativar sua conta.</p>
      @error('email')<p class="error">{{ $message }}</p>@enderror
    </div>
    <div class="field">
      <label for="phone">WhatsApp <span style="color:#bbb;font-weight:400">(opcional)</span></label>
      <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+55 88 99999-0000">
      @error('phone')<p class="error">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="btn">Criar conta gratuita</button>
    <p class="terms">Ao criar uma conta você concorda com os <a href="#">Termos de Uso</a> do Envago.</p>
  </form>

  <p class="link-row">Já tem conta? <a href="{{ route('login') }}">Entrar</a></p>
</div>
<script>
document.querySelector('form').addEventListener('submit', function() {
  var btn = this.querySelector('button[type=submit]');
  btn.classList.add('loading');
});
</script>
</body>
</html>
