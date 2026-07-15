<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Código de verificação — Envago</title>
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
          <p style="font-size:20px; font-weight:600; color:#111; margin:0 0 8px;">Seu código de verificação</p>
          <p style="font-size:15px; line-height:1.7; color:#555; margin:0 0 28px;">
            Para reivindicar a página <strong>{{ $businessName }}</strong> no Envago, use o código abaixo:
          </p>
          <div style="background:#f7f5f2; border-radius:12px; padding:32px; text-align:center; margin-bottom:28px;">
            <span style="font-size:48px; font-weight:700; color:#111; letter-spacing:12px;">{{ $otp }}</span>
          </div>
          <p style="font-size:13px; color:#999; line-height:1.6; margin:0;">
            Este código é válido por 10 minutos. Se você não solicitou este código, ignore este e-mail.
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
