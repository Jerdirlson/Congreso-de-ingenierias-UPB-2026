# Autenticación y roles

## Propósito
Registro, inicio de sesión y gestión de identidad/roles. Auth con **Laravel Sanctum**
(tokens) y roles con **Spatie Permission**.

## Archivos clave
- Backend: `AuthController`, `EmailVerificationController`, `PasswordResetController`,
  `AdminImpersonateController`, modelo `User`, notificaciones `EmailVerificationCode` /
  `PasswordResetCode`.
- Frontend: `stores/auth.ts`, vistas `public/LoginView`, `public/RegisterView`,
  `public/VerifyEmailView`, `public/ForgotPasswordView`, `components/layout/AppHeader.vue`.

## Roles
`admin`, `administrativo`, `revisor`, `ponente`, `participante`. **Múltiples roles** por
usuario. El rol "primario" (`role`) prioriza el **no-revisor** (`roles` trae el array completo).

## Funcionalidades
- **Registro** (`POST /register`): crea usuario y asigna rol según `registration_type`
  (`ponente` | `participante`). Dispara verificación de correo. El registro de ponentes
  puede estar **cerrado** por configuración (ver configuracion-cierre.md).
- **Login** (`POST /login`): devuelve token + payload con `role` y `roles`.
- **Verificación de correo por código** (6 dígitos, expira 15 min):
  `POST /email/verify-code`, `POST /email/verification-notification`. Ponentes y
  participantes deben verificar antes de operar (middleware `verified`).
- **Olvidé mi contraseña** (código de 6 dígitos, 15 min): `POST /password/forgot`
  (respuesta genérica anti-enumeración) → `POST /password/verify-code` →
  `POST /password/reset` (cambia clave y **revoca todos los tokens**).
- **Cambiar contraseña** (logueado): `PATCH /me/password` (requiere contraseña actual).
- **Doble rol**: si un usuario tiene `revisor` + otro rol, debe elegir con cuál entrar.
  El front muestra un modal (login) y un selector "Cambiar rol" en la cabecera.
  `activeRole` se guarda en `sessionStorage` (`cgr_active_role`).
- **Impersonación (admin)**: `POST /admin/users/{user}/impersonate` devuelve un token del
  usuario objetivo (no se puede impersonar admins). El token del admin se guarda en
  `localStorage` (`cgr_admin_token`) para poder volver. Durante impersonación el router
  **no** redirige al login aunque haya doble rol.

## Reglas / notas
- El payload de usuario (`AuthController::userPayload`) y el de impersonación deben incluir
  **`roles`** (array) además de `role`; el front depende de `roles` para el doble rol.
- Campos sensibles (`*_verification_code`, `*_reset_code`, etc.) están en `$hidden`.
- Verificación/reset guardan el código en columnas del usuario y validan con `hash_equals`.
