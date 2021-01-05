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
    /**
     * Decoding tests are be mandatory for all implementations. They make
     * sure the implementation can consume tokens with different kind of edge
     * cases.
     */

    /**
      * Test vector X (currently 3)
      */
    public function testShouldDecodeHelloWorldWithZeroTimestamp()
    {
        $token = "870S4BYxgHw0KnP3W9fgVUHEhT5g86vJ17etaC5Kh5uIraWHCI1psNQGv298ZmjPwoYbjDQ9chy2z";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $payload = $branca->decode($token);

        $this->assertEquals("Hello world!", $payload);
        $this->assertEquals(0, $branca->timestamp($token));
    }

    /**
      * Test vector X (currently 4)
      */
    public function testShouldDecodeHelloWorldWithMaxTimestamp()
    {
        $token = "89i7YCwu5tWAJNHUDdmIqhzOi5hVHOd4afjZcGMcVmM4enl4yeLiDyYv41eMkNmTX6IwYEFErCSqr";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("Hello world!", $decoded);
        $this->assertEquals(4294967295, $branca->timestamp($token));
    }
    /**
      * Test vector X (currently 5)
      */
    public function testShouldDecodeHelloWorldWithNov27Timestamp()
    {
        $token = "875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("Hello world!", $decoded);
        $this->assertEquals(123206400, $branca->timestamp($token));
    }

    /**
      * Test vector X (currently 6)
      */
    public function testShouldDecodeEightNullBytesWithZeroTimestamp()
    {
        $token = "1jIBheHbDdkCDFQmtgw4RUZeQoOJgGwTFJSpwOAk3XYpJJr52DEpILLmmwYl4tjdSbbNqcF1";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("0000000000000000", bin2hex($decoded));
        $this->assertEquals(0, $branca->timestamp($token));
    }

    /**
      * Test vector X (currently 7)
      */
    public function testShouldDecodeEightNullBytesWithMaxTimestamp()
    {
        $token = "1jrx6DUu5q06oxykef2e2ZMyTcDRTQot9ZnwgifUtzAphGtjsxfbxXNhQyBEOGtpbkBgvIQx";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("0000000000000000", bin2hex($decoded));
        $this->assertEquals(4294967295, $branca->timestamp($token));
    }

    /**
      * Test vector X (currently 8)
      */
    public function testShouldDecodeEightNullBytesWithNov27Timestamp()
    {
        $token = "1jJDJOEjuwVb9Csz1Ypw1KBWSkr0YDpeBeJN6NzJWx1VgPLmcBhu2SbkpQ9JjZ3nfUf7Aytp";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);

        $this->assertEquals("0000000000000000", bin2hex($decoded));
        $this->assertEquals(123206400, $branca->timestamp($token));
    }

    /**
      * Test vector X (currently 1)
      */
    public function testShouldDecodeEmptyPayloadWithZeroTimestamp()
    {
        $token = "4sfD0vPFhIif8cy4nB3BQkHeJqkOkDvinI4zIhMjYX4YXZU5WIq9ycCVjGzB5";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $payload = $branca->decode($token);

        $this->assertEquals("", $payload);
        $this->assertEquals(0, $branca->timestamp($token));
    }

    /**
      * Test vector X (currently 0)
      */
    public function testShouldDecodeNonUtf8CharactersWithNov27Timestamp()
    {
        $token = "K9u6d0zjXp8RXNUGDyXAsB9AtPo60CD3xxQ2ulL8aQoTzXbvockRff0y1eXoHm";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $payload = $branca->decode($token);

        $this->assertEquals("80", bin2hex($payload));
        $this->assertEquals(123206400, $branca->timestamp($token));
    }

    /**
     * Test vector X (currently 9)
     * Token is otherwise valid, but it has version 0xBB.
     */
    public function testShouldThrowWithWrongVersion()
    {
        $this->expectException(RuntimeException::class);

        $token = "89mvl3RkwXjpEj5WMxK7GUDEHEeeeZtwjMIOogTthvr44qBfYtQSIZH5MHOTC0GzoutDIeoPVZk3w";

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    /**
      * Test vector X (currently 10)
      */
    public function testShouldThrowWithInvalidBase62Characters()
    {
        $this->expectException(InvalidArgumentException::class);

        $token = "875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT_";

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    /**
     * Test vector X (currently 11)
     *
     * Token was created with version: 0xba
     * 875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT
     *
     * Before base62 encoding the time was modified to: 0xbb
     * 89mvl3S0BE0UCMIY94xxIux4eg1w5oXrhvCEXrDAjusSbO0Yk7AU6FjjTnbTWTqogLfNPJLzecHVb
     */
    public function testShouldThrowWithModifiedVersion()
    {
        $this->expectException(RuntimeException::class);

        $token = "89mvl3S0BE0UCMIY94xxIux4eg1w5oXrhvCEXrDAjusSbO0Yk7AU6FjjTnbTWTqogLfNPJLzecHVb";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    /**
      * Test vector X (currently 12)
      *
      * Token was created with nonce:
      * hex2bin("beefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef")
      * 875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT
      *
      * Before base62 encoding the nonce was modified to:
      * hex2bin("00efbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef")
      * 875GH233SUysT7fQ711EWd9BXpwOjB72ng3ZLnjWFrmOqVy49Bv93b78JU5331LbcY0EEzhLfpmSx
      */
    public function testShouldThrowWithModifiedNonce()
    {
        $this->expectException(RuntimeException::class);

        $token = "875GH233SUysT7fQ711EWd9BXpwOjB72ng3ZLnjWFrmOqVy49Bv93b78JU5331LbcY0EEzhLfpmSx";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    /**
     * Test vector X (currently 13)
     *
     * Token was created with time: hex2bin("0757fb00")
     * 875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT
     *
     * Before base62 encoding the time was modified to: hex2bin("0057fb00")
     * 870g1RCk4lW1YInhaU3TP8u2hGtfol16ettLcTOSoA0JIpjCaQRW7tQeP6dQmTvFIB2s6wL5deMXr
     */
    public function testShouldThrowWithModifiedTimestamp()
    {
        $this->expectException(RuntimeException::class);

        $token = "870g1RCk4lW1YInhaU3TP8u2hGtfol16ettLcTOSoA0JIpjCaQRW7tQeP6dQmTvFIB2s6wL5deMXr";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    /**
     * Test vector X (currently 14)
     *
     * Original ciphertext in token was:
     * hex2bin("d8fdbaf35dc37a98b523e6fe")
     * 875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT
     *
     * Before base62 encoding the ciphertext was modified to:
     * hex2bin("d8fdbaf35dc37a98b523e600")
     * 875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5Qw6Jpo96myliI3hHD7VbKZBYh
     */
    public function testShouldThrowWithModifiedCiphertext()
    {
        $this->expectException(RuntimeException::class);

        $token = "875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5Qw6Jpo96myliI3hHD7VbKZBYh";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    /**
     * Test vector X (currently 15)
     *
     * Original Poly1305 tag was:
     * hex2bin("f3faf98dd385c68046fb7ed63c94995b")
     * 875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT
     *
     * Before base62 encoding the Poly1305 tag was modified to:
     * hex2bin("f3faf98dd385c68046fb7ed63c949900")
     * 875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trk0
     */
    public function testShouldThrowWithModifiedPoly1305Tag()
    {
        $this->expectException(RuntimeException::class);

        $token = "875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trk0";
        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    /**
     * Test vector X (currently 16)
     */
    public function testShouldThrowWithWrongKey()
    {
        $this->expectException(RuntimeException::class);

        $token = "870S4BYxgHw0KnP3W9fgVUHEhT5g86vJ17etaC5Kh5uIraWHCI1psNQGv298ZmjPwoYbjDQ9chy2z";

        $branca = new Branca("wrongsecretkeyyoushouldnotcommit");
        $decoded = $branca->decode($token);
    }

    /**
     * Test vector X (currently 17)
     *
     * Key length should be 32 bytes.
     */
    public function testShouldThrowWithInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $token = "870S4BYxgHw0KnP3W9fgVUHEhT5g86vJ17etaC5Kh5uIraWHCI1psNQGv298ZmjPwoYbjDQ9chy2z";

        $branca = new Branca("tooshortkey");
        $decoded = $branca->decode($token);
    }

    /**
     * Encoding tests should be implemented if the implementation allows user provided
     * nonce for unit tests. User provided nonce should *not* be part of public API
     * since it is a footgun.
     */

    public function testShouldEncodeHelloWorldWithZeroTimestamp()
    {
        $token = "870S4BYxgHw0KnP3W9fgVUHEhT5g86vJ17etaC5Kh5uIraWHCI1psNQGv298ZmjPwoYbjDQ9chy2z";
        $nonce = hex2bin("beefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef");
        $payload = "Hello world!";
        $timestamp = 0;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload, $timestamp);

        $this->assertEquals($token, $encoded);
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

    /**
      * Test vector 10
      */
    public function testShouldThrowWithInvalidNonce()
    {
        $this->expectException(SodiumException::class);

        $nonce = hex2bin("dead");
        $payload = "Hello world!";

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload);
    }

    /**
      * Test vector 19
      */
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

    /**
      * Test vector 20
      */
    public function testShouldEncodeNonUtf8Characters()
    {
        $token = "K9u6d0zjXp8RXNUGDyXAsB9AtPo60CD3xxQ2ulL8aQoTzXbvockRff0y1eXoHm";
        $nonce = hex2bin("beefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeefbeef");
        $payload = hex2bin("80");
        $timestamp = 123206400;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        NSA::setProperty($branca, "nonce", $nonce);

        $encoded = $branca->encode($payload, $timestamp);

        $this->assertEquals($token, $encoded);
    }

    /**
     * These are PHP implementation specific tests. For example TTL check is not a
     * feature of the token, it is feature of the implementation.
     */
    public function testShouldThrowWhenTtlExpired()
    {
        $this->expectException(\RuntimeException::class);

        $timestamp = 123206400;

        $branca = new Branca("supersecretkeyyoushouldnotcommit");
        $token = $branca->encode("Hello world!", $timestamp);
        $decoded = $branca->decode($token, 3600);
    }

    // public function testShouldThrowWhenTimestampOverflows()
    // {
    //     $this->expectException(\RuntimeException::class);

    //     /* Token with 123206400 timestamp. */
    //     $token = "875GH23U0Dr6nHFA63DhOyd9LkYudBkX8RsCTOMz5xoYAMw9sMd5QwcEqLDRnTDHPenOX7nP2trlT";

    //     $branca = new Branca("supersecretkeyyoushouldnotcommit");

    //     /* Add maximum value to make timestamp overflow. */
    //     $ttl = 4294967295;
    //     $decoded = $branca->decode($token, $ttl);
    // }
}
