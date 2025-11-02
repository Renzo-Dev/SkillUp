<?php

namespace App\Support\JWT;

use App\Exceptions\JWT\TokenExpiredException;
use App\Exceptions\JWT\TokenInvalidException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Illuminate\Support\Facades\Log;

class JwtManager
{
    private string $algorithm;
    private string $privateKey;
    private string $publicKey;
    
    /**
     * @param string $privateKeyPath
     * @param string $publicKeyPath
     * @param string $algorithm
     */
    public function __construct(
        string $privateKeyPath,
        string $publicKeyPath,
        string $algorithm = 'RS256'
    ) {
        $this->algorithm = $algorithm;
        $this->privateKey = $this->loadKey($privateKeyPath);
        $this->publicKey = $this->loadKey($publicKeyPath);
    }
    
    /**
     * Закодировать payload в JWT токен
     * 
     * @param array $payload
     * @return string
     * @throws TokenInvalidException
     */
    public function encode(array $payload): string
    {
        try {
            return JWT::encode($payload, $this->privateKey, $this->algorithm);
        } catch (\Exception $e) {
            Log::error('Failed to encode JWT token', [
                'error' => $e->getMessage(),
                'payload_keys' => array_keys($payload)
            ]);
            throw new TokenInvalidException('Не удалось создать JWT токен: ' . $e->getMessage());
        }
    }
    
    /**
     * Декодировать JWT токен и вернуть payload
     * 
     * @param string $token
     * @return array
     * @throws TokenExpiredException
     * @throws TokenInvalidException
     */
    public function decode(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->publicKey, $this->algorithm));
            
            // Конвертируем stdClass в array
            return (array) $decoded;
            
        } catch (ExpiredException $e) {
            Log::info('JWT token expired', ['error' => $e->getMessage()]);
            throw new TokenExpiredException('JWT токен истек');
            
        } catch (SignatureInvalidException $e) {
            Log::warning('JWT signature invalid', ['error' => $e->getMessage()]);
            throw new TokenInvalidException('Недействительная подпись токена');
            
        } catch (BeforeValidException $e) {
            Log::warning('JWT not yet valid (nbf)', ['error' => $e->getMessage()]);
            throw new TokenInvalidException('Токен ещё не действителен');
            
        } catch (\UnexpectedValueException $e) {
            Log::warning('JWT unexpected value', ['error' => $e->getMessage()]);
            throw new TokenInvalidException('Неверный формат токена');
            
        } catch (\DomainException $e) {
            Log::error('JWT domain exception', ['error' => $e->getMessage()]);
            throw new TokenInvalidException('Ошибка алгоритма токена');
            
        } catch (\Exception $e) {
            Log::error('JWT decode error', ['error' => $e->getMessage()]);
            throw new TokenInvalidException('Не удалось декодировать токен: ' . $e->getMessage());
        }
    }
    
    /**
     * Валидировать токен (без выброса исключения)
     * 
     * @param string $token
     * @return bool
     */
    public function validate(string $token): bool
    {
        try {
            $this->decode($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Получить payload без валидации подписи (для отладки)
     * 
     * @param string $token
     * @return array|null
     */
    public function getPayloadWithoutValidation(string $token): ?array
    {
        try {
            // Разбираем токен на части
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                return null;
            }
            
            // Декодируем payload (вторая часть)
            $payload = json_decode(base64_decode($parts[1]), true);
            
            return $payload ?: null;
            
        } catch (\Exception $e) {
            Log::debug('Failed to extract payload without validation', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Проверить истёк ли токен
     * 
     * @param string $token
     * @return bool
     */
    public function isExpired(string $token): bool
    {
        try {
            $payload = $this->getPayloadWithoutValidation($token);
            
            if (!$payload || !isset($payload['exp'])) {
                return true;
            }
            
            return $payload['exp'] < time();
            
        } catch (\Exception $e) {
            return true;
        }
    }
    
    /**
     * Получить время истечения токена
     * 
     * @param string $token
     * @return int|null
     */
    public function getExpirationTime(string $token): ?int
    {
        try {
            $payload = $this->getPayloadWithoutValidation($token);
            return $payload['exp'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Загрузить ключ из файла
     * 
     * @param string $keyPath
     * @return string
     * @throws \RuntimeException
     */
    private function loadKey(string $keyPath): string
    {
        // Убираем префикс file://
        $path = str_replace('file://', '', $keyPath);
        
        if (!file_exists($path)) {
            throw new \RuntimeException("JWT key file not found: {$path}");
        }
        
        $key = file_get_contents($path);
        
        if ($key === false) {
            throw new \RuntimeException("Failed to read JWT key: {$path}");
        }
        
        return $key;
    }
    
    /**
     * Получить алгоритм
     * 
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }
    
    /**
     * Получить публичный ключ
     * 
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->publicKey;
    }
}

