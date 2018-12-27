<?php

declare(strict_types = 1);

/*

Copyright (c) 2017-2018 Mika Tuupola

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
        $nonce = hex2bin("0102030405060708090a0b0c0102030405060708090a0b0c");
        $timestamp = 123206400;

        $branca = new Branca($key);
        NSA::setProperty($branca, "nonce", $nonce);
        $token = $branca->encode("Hello world!", $timestamp);
        $decoded = $branca->decode($token);

        $this->assertEquals(
            "875GH233T7IYrxtgXxlQBYiFobZMQdHAT51vChKsAIYCFxZtL1evV54vYqLyZtQ0ekPHt8kJHQp0a",
            $token
        );

        $this->assertEquals("Hello world!", $decoded);
    }

    public function testShouldPassTestVector2()
    {
        $this->expectException(\RuntimeException::class);

        $key = "supersecretkeyyoushouldnotcommit";
        $nonce = hex2bin("0102030405060708090a0b0c0102030405060708090a0b0c");
        $timestamp = 123206400;

        $branca = new Branca($key);
        NSA::setProperty($branca, "nonce", $nonce);
        $token = $branca->encode("Hello world!", $timestamp);
        $decoded = $branca->decode($token, 3600);
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
        $token = $branca->encode("Hello world!", 0);
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
}
