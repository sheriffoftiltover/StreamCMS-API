<?php

declare(strict_types=1);

namespace StreamCMS\Utility\Security\Encryption;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

final class Crypt
{
    private Key $encryptionKey;

    public function __construct(string $encryptionKeyString)
    {
        $this->encryptionKey = Key::loadFromAsciiSafeString($encryptionKeyString);
    }

    public function encrypt(string $data): string
    {
        return Crypto::encrypt($data, $this->encryptionKey);
    }

    public function decrypt(string $data): string
    {
        return Crypto::decrypt($data, $this->encryptionKey);
    }
}
