<?php

declare(strict_types=1);

namespace Security\Tokens;

use Ahc\Jwt\JWT;
use Carbon\CarbonImmutable;
use JetBrains\PhpStorm\ArrayShape;

abstract class AbstractJWT
{
    protected JWT $token;

    public function __construct()
    {
        $this->token = new JWT($this->getSecret(), $this->getAlgorithm(), $this->getMaxAge(), $this->getAllowedSkew());
    }

    abstract protected function getSecret(): string;

    abstract protected function getMaxAge(): int;

    abstract protected function getAllowedSkew(): int;

    protected function getAlgorithm(): string
    {
        return 'HS256';
    }

    protected function getIssuedAt(): int
    {
        return time();
    }

    public function getMaxExpirationTime(): int
    {
        return CarbonImmutable::now()->addSeconds($this->getMaxAge())->unix();
    }

    #[ArrayShape(['iat' => 'int', 'nbf' => 'int'])]
    protected function getDefaultPayload(): array
    {
        return [
            'iat' => $this->getIssuedAt(),
            'nbf' => $this->getIssuedAt(),
        ];
    }

    public function encode(array $payload, int $expirationTime): string
    {
        $payload = array_merge(
            $this->getDefaultPayload(),
            [
                'exp' => $expirationTime,
            ],
            $payload,
        );
        return $this->token->encode($payload);
    }

    public function decode(string $token): array
    {
        return $this->token->decode($token);
    }
}
