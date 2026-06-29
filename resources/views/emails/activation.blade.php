<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ative sua conta no Envago</title>
</head>
<body style="margin:0; padding:0; background:#f5f5f3; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f3; padding:40px 16px;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#fff; border-radius:12px; overflow:hidden; max-width:560px; width:100%;">

      {{-- Header --}}
      <tr>
        <td style="background:#111; padding:32px 40px; text-align:center;">
          <img src="{{ rtrim(env('APP_URL', 'https://envago.live'), '/') }}/logo.svg" alt="Envago" width="120" style="height:36px; width:auto; display:inline-block;">
        </td>
      </tr>

      {{-- Body --}}
      <tr>
        <td style="padding:40px;">
          <p style="font-size:20px; font-weight:600; color:#111; margin:0 0 16px;">Olá, {{ $user->name }}.</p>
          <p style="font-size:15px; line-height:1.7; color:#555; margin:0 0 28px;">
            Recebemos o seu cadastro no Envago. Para ativar sua conta e definir sua senha, clique no botão abaixo.
          </p>
          <table cellpadding="0" cellspacing="0">
            <tr>
              <td style="background:#FF2924; border-radius:8px;">
                <a href="{{ $activationUrl }}" style="display:inline-block; background:#FF2924; color:#ffffff; text-decoration:none; padding:14px 32px; border-radius:8px; font-size:15px; font-weight:600; letter-spacing:0.2px; color:#ffffff !important;">Ativar minha conta</a>
              </td>
            </tr>
          </table>
          <hr style="border:none; border-top:1px solid #f0ede8; margin:32px 0;">
          <p style="font-size:13px; color:#999; line-height:1.6; margin:0;">
            Este link é válido por 72 horas. Se você não solicitou este cadastro, ignore este e-mail.<br><br>
            Caso o botão não funcione, copie e cole este endereço no seu navegador:<br>
            <a href="{{ $activationUrl }}" style="color:#FF2924; word-break:break-all;">{{ $activationUrl }}</a>
          </p>
        </td>
      </tr>

      {{-- Footer --}}
      <tr>
        <td style="background:#fafaf8; padding:24px 40px; text-align:center; font-size:12px; color:#aaa;">
          © {{ date('Y') }} Envago · envago.live
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
