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
use RuntimeException;
use SodiumException;
use PHPUnit\Framework\TestCase;
use Tuupola\Base62;
use Nyholm\NSA;

class BrancaTest extends TestCase
{
    public function testShouldBeTrue()
    {
        $this->assertTrue(true);
    }

    /* These are the tests each implementation should have. */
    public function testShouldDecodeHelloWorldWithZeroTimestamp()
    {
        //$token = "870S4BYjk7NvyViEjUNsTEmGXbARAX9PamXZg0b3JyeIdGyZkFJhNsOQW6m0K9KnXt3ZUBqDB6hF4";
        $token = "870S4BYxgHw0KnP3W9fgVUHEhT5g86vJ17etaC5Kh5uIraWHCI1psNQGv298ZmjPwoYbjDQ9chy2z";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $payload = $branca->decode($token);

        $this->assertEquals("Hello world!", $payload);
        $this->assertEquals(0, $branca->timestamp($token));
    }

    public function testShouldEncodeHelloWorldWithZeroTimestamp()
    {
        $token = "870S4BYxgHw0KnP3W9fgVUHEhT5g86vJ17etaC5Kh5uIraWHCI1psNQGv298ZmjPwoYbjDQ9chy2z";
        //$nonce = hex2bin("5b2add425fb626281c495a6fa8831fc9f0cf40328740751a");
        $nonce = hex2bin("beefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef");
        $payload = "Hello world!";
        $timestamp = 0;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload, $timestamp);

