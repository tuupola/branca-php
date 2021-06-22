<?php

declare(strict_types = 1);

/*

Copyright (c) 2017-2021 Mika Tuupola

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

use Tuupola\Base62;

class Branca
{
     /**
      *  Magic byte, BrancA.
      */
    const VERSION = 0xBA;

    /**
     * @var string
     */
    private $key;

    /**
     * @var int
     */
    private $timestamp = null;

    /**
     * Nonce used for unit testing only.
     * @var string
     */
    private $nonce = null;


    public function __construct(string $key)
    {
        /* Apparently some PHP 7.2 versions do not have this defined. */
        if (!defined("SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES")) {
            define("SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES", 32);
        }

        if (SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES !== strlen($key)) {
            throw new InvalidKeyException(
                sprintf("Key must be exactly %d bytes", SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES)
            );
        }
        $this->key = $key;
    }

    /**
     * Create a token from payload and optional timestamp.
     */
    public function encode(string $payload, int $timestamp = null): string
    {
        if (null === $timestamp) {
            $timestamp = time();
        }

        $version = pack("C", self::VERSION);
        $time = pack("N", $timestamp);

        $nonce = $this->nonce;
        if (empty($nonce)) {
            $nonce = random_bytes(SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        }

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

    /**
     * Extract the payload from a token.
     */
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

        /* Unpack failed, should not ever happen. */
        if (false === $parts) {
            throw new InvalidTokenException("Cannot extract token header");
        }

        /* Implementation should accept only current version. */
        if ($parts["version"] !== self::VERSION) {
            throw new InvalidTokenVersionException("Invalid token version");
        }

        try {
            $payload = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
                $ciphertext,
                $header,
                $nonce,
                $this->key
            );
        } catch (\Throwable $error) {
            throw new InvalidTokenException("Invalid token");
        /** @phpstan-ignore-next-line */
        } catch (\Exception $error) {
            throw new InvalidTokenException("Invalid token");
        }

        /* In some cases sodium returns false. */
        if (false === $payload) {
            throw new InvalidTokenException("Invalid token");
        }

        /* Store timestamp value for the helper. */
        $this->timestamp = $parts["time"];

        /* Check for expired token if TTL is set. */
        if (is_integer($ttl)) {
            $future = $parts["time"] + $ttl;
            if ($future < time()) {
                throw new ExpiredTokenException("Token is expired");
            }
        }

        return $payload;
    }

    public function timestamp(string $token): int
    {
        if (null === $this->timestamp) {
            $this->decode($token);
        }

        return $this->timestamp;
    }
}
