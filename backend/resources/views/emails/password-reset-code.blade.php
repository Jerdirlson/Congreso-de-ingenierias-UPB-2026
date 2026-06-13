<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restablecer contraseña — Congreso Ingenierías 2026</title>
</head>
<body style="margin:0;padding:0;background:#0f1117;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#0f1117;min-height:100vh;">
    <tr>
      <td align="center" style="padding:40px 20px;">

        <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
               style="max-width:480px;background:#1a1d2e;border-radius:16px;overflow:hidden;border:1px solid #2d3148;">

          {{-- Header --}}
          <tr>
            <td style="background:linear-gradient(135deg,#5b21b6,#7c3aed);padding:32px 40px;text-align:center;">
              <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;letter-spacing:-0.3px;">
                Congreso Ingenierías 2026
              </h1>
              <p style="margin:6px 0 0;color:rgba(255,255,255,0.65);font-size:12px;">
                Universidad Pontificia Bolivariana · Bucaramanga
              </p>
            </td>
          </tr>

          {{-- Body --}}
          <tr>
            <td style="padding:40px;">

              <p style="margin:0 0 6px;color:#e2e8f0;font-size:15px;">
                Hola, <strong>{{ $name }}</strong>
              </p>
              <p style="margin:0 0 32px;color:#94a3b8;font-size:14px;line-height:1.6;">
                Recibimos una solicitud para restablecer la contraseña de tu cuenta.
                Usa el siguiente código para continuar. El código expira en
                <strong style="color:#a78bfa;">{{ $expiresMinutes }} minutos</strong>.
              </p>

              {{-- Code box --}}
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation"
                     style="background:#0f1117;border:2px solid #6d28d9;border-radius:12px;margin-bottom:32px;">
                <tr>
                  <td style="padding:28px 24px;text-align:center;">
                    <p style="margin:0 0 10px;color:#64748b;font-size:10px;text-transform:uppercase;letter-spacing:3px;">
                      Código para restablecer
                    </p>
                    <p style="margin:0;color:#ffffff;font-size:44px;font-weight:800;
                               letter-spacing:18px;font-variant-numeric:tabular-nums;
                               font-family:'Courier New',Courier,monospace;">
                      {{ $code }}
                    </p>
                  </td>
                </tr>
              </table>

              <p style="margin:0;color:#475569;font-size:12px;line-height:1.7;">
                Ingresa este código en la plataforma para crear una nueva contraseña.<br>
                Si no solicitaste este cambio, puedes ignorar este mensaje; tu contraseña no se modificará.<br>
                <strong style="color:#64748b;">Nunca compartas este código con nadie.</strong>
              </p>

            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="padding:20px 40px;border-top:1px solid #2d3148;text-align:center;">
              <p style="margin:0;color:#334155;font-size:11px;">
                © 2026 Congreso Ingenierías · UPB Bucaramanga
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
