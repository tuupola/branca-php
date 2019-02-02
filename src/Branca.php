<?php

declare(strict_types = 1);

/*

Copyright (c) 2017-2019 Mika Tuupola

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

*/

/**
 * @see       https://branca.io/
 * @see       https://github.com/tuupola/branca-php
 * @see       https://github.com/tuupola/branca-spec
 * @license   https://www.opensource.org/licenses/mit-license.php
 */

namespace Branca;

use InvalidArgumentException;
use Tuupola\Base62;

class Branca
{
    const VERSION = 0xBA; /* Magic byte, BrancA. */

    private $key;

    public function __construct(string $key)
    {
        if (SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES !== strlen($key)) {
            throw new InvalidArgumentException(
                sprintf("Key must be exactly %d bytes", SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES)
            );
        }
        $this->key = $key;
    }

    public function encode(string $payload, int $timestamp = null): string
    {
        if (null === $timestamp) {
            $timestamp = time();
        }

        $version = pack("C", self::VERSION);
        $time = pack("N", $timestamp);
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);

        $header = $version . $time . $nonce;

        $ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $payload,
            $header,
            $nonce,
            $this->key
        );

        $token =  $header . $ciphertext;
        return (new Base62)->encode($token);
    }

    public function decode(string $token, int $ttl = null): string
    {
        $token = (new Base62)->decode($token);
        $header = substr(
            $token,
            0,
            SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES + 5
        );
        $nonce = substr(
            $header,
            5,
            SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES
        );
        $ciphertext = substr(
            $token,
            SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES + 5
        );

        $parts = unpack("Cversion/Ntime", $header);

        /* Implementation should accept only current version. */
        if ($parts["version"] !== self::VERSION) {
            throw new \RuntimeException("Invalid token version");
        }

        try {
            $payload = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
                $ciphertext,
                $header,
                $nonce,
                $this->key
            );
        } catch (\Throwable $error) {
            throw new \RuntimeException("Invalid token");
        } catch (\Exception $error) {
            throw new \RuntimeException("Invalid token");
        }

        /* Check for expired token if TTL is set. */
        if (is_integer($ttl)) {
            $future = $parts["time"] + $ttl;
            if ($future < time()) {
                throw new \RuntimeException("Token is expired");
            }
        }

        return $payload;
    }

    public function timestamp(string $token): int
    {
        $token = (new Base62)->decode($token);
        $parts = unpack("Cversion/Ntime", $token);
        return $parts["time"];
    }
}
