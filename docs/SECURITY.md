# Seguridad

Registro de las medidas de seguridad implementadas y pendientes en el proyecto.

---

## Medidas implementadas

### Infraestructura del VPS

| Medida | Estado | Detalle |
|--------|--------|---------|
| Firewall ufw | ✅ Activo | Solo puertos 22, 80, 443 abiertos |
| Fail2ban | ✅ Activo | Bloquea IPs con múltiples intentos fallidos de SSH |
| Puertos internos cerrados | ✅ | MySQL y Redis no expuestos al exterior |

### Nginx

| Medida | Estado | Detalle |
|--------|--------|---------|
| `server_tokens off` | ✅ | No revela la versión de Nginx en headers ni errores |
| Rate limiting API | ✅ | 30 req/s por IP en `/api`, burst de 60 |
| `client_max_body_size` | ✅ | Límite de 110 MB por request |
| X-Frame-Options | ✅ | `SAMEORIGIN` — previene clickjacking |
| X-Content-Type-Options | ✅ | `nosniff` — previene MIME sniffing |
| X-XSS-Protection | ✅ | `1; mode=block` |
| Referrer-Policy | ✅ | `strict-origin-when-cross-origin` |
| Permissions-Policy | ✅ | Deshabilita geolocation, micrófono y cámara |

### Laravel (Backend)

| Medida | Estado | Detalle |
|--------|--------|---------|
| APP_DEBUG=false | ✅ | No expone stack traces en producción |
| APP_ENV=production | ✅ | Modo producción activo |
| Throttle rutas públicas | ✅ | 120 req/min por IP |
| Throttle rutas autenticadas | ✅ | 60 req/min por usuario |
| Health endpoint sin versiones | ✅ | No expone versiones de PHP ni Laravel |
| Errores internos sin detalle | ✅ | El catch no devuelve file/line al cliente |
| Sanctum (autenticación) | ✅ | Tokens seguros para la API |
| Eloquent ORM | ✅ | Previene inyección SQL por defecto |
| CORS restringido | ✅ | Solo dominios autorizados |

### CI/CD

| Medida | Estado | Detalle |
|--------|--------|---------|
| Tests requeridos | ✅ | Deploy bloqueado si los tests fallan |
| Secretos en GitHub Secrets | ✅ | No hay credenciales en el código |
| Self-hosted runner | ✅ | El runner corre en el VPS, no expone SSH |
| Deploy solo en `main` | ✅ | PRs solo ejecutan tests, no deploy |

### Docker

| Medida | Estado | Detalle |
|--------|--------|---------|
| MySQL sin puerto externo | ✅ | Solo accesible desde la red Docker interna |
| Redis sin puerto externo | ✅ | Solo accesible desde la red Docker interna |
| Red Docker aislada | ✅ | `cgr-network` bridge privado |

---

## Pendiente

| Medida | Prioridad | Detalle |
|--------|-----------|---------|
| HTTPS / TLS | Alta | Esperando certificado wildcard del CTIC |
| HSTS header | Alta | Requiere HTTPS activo primero |
| Content-Security-Policy | Media | Definir política CSP para el frontend |
| SSH solo con llaves | Media | Actualmente usa contraseña — requiere configurar llaves primero |
| Contraseña a Redis | Media | Redis sin autenticación en producción |
| Rotación de contraseñas DB | Baja | Establecer política de cambio periódico |

---

## Configurar HTTPS cuando llegue el certificado

Cuando el CTIC entregue los archivos del certificado wildcard:

```bash
# 1. Crear carpeta y copiar los archivos
sudo mkdir -p /etc/ssl/congreso
sudo cp certificate.crt /etc/ssl/congreso/
sudo cp private.key /etc/ssl/congreso/
sudo chmod 600 /etc/ssl/congreso/private.key
```

Luego actualizar `docker/nginx/nginx.prod.conf` para agregar:

```nginx
server {
    listen 80;
    server_name congreso2026.bucaramanga.upb.edu.co;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name congreso2026.bucaramanga.upb.edu.co;

    ssl_certificate     /etc/ssl/congreso/certificate.crt;
    ssl_certificate_key /etc/ssl/congreso/private.key;
    ssl_protocols       TLSv1.2 TLSv1.3;

    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # ... resto de la configuración ...
}
```

Y descomentar el volumen SSL en `docker-compose.prod.yml`:
```yaml
- /etc/ssl/congreso:/etc/ssl/congreso:ro
```

---

## Agregar contraseña a Redis

Editar `.env` en el VPS:
```
REDIS_PASSWORD=password_seguro_aqui
```

Actualizar `docker-compose.prod.yml` para pasar la contraseña a Redis y al backend.

---

## Historial de cambios de seguridad

| Fecha | Cambio |
|-------|--------|
| 2026-02-21 | Despliegue inicial en VPS |
| 2026-02-21 | Firewall ufw configurado (puertos 22, 80, 443) |
| 2026-02-21 | Fail2ban instalado y activo |
| 2026-02-21 | Nginx: server_tokens off, rate limiting, security headers |
| 2026-02-21 | Laravel: throttle en rutas públicas y autenticadas |
| 2026-02-21 | Health endpoint: removidas versiones de PHP y Laravel |
| 2026-02-21 | CI/CD: deploy bloqueado si tests fallan |
