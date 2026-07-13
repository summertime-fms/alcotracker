# Настройка системы авторизации AlcoTracker

Система авторизации успешно реализована! Этот документ содержит инструкции по настройке и запуску.

## Что было реализовано

### Backend (Laravel + Sanctum)

✅ **Установлен и настроен Laravel Sanctum** для SPA аутентификации

- Cookie-based аутентификация
- CORS настроен для работы с Nuxt frontend
- Middleware для защиты API endpoints

✅ **Архитектура: Fat Services, Slim Controllers**

- `AuthService` - логика регистрации, входа, выхода
- `PasswordResetService` - логика восстановления пароля
- Контроллеры - тонкий слой, только вызовы сервисов

✅ **Валидация запросов** через Request классы:

- `RegisterRequest` - валидация регистрации (name, email, password)
- `LoginRequest` - валидация входа
- `ForgotPasswordRequest` - валидация запроса сброса пароля
- `ResetPasswordRequest` - валидация сброса пароля

✅ **API Routes**:

- `POST /api/auth/register` - регистрация
- `POST /api/auth/login` - вход
- `POST /api/auth/logout` - выход (защищен)
- `GET /api/auth/user` - получение текущего пользователя (защищен)
- `POST /api/auth/forgot-password` - запрос сброса пароля
- `POST /api/auth/reset-password` - сброс пароля

### Frontend (Nuxt 4)

✅ **Дизайн-система** (минимализм, светлые тона):

- CSS переменные для цветов, spacing, typography
- Шрифт Roboto из Google Fonts
- Анимации и transitions
- Цветовая палитра: светло-серый, белый, голубые акценты

✅ **Composables**:

- `useApi` - работа с API (CSRF, credentials)
- `useAuth` - управление аутентификацией и состоянием пользователя

✅ **Middleware**:

- `auth.ts` - защита страниц (только для авторизованных)
- `guest.ts` - страницы для гостей (только для неавторизованных)

✅ **UI Компоненты**:

- `Button` - универсальная кнопка (variants: primary, secondary, outline, ghost, danger)
- `Input` - поле ввода с валидацией и ошибками

✅ **Auth Компоненты**:

- `LoginForm` - форма входа
- `RegisterForm` - форма регистрации
- `ForgotPasswordForm` - форма запроса сброса пароля
- `ResetPasswordForm` - форма установки нового пароля

✅ **Страницы**:

- `/login` - вход (guest)
- `/register` - регистрация (guest)
- `/forgot-password` - восстановление пароля (guest)
- `/reset-password` - сброс пароля (guest)
- `/` - главная страница (защищена)
- `/dashboard` - дашборд (защищен)

## Настройка и запуск

### Backend (Laravel)

1. **Настройка .env файла**:

Убедитесь, что в файле `/backend/.env` есть следующие настройки:

```env
APP_URL=http://localhost:8080

# Настройки базы данных
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=alcotracker
DB_USERNAME=laravel
DB_PASSWORD=secret

# Sanctum настройки для SPA
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DOMAIN=localhost
SESSION_DRIVER=cookie
```

2. **Запуск через Docker**:

```bash
# Из корня проекта
docker-compose up -d

# Проверка, что контейнеры запущены
docker-compose ps
```

3. **Миграции базы данных** (если еще не выполнены):

```bash
docker exec -it laravel_app php artisan migrate
```

### Frontend (Nuxt)

Frontend уже настроен и будет запущен через docker-compose на порту 3000.

1. **Проверка работы**:

```bash
# Frontend доступен по адресу
http://localhost:3000
```

### Полная перезагрузка

Если нужно перезапустить все сервисы:

```bash
docker-compose down
docker-compose up -d
```

## Тестирование

### 1. Регистрация нового пользователя

1. Откройте http://localhost:3000/register
2. Заполните форму:
   - Имя: Test User
   - Email: test@example.com
   - Пароль: password123
   - Подтверждение пароля: password123
3. Нажмите "Зарегистрироваться"
4. Вы будете перенаправлены на главную страницу

### 2. Вход в систему

1. Откройте http://localhost:3000/login
2. Введите данные:
   - Email: test@example.com
   - Пароль: password123
3. Нажмите "Войти"
4. Вы будете перенаправлены на главную страницу

### 3. Восстановление пароля

1. Откройте http://localhost:3000/forgot-password
2. Введите email
3. Нажмите "Отправить ссылку"

**Примечание**: Для полноценной работы восстановления пароля нужно настроить отправку email в Laravel. Для разработки можно использовать Mailhog:

Добавьте в `docker-compose.yml`:

```yaml
mailhog:
  image: mailhog/mailhog
  ports:
    - "1025:1025"
    - "8025:8025"
```

