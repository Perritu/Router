# The Router class

(php 8 >= 8.5, Uri\Rfc3986\Uri >= *)

## Introduction

The **Router** class provides a lightweight, blazing-fast bitwise-driven HTTP request routing mechanism. It supports exact path (flat) matching, regular expression matching, dynamic namespace-to-class mapping, and custom callback execution.

## Class synopsis

```php
class Router
{
  /* Constants */
  public const ANY     = 255; # Bits 1 to 8
  public const DELETE  = 1;   # Bit 1
  public const GET     = 2;   # Bit 2
  public const HEAD    = 4;   # Bit 3
  public const OPTIONS = 8;   # Bit 4
  public const PATCH   = 16;  # Bit 5
  public const POST    = 32;  # Bit 6
  public const PUT     = 64;  # Bit 7
  public const QUERY   = 128; # Bit 8

  public const CASE_I = 1; # 001
  public const FLAT   = 2; # 010
  public const PREG   = 4; # 100
  public const IFLAT  = 3; # 011
  public const IPREG  = 5; # 101

  /* Properties */
  public static ?string $Path = null;
  public static ?string $Method = null;
  public static int $MethodBit = 0;
  public static string $CriteriaPrefix = '';
  public static string $ClassPrefix = '';
  public protected(set) static bool $PreventCallback = false;

  /* Methods */
  public function __construct(
    ?string $cPath = null,
    ?string $cMethod = null,
    ?string $cHost = null,
    ?int    $uPort = null
  )
  public static function init(
    ?string $cPath = null,
    ?string $cMethod = null,
    ?string $cHost = null,
    ?int    $uPort = null
  ): string
  public static function MATCH(
    int $bitMethod,
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true,
  ): array|false
  public static function ANY, DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT, QUERY(
    string|array $cCriteria,
    callable|string|array|null $cCallback = null,
    bool $bTerminate = true
  ): array|false
  public static function MapNamespace(
    string $cNamespace,
    string $cBasePath,
    int $bitMethods = self::ANY,
    bool $bTerminate = true
  ): void
  protected static function ForwardCallback(
    callable|string|array $cCallback,
    array $aArguments = []
  ): mixed
}
```

## Changelog

| Version   | Description
| --------- | ---------
| 1.0.0-DEV | Initial release. Internal development state. Unstable
| 1.0.2-DEV | Implementing `Path\To\Class::Method` routing.
| 1.0.3-DEV | Refactoring `Router::Dispatch` to prevent calling non-public methods.
| 1.0.5     | First stable release.
| 1.0.6-rc1 | Critical bug fixes. Downgraded to Release Candidate.
| 2.0.0     | Major refactor. Removed support for Api (json/xml) responses.
| 2.0.1     | Using `parse_url` to process request components.
| 2.0.3     | Replacing `['REQUEST_SCHEME']` with `['REQUEST_METHOD']`.
| 2.0.4     | Enforcing Bitwise check method to be more explicit.
| 2.0.5     | Fix `preg_match` comparison to be strictly `1`.
| 3.0.0     | Major refactor. Updating to PHP 8.5. Maintains retrocompatibility.

## Table of Contents

- [Router::__construct][] — Initiates the router and instances the Router for [OOP][].
- [Router::init][] — Initiates the router and return the class string for [PP][].
- [Router::MATCH][] — Perform a route evaluation and return the matching results.
- [Router aliases][] — Shorts for perfoming MATCH calls for a single http method.
- [Router::MapNamespace][] — Mount a namespace so it behaves as virtual directory.
- [Router::ForwardCallback][] — Internal method to process and dispatch callback.

## External links

- [RFC 3986] uri parser. Library used to decompose the path sctructures.

[RFC 3986]:https://www.php.net/manual/class.uri-rfc3986-uri.php
[Router::__construct]:router.constructor.md
[Router::init]:router.init.md
[Router::MATCH]:router.match.md
[Router aliases]:router.aliases.md
[Router::MapNamespace]:router.mapnamespace.md
[Router::ForwardCallback]:router.forwardcallback.md
[OOP]:https://en.wikipedia.org/wiki/Object-oriented_programming
[PP]:https://en.wikipedia.org/wiki/Procedural_programming
