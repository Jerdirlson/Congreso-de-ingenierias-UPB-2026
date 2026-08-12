# Inscripciones y pagos

## Propósito
Pago de inscripción y emisión de ticket, para **participantes** y para **ponentes**.

> **Cambio 30-jul-2026**: la inscripción del ponente **ya no es un paso de la ponencia**.
> La ponencia queda `confirmed` al aprobarse; la inscripción/pago se hace en el portal
> UPB y se refleja en el usuario (`external_registration_at` / `external_registration_paid_at`),
> no en el estado de la ponencia.

## Archivos clave
- Backend: `PaymentController`, `RegistrationController`, modelos `Payment`, `Registration`,
  `CongressEvent`; `AuthController::confirmExternalRegistration`.
- Frontend: `views/participante/ParticipantePago`, `ParticipanteHome`,
  `views/ponente/PonenteInscripcion`.

## Funcionalidades / endpoints (rol `ponente|participante`, correo verificado)
- **Iniciar pago** `POST /payments` (`registration_type`: `participant` | `speaker`):
  - `speaker`: requiere `submission_id` en estado `confirmed` (o `payment_pending`,
    legado); genera el ticket. La ponencia ya viene confirmada.
  - `participant`: una sola inscripción confirmada por usuario.
  - ⚠️ **Modo demo**: marca el pago como `completed` de inmediato y genera `ticket_code`
    (no hay pasarela real todavía — TODO Wompi/PayU).
- **Webhook** `POST /webhooks/payment`: callback de pasarela (mock; en prod verificar firma).
- **Mis inscripciones** `GET /registrations`: lista con pago, ponencia y evento.

## Inscripción externa (plataforma institucional UPB)
- `POST /me/confirm-external-registration`: marca `external_registration_at` (es lo que
  marca el paso "Inscripción" como completado en el detalle de la ponencia). Sigue
  avanzando a `confirmed` cualquier ponencia antigua en `payment_pending`. Idempotente.
- ⚠️ `external_registration_paid_at` (pago verificado) **no lo escribe ningún endpoint**:
  hoy solo se puede marcar a mano en la BD.

## Reglas / notas
- Precio: `congress_event.price` si se indica evento; si no, valor por defecto (200000 COP).
- `Registration::generateTicketCode()` genera el código del ticket.
- `mapModality()` traduce la modalidad de la ponencia a la de la inscripción
  (`presencial_oral|presencial_poster` → `presencial`).