И в `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@alcotracker.local
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Проверка защищенных страниц

1. Будучи авторизованным, перейдите на:
   - http://localhost:3000/ (главная)
   - http://localhost:3000/dashboard (дашборд)
2. Нажмите "Выйти"
3. Попробуйте открыть эти страницы снова - вас перенаправит на `/login`

## Структура проекта

```
alcotracker/
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Auth/
│   │   │   │   ├── AuthController.php (slim)
│   │   │   │   └── PasswordResetController.php (slim)
│   │   │   └── Requests/Auth/
│   │   │       ├── RegisterRequest.php
│   │   │       ├── LoginRequest.php
│   │   │       ├── ForgotPasswordRequest.php
│   │   │       └── ResetPasswordRequest.php
│   │   ├── Models/
│   │   │   └── User.php (+ HasApiTokens trait)
│   │   └── Services/Auth/
│   │       ├── AuthService.php (fat - бизнес-логика)
│   │       └── PasswordResetService.php (fat - бизнес-логика)
│   ├── config/
│   │   ├── sanctum.php (SPA настройки)
│   │   └── cors.php (credentials: true)
│   └── routes/
│       └── api.php (auth routes)
│
└── frontend/
    ├── assets/css/
    │   ├── variables.css (дизайн-система)
    │   └── main.css (глобальные стили)
    ├── components/
    │   ├── auth/
    │   │   ├── LoginForm.vue
    │   │   ├── RegisterForm.vue
    │   │   ├── ForgotPasswordForm.vue
    │   │   └── ResetPasswordForm.vue
    │   └── ui/
    │       ├── Button.vue
    │       └── Input.vue
    ├── composables/
    │   ├── useApi.ts (CSRF, fetch wrapper)
    │   └── useAuth.ts (auth state & methods)
    ├── middleware/
    │   ├── auth.ts (защита страниц)
    │   └── guest.ts (гостевые страницы)
    ├── pages/
    │   ├── login.vue
    │   ├── register.vue
    │   ├── forgot-password.vue
    │   ├── reset-password.vue
    │   ├── index.vue (защищена)
    │   └── dashboard.vue (защищена)
    └── nuxt.config.ts
```

## Особенности реализации

### Backend

- **Fat Services, Slim Controllers**: вся бизнес-логика в сервисах, контроллеры только валидируют и вызывают сервисы
- **Dependency Injection**: сервисы внедряются через конструктор контроллера
- **Request валидация**: отдельные классы для каждого типа запроса с русскими сообщениями об ошибках
- **Sanctum SPA mode**: cookie-based аутентификация без токенов в localStorage

### Frontend

- **Минималистичный дизайн**: светлые тона, белый, голубые акценты
- **Google Fonts Roboto**: для красивой типографики
- **CSS переменные**: легко менять цвета и spacing
- **Реактивное состояние**: через useState и computed
- **Автоматический CSRF**: useApi сам получает CSRF токен перед мутирующими запросами
- **Type safety**: TypeScript для всех composables и компонентов

## Следующие шаги

1. **Email верификация** (опционально):

   - Добавить проверку email после регистрации
   - Реализовать страницу "Подтвердите email"

2. **Помнить меня**:

   - Добавить чекбокс "Запомнить меня" в форму входа
   - Настроить длительность сессии

3. **Профиль пользователя**:

   - Страница редактирования профиля
   - Изменение пароля
   - Загрузка аватара

4. **Роли и права**:
   - Система ролей (admin, user)
   - Middleware для проверки прав
   - API endpoints с разными уровнями доступа

## Troubleshooting

### Проблема: CORS ошибки

**Решение**: Убедитесь, что:

- В `backend/config/cors.php` в `supports_credentials` стоит `true`
- В `allowed_origins` добавлен `http://localhost:3000`
- Sanctum middleware добавлен в `bootstrap/app.php`

### Проблема: 419 CSRF token mismatch

**Решение**:

- Проверьте, что `useApi` вызывает `getCsrfCookie` перед POST запросами
- Убедитесь, что в запросах указан `credentials: 'include'`
- Проверьте SESSION_DOMAIN в .env

### Проблема: Пользователь не авторизуется

**Решение**:

- Проверьте настройки SANCTUM_STATEFUL_DOMAINS в .env
- Убедитесь, что фронтенд и бэкенд на одном домене (localhost)
- Очистите cookies браузера и попробуйте снова

## Контакты и поддержка

Если возникли вопросы или проблемы с системой авторизации, проверьте:

- Логи Laravel: `docker-compose logs app`
- Логи Nuxt: `docker-compose logs frontend`
- Browser DevTools: Network и Console tabs

---

Система готова к использованию! 🚀



