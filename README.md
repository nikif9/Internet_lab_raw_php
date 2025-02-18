# User Management REST API с PSR-4 автозагрузкой (Pure PHP)

Этот проект реализует REST API для управления пользователями на чистом PHP с использованием PSR-4 автозагрузки.

## Установка

1. **Установите зависимости с помощью Composer:**
   ```bash
   composer install 
   ```
2. **Запустите встроенный PHP-сервер:**
   ```bash
   php -S localhost:8000 -t public
   ```

Теперь API доступно по адресу: [http://localhost:8000](http://localhost:8000)

## Реализованные методы

Некоторые методы требуют авторизации. Для этого в заголовок запроса необходимо добавить:
```
Authorization: Bearer <token>
```

### 1. Создание пользователя
- **URL:** `/users`
- **Метод:** `POST`
- **Тело запроса (JSON):**
  ```json
  {
    "username": "ваше_имя",
    "password": "ваш_пароль",
    "email": "email@пример.com"
  }
  ```
- **Ответ:**
  - **201 Created**: Пользователь создан, возвращается ID.
  - **Ошибки:** `400` (отсутствуют обязательные поля), `500` (ошибка сервера)

### 2. Получение информации о пользователе
- **URL:** `/users/{id}`
- **Метод:** `GET`
- **Ответ:**
  ```json
  {
    "id": 1,
    "username": "ваше_имя",
    "email": "email@пример.com",
    "created_at": "2025-02-18 12:34:56"
  }
  ```
  - **Ошибки:** `404` (пользователь не найден)

### 3. Обновление информации о пользователе
- **URL:** `/users/{id}`
- **Метод:** `PUT`
- **Требуется авторизация**
- **Тело запроса (JSON):**
  ```json
  {
    "username": "новое_имя",
    "password": "новый_пароль",
    "email": "новый_email@пример.com"
  }
  ```
- **Ответ:**
  - **200 OK**: Пользователь обновлён.
  - **Ошибки:** `400` (нет полей для обновления), `500` (ошибка сервера)

### 4. Удаление пользователя
- **URL:** `/users/{id}`
- **Метод:** `DELETE`
- **Требуется авторизация**
- **Ответ:**
  - **200 OK**: Пользователь удалён.
  - **Ошибки:** `500` (ошибка сервера)

### 5. Авторизация пользователя (Login)
- **URL:** `/login`
- **Метод:** `POST`
- **Тело запроса (JSON):**
  ```json
  {
    "username": "ваше_имя",
    "password": "ваш_пароль",
    "token": "ваш_токен"
  }
  ```
- **Ответ:**
  - **200 OK**: Авторизация успешна, возвращается токен.
  - **Ошибки:** `400` (отсутствуют обязательные поля), `401` (неверные учётные данные)

## Примечания
- Для хранения данных используется SQLite, файл базы данных `database.sqlite` создаётся автоматически.
- Пароли хранятся в виде хэшей с использованием `password_hash()`.