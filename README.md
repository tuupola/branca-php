#  Branca Tokens for PHP

Authenticated and encrypted API tokens using modern crypto.

[![Latest Version](https://img.shields.io/packagist/v/tuupola/branca.svg?style=flat-square)](https://packagist.org/packages/tuupola/branca)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://img.shields.io/github/workflow/status/tuupola/branca-php/Tests/2.x?style=flat-square)](https://github.com/tuupola/branca-php/actions)
[![Coverage](https://img.shields.io/codecov/c/github/tuupola/branca-php.svg?style=flat-square)](https://codecov.io/github/tuupola/branca-php)



## What?

[Branca](https://github.com/tuupola/branca-spec) is a secure easy to use token format which makes it hard to shoot yourself in the foot. It uses IETF XChaCha20-Poly1305 AEAD symmetric encryption to create encrypted and tamperproof tokens. Payload itself is an arbitrary sequence of bytes. You can use for example a JSON object, plain text string or even binary data serialized by [MessagePack](http://msgpack.org/) or [Protocol Buffers](https://developers.google.com/protocol-buffers/).

It is possible to use [Branca as an alternative to JWT](https://appelsiini.net/2017/branca-alternative-to-jwt/). There is also an [authentication middleware](https://github.com/tuupola/branca-middleware) for frameworks which support PSR-7 doublepass or PSR-15 standards.

## Install

Install the library using [Composer](https://getcomposer.org/).


``` bash
$ composer require tuupola/branca
```

This branch requires PHP 7.2 or up. The older 1.x branch supports also PHP 5.6, 7.0 and 7.1.

``` bash
$ composer require "tuupola/branca:^1.0"
```

## Usage

Token payload can be any arbitrary data such as string containing an email
address. You also must provide a 32 byte secret key. The key is used for encrypting the payload.

```php
use Branca\Branca;

$branca = new Branca("supersecretkeyyoushouldnotcommit");
$payload = "tuupola@appelsiini.net";
$token = $branca->encode($payload);
/* hGgg0dPSseaUPZqGloWlDGb2i8hb6iamFBIQaatgYDRhEuaXyByaX0nzmyQk1WYAuSBEMWpB20Z1dENLFItwf1 */

$decoded = $branca->decode($token); /* someone@example.com */
```

Sometimes you might prefer JSON.

```php
use Branca\Branca;

$branca = new Branca("supersecretkeyyoushouldnotcommit");

$payload = json_encode(["scope" => ["read", "write", "delete"]]);
$token = $branca->encode($payload);

/*
5R7p5pC1gU5kfVuBUzhl43Ndh4HLT9fxAHrhN1zNRivTuehY8zYYzrVZ8C6d6VcNLfCk3EUgBwwW6kIk0wm32O34OFIYz5LnOIezwcV2Xsfc
*/

$decoded = $branca->decode($token);
$array = json_decode($decoded, true);

/*
Array
(
    [scope] => Array
        (
            [0] => read
            [1] => write
            [2] => delete
        )

)
*/
```

You can keep the token size small by using a space efficient serialization method such as [MessagePack](http://msgpack.org/) or [Protocol Buffers](https://developers.google.com/protocol-buffers/).

```php
use Branca\Branca;
use MessagePack\MessagePack;
use MessagePack\Packer;
use MessagePack\BufferUnpacker;

$branca = new Branca("supersecretkeyyoushouldnotcommit");
$payload = (new Packer)->pack(["scope" => ["read", "write", "delete"]]);
$token = $branca->encode($payload);

/*
3iJt0CjqTRh3FGuAf0DHEmhULFIbPVInjguWIkmyCm7RMps5BMJZKa1KwZMN0z58IpPeCxdjoTdkurn9pl0YNrxAQfg3deP0
*/

$decoded = $branca->decode($token);
$unpacked = (new BufferUnpacker($decoded))->unpack();
print_r($unpacked);

/*
Array
(
    [scope] => Array
        (
            [0] => read
            [1] => write
            [2] => delete
        )

)
*/
```

## Timestamp

Branca token includes a timestamp when it was created. When decoding you can optionally pass a `ttl` parameter. Value is passed in seconds. Below example throws en exception if token is older than 60 minutes.

```php
use Branca\Branca;

$branca = new Branca("supersecretkeyyoushouldnotcommit");
$token = "1jJDJOEeG2FutA8g7NAOHK4Mh5RIE8jtbXd63uYbrFDSR06dtQl9o2gZYhBa36nZHXVfiGFz";

print $branca->timestamp($token); /* 123206400 */

try {
    $decoded = $branca->decode($token, 3600);
} catch (RuntimeException $exception) {
    print $exception->getMessage(); /* Token is expired */
}
```

## Testing

You can run tests either manually or automatically on every code change. Automatic tests require [entr](http://entrproject.org/) to work.

``` bash
$ make test
```
``` bash
$ brew install entr
$ make watch
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email tuupola@appelsiini.net instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.txt) for more information.
