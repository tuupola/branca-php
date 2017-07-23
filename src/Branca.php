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
use ParagonIE\Sodium\Compat;

class Branca
{
    const VERSION = 0xBA; /* Magic byte, BrancA. */

    private $key;
    private $nonce = null; /* This exists only for unit testing. */

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encode($payload, $timestamp = null)
    {
        if (null === $timestamp) {
            $timestamp = time();
        } else {
            $timestamp = (integer) $timestamp;
        }

        $version = pack("C", self::VERSION);
        $time = pack("N", $timestamp);

        $nonce = $this->nonce;
        if (empty($nonce)) {
            $nonce = random_bytes(Compat::CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES);
        }

        $header = $version . $time . $nonce;

        $ciphertext = Compat::crypto_aead_xchacha20poly1305_ietf_encrypt(
            $payload,
            $header,
            $nonce,
            $this->key
        );

        $token =  $header . $ciphertext;
        return (new Base62)->encode($token);
    }

    public function decode($token, $ttl = null)
    {
        $token = (new Base62)->decode($token);
        $header = substr(
            $token,
            0,
            Compat::CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES + 5
        );
        $ciphertext = substr(
            $token,
            Compat::CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES + 5
        );
        $parts = unpack("Cversion/Ntime/Z*nonce", $header);

        try {
            $payload = Compat::crypto_aead_xchacha20poly1305_ietf_decrypt(
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
            if ($future < time()) {
                throw new \RuntimeException("Token is expired.");
            }
        }

        return $payload;
    }
}
