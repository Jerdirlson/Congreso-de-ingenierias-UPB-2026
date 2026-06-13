# Inscripciones y pagos

## Propósito
Pago de inscripción y emisión de ticket, para **participantes** y para **ponentes** (estos
pagan al final, cuando su ponencia llega a `payment_pending`).

## Archivos clave
- Backend: `PaymentController`, `RegistrationController`, modelos `Payment`, `Registration`,
  `CongressEvent`; `AuthController::confirmExternalRegistration`.
- Frontend: `views/participante/ParticipantePago`, `ParticipanteHome`,
  `views/ponente/PonenteInscripcion`.

## Funcionalidades / endpoints (rol `ponente|participante`, correo verificado)
- **Iniciar pago** `POST /payments` (`registration_type`: `participant` | `speaker`):
  - `speaker`: requiere `submission_id` en estado `payment_pending`; al pagar, la ponencia
    pasa a `confirmed`.
  - `participant`: una sola inscripción confirmada por usuario.
  - ⚠️ **Modo demo**: marca el pago como `completed` de inmediato y genera `ticket_code`
    (no hay pasarela real todavía — TODO Wompi/PayU).
- **Webhook** `POST /webhooks/payment`: callback de pasarela (mock; en prod verificar firma).
- **Mis inscripciones** `GET /registrations`: lista con pago, ponencia y evento.

## Inscripción externa (plataforma institucional UPB)
- `POST /me/confirm-external-registration`: marca `external_registration_at`. Para ponentes
  con ponencia en `payment_pending`, también la avanza a `confirmed`. Idempotente.

## Reglas / notas
- Precio: `congress_event.price` si se indica evento; si no, valor por defecto (200000 COP).
- `Registration::generateTicketCode()` genera el código del ticket.
- `mapModality()` traduce la modalidad de la ponencia a la de la inscripción
  (`presencial_oral|presencial_poster` → `presencial`).
