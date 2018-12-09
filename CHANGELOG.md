# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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
