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
    const VERSION = 0xBA; /* Magic byte, BrancA. */

    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encode($payload, $timestamp = null)
    {
        /* Microsecond timestamp internally, optionally passed in as seconds. */
        if (null === $timestamp) {
            $timestamp = vsprintf("%d%06d", gettimeofday());
        } else {
            $timestamp = (integer) $timestamp * 1000000;
        }

        $version = pack("C", self::VERSION);
        $time = pack("J", $timestamp);
        $nonce = random_bytes(12);
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

    public function decode($token, $ttl = null)
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

        /* Check for expired token if TTL is set. TTL is passed as seconds. */
        if (is_integer($ttl)) {
            $future = $parts["time"] + ($ttl * 1000000);
            $usec = (integer) vsprintf("%d%06d", gettimeofday());
            if ($future < $usec) {
                throw new \RuntimeException("Expired token.");
            }
        }

        return $payload;
    }
}
