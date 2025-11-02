<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Contracts\Services\JwtServiceInterface;
use App\Support\JWT\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Тест проверки работы JWT с RS256 алгоритмом (Firebase PHP-JWT)
 */
class JwtRS256Test extends TestCase
{
    use RefreshDatabase;
    
    private JwtServiceInterface $jwtService;
    private JwtManager $jwtManager;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = app(JwtServiceInterface::class);
        $this->jwtManager = app(JwtManager::class);
    }

    /**
     * Тест: Проверка алгоритма в конфиге
     */
    public function test_jwt_algorithm_is_rs256(): void
    {
        $algorithm = config('jwt.algo');
        
        $this->assertEquals('RS256', $algorithm, 'JWT алгоритм должен быть RS256');
    }

    /**
     * Тест: Проверка наличия публичного и приватного ключа
     */
    public function test_jwt_keys_are_configured(): void
    {
        $publicKey = config('jwt.keys.public');
        $privateKey = config('jwt.keys.private');
        
        $this->assertNotEmpty($publicKey, 'Публичный ключ должен быть настроен');
        $this->assertNotEmpty($privateKey, 'Приватный ключ должен быть настроен');
        
        $this->assertStringContainsString('file://', $publicKey, 'Публичный ключ должен быть file:// путь');
        $this->assertStringContainsString('file://', $privateKey, 'Приватный ключ должен быть file:// путь');
    }

    /**
     * Тест: Генерация JWT токена с RS256
     */
    public function test_can_generate_jwt_token_with_rs256(): void
    {
        // Создаем тестового пользователя
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Генерируем токен через JwtService
        $token = $this->jwtService->generate($user);
        
        $this->assertNotEmpty($token, 'Токен должен быть сгенерирован');
        $this->assertIsString($token, 'Токен должен быть строкой');
        
        // JWT токен состоит из 3 частей: header.payload.signature
        $parts = explode('.', $token);
        $this->assertCount(3, $parts, 'JWT должен состоять из 3 частей');
    }

    /**
     * Тест: Валидация JWT токена с RS256
     */
    public function test_can_validate_jwt_token_with_rs256(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Генерируем токен
        $token = $this->jwtService->generate($user);
        
        // Валидируем и декодируем токен
        $payload = $this->jwtService->decode($token);
        
        $this->assertNotNull($payload, 'Payload должен быть доступен');
        $this->assertEquals((string) $user->id, $payload['sub'], 'User ID должен совпадать');
    }

    /**
     * Тест: Декодирование токена возвращает правильные данные
     */
    public function test_decoded_token_contains_correct_data(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $token = $this->jwtService->generate($user);
        $payload = $this->jwtService->decode($token);
        
        // Проверяем required claims
        $this->assertArrayHasKey('iss', $payload, 'Должен быть claim iss');
        $this->assertArrayHasKey('iat', $payload, 'Должен быть claim iat');
        $this->assertArrayHasKey('exp', $payload, 'Должен быть claim exp');
        $this->assertArrayHasKey('nbf', $payload, 'Должен быть claim nbf');
        $this->assertArrayHasKey('sub', $payload, 'Должен быть claim sub');
        $this->assertArrayHasKey('jti', $payload, 'Должен быть claim jti');
        
        // Проверяем user ID
        $this->assertEquals((string) $user->id, $payload['sub']);
    }

    /**
     * Тест: Токен использует RS256 (не HS256)
     */
    public function test_token_uses_rs256_algorithm(): void
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generate($user);
        
        // Декодируем header (первая часть JWT)
        $parts = explode('.', $token);
        $header = json_decode(base64_decode($parts[0]), true);
        
        $this->assertArrayHasKey('alg', $header, 'Header должен содержать alg');
        $this->assertEquals('RS256', $header['alg'], 'Алгоритм должен быть RS256');
    }

    /**
     * Тест: Невалидный токен выбрасывает исключение
     */
    public function test_invalid_token_throws_exception(): void
    {
        $this->expectException(\App\Exceptions\JWT\TokenInvalidException::class);
        
        $invalidToken = 'invalid.token.here';
        $this->jwtManager->decode($invalidToken);
    }

    /**
     * Тест: Истекший токен можно определить
     */
    public function test_can_check_token_expiration(): void
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generate($user);
        
        $payload = $this->jwtService->decode($token);
        
        $exp = $payload['exp'];
        $now = time();
        
        // Токен должен быть валиден (exp > now)
        $this->assertGreaterThan($now, $exp, 'Токен не должен быть истекшим');
        
        // Проверяем TTL (должно быть примерно 60 минут = 3600 секунд)
        $ttl = $exp - $payload['iat'];
        $this->assertEqualsWithDelta(3600, $ttl, 10, 'TTL должен быть ~60 минут');
    }
}

