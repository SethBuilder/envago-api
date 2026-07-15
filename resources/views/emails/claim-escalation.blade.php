<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Reivindicação bloqueada</title>
</head>
<body style="margin:0; padding:0; background:#f5f5f3; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f5f3; padding:40px 16px;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#fff; border-radius:12px; overflow:hidden; max-width:560px; width:100%;">

      <tr>
        <td style="background:#111; padding:24px 40px;">
          <p style="margin:0; color:#fff; font-size:14px; font-weight:600;">Envago · Alerta de reivindicação</p>
        </td>
      </tr>

      <tr>
        <td style="padding:32px 40px;">
          <p style="font-size:16px; font-weight:600; color:#111; margin:0 0 20px;">Reivindicação não concluída</p>

          <table cellpadding="0" cellspacing="0" width="100%" style="font-size:14px; color:#555; line-height:1.8;">
            <tr>
              <td style="color:#999; width:160px; padding-bottom:8px;">Operador</td>
              <td style="font-weight:600; color:#111; padding-bottom:8px;">{{ $providerName }}</td>
            </tr>
            <tr>
              <td style="color:#999; padding-bottom:8px;">Slug</td>
              <td style="padding-bottom:8px;">
                <a href="https://painel.envago.live/admin/providers" style="color:#FF2924;">{{ $providerSlug }}</a>
              </td>
            </tr>
            <tr>
              <td style="color:#999; padding-bottom:8px;">E-mail tentado</td>
              <td style="padding-bottom:8px;">{{ $emailAttempted }}</td>
            </tr>
            <tr>
              <td style="color:#999; padding-bottom:8px;">Motivo</td>
              <td style="padding-bottom:8px;">{{ $reason }}</td>
            </tr>
          </table>

          <hr style="border:none; border-top:1px solid #f0ede8; margin:24px 0;">
          <p style="font-size:13px; color:#999; margin:0;">O operador foi informado que a equipe entrará em contato em até 24h.</p>
        </td>
      </tr>

      <tr>
        <td style="background:#fafaf8; padding:20px 40px; text-align:center; font-size:12px; color:#aaa;">
          © {{ date('Y') }} Envago · envago.live
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
