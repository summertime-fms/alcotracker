#!/usr/bin/env bash
set -euo pipefail

# Деплой AlcoTracker на VPS 2 vCPU / 2 GB RAM
# Запуск: bash scripts/deploy.sh

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PROJECT_DIR"

COMPOSE="docker compose -f docker-compose.prod.yml --env-file .env.prod"

echo "==> Проверка .env.prod"
if [ ! -f .env.prod ]; then
  echo "Создай .env.prod из .env.prod.example и заполни домены/пароли"
  exit 1
fi

# shellcheck disable=SC1091
source .env.prod

echo "==> Проверка backend/.env"
if [ ! -f backend/.env ]; then
  cp backend/.env.production.example backend/.env
  echo "Создан backend/.env — отредактируй его перед продолжением!"
  exit 1
fi

echo "==> Swap 2 GB (если ещё нет)"
if [ ! -f /swapfile ]; then
  fallocate -l 2G /swapfile
  chmod 600 /swapfile
  mkswap /swapfile
  swapon /swapfile
  grep -q '/swapfile' /etc/fstab || echo '/swapfile none swap sw 0 0' >> /etc/fstab
  echo "Swap включён"
else
  echo "Swap уже есть"
fi

echo "==> Composer install (backend)"
docker compose -f docker-compose.prod.yml --env-file .env.prod run --rm --no-deps app \
  composer install --no-dev --optimize-autoloader

echo "==> APP_KEY (если пустой)"
if ! grep -q '^APP_KEY=base64:' backend/.env; then
  $COMPOSE run --rm --no-deps app php artisan key:generate --force
fi

echo "==> Сборка и запуск контейнеров"
$COMPOSE up -d --build

echo "==> Ожидание MySQL..."
sleep 15

echo "==> Миграции"
docker exec laravel_app php artisan migrate --force

echo "==> Кэш Laravel"
docker exec laravel_app php artisan config:cache
docker exec laravel_app php artisan route:cache
docker exec laravel_app php artisan view:cache

echo "==> Права на storage"
docker exec laravel_app chown -R www-data:www-data storage bootstrap/cache
docker exec laravel_app chmod -R 775 storage bootstrap/cache

echo ""
echo "Готово!"
echo "  Frontend: https://${FRONTEND_DOMAIN}"
echo "  API:      https://${API_DOMAIN}"
echo ""
echo "Проверка: docker compose -f docker-compose.prod.yml --env-file .env.prod ps"
