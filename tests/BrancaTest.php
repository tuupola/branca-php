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

use Nyholm\NSA;
use Tuupola\Base62;
use PHPUnit\Framework\TestCase;

class BrancaTest extends TestCase
{
    public function testShouldBeTrue()
    {
        $this->assertTrue(true);
    }

    public function testShouldPassTestVector1()
    {
        $key = "supersecretkeyyoushouldnotcommit";
        $nonce = hex2bin("0102030405060708090a0b0c");
        $timestamp = 123206400;

        $branca = new Branca($key);
        NSA::setProperty($branca, "nonce", $nonce);
        $token = $branca->encode("Hello world!", $timestamp);
        $decoded = $branca->decode($token);

        $this->assertEquals(
            "4si6Rr26CjfyVydzEiKBwuwkQwvjQhmBHTKSXAyHGcaFYA5kEp45XR1Amgblh",
            $token
        );

        $this->assertEquals("Hello world!", $decoded);
    }

    public function testShouldEncodeAndDecode()
    {
        $key = "supersecretkeyyoushouldnotcommit";
        $branca = new Branca($key);
        $token = $branca->encode("Hello world!");
        $decoded = $branca->decode($token);
        $this->assertEquals("Hello world!", $decoded);
    }

    public function testShouldEncodeWithTimestamp()
    {
        $key = "supersecretkeyyoushouldnotcommit";
        $branca = new Branca($key);
        $token = $branca->encode("Hello world!", 123206400);
        $binary = (new Base62)->decode($token);
        $parts = unpack("Cversion/Ntime", $binary);
        $this->assertEquals(123206400, $parts["time"]);
    }

    public function testShouldEncodeWithZeroTimestamp()
    {
        $key = "supersecretkeyyoushouldnotcommit";
        $branca = new Branca($key);
        $token = $branca->encode("Hello world!", false);
        $binary = (new Base62)->decode($token);
        $parts = unpack("Cversion/Ntime", $binary);
        $this->assertEquals(0, $parts["time"]);
    }

    public function testShouldThrowInvalidToken()
    {
        $this->expectException(\RuntimeException::class);
        $key = "supersecretkeyyoushouldnotcommit";
        $branca = new Branca($key);
        $token = $branca->encode("Hello world!");
        $decoded = $branca->decode("XX{$token}XX");
    }

    public function testShouldThrowExpiredToken()
    {
        $this->expectException(\RuntimeException::class);
        $key = "supersecretkeyyoushouldnotcommit";
        $branca = new Branca($key);
        $token = $branca->encode("Hello world!");
        sleep(2);
        $decoded = $branca->decode($token, 1);
    }
}
