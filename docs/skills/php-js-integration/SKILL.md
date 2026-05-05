---
name: php-js-integration
description: Написание кода Rest api на PHP 
---

# Навык: Интеграция PHP и JavaScript (AJAX, JSON, безопасность)

## Описание
Генерация кода, который связывает PHP-бэкенд и JS-фронтенд: создание API-точек, обработка JSON, защита от XSS и CSRF.

## Правила генерации
- PHP предпочтительно, должен возвращать JSON с правильными HTTP-заголовками (`Content-Type: application/json`).
- Выходные данные в PHP перед JSON-кодированием экранировать не нужно, но убедиться, что они в UTF-8.
- JS должен обрабатывать ответы проверкой `response.ok` и парсить JSON через `response.json()`.

## Примеры

### Пример запроса:
> PHP-скрипт, возвращающий список пользователей в JSON, и JS-код для его получения

### Ожидаемый ответ (PHP):
```php
<?php
$users = [
    'users' => [
        ['id' => 1, 'name' => 'Иван'], 
        ['id' => 2, 'name' => 'Мария']
    ]
];

header('Content-Type: application/json');
echo json_encode($users, JSON_UNESCAPED_UNICODE);
```