# Production-деплой AlcoTracker

Сервер: **2 vCPU / 2 GB RAM / 40 GB NVMe** (Stdp C2-M2-D40)

## 1. DNS

У регистратора домена:

```
A    alcotracker.ru       → IP_СЕРВЕРА
A    api.alcotracker.ru   → IP_СЕРВЕРА
```

## 2. Первичная настройка сервера

```bash
ssh root@IP_СЕРВЕРА

apt update && apt upgrade -y
curl -fsSL https://get.docker.com | sh
apt install -y docker-compose-plugin git ufw

ufw allow 22,80,443/tcp
ufw enable
```

## 3. Клонирование и конфигурация

```bash
git clone https://github.com/YOUR_USER/alcotracker.git /opt/alcotracker
cd /opt/alcotracker

# Переменные Docker Compose
cp .env.prod.example .env.prod
nano .env.prod   # домены + пароли MySQL

# Laravel .env
cp backend/.env.production.example backend/.env
nano backend/.env   # APP_URL, SANCTUM, CORS, MAIL, пароль БД
```

### Что заполнить в `.env.prod`

| Переменная | Пример |
|------------|--------|
| `FRONTEND_DOMAIN` | `alcotracker.ru` |
| `API_DOMAIN` | `api.alcotracker.ru` |
| `DB_PASSWORD` | случайный пароль 32+ символов |
| `DB_ROOT_PASSWORD` | другой случайный пароль |

### Что заполнить в `backend/.env`

| Переменная | Пример |
|------------|--------|
| `APP_URL` | `https://api.alcotracker.ru` |
| `SANCTUM_STATEFUL_DOMAINS` | `alcotracker.ru` |
| `SESSION_DOMAIN` | `.alcotracker.ru` |
| `CORS_ALLOWED_ORIGINS` | `https://alcotracker.ru` |
| `DB_PASSWORD` | тот же, что в `.env.prod` |

## 4. Деплой

```bash
chmod +x scripts/deploy.sh
bash scripts/deploy.sh
```

Скрипт автоматически:
- создаёт swap 2 GB (обязательно на 2 GB RAM)
- ставит зависимости Composer
- генерирует `APP_KEY`
- собирает и запускает контейнеры
- выполняет миграции и кэширует конфиг

## 5. Проверка

```bash
# Статус контейнеров
docker compose -f docker-compose.prod.yml --env-file .env.prod ps

# Логи
docker compose -f docker-compose.prod.yml --env-file .env.prod logs -f

# Память
free -h && docker stats --no-stream
```

Открой в браузере:
- `https://alcotracker.ru` — фронтенд
- `https://api.alcotracker.ru/api` — API (должен ответить)

## Лимиты памяти (под 2 GB)

| Контейнер | Лимит |
|-----------|-------|
| MySQL | 512 MB |
| Nuxt (Node) | 512 MB |
| PHP-FPM | 256 MB |
| Nginx | 64 MB |
| Caddy | 64 MB |
| **Итого** | ~1.4 GB + swap |

MySQL дополнительно настроен через `docker/mysql/my.cnf` (buffer pool 256 MB).

## Обновление после git pull

```bash
cd /opt/alcotracker
git pull
bash scripts/deploy.sh
```

## Бэкап БД (cron, раз в день)

```bash
mkdir -p /opt/backups
crontab -e
```

```
0 3 * * * docker exec mysql_db mysqldump -u laravel -pПАРОЛЬ alcotracker | gzip > /opt/backups/db_$(date +\%Y\%m\%d).sql.gz
```

## Очистка диска (раз в месяц)

```bash
docker system prune -f
docker image prune -a -f --filter "until=720h"
```

40 GB хватит с запасом при регулярной очистке.

## Troubleshooting

**502 Bad Gateway** — контейнер упал из-за нехватки RAM:
```bash
docker stats
free -h
# Перезапуск
docker compose -f docker-compose.prod.yml --env-file .env.prod restart
```

**CORS / 419 CSRF** — проверь `CORS_ALLOWED_ORIGINS`, `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN` в `backend/.env`.

**Фронт не ходит на API** — пересобери фронт (API URL вшивается в билд):
```bash
docker compose -f docker-compose.prod.yml --env-file .env.prod up -d --build frontend
```
