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
