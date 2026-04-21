<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $mailSubject }}</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
    .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
    .header { background: #5b21b6; padding: 28px 40px; text-align: center; }
    .header h1 { color: #fff; margin: 0; font-size: 20px; }
    .header p { color: #c4b5fd; margin: 6px 0 0; font-size: 13px; }
    .body { padding: 36px 40px; }
    .body p { line-height: 1.7; color: #444; font-size: 15px; }
    .footer { background: #f9fafb; padding: 20px 40px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Congreso de Ingenierías 2026</h1>
      <p>Universidad Pontificia Bolivariana · Seccional Bucaramanga</p>
    </div>
    <div class="body">
      @if($recipientName)
        <p>Estimado/a <strong>{{ $recipientName }}</strong>,</p>
      @endif
      {!! nl2br(e($mailBody)) !!}
      <p style="margin-top: 28px; color: #666;">
        Cordialmente,<br>
        <strong>Comité Organizador — Congreso de Ingenierías 2026</strong>
      </p>
    </div>
    <div class="footer">
      Universidad Pontificia Bolivariana · Bucaramanga, Colombia<br>
      Este mensaje fue enviado por el equipo organizador del Congreso de Ingenierías 2026.
    </div>
  </div>
</body>
</html>
