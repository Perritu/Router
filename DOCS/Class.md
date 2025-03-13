# The Router class

(PHP 8)

## Introduction

Simplifies the process of handle the incoming requests and directing them to
developer-defined code flow.

## Class synopsis

```php
class Router {
  /* Constants */
  public const ANY     = 127; # Bits 1 to 7
  public const DELETE  = 1;   # Bit 1
  public const GET     = 2;   # Bit 2
  public const HEAD    = 4;   # Bit 3
  public const OPTIONS = 8;   # Bit 4
  public const PATCH   = 16;  # Bit 5
  public const POST    = 32;  # Bit 6
  public const PUT     = 64;  # Bit 7

  public const CASE_I = 1; # 001
  public const FLAT   = 2; # 010
  public const PREG   = 4; # 100
  public const IFLAT  = 3; # 011
  public const IPREG  = 5; # 101

  /* Properties */
  public string $basePath;
  public static ?string $Path = null;
  public static ?string $Method = null;
  public static int $MethodBit = 0;
  public static string $CriteriaPrefix = '';
  public static string $ClassPrefix = '';

  /* Methods */
  public function __constructor(?string $Path = null, ?string $Method = null);
  public static function init(?string $Path = null, ?string $Method = null): string;
  public static function MATCH(
    int $MethodBit,
    string|array $Criteria,
    callable|string|array $Callback,
    bool $Terminate = true
  ): mixed;
  public static function ANY, DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT(
    string|array $Criteria,
    callable|string|array $Callback,
    bool $Terminate = true
  ): mixed;
  public static function USE(
    string $Namespace,
    string $MountPoint,
    int $MethodBit = self::ANY,
    bool $Terminate = true
  ): void;
  protected static function Dispatch(
    callable|string|array $Callback,
    bool $Terminate,
    array $Arguments = []
  ): mixed;
}
```

## Changelog

- `1.0.5` First public release. Stable, production ready.
- `1.0.6-rc1` First implementationn of `MountNamespace`. Release candidate.
- `2.0.0` :warning: This is a breaking change.
  - Refractoring. Better performance, legibility, and stability.
  - Removed the api response handling: It's not something that should be handled
    by a router.

## Contents

- [init] -- The initialization function. Constructor.
- [MATCH] -- Launch the defined callback if given criteria matches the request.
- [Shortcuts] -- Alias functions for the `Router::MATCH` method.
- [USE] -- Mount a namespace to handle the requests.
- [Dispatch] -- :warning: Internal use only. Executes the callback.
- [Constants] -- Constants in the `Router` class.
- [Properties] -- Properties in the `Router` class.

[init]: function.init.md
[MATCH]: function.match.md
[Shortcuts]: function.shortcuts.md
[USE]: function.use.md
[Dispatch]: function.dispatch.md
[Constants]: Class.md#constants
[Properties]: Class.md#properties

## Contributing

Found a bug? Please report it on the [GitHub issue tracker]. :cockroach:

You can also contribute by submitting a pull request to the [Canary branch].
:heart: <br>
When doing so, please be sure to follow the [Contributing guidelines].
:nerd_face:

[GitHub issue tracker]: https://github.com/Perritu/Router/issues/new/choose
[Canary branch]: https://github.com/Perritu/Router/tree/Canary
[Contributing guidelines]: docs.contributing.md

## License

The Router is public domain software. It's licennsed under the [Unlicense].

[Unlicense]: ../LICENSE.txt

## Constants <a name="constants"></a>

Represents the HTTP verbs. `Router::ANY` is a bitwise constant that represents
all the HTTP verbs.

```php
  public const ANY     = 127; # 1111111
  public const DELETE  = 1;   # 0000001
  public const GET     = 2;   # 0000010
  public const HEAD    = 4;   # 0000100
  public const OPTIONS = 8;   # 0001000
  public const PATCH   = 16;  # 0010000
  public const POST    = 32;  # 0100000
  public const PUT     = 64;  # 1000000
```

-----

Flags used to specify the criteria matching method.

- The first bit marks it to be case-insensitive.
- The second bit marks it to be flat.
- The third bit marks it to be a regular expression.

```php
  public const CASE_I = 1; # 001
  public const FLAT   = 2; # 010
  public const PREG   = 4; # 100
  public const IFLAT  = 3; # 011
  public const IPREG  = 5; # 101
```

## Properties <a name="properties"></a>

Description:

```php
  public string $basePath;
  public static ?string $Path = null;
  public static ?string $Method = null;
  public static int $MethodBit = 0;
  public static string $CriteriaPrefix = '';
  public static string $ClassPrefix = '';
```

`$basePath` is the base path of the router. It can be used to use the router in
a subdirectory.

`$Path` is the path of the request.

`$Method` is the string representation of the HTTP method of the request.

`$MethodBit` is the bitwise representation of the HTTP method of the request.

`$CriteriaPrefix` is the prefix of the criteria. It's prefixed to the criteria
before matching.

`$ClassPrefix` is the prefix of the class. It's prefixed to the callback string
when calling the callback.
