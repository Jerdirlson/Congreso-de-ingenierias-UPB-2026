# Cloudflare Stream — Guía de configuración

Guía paso a paso para configurar Cloudflare Stream como plataforma de streaming en vivo para el Congreso de Ingenierías 2026.

---

## 1. Crear cuenta y habilitar Stream

1. Ir a [dash.cloudflare.com](https://dash.cloudflare.com) e iniciar sesión (o crear cuenta).
2. En el menú lateral, ir a **Stream** → **Get started**.
3. Se requiere un método de pago (tarjeta o PayPal). El cobro es por uso.
4. Una vez habilitado, verás el dashboard de Stream.

## 2. Obtener Account ID

1. En el dashboard de Cloudflare, ir a cualquier dominio o a la página de inicio.
2. En la barra lateral derecha verás **Account ID** (un string hexadecimal de 32 caracteres).
3. Copiarlo → es el valor para `CLOUDFLARE_ACCOUNT_ID`.

## 3. Crear API Token

1. Ir a **My Profile** → **API Tokens** → **Create Token**.
2. Seleccionar **Create Custom Token**.
3. Configurar:
   - **Token name:** `congreso-stream`
   - **Permissions:**
     - Account → Stream → **Edit**
   - **Account Resources:**
     - Include → tu cuenta
4. Click en **Continue to summary** → **Create Token**.
5. Copiar el token generado → es el valor para `CLOUDFLARE_API_TOKEN`.

> **IMPORTANTE:** El token solo se muestra una vez. Guárdalo en un lugar seguro.

## 4. Configurar Webhook (opcional pero recomendado)

Los webhooks permiten que Cloudflare notifique a tu app cuando un stream empieza, termina o la grabación está lista.

1. En el dashboard de Stream, ir a **Notifications** o configurar vía API:
   ```bash
   curl -X PUT "https://api.cloudflare.com/client/v4/accounts/{ACCOUNT_ID}/stream/webhook" \
     -H "Authorization: Bearer {API_TOKEN}" \
     -H "Content-Type: application/json" \
     --data '{
       "notificationUrl": "https://congreso2026.bucaramanga.upb.edu.co/api/webhooks/cloudflare-stream"
     }'
   ```
2. La respuesta incluirá un `secret` → es el valor para `CLOUDFLARE_STREAM_WEBHOOK_SECRET`.

## 5. Configurar variables de entorno

### Desarrollo (backend/.env)
```env
CLOUDFLARE_ACCOUNT_ID=tu_account_id_aqui
CLOUDFLARE_API_TOKEN=tu_api_token_aqui
CLOUDFLARE_STREAM_WEBHOOK_SECRET=tu_webhook_secret_aqui
```

### Producción (.env)
```env
CLOUDFLARE_ACCOUNT_ID=tu_account_id_aqui
CLOUDFLARE_API_TOKEN=tu_api_token_aqui
CLOUDFLARE_STREAM_WEBHOOK_SECRET=tu_webhook_secret_aqui
```

## 6. Ejecutar migración

```bash
# En Docker
docker exec cgr-backend php artisan migrate

# O con Make
make artisan CMD="migrate"
```

---

## Flujo de uso

### Crear una transmisión

```bash
curl -X POST http://localhost:8000/api/streams \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Conferencia de apertura",
    "scheduled_at": "2026-10-13T09:00:00",
    "platform": "cloudflare"
  }'
```

La respuesta incluye las **credenciales de ingest** para configurar OBS o cualquier encoder:
- `rtmps_url` + `rtmps_stream_key` → para RTMP
- `srt_url` + `srt_passphrase` → para SRT (menor latencia)

### Configurar OBS Studio

1. Abrir OBS → **Settings** → **Stream**.
2. **Service:** Custom
3. **Server:** pegar el valor de `rtmps_url` (ejemplo: `rtmps://live.cloudflare.com:443/live/`)
4. **Stream Key:** pegar el valor de `rtmps_stream_key`
5. Click en **Apply** → **OK**.
6. Iniciar transmisión en OBS.

### Configurar OBS con SRT (menor latencia)

1. Abrir OBS → **Settings** → **Stream**.
2. **Service:** Custom
3. **Server:** pegar el valor de `srt_url`
4. Dejar Stream Key vacío (el passphrase va en la URL).
5. Iniciar transmisión.

### Iniciar/terminar desde la API

```bash
# Marcar como "en vivo"
curl -X POST http://localhost:8000/api/streams/1/go-live \
  -H "Authorization: Bearer {TOKEN}"

# Terminar transmisión
curl -X POST http://localhost:8000/api/streams/1/end \
  -H "Authorization: Bearer {TOKEN}"
```

> **Nota:** Si el webhook está configurado, el estado se actualiza automáticamente
> cuando Cloudflare detecta que el stream empieza o termina.

### Ver credenciales de un stream existente

```bash
curl http://localhost:8000/api/streams/1/credentials \
  -H "Authorization: Bearer {TOKEN}"
```

---

## Costos estimados

| Concepto | Precio |
|----------|--------|
| Almacenamiento de video | $5 USD / 1,000 min almacenados |
| Entrega (viewing) | $1 USD / 1,000 min de visualización |
| Live Input (ingest) | Incluido sin costo adicional |

**Ejemplo para el congreso:**
- 5 conferencias × 2 hrs = 600 min almacenados → ~$3 USD
- 200 personas × 1.5 hrs promedio = 90,000 min de visualización → ~$90 USD
- **Total estimado: ~$93 USD**

---

## Arquitectura

```
Ponente (OBS/StreamYard)
    │
    │ RTMP/SRT push
    ▼
Cloudflare Stream (ingest)
    │
    │ Transcodifica (360p, 720p, 1080p)
    │ Distribuye por CDN (300+ datacenters)
    ▼
Tu app (iframe embed) ← Webhook notifica estado
    │
    ▼
Espectadores (CDN de Cloudflare, sin cargar tu VPS)
```

Tu servidor **nunca transmite video**. Solo sirve metadata y la URL del iframe. Todo el peso del streaming lo maneja la CDN de Cloudflare.

---

## Troubleshooting

| Problema | Solución |
|----------|----------|
| Error 502 al crear stream | Verificar `CLOUDFLARE_ACCOUNT_ID` y `CLOUDFLARE_API_TOKEN` |
| Stream no aparece como "live" | Verificar que el webhook esté configurado, o usar `POST /streams/{id}/go-live` manualmente |
| Video no se reproduce | Verificar que `cloudflare_video_uid` se haya asignado (puede tardar unos segundos) |
| OBS no conecta | Verificar que se usa `rtmps://` (con S) y el stream key correcto |
