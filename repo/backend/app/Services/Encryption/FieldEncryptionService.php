<?php

namespace App\Services\Encryption;

use Illuminate\Support\Facades\Crypt;

class FieldEncryptionService
{
    public function encrypt(string $plaintext): string
    {
        return Crypt::encryptString($plaintext);
    }

    public function decrypt(string $ciphertext): string
    {
        return Crypt::decryptString($ciphertext);
    }

    public function hash(string $value): string
    {
        $key = config('app.key');

        return hash_hmac('sha256', $value, $key);
    }
}
