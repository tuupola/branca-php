# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## [2.2.1](https://github.com/tuupola/branca/compare/2.2.0...2.x) - unreleased
### Fixed
- Add `SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES` define which is missing from some PHP 7.2 installations ([#12](https://github.com/tuupola/branca-php/pull/12)).

## [2.2.0](https://github.com/tuupola/branca/compare/2.1.0...2.2.0) - 2020-09-10
### Added
- Allow installing with PHP 8 ([#11](https://github.com/tuupola/branca-php/pull/11)).

## [2.1.0](https://github.com/tuupola/branca/compare/2.0.0...2.1.0) - 2019-02-03
### Added
- Helper method to extract timestamp from a token ([#4](https://github.com/tuupola/branca-php/pull/4)).
- Compatibility tests from spec ([#7](https://github.com/tuupola/branca-php/pull/7)).

## [2.0.0](https://github.com/tuupola/branca/compare/1.0.0...2.0.0) - 2019-01-11
### Changed
- PHP 7.2 is now minimum requirement
- PHP bundled Sodium extension is now used
- All methods have return types
- All methods are typehinted
- All type juggling is removed

## [1.0.0](https://github.com/tuupola/branca/compare/0.3.4...1.0.0) - 2019-01-08
### Added
- Allow using tuupola/base62 ^2.0.

### Changed
- Constructor now throws `InvalidArgumentException` if invalid key is given.

## [0.3.4](https://github.com/tuupola/branca/compare/0.3.3...0.3.4) - 2018-12-09
### Added
- Allow using tuupola/base62 ^1.0.

## [0.3.3](https://github.com/tuupola/branca/compare/0.3.2...0.3.3) - 2018-09-11
### Added
- Allow using tuupola/base62 ^0.11.0.

## [0.3.2](https://github.com/tuupola/branca/compare/0.3.1...0.3.2) - 2018-04-05
### Added
- Allow using tuupola/base62 ^0.10.0.

## [0.3.1](https://github.com/tuupola/branca/compare/0.3.0...0.3.1) - 2017-12-12
### Fixed
- Token validation intermittently failed if nonce contained null bytes.

### Added
- Allow using both tuupola/base62 ^0.8.0 and ^0.9.0.

## [0.3.0](https://github.com/tuupola/branca/compare/0.2.0...0.3.0) - 2017-07-23
### Changed
- Use more secure IETF XChaCha20-Poly1305 AEAD encryption.

## [0.2.0](https://github.com/tuupola/branca/compare/0.1.0...0.2.0) - 2017-07-21
### Changed
-  Use more developer friendly unsigned 32 bit second timestamps.

## 0.1.0 - 2017-07-20

Initial realease using IETF ChaCha20-Poly1305 AEAD and 64 bit microsecond timestamps.
