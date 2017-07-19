#  branca

[![Latest Version](https://img.shields.io/packagist/v/tuupola/branca.svg?style=flat-square)](https://packagist.org/packages/tuupola/branca)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/tuupola/branca/master.svg?style=flat-square)](https://travis-ci.org/tuupola/branca)[![Coverage](http://img.shields.io/codecov/c/github/tuupola/branca.svg?style=flat-square)](https://codecov.io/github/tuupola/branca)

### What?

Branca allows you to generate and verify encrypted authentication tokens. Branca is based on [Fernet](https://github.com/fernet/spec/blob/master/Spec.md) with the following differences.

1. Instead of AES 128 CBC and SHA256 HMAC used by Fernet, Branca uses [ChaCha20-Poly1305](https://download.libsodium.org/doc/secret-key_cryptography/chacha20-poly1305.html) Authenticated Encryption with Additional Data (AEAD).
2. Instead of of Base64URL encoding branca uses Base62 encoding for the token.
3. You can opt-out from including the timestamp in the token header. When opting out the timestamp will have value of 0.
4. Timestamps are stored as microseconds instead of seconds to avoid [race conditions](https://www.lbragstad.com/blog/the-future-of-fernet-tokens) in M2M environments.

## Install

Install the library using [Composer](https://getcomposer.org/). Heavy lifting is done by [paragonie/sodium_compat](https://github.com/paragonie/sodium_compat) which in turn will use [libsodium](https://paragonie.com/book/pecl-libsodium) if available.

``` bash
$ composer require tuupola/branca
```

## Usage

Token payload can be any arbitrary data. In below example payload is simple email address. Payload is always tamper proof.

```php
use Branca\Branca;

$branca = new Branca("supersecretkeyyoushouldnotcommit");
$payload = "tuupola@appelsiini.net";
$token = $branca->encode($payload);
/* k6ZeAoUNhKljdHYPaIQ7SrjSVebrajjD2ZkymZlbAc5IJl2gypuh41OHZOMZ3P1vwtEEWiSsqU1tP8 */

$decoded = $branca->decode($token); /* tuupola@appelsiini.net */
```

Sometimes you might prefer JSON.

```php
use Branca\Branca;

$branca = new Branca("supersecretkeyyoushouldnotcommit");

$payload = json_encode(["scope" => ["read", "write", "delete"]]);
$token = $branca->encode($payload);

/*
56NztEBTV3hVj47wEjinRKAWP8tiwyns2bm4e9931xPEo1tIp4VXOvSM1IirLPfMUcYRFWAosDrK7s038MdH7QbdClQcvqi4
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
