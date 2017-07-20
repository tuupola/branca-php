#  Branca

[![Latest Version](https://img.shields.io/packagist/v/tuupola/branca.svg?style=flat-square)](https://packagist.org/packages/tuupola/branca)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/tuupola/branca/master.svg?style=flat-square)](https://travis-ci.org/tuupola/branca)[![Coverage](http://img.shields.io/codecov/c/github/tuupola/branca.svg?style=flat-square)](https://codecov.io/github/tuupola/branca)

## What?

Branca allows you to generate and verify encrypted authentication tokens. It
defines the external format and encryption scheme of the token. Branca is based on
[Fernet specification](https://github.com/fernet/spec/blob/master/Spec.md).

Payload in Branca token is an an arbitrary sequence of bytes. Payload can be for example
a JSON object, plain text string or even binary data serialized by [MessagePack](http://msgpack.org/) or [Protocol Buffers](https://developers.google.com/protocol-buffers/).

## Install

Install the library using [Composer](https://getcomposer.org/). Heavy lifting is done by [paragonie/sodium_compat](https://github.com/paragonie/sodium_compat) which in turn will use [libsodium](https://paragonie.com/book/pecl-libsodium) if available.

``` bash
$ composer require tuupola/branca
```

## Token Format

A Branca token is Base62 encoding of a header and ciphertext. Header consists of
version, timestamp and nonce. Putting them all together we get the structure below.

```
Version || Timestamp || Nonce || Ciphertext
```

### Version

Version is 8 bits ie. one byte. Currently the only version is `0xBA`. This is a
magic byte you can use to quickly identify a given token. Version number defines
the token format and encryption algorithm.

### Timestamp

Timestamp is 64 bits ie. 8 bytes. Unlike Fernet which uses one second timestamps,
Branca uses microsecond timestamps. This is to avoid [possible race conditions](https://github.com/fernet/spec/issues/12) in m2m environments.

### Nonce

Nonce is 96 bits ie. 12 bytes. These should be cryptographically secure random bytes
and never reused.

### Ciphertext

Payload is encrypted and authenticated using [IETF ChaCha20-Poly1305](https://download.libsodium.org/doc/secret-key_cryptography/chacha20-poly1305.html).
Note that this is Authenticated Encryption with Additional Data (AEAD) where the
he header part of the token is the additional data. This means the data in the
header (`version`, `timestamp` and `nonce`) is not encrypted, it is only
authenticated. In laymans terms, header can be seen but it cannot be tampered.

## Usage

Token payload can be any arbitrary data. In below example payload is simple email address. Payload is always tamper proof.

```php
use Branca\Branca;

$branca = new Branca("supersecretkeyyoushouldnotcommit");
$payload = "tuupola@appelsiini.net";
$token = $branca->encode($payload);
/* 2EJjAw8kaF6RwbS8lkVU99fuTlaTLHpxlq2GAr8Jzt2WAIOlqR5jSumGvpxhtPt7lGtTjcdOcYcc8tnT */

$decoded = $branca->decode($token); /* tuupola@appelsiini.net */
```

Sometimes you might prefer JSON.

```php
use Branca\Branca;

$branca = new Branca("supersecretkeyyoushouldnotcommit");

$payload = json_encode(["scope" => ["read", "write", "delete"]]);
$token = $branca->encode($payload);

/*
FJA1GbFlJ0qJ8nRzdTfpODKz9WuRD9Vgi9e0KmEW96WCzPL6mo7l8El2P3LDsDm5pYyQ4mV3CIY0HOOT5M87w0nlwJysgafIE
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
3U9QO31MBzZwQ8gF63O1d5bvIvdSzJx9qtNFq8UXV8Jt52u1tDvSSgG6xIeNVolkMuhrpsuHlf2pSMYMHX5W
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
