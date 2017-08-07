#  Branca Tokens for PHP

[![Latest Version](https://img.shields.io/packagist/v/tuupola/branca.svg?style=flat-square)](https://packagist.org/packages/tuupola/branca)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/tuupola/branca-php/master.svg?style=flat-square)](https://travis-ci.org/tuupola/branca-php)[![Coverage](http://img.shields.io/codecov/c/github/tuupola/branca-php.svg?style=flat-square)](https://codecov.io/github/tuupola/branca-php)

## What?

[Branca](https://github.com/tuupola/branca-spec) allows you to generate and
verify encrypted authentication tokens. It defines the external format and
encryption scheme of the token. Branca is based on
[Fernet specification](https://github.com/fernet/spec/blob/master/Spec.md).
Payload in Branca token is an arbitrary sequence of bytes. Payload can be
for example a JSON object, plain text string or even binary data serialized
by [MessagePack](http://msgpack.org/) or
[Protocol Buffers](https://developers.google.com/protocol-buffers/).

## Install

Install the library using [Composer](https://getcomposer.org/). Heavy lifting
is done by [paragonie/sodium_compat](https://github.com/paragonie/sodium_compat)
which in turn will use [libsodium](https://paragonie.com/book/pecl-libsodium)
if available.

``` bash
$ composer require tuupola/branca
```
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
$ make test
```
``` bash
$ brew install entr
$ make watch
```

## Token Format

Branca token consists of header, ciphertext and an authentication tag. Header
consists of version, timestamp and nonce. Putting them all together we get
following structure.

```
Version (1B) || Timestamp (4B) || Nonce (24B) || Ciphertext (*B) || Tag (16B)
```

String representation of the above binary token must use base62 encoding with
the following character set.


```
0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy
```

### Version

Version is 8 bits ie. one byte. Currently the only version is `0xBA`. This is a
magic byte which you can use to quickly identify a given token. Version number
guarantees the token format and encryption algorithm.

### Timestamp

Timestamp is 32 bits ie. unsigned big endian 4 byte UNIX timestamp. By having a
timestamp instead of expiration time enables the consuming side to decide how
long tokens are valid. You cannot accidentaly create tokens which are valid for
the next 10 years.

Storing timestamp as unsigned integer allows us to avoid 2038 problem. Unsigned
integer overflow will happen in year 2106.

### Nonce

Nonce is 192 bits ie. 24 bytes. These should be cryptographically secure random
bytes and never reused between tokens.

### Ciphertext

Payload is encrypted and authenticated using [IETF XChaCha20-Poly1305](https://download.libsodium.org/doc/secret-key_cryptography/xchacha20-poly1305_construction.html).
Note that this is [Authenticated Encryption with Additional Data (AEAD)](https://tools.ietf.org/html/rfc7539#section-2.8) where the
he header part of the token is the additional data. This means the data in the
header (version, timestamp and nonce) is not encrypted, it is only
authenticated. In laymans terms, header can be seen but it cannot be tampered.

### Tag

The authentication tag is 128 bits ie. 16 bytes. This is the
[Poly1305](https://en.wikipedia.org/wiki/Poly1305) message authentication
code. It is used to make sure that the payload, as well as the
non-encrypted header have not been tampered with.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email tuupola@appelsiini.net instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
