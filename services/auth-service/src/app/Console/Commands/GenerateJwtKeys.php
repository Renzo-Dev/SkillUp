<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateJwtKeys extends Command
{
    /**
     * Название и сигнатура консольной команды
     */
    protected $signature = 'jwt:generate-keys 
                            {--bits=4096 : Размер ключа в битах}
                            {--force : Перезаписать существующие ключи}';

    /**
     * Описание команды
     */
    protected $description = 'Генерация RSA пары ключей (public/private) для JWT подписи';

    /**
     * Выполнение команды
     */
    public function handle(): int
    {
        $bits = (int) $this->option('bits');
        $force = $this->option('force');
        
        $jwtDir = storage_path('jwt');
        $privateKeyPath = $jwtDir . '/private.pem';
        $publicKeyPath = $jwtDir . '/public.pem';

        // Проверяем существование ключей
        if (File::exists($privateKeyPath) && !$force) {
            $this->error('RSA ключи уже существуют! Используйте --force для перезаписи.');
            return self::FAILURE;
        }

        // Создаем директорию если не существует
        if (!File::exists($jwtDir)) {
            File::makeDirectory($jwtDir, 0755, true);
            $this->info("Создана директория: {$jwtDir}");
        }

        $this->info("Генерация RSA ключей ({$bits} бит)...");

        try {
            // Генерируем приватный ключ
            $config = [
                'private_key_bits' => $bits,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ];

            $resource = openssl_pkey_new($config);
            
            if ($resource === false) {
                throw new \Exception('Ошибка генерации приватного ключа: ' . openssl_error_string());
            }

            // Экспортируем приватный ключ
            openssl_pkey_export($resource, $privateKey);
            File::put($privateKeyPath, $privateKey);
            File::chmod($privateKeyPath, 0600); // Только владелец может читать
            $this->info("✓ Приватный ключ сохранен: {$privateKeyPath}");

            // Экспортируем публичный ключ
            $publicKeyDetails = openssl_pkey_get_details($resource);
            $publicKey = $publicKeyDetails['key'];
            File::put($publicKeyPath, $publicKey);
            File::chmod($publicKeyPath, 0644); // Все могут читать
            $this->info("✓ Публичный ключ сохранен: {$publicKeyPath}");

            $this->newLine();
            $this->info('🎉 RSA ключи успешно сгенерированы!');
            $this->newLine();
            $this->warn('⚠️  ВАЖНО: Добавьте в .env файл:');
            $this->line("JWT_ALGO=RS256");
            $this->line("JWT_PUBLIC_KEY=file://{$publicKeyPath}");
            $this->line("JWT_PRIVATE_KEY=file://{$privateKeyPath}");
            $this->newLine();
            $this->warn('🔒 Не коммитьте приватный ключ в Git!');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Ошибка генерации ключей: ' . $e->getMessage());
            
            // Очищаем частично созданные файлы
            if (File::exists($privateKeyPath)) {
                File::delete($privateKeyPath);
            }
            if (File::exists($publicKeyPath)) {
                File::delete($publicKeyPath);
            }
            
            return self::FAILURE;
        }
    }
}

