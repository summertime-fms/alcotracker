# Инструкция по деплою фронтенда

## Проблема

После деплоя на production фронтенд не делает запросы на бекенд и открывает интерфейс без проверки аутентификации.

## Решение

### 1. Настройка переменной окружения API_BASE_URL

**КРИТИЧЕСКИ ВАЖНО**: Для production необходимо установить переменную окружения `API_BASE_URL` с URL вашего бекенда.

#### Для Docker:

**ВАЖНО**: В Nuxt переменные окружения встраиваются в билд, поэтому `API_BASE_URL` нужно передавать как build argument.

Добавьте в `docker-compose.yml`:

```yaml
frontend:
  build:
    context: ./frontend
    dockerfile: Dockerfile
    args:
      - API_BASE_URL=http://your-backend-domain.com
  environment:
    - API_BASE_URL=http://your-backend-domain.com
```

Или при сборке через docker build:

```bash
docker build \
  --build-arg API_BASE_URL=http://your-backend-domain.com \
  -t alcotracker-frontend \
  ./frontend
```

#### Для обычного деплоя:

Создайте файл `.env.production` в папке `frontend/`:

```env
API_BASE_URL=http://your-backend-domain.com
```

Или установите переменную окружения перед сборкой:

```bash
export API_BASE_URL=http://your-backend-domain.com
npm run build
```

### 2. Проверка CORS на бекенде

Убедитесь, что на бекенде настроен CORS для вашего фронтенд домена:

```php
// config/cors.php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['http://your-frontend-domain.com'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

### 3. Проверка настроек Sanctum

Убедитесь, что в `config/sanctum.php` правильно настроены домены:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
```

### 4. Проверка после деплоя

После деплоя проверьте в консоли браузера:

1. Откройте DevTools (F12)
2. Перейдите на вкладку Console
3. Проверьте наличие предупреждений о API URL
4. Перейдите на вкладку Network
5. Убедитесь, что запросы идут на правильный URL бекенда

### 5. Что было исправлено

- ✅ Middleware теперь всегда проверяет аутентификацию, даже если пользователь есть в состоянии
- ✅ Добавлена обработка ошибок при недоступности API
- ✅ Добавлены предупреждения в консоль, если API URL не настроен
- ✅ Улучшена инициализация аутентификации при загрузке приложения

## Примеры конфигурации

### Локальная разработка (docker-compose.yml)
```yaml
frontend:
  environment:
    - API_BASE_URL=http://nginx_server:80
```

### Production с отдельными доменами
```yaml
frontend:
  environment:
    - API_BASE_URL=https://api.yourdomain.com
```

### Production с одним доменом (subpath)
```yaml
frontend:
  environment:
    - API_BASE_URL=https://yourdomain.com
```

В этом случае убедитесь, что бекенд доступен по пути `/api` на том же домене.
