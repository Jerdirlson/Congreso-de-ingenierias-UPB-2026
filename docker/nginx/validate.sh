#!/bin/sh
# Valida nginx.prod.conf con Docker.
# Genera certs SSL dummy y resuelve el upstream 'backend' para evitar falsos negativos.

CONF="$(cd "$(dirname "$0")" && pwd)/nginx.prod.conf"
IMAGE="nginx:1.27-alpine"

docker run --rm \
  --add-host backend:127.0.0.1 \
  -v "${CONF}:/etc/nginx/conf.d/default.conf:ro" \
  "${IMAGE}" sh -c "
    mkdir -p /etc/ssl/congreso &&
    openssl req -x509 -nodes -days 1 -newkey rsa:2048 \
      -keyout /etc/ssl/congreso/privkey.pem \
      -out  /etc/ssl/congreso/fullchain.pem \
      -subj '/CN=localhost' 2>/dev/null &&
    nginx -t
  "
