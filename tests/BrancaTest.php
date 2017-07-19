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
use PHPUnit\Framework\TestCase;

class BrancaTest extends TestCase
{
    public function testShouldBeTrue()
    {
        $this->assertTrue(true);
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
        $token = $branca->encode("Hello world!", 123206400000000);
        $binary = (new Base62)->decode($token);
        $parts = unpack("Cversion/Jtime", $binary);
        $this->assertEquals(123206400000000, $parts["time"]);
    }

    public function testShouldEncodeWithZeroTimestamp()
    {
        $key = "supersecretkeyyoushouldnotcommit";
        $branca = new Branca($key);
        $token = $branca->encode("Hello world!", false);
        $binary = (new Base62)->decode($token);
        $parts = unpack("Cversion/Jtime", $binary);
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
        $decoded = $branca->decode($token, 10);
    }
}
