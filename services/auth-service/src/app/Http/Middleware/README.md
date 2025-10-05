# JWT Authentication Middleware

## Описание
Middleware для проверки JWT токенов в Laravel приложении.

## Функциональность
- ✅ Проверка JWT токена из заголовка `Authorization: Bearer <token>`
- ✅ Валидация токена и извлечение пользователя
- ✅ Проверка активности пользователя (`is_active`)
- ✅ Обработка различных типов ошибок JWT
- ✅ Логирование успешных и неудачных попыток аутентификации
- ✅ Установка пользователя в контекст запроса

## Использование

### В роутах
```php
// Защитить отдельный роут
Route::get('/protected', [Controller::class, 'method'])->middleware('jwt.auth');

// Защитить группу роутов
Route::middleware('jwt.auth')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

### В контроллере
```php
public function protectedMethod(Request $request)
{
    // Получить аутентифицированного пользователя
    $user = $request->user();
    
    return response()->json([
        'user' => $user,
        'message' => 'Доступ разрешен'
    ]);
}
```

## Обработка ошибок

### 401 Unauthorized
- Токен не предоставлен
- Токен истек
- Токен недействителен
- Пользователь не найден
- Пользователь деактивирован

### 500 Internal Server Error
- Неожиданные ошибки сервера

## Формат ответов

### Успешная аутентификация
Middleware пропускает запрос дальше, пользователь доступен через `$request->user()`

### Ошибка аутентификации
```json
{
    "success": false,
    "message": "Токен истек",
    "error_code": "UNAUTHORIZED"
}
```

## Логирование
- Успешная аутентификация: `info` уровень
- Ошибки токена: `warning` уровень  
- Неожиданные ошибки: `error` уровень

Все логи содержат контекстную информацию (user_id, email, IP).
