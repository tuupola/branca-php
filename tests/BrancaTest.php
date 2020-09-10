<?php

declare(strict_types = 1);

/*

Copyright (c) 2017-2020 Mika Tuupola

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

    /* These are the tests each implementation should have. */
    public function testShouldHaveHelloWorldWithZeroTimestamp()
    {
        $token = "870S4BYjk7NvyViEjUNsTEmGXbARAX9PamXZg0b3JyeIdGyZkFJhNsOQW6m0K9KnXt3ZUBqDB6hF4";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("Hello world!", $decoded);
        $this->assertEquals(0, $branca->timestamp($token));
    }

    public function testShouldHaveHelloWorldWithMaxTimestamp()
    {
        $token = "89i7YCwtsSiYfXvOKlgkCyElnGCOEYG7zLCjUp4MuDIZGbkKJgt79Sts9RdW2Yo4imonXsILmqtNb";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("Hello world!", $decoded);
        $this->assertEquals(4294967295, $branca->timestamp($token));
    }

    public function testShouldHaveHelloWorldWithNov27Timestamp()
    {
        $token = "875GH234UdXU6PkYq8g7tIM80XapDQOH72bU48YJ7SK1iHiLkrqT8Mly7P59TebOxCyQeqpMJ0a7a";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("Hello world!", $decoded);
        $this->assertEquals(123206400, $branca->timestamp($token));
    }

    public function testShouldHaveEightNullBytesWithZeroTimestamp()
    {
        $token = "1jIBheHWEwYIP59Wpm4QkjkIKuhc12NcYdp9Y60B6av7sZc3vJ5wBwmKJyQzGfJCrvuBgGnf";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("0000000000000000", bin2hex($decoded));
        $this->assertEquals(0, $branca->timestamp($token));
    }

    public function testShouldHaveEightNullBytesWithMaxTimestamp()
    {
        $token = "1jrx6DUq9HmXvYdmhWMhXzx3klRzhlAjsc3tUFxDPCvZZLm16GYOzsBG4KwF1djjW1yTeZ2B";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("0000000000000000", bin2hex($decoded));
        $this->assertEquals(4294967295, $branca->timestamp($token));
    }

    public function testShouldHaveEightNullBytesWithNov27Timestamp()
    {
        $token = "1jJDJOEfuc4uBJh5ivaadjo6UaBZJDZ1NsWixVCz2mXw3824JRDQZIgflRqCNKz6yC7a0JKC";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("0000000000000000", bin2hex($decoded));
        $this->assertEquals(123206400, $branca->timestamp($token));
    }

    public function testShouldThrowWithWrongVersion()
    {
        $this->expectException(\RuntimeException::class);

        /* This token has version 0xBB. */
        $token = "89mvl3RkwXjpEj5WMxK7GUDEHEeeeZtwjMIOogTthvr44qBfYtQSIZH5MHOTC0GzoutDIeoPVZk3w";

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    /* These are the PHP implementation specific tests. */
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
        $payload = (string) hex2bin("00000000000000ff");

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
}
