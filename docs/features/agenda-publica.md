# Agenda pública

## Propósito
Vista pública `/agenda` con la programación del congreso. Solicitada por el comité de
Comunicaciones en agosto de 2026. El congreso se celebra del **13 al 17 de octubre de 2026**,
pero la agenda pública solo muestra del **miércoles 14 al sábado 17**.

## Archivos clave
- `frontend/src/views/public/AgendaView.vue` — la vista completa (datos incluidos).
- `frontend/src/router/index.ts` — ruta `/agenda`, `meta: { public: true }`.
- `frontend/src/components/NavBar.vue` — entrada "Agenda" del menú lateral.
- `frontend/src/components/FooterSection.vue` — enlace en la columna "Congreso".

Los datos están **hardcodeados** en el `.vue` (igual que `ComitesView` y
`ConferencistasView`); no hay endpoint ni tabla para la agenda.

## Qué se publica y qué no
El comité entregó la agenda en `Agenda V10_Agosto5_FISI.xlsx` con el detalle completo por
franja horaria, pero pidió publicar **solo el inicio de cada jornada**. Por cada día se
muestra: fecha, día de la semana, nombre de la jornada, enfoque (cuando lo tiene), hora de
inicio, actividad de apertura y lugar.

**No se publica:**
- **El martes 13 completo.** Es la jornada "Futuros Ingenieros" (semilleros y colegios) y en
  el Excel venía marcada en rojo entera, no solo su columna "Responsable". Francisco lo
  confirmó el 12-ago-2026: *"Ese día no se publica, solo hay que publicar a partir del
  miércoles"*.
- La columna **"Responsable"** del Excel (asignaciones internas: "Mecánica/Logística",
  "Electrónica/Juan Carlos Mantilla", "Chairs"…). El propio archivo lo indica en una nota:
  *"lo que está en color rojo es informativo y no se publica en la web"*.
- El detalle por franja horaria de cada día — la agenda sigue siendo **preliminar** y hay
  actividades sin confirmar (p. ej. "Experto - Vías Terrestres, pendiente por confirmar").

La vista muestra un aviso visible de que la agenda es preliminar y puede cambiar.

## Las cuatro jornadas publicadas
| Fecha | Jornada | Inicio |
|---|---|---|
| Mié 14 oct | Transformación Digital y Tecnología Humanocéntrica | 7:30 — registro general y kits |
| Jue 15 oct | Tecnologías Emergentes y Sociedad | 8:00 |
| Vie 16 oct | Sostenibilidad, Inteligencia Avanzada y Redes del Futuro | 8:00 |
| Sáb 17 oct | Integración y Salud | 8:30 — jornada deportiva |

## ⚠️ Inconsistencia de fechas en el resto del sitio
La vista `/agenda` anuncia **14 al 17 de octubre** (lo que efectivamente muestra), pero el
resto del sitio público sigue diciendo **13 al 17**:
`HeroSection.vue:173`, `FooterSection.vue:110`, `TimelineSection.vue:14`,
`CallForAbstractView.vue:48` y `:90`. En cambio `ParticipanteHome.vue:67` ya decía
*"Del Miércoles 14 al Sábado 17"*.

Está **sin resolver a propósito**: el congreso sí ocurre del 13 al 17 (el martes existe, solo
que no se publica), así que unificar a "14 al 17" es una decisión del comité, no técnica.
Preguntar antes de tocarlo.

## Pendiente por confirmar con el comité
- Cuando la agenda deje de ser preliminar, decidir si se publica el detalle por franja
  horaria (sin responsables) y si conviene moverla a base de datos + panel de admin en vez
  de dejarla hardcodeada.

## Nota
`components/ScheduleSection.vue` es un componente viejo con datos de ejemplo de 2025 que
**no está enlazado en ninguna vista**. No es la fuente de la agenda; no confundirlo con
`AgendaView.vue`.
