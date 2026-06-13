# Correos y notificaciones

## Propósito
Envío de correos transaccionales (códigos, confirmaciones) y correo masivo segmentado.

## Archivos clave
- Notifications: `EmailVerificationCode`, `PasswordResetCode`.
- Mailables: `SubmissionConfirmedMail`, `BulkMail`.
- Plantillas Blade: `backend/resources/views/emails/` (`verification-code`,
  `password-reset-code`, `submission-confirmed`, `bulk`).
- Correo masivo: `AdminMailController` + `views/admin/AdminMailView`.
- Dev: contenedor **Mailpit** captura los correos en local.

## Correos
- **Código de verificación** (registro): 6 dígitos, expira 15 min. Disparado por
  `User::sendEmailVerificationNotification()`.
- **Código de restablecimiento de contraseña**: 6 dígitos, 15 min, vía
  `User::sendPasswordResetCode()`.
- **Confirmación de ponencia**: `SubmissionConfirmedMail`.
- **Correo masivo**: el admin compone y envía a segmentos por rol (`POST /admin/mail/send`,
  con `POST /admin/mail/preview`).

## Patrón para nuevos correos por código
1. Columnas en `users` para el código + expiración (en `$hidden`, cast a datetime).
2. Método en `User` que genera el código (`str_pad(random_int(...))`), guarda con
   `saveQuietly()` y llama `notify(new XxxCode($code))`.
3. Notification que renderiza una plantilla Blade en `emails/`.
4. Validación con `hash_equals` y chequeo de expiración en el controlador.

(Ver auth-y-roles.md para los flujos de verificación y reset.)
