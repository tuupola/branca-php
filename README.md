#  Branca Tokens for PHP

Authenticated and encrypted API tokens using modern crypto.

[![Latest Version](https://img.shields.io/packagist/v/tuupola/branca.svg?style=flat-square)](https://packagist.org/packages/tuupola/branca)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/tuupola/branca-php/master.svg?style=flat-square)](https://travis-ci.org/tuupola/branca-php)[![Coverage](https://img.shields.io/codecov/c/github/tuupola/branca-php.svg?style=flat-square)](https://codecov.io/github/tuupola/branca-php)

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
/* 87x2GqCUw7fho4DVETyEPrv8s79gbfRIZB3ql5nliJ42xNNA88VQm7MZZzZs07O8zMC9vke0XuMxb */

$decoded = $branca->decode($token); /* tuupola@appelsiini.net */
```

Sometimes you might prefer JSON.

```php
use Branca\Branca;

$branca = new Branca("supersecretkeyyoushouldnotcommit");

$payload = json_encode(["scope" => ["read", "write", "delete"]]);
$token = $branca->encode($payload);

/*
3Gq503aijMphOZduh8o0oCw2gtIrsJRFR7CR2Hpbys0A2Fy0bUT6V3j2XvyA0Hu4NwYpODnIkK8cRZbOyCs5amPic8ys
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
use MessagePack\Packer;
use MessagePack\Unpacker;

$branca = new Branca("supersecretkeyyoushouldnotcommit");
$payload = (new Packer)->pack(["scope" => ["read", "write", "delete"]]);
$token = $branca->encode($payload);

/*
2EZpjcyhfw5ctQzV67S0swiEJ9U7g30AGUpjL8ovH2chStYP0urF7EXCpNUDDul0IP6iI7bBSnELZita
*/

$decoded = $branca->decode($token);
$unpacked = (new Unpacker)->unpack($decoded);
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

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
