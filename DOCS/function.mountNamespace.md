# Router::IsApi

(PHP 8)
Perform a namespace-based evaluation for the request.

## Description

```php
public static MountNamespace(
  string $BaseNamespace,
  string $BaseCriteria = '/',
  int    $Verb         = self::ANY,
  bool   $Terminate    = true
): bool
```

## Parameters

- `BaseNamespace`
  Path to the root namespace where the evaluation will be perormed.
- `BaseCriteria`
  The root path to be used for the evaluation. (root request)
- `Verb`
  Bitwise representation of the desired HTTP verbs to be handled.

  The possible values are: `Router::GET`, `Router::HEAD`, `Router::POST`,
  `Router::PUT`, `Router::DELETE`, `Router::CONNECT`, `Router::OPTIONS`,
  `Router::TRACE` and `Router::PATCH`. Each flag responds to each type of HTTP
  verb.

  Multiple options can be used using the or `|` operator.
- `Terminate`
  Flag to perform execution shutdown after `Callback` finishes it's performing.

## Return values

Return true if a callback was called and performed it's execution succesfully
(nothing thrown), false if no call was performed, and throw if `$Callback` does
it.

> # **Warning**
> This function may return boolean `false`, but may also return a non-boolean
> which evaluates to `false`. Use of the identique `===` operator is advised
> when handling returned value of this function

## Examples

@TODO.

## Throws

This function throw a `EXCEPTION` if `$Verb` or `$EvalFlags` are set outide of
its allowed values.

## Final notes

( :warning: ) Please be aware that this function is still a work in progress.
Even if it's functional, it's documentation and references may not be precise.
