<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Participación Confirmada</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; color: #333; }
    .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
    .header { background: #5b21b6; padding: 32px 40px; text-align: center; }
    .header h1 { color: #fff; margin: 0; font-size: 22px; }
    .header p { color: #c4b5fd; margin: 8px 0 0; font-size: 14px; }
    .body { padding: 40px; }
    .body h2 { color: #5b21b6; font-size: 18px; margin-top: 0; }
    .body p { line-height: 1.6; color: #555; }
    .box { background: #f5f3ff; border-left: 4px solid #5b21b6; border-radius: 4px; padding: 16px 20px; margin: 24px 0; }
    .box p { margin: 4px 0; font-size: 14px; color: #4c1d95; }
    .box strong { color: #3b0764; }
    .badge { display: inline-block; background: #22c55e; color: #fff; font-size: 12px; font-weight: bold; border-radius: 20px; padding: 4px 12px; margin-bottom: 16px; }
    .footer { background: #f9fafb; padding: 24px 40px; text-align: center; font-size: 12px; color: #9ca3af; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Congreso de Ingenierías 2026</h1>
      <p>Universidad Pontificia Bolivariana</p>
    </div>
    <div class="body">
      <span class="badge">✓ CONFIRMADO</span>
      <h2>¡Tu participación está confirmada!</h2>
      <p>Estimado/a <strong>{{ $submission->user->name }}</strong>,</p>
      <p>
        Nos complace informarte que tu ponencia ha completado exitosamente el proceso de evaluación
        y <strong>tu participación en el Congreso de Ingenierías 2026 está oficialmente confirmada</strong>.
      </p>
      <div class="box">
        <p><strong>Ponencia:</strong> {{ Str::limit($submission->title, 120) }}</p>
        @if($submission->thematicAxis)
        <p><strong>Eje temático:</strong> {{ $submission->thematicAxis->name }}</p>
        @endif
        @if($submission->modality)
        <p><strong>Modalidad:</strong>
          @php
            $labels = [
              'presencial_oral'   => 'Presencial oral',
              'presencial_poster' => 'Presencial póster',
              'virtual'           => 'Virtual',
              'proyecto_aula'     => 'Proyecto de aula',
            ];
          @endphp
          {{ $labels[$submission->modality] ?? $submission->modality }}
        </p>
        @endif
      </div>
      <p>
        En los próximos días recibirás información sobre el programa oficial, horarios y logística del evento.
        Si tienes alguna pregunta, no dudes en contactarnos respondiendo a este correo.
      </p>
      <p>¡Gracias por tu contribución al conocimiento y la innovación en ingeniería!</p>
      <p>Cordialmente,<br><strong>Comité Organizador — Congreso de Ingenierías 2026</strong></p>
    </div>
    <div class="footer">
      Este correo fue enviado automáticamente. Por favor no respondas directamente a este mensaje.<br>
      Universidad Pontificia Bolivariana · Congreso de Ingenierías 2026
    </div>
  </div>
</body>
</html>
