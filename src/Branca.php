<?php

/*
 * This file is part of Branca package
 *
 * Copyright (c) 2017 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://github.com/tuupola/branca
 *
 */

namespace Branca;

use Tuupola\Base62;
use function Sodium\crypto_aead_chacha20poly1305_ietf_encrypt;
use function Sodium\crypto_aead_chacha20poly1305_ietf_decrypt;

class Branca
{
    const VERSION = 0x01;

    private $key;
    private $ttl;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encode($payload)
    {
        $nonce = random_bytes(12);
        $time = pack("J", (new \DateTime)->getTimeStamp());
        $version = pack("C", self::VERSION);
        $header = $version . $time . $nonce;

        $ciphertext = crypto_aead_chacha20poly1305_ietf_encrypt(
            $payload,
            $header,
            $nonce,
            $this->key
        );

        $token =  $header . $ciphertext;
        return (new Base62)->encode($token);
    }

    public function decode($token, $ttl = 3600)
    {
        $token = (new Base62)->decode($token);
        $header = substr($token, 0, 21);
        $ciphertext = substr($token, 21);
        $parts = unpack("Cversion/Jtime/Z*nonce", $header);

        try {
            $payload = crypto_aead_chacha20poly1305_ietf_decrypt(
                $ciphertext,
                $header,
                $parts["nonce"],
                $this->key
            );
        } catch (\Throwable $error) {
            throw new \RuntimeException("Invalid token.");
        } catch (\Exception $error) {
            throw new \RuntimeException("Invalid token.");
        }

        /* Check for expired token if TTL is set. */
        if (is_integer($ttl)) {
            $future = $parts["time"] + $ttl;
            if ($future < (new \DateTime)->getTimeStamp()) {
                throw new \RuntimeException("Expired token.");
            }
        }

        return $payload;
    }
}
