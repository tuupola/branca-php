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
use PHPUnit\Framework\TestCase;
use Tuupola\Base62;

class BrancaTest extends TestCase
{
    public function testShouldBeTrue()
    {
        $this->assertTrue(true);
    }

    public function testShouldCreateTokenWithTimestamp()
    {
        $timestamp = 123206400;
        $payload = "Hello world!";

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $token = $branca->encode($payload, $timestamp);
        $decoded = $branca->decode($token);

        $this->assertEquals($payload, $decoded);
        $this->assertEquals($timestamp, $branca->timestamp($token));
    }

    public function testShouldCreateTokenWithZeroTimestamp()
    {
        $timestamp = 0;
        $payload = "Hello world!";

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $token = $branca->encode($payload, $timestamp);
        $decoded = $branca->decode($token);

        $this->assertEquals($payload, $decoded);
        $this->assertEquals($timestamp, $branca->timestamp($token));
    }

    public function testShouldThrowWithWrongVersion()
    {
        $this->expectException(\RuntimeException::class);

        /* This token has version 0xBB. */
        $token = "89mvl3RZe7RwH2x4azVg5V2B7X2NtG4V2YLxHAB3oFc6gyeICmCKAOCQ7Y0n08klY33eQWACd7cSZ";

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    public function testShouldThrowWhenTtlExpired()
    {
        $this->expectException(\RuntimeException::class);

        $timestamp = 123206400;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $token = $branca->encode("Hello world!", $timestamp);
        $decoded = $branca->decode($token, 3600);
    }

    public function testShouldThrowInvalidToken()
    {
        $this->expectException(\RuntimeException::class);

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $token = $branca->encode("Hello world!");
        $decoded = $branca->decode("XX{$token}XX");
    }

    public function testShouldHandlePaylodWithLeadingZeroes()
    {
        $payload = hex2bin("00000000000000ff");

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $token = $branca->encode($payload);
        $decoded = $branca->decode($token);

        $this->assertEquals("00000000000000ff", bin2hex($decoded));
    }

    public function testShouldThrowWithInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $branca = new Branca("tooshortkey");
    }

    public function testShouldGetTimestamp()
    {
        $token = "1jJDJOEeG2FutA8g7NAOHK4Mh5RIE8jtbXd63uYbrFDSR06dtQl9o2gZYhBa36nZHXVfiGFz";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $this->assertEquals(123206400, $branca->timestamp($token));
    }

    public function testShouldGetTimestamp()
    {
        $key = "supersecretkeyyoushouldnotcommit";
        $token = "1jJDJOEeG2FutA8g7NAOHK4Mh5RIE8jtbXd63uYbrFDSR06dtQl9o2gZYhBa36nZHXVfiGFz";
        $branca = new Branca($key);
        $this->assertEquals(123206400, $branca->timestamp($token));
    }
}