        $this->assertEquals($token, $encoded);
    }

    public function testShouldDecodeHelloWorldWithMaxTimestamp()
    {
        $token = "89i7YCwu5tWAJNHUDdmIqhzOi5hVHOd4afjZcGMcVmM4enl4yeLiDyYv41eMkNmTX6IwYEFErCSqr";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("Hello world!", $decoded);
        $this->assertEquals(4294967295, $branca->timestamp($token));
    }

    public function testShouldEncodeHelloWorldWithMaxTimestamp()
    {
        $token = "89i7YCwu5tWAJNHUDdmIqhzOi5hVHOd4afjZcGMcVmM4enl4yeLiDyYv41eMkNmTX6IwYEFErCSqr";
        $nonce = hex2bin("beefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef");
        $payload = "Hello world!";
        $timestamp = 4294967295;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload, $timestamp);

        $this->assertEquals($token, $encoded);
    }

    public function testShouldDecodeHelloWorldWithNov27Timestamp()
    {
        $token = "875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("Hello world!", $decoded);
        $this->assertEquals(123206400, $branca->timestamp($token));
    }

    public function testShouldEncodeHelloWorldWithNov27Timestamp()
    {
        $token = "875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT";
        $nonce = hex2bin("beefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef");
        $payload = "Hello world!";
        $timestamp = 123206400;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload, $timestamp);

        $this->assertEquals($token, $encoded);
    }

    public function testShouldDecodeEightNullBytesWithZeroTimestamp()
    {
        $token = "1jIBheHbDdkCDFQmtgw4RUZeQoOJgGwTFJSpwOAk3XYpJJr52DEpILLmmwYl4tjdSbbNqcF1";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("0000000000000000", bin2hex($decoded));
        $this->assertEquals(0, $branca->timestamp($token));
    }

    public function testShouldEncodeEightNullBytesWithZeroTimestamp()
    {
        $token = "1jIBheHbDdkCDFQmtgw4RUZeQoOJgGwTFJSpwOAk3XYpJJr52DEpILLmmwYl4tjdSbbNqcF1";
        $nonce = hex2bin("beefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef");
        $payload = hex2bin("0000000000000000");
        $timestamp = 0;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload, $timestamp);

        $this->assertEquals($token, $encoded);
    }

    public function testShouldDecodeEightNullBytesWithMaxTimestamp()
    {
        $token = "1jrx6DUu5q06oxykef2e2ZMyTcDRTQot9ZnwgifUtzAphGtjsxfbxXNhQyBEOGtpbkBgvIQx";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("0000000000000000", bin2hex($decoded));
        $this->assertEquals(4294967295, $branca->timestamp($token));
    }

    public function testShouldEncodeEightNullBytesWithMaxTimestamp()
    {
        $token = "1jrx6DUu5q06oxykef2e2ZMyTcDRTQot9ZnwgifUtzAphGtjsxfbxXNhQyBEOGtpbkBgvIQx";
        $nonce = hex2bin("beefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef");
        $payload = hex2bin("0000000000000000");
        $timestamp = 4294967295;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload, $timestamp);

        $this->assertEquals($token, $encoded);
    }

    public function testShouldDecodeEightNullBytesWithNov27Timestamp()
    {
        $token = "1jJDJOEjuwVb9Csz1Ypw1KBWSkr0YDpeBeJN6NzJWx1VgPLmcBhu2SbkpQ9JjZ3nfUf7Aytp";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("0000000000000000", bin2hex($decoded));
        $this->assertEquals(123206400, $branca->timestamp($token));
    }

    public function testShouldEncodeEightNullBytesWithNov27Timestamp()
    {
        $token = "1jJDJOEjuwVb9Csz1Ypw1KBWSkr0YDpeBeJN6NzJWx1VgPLmcBhu2SbkpQ9JjZ3nfUf7Aytp";
        $nonce = hex2bin("beefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef");
        $payload = hex2bin("0000000000000000");
        $timestamp = 123206400;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload, $timestamp);

        $this->assertEquals($token, $encoded);
    }

    public function testShouldThrowWithWrongVersion()
    {
        $this->expectException(RuntimeException::class);

        /* This token has version 0xBB. */
        $token = "89mvl3RkwXjpEj5WMxK7GUDEHEeeeZtwjMIOogTthvr44qBfYtQSIZH5MHOTC0GzoutDIeoPVZk3w";

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    /* TTL expired */
    // public function testShouldThrowWhenExpired() {

    // }

    public function testShouldThrowWithInvalidBase62Characters()
    {
        $this->expectException(InvalidArgumentException::class);

        $token = "875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT_";

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    public function testShouldThrowWithInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $token = "870S4BYxgHw0KnP3W9fgVUHEhT5g86vJ17etaC5Kh5uIraWHCI1psNQGv298ZmjPwoYbjDQ9chy2z";

        $branca = new Branca("tooshortkey");
        $decoded = $branca->decode($token);
    }

    public function testShouldThrowWithInvalidNonce()
    {
        $this->expectException(SodiumException::class);

        $nonce = hex2bin("dead");
        $payload = "Hello world!";

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload);
    }

    public function testShouldThrowWithWrongKey()
    {
        $this->expectException(RuntimeException::class);

        $token = "870S4BYxgHw0KnP3W9fgVUHEhT5g86vJ17etaC5Kh5uIraWHCI1psNQGv298ZmjPwoYbjDQ9chy2z";

        $branca = new Branca("wrongsecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    public function testShouldDecodeEmptyPayloadWithZeroTimestamp()
    {
        $token = "4sfD0vPFhIif8cy4nB3BQkHeJqkOkDvinI4zIhMjYX4YXZU5WIq9ycCVjGzB5";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $payload = $branca->decode($token);

        $this->assertEquals("", $payload);
        $this->assertEquals(0, $branca->timestamp($token));
    }

    public function testShouldEncodeEmptyPayloadWithZeroTimestamp()
    {
        $token = "4sfD0vPFhIif8cy4nB3BQkHeJqkOkDvinI4zIhMjYX4YXZU5WIq9ycCVjGzB5";
        $nonce = hex2bin("beefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef");
        $payload = "";
        $timestamp = 0;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload, $timestamp);

        $this->assertEquals($token, $encoded);
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

    // public function testShouldThrowWithInvalidKey()
    // {
    //     $this->expectException(InvalidArgumentException::class);
    //     $branca = new Branca("tooshortkey");
    // }

    public function testShouldGetTimestamp()
    {
        $token = "1jJDJOEeG2FutA8g7NAOHK4Mh5RIE8jtbXd63uYbrFDSR06dtQl9o2gZYhBa36nZHXVfiGFz";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $this->assertEquals(123206400, $branca->timestamp($token));
    }
}
