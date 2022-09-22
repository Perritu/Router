# Router::Evaluate

(PHP 8)
Perform request evaluation against given parametters.

## Description

```php
public Router::Evaluate(
  string $Criteria,
  int    $EvalFlags = Router::E_FLAT_I,
): bool
```

## Parameters

- `Criteria`
  String to be used in the matching process.
- `EvalFlags`
  Bitwise flags to be used during the matching process.

  The possible values are:
  - `Router::E_FLAT`
    Perform a plain text evaluation to the requested path.
  - `Router::E_FLAT_I`
    Same as `Router::E_FLAT`, but case insensitively.
  - `Router::E_PREG`
    Perform the evaluation using [preg_match].
  - `Router::E_PREG_I`
    Same as `Router::E_PREG`, but case insensitively.

## Return values

Return true if the given evaluation conditions match the current request.

## Examples

@TODO

## Throws

This function throw a `EXCEPTION` if `$Criteria` is malformed or invalid.

This function throw a `EXCEPTION` if `$EvalFlags` is outide of its allowed values.

[preg_match]:https://www.php.net/manual-lookup.php?pattern=function.preg_match
