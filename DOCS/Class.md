# The Router class

(PHP 8)

## Introduction

Simplifies the process of handle the incoming requests and directing them to
developer-defined code flow.

## Class synopsis

```php
class Router {
  /* Properties */
  public static readonly string $RequestPath;
  public static readonly string $RequestVerb;
  public static readonly int    $RequestVerbBitwise;
  public static          string $ClassPrefix;

  /* Methods */
  public __constructor(
    string $Path = null,
    string $Verb = null
  )
  public static MATCH(
    int    $Verb,
    string $Criteria,
    mixed  $Callback,
    int    $EvalFlags = Router::E_FLAT_I,
    bool   $Terminate = true
  ): mixed
  public static DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT, ANY(
    string $Criteria,
    mixed  $Callback,
    int    $EvalFlags = Router::E_FLAT_I,
    bool   $Terminate = true
  ): mixed
  public static MountNamespace(
    string $BaseNamespace,
    string $BaseCriteria = '/',
    int    $Verb         = self::ANY,
    bool   $Terminate    = true
  ): bool
  public Evaluate(
    string $Criteria,
    int    $EvalFlags = Router::E_FLAT_I
  ): bool
  public IsApi(): bool
  public Mount(
    string   $Prefix,
    callable $Callback,
    callable $notFound = null
  ): bool
}
```

## Properties

- RequestPath
  Path used to perform the current http request.
- RequestVerb
  HTTP verb used to perform the current http request.
- RequestVerbBitwise
  Bitwise representation for `$RequestVerb`. See [Router::Evaluate][].
- ClassPrefix
  String prefixed when calling methods by the `Path\To\Public@method` way. This
  is usefull when calling class in the same napespace.

## Contents

- [Router::__constructor][]
  Create an instance andset the request path and verb.
- [Router::MATCH][]
  Perform code call wheen a request matches with the given conditions.
- [Shortcuts][] (Router::GET, Router::HEAD, Router::POST, etc.)
  Perform code call wheen a request matches with the given conditions.
- [Router::Evaluate][]
  Evaluates the current request against the given evaluation conditions.
- [Router::MountNamespace][]
  Use a namespace-based tree to perform the request evaluations.
- [Router::Mount][]
  Conducts a traffic throug a set of subroutes. (:warning: DRAFT FUNCTION :warning:)
- [Router::IsApi][]
  Guess if the current request is expecting an API response.

[Router::__constructor]:function.constructor.md
[Router::MATCH]:function.match.md
[Router::Evaluate]:function.evaluate.md
[Router::IsApi]:function.isapi.md
[Router::MountNamespace]:function.mountNamespace.md
[Router::Mount]:function.mount.md
[Shortcuts]:shortcuts.md
