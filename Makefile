# ── Congreso Ingenierías 2026 ─────────────────────────────────────────────────
# Comandos Make para desarrollo con Docker Compose
#
# Uso: make [comando]
# Ejemplo: make up

.PHONY: help up down build rebuild logs ps restart shell-backend shell-frontend artisan migrate fresh composer npm clean

# Comando por defecto: mostrar ayuda
.DEFAULT_GOAL := help

# ── Docker Compose ────────────────────────────────────────────────────────────

up: ## Levantar todos los servicios (modo detached)
	docker compose up -d
	@echo ""
	@echo "  Backend     → http://localhost:8000"
	@echo "  Frontend    → http://localhost:5173"
	@echo "  phpMyAdmin  → http://localhost:8080"
	@echo ""

up-fg: ## Levantar servicios en primer plano (ver logs)
	docker compose up

down: ## Detener y eliminar contenedores
	docker compose down

stop: down ## Alias de down

build: ## Construir imágenes Docker
	docker compose build

rebuild: ## Reconstruir imágenes y levantar servicios
	docker compose build --no-cache
	docker compose up -d

restart: ## Reiniciar todos los servicios
	docker compose restart

ps: ## Listar contenedores en ejecución
	docker compose ps

logs: ## Ver logs de todos los servicios (seguir en vivo)
	docker compose logs -f

logs-backend: ## Ver logs del backend
	docker compose logs -f backend

logs-frontend: ## Ver logs del frontend
	docker compose logs -f frontend

# ── Shell / Ejecución ───────────────────────────────────────────────────────

shell-backend: ## Abrir shell en el contenedor backend (bash)
	docker compose exec backend sh

shell-frontend: ## Abrir shell en el contenedor frontend
	docker compose exec frontend sh

shell-mysql: ## Abrir MySQL CLI
	docker compose exec mysql mysql -u congreso -psecret congreso_db

# ── Laravel / Backend ────────────────────────────────────────────────────────

artisan: ## Ejecutar comando artisan (uso: make artisan CMD="migrate")
	docker compose exec backend php artisan $(CMD)

migrate: ## Ejecutar migraciones
	docker compose exec backend php artisan migrate --force

fresh: ## Migración fresca con seeders
	docker compose exec backend php artisan migrate:fresh --seed --force

composer: ## Ejecutar composer (uso: make composer CMD="install")
	docker compose exec backend composer $(CMD)

# ── Frontend ──────────────────────────────────────────────────────────────────

npm: ## Ejecutar npm en frontend (uso: make npm CMD="run build")
	docker compose exec frontend npm $(CMD)

# ── Limpieza ───────────────────────────────────────────────────────────────────

clean: ## Detener contenedores y eliminar volúmenes
	docker compose down -v
	@echo "Volúmenes eliminados. La próxima vez que ejecutes 'make up' se recreará la base de datos."

# ── Ayuda ────────────────────────────────────────────────────────────────────

help: ## Mostrar esta ayuda
	@echo ""
	@echo "  Congreso Ingenierías 2026 - Comandos Make"
	@echo "  =========================================="
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'
	@echo ""
