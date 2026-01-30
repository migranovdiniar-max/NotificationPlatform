# NotificationPlatform

Проект принимает события и создаёт уведомления. Уведомления отправляются через очередь. Каналы: log и webhook.

## Что есть
- POST /api/events — создаёт событие и ставит jobs в очередь.
- GET /api/notifications — список уведомлений.
- POST /api/notifications/{notification}/retry — повтор для failed.
- GET /api/webhook-test — тестовый webhook (POST).

## Требования
- PHP 8.2+
- Composer
- Node.js 18+
- Redis или RabbitMQ для очередей

## Установка
1) Установи зависимости.
   - composer install
   - npm install
2) Создай .env и ключ.
   - cp .env.example .env
   - php artisan key:generate
3) Подними базу и миграции.
   - php artisan migrate

## Запуск
1) Запусти бэкенд.
   - php artisan serve
2) Запусти фронт.
   - npm run dev
3) Запусти воркер очереди.
   - php artisan queue:work

Открой http://127.0.0.1:8000

## Очереди
По умолчанию стоит redis. В .env можно переключить:
- QUEUE_CONNECTION=redis
- QUEUE_CONNECTION=rabbitmq

### Redis
Запуск через Docker:
docker run -d --name redis -p 6379:6379 redis:7-alpine

### RabbitMQ
Запуск через Docker:
docker run -d --name rabbitmq -p 5672:5672 -p 15672:15672 rabbitmq:3-management

UI: http://127.0.0.1:15672 (guest/guest)

## Проверка
1) Открой фронт и создай событие.
2) Проверь, что статус уведомления меняется с pending на sent или failed.
3) Для webhook можно указать URL http://127.0.0.1:8000/api/webhook-test
4) Для имитации ошибки укажи recipient=fail и попробуй retry.
