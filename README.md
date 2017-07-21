#  Branca

[![Latest Version](https://img.shields.io/packagist/v/tuupola/branca.svg?style=flat-square)](https://packagist.org/packages/tuupola/branca)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/tuupola/branca/master.svg?style=flat-square)](https://travis-ci.org/tuupola/branca)[![Coverage](http://img.shields.io/codecov/c/github/tuupola/branca.svg?style=flat-square)](https://codecov.io/github/tuupola/branca)

## What?

Branca allows you to generate and verify encrypted authentication tokens. It
defines the external format and encryption scheme of the token. Branca is based on
[Fernet specification](https://github.com/fernet/spec/blob/master/Spec.md). Payload in Branca token is an arbitrary sequence of bytes. Payload can be for example
a JSON object, plain text string or even binary data serialized by [MessagePack](http://msgpack.org/) or [Protocol Buffers](https://developers.google.com/protocol-buffers/).

## Install

Install the library using [Composer](https://getcomposer.org/). Heavy lifting is done by [paragonie/sodium_compat](https://github.com/paragonie/sodium_compat) which in turn will use [libsodium](https://paragonie.com/book/pecl-libsodium) if available.

``` bash
$ composer require tuupola/branca
```

## Token Format

A Branca token is Base62 encoded concatenation of a header and ciphertext. Header
consists of version, timestamp and nonce. Putting them all together we get the
structure below.

```
Version || Timestamp || Nonce || Ciphertext || MAC
```

### Version

Version is 8 bits ie. one byte. Currently the only version is `0xBA`. This is a
magic byte you can use to quickly identify a given token. Version number guarantees
the token format and encryption algorithm.

### Timestamp

Timestamp is 32 bits ie. standard 4 byte UNIX timestamp.

### Nonce

Nonce is 96 bits ie. 12 bytes. These should be cryptographically secure random bytes
and never reused between tokens.

### Ciphertext

Payload is encrypted and authenticated using [IETF ChaCha20-Poly1305](https://download.libsodium.org/doc/secret-key_cryptography/chacha20-poly1305.html).
Note that this is Authenticated Encryption with Additional Data (AEAD) where the
he header part of the token is the additional data. This means the data in the
header (`version`, `timestamp` and `nonce`) is not encrypted, it is only
authenticated. In laymans terms, header can be seen but it cannot be tampered.

### MAC

The authentication tag is 128 bits ie. 16 bytes. This is the [Poly1305](https://en.wikipedia.org/wiki/Poly1305) message authentication code (MAC). It is used to make sure that the message, as well as the non-encrypted header has not been tampered with.

## Usage

Token payload can be any arbitrary data such as string containing an email
address.

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
$ composer test
```
``` bash
$ brew install entr
$ composer watch
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email tuupola@appelsiini.net instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
