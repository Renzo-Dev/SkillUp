<?php

namespace App\Services\Jwt;

use GuzzleHttp\Client;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Arr;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;

class JwtVerifier
{
    public function __construct(
        private CacheRepository $cache,
        private Client $httpClient,
        private array $config,
    ) {
    }

    public function verify(string $token): UnencryptedToken
    {
        $parser = new Parser(new JoseEncoder());
        /** @var UnencryptedToken $parsed */
        $parsed = $parser->parse($token);

        $kid = $parsed->headers()->get('kid');
        $publicKey = $this->getPublicKey($kid);

        $configuration = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText(''),
            InMemory::plainText($publicKey),
        );

        $constraints = [
            new \Lcobucci\JWT\Validation\Constraint\SignedWith($configuration->signer(), $configuration->verificationKey()),
        ];

        foreach ($constraints as $constraint) {
            $configuration->validator()->assert($parsed, $constraint);
        }

        return $parsed;
    }

    private function getPublicKey(?string $kid): string
    {
        if ($cached = $this->cache->get($this->cacheKey($kid))) {
            return $cached;
        }

        if ($key = $this->config['public_key'] ?? null) {
            $this->cache->put($this->cacheKey($kid), $key, $this->config['jwks']['cache_ttl']);
            return $key;
        }

        $jwks = $this->fetchJwks();
        $matching = collect($jwks['keys'] ?? [])->first(function ($entry) use ($kid) {
            return $entry['kid'] ?? null === $kid;
        });

        if (!$matching) {
            throw new \RuntimeException('Public key not found');
        }

        $pem = $this->convertToPem($matching['n'], $matching['e']);
        $this->cache->put($this->cacheKey($kid), $pem, $this->config['jwks']['cache_ttl']);

        return $pem;
    }

    private function fetchJwks(): array
    {
        if (!$this->config['jwks']['url']) {
            throw new \RuntimeException('JWKS url not configured');
        }

        $response = $this->httpClient->get($this->config['jwks']['url'], [
            'timeout' => $this->config['jwks']['timeout'] ?? 3,
        ]);

        return json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }

    private function convertToPem(string $n, string $e): string
    {
        $modulus = $this->base64UrlDecode($n);
        $exponent = $this->base64UrlDecode($e);

        $modulus = chr(0x00) . $modulus;

        $components = [
            $this->encodeLength(strlen($modulus)),
            $modulus,
            $this->encodeLength(strlen($exponent)),
            $exponent,
        ];

        $rsaPublicKey = chr(0x30) .
            $this->encodeLength(strlen($components[0]) + strlen($components[1]) + strlen($components[2]) + strlen($components[3])) .
            chr(0x02) . $components[0] . $components[1] . chr(0x02) . $components[2] . $components[3];

        $rsaOID = chr(0x30) . chr(0x0d) . chr(0x06) . chr(0x09) .
            chr(0x2a) . chr(0x86) . chr(0x48) . chr(0x86) .
            chr(0xf7) . chr(0x0d) . chr(0x01) . chr(0x01) .
            chr(0x01) . chr(0x05) . chr(0x00);

        $rsaPublicKey = chr(0x30) .
            $this->encodeLength(strlen($rsaOID) + 2 + strlen($rsaPublicKey)) .
            $rsaOID . chr(0x03) . $this->encodeLength(strlen($rsaPublicKey) + 1) .
            chr(0x00) . $rsaPublicKey;

        return "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split(base64_encode($rsaPublicKey), 64, "\n") .
            "-----END PUBLIC KEY-----\n";
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'), true);
    }

    private function encodeLength(int $length): string
    {
        if ($length <= 0x7F) {
            return chr($length);
        }

        $temp = ltrim(pack('N', $length), "\x00");

        return chr(0x80 | strlen($temp)) . $temp;
    }

    private function cacheKey(?string $kid): string
    {
        return Arr::toDot(['jwt', $kid ?? 'default']);
    }
}

