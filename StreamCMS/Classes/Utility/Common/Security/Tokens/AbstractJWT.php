<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Common\Security\Tokens;

use Ahc\Jwt\JWT;
use JetBrains\PhpStorm\ArrayShape;

abstract class AbstractJWT
{
    protected JWT $token;

    abstract public function getSecret(): string;

    abstract public function getMaxAge(): int;

    abstract public function getAllowedSkew(): int;

    abstract public function validate(string $token): void;

    abstract public function create(): string;

    public function __construct()
    {
        $this->token = new JWT($this->getSecret(), $this->getAlgorithm(), $this->getMaxAge(), $this->getAllowedSkew());
    }

    protected function getAlgorithm(): string
    {
        return 'HS256';
    }

    protected function getIssuedAt(): int
    {
        return time();
    }

    #[ArrayShape(['iat' => 'int', 'nbf' => 'int'])]
    protected function getDefaultPayload(): array
    {
        return [
            'iat' => $this->getIssuedAt(),
            'nbf' => $this->getIssuedAt(),
        ];
    }

    protected function encode(array $payload, int $expirationTime): string
    {
        $payload = array_merge(
            $this->getDefaultPayload(),
            [
                'exp' => $expirationTime,
            ],
        );
        return $this->token->encode($payload);
    }
}
