# Notification Platform

Учебный backend-проект на Laravel, который реализует платформу событий и уведомлений с асинхронной обработкой.

Проект сделан для изучения:
- PHP и Laravel
- очередей (queues)
- Redis и RabbitMQ
- фоновых задач (jobs)
- архитектуры event → notification → delivery
- REST API

---

## Общая идея

Система принимает события (events), сохраняет их в базе данных и асинхронно создаёт и отправляет уведомления (notifications).

Пользователь получает ответ от API сразу, а тяжёлая работа выполняется в фоне через очередь.

---

## Архитектура

### Основные сущности

- **Event**
  - type
  - payload (JSON)
  - source
  - occurred_at

- **Notification**
  - event_id
  - channel (log / webhook)
  - recipient
  - status (pending / sent / failed)
  - attempts
  - last_error
  - sent_at

### Поток данных

1. Клиент отправляет `POST /api/events`
2. Event сохраняется в БД
3. В очередь отправляется job `ProcessEvent`
4. `ProcessEvent` создаёт Notification
5. В очередь отправляется job `SendNotification`
6. `SendNotification`:
   - отправляет лог или webhook
   - использует retry и backoff
   - при ошибке помечает notification как failed

---

## Технологии

- PHP 8+
- Laravel
- MySQL
- Redis (очереди и кэш)
- RabbitMQ (message broker)
- Laravel Queue
- Docker (для Redis и RabbitMQ)

---

## Установка

### 1. Клонирование проекта

```bash
git clone <repo-url>
cd NotificationPlatform
