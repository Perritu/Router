# Router::Dispatch

Executes the callback and returns the result.

:warning: Protected method.

## Description

```php
protected static function Dispatch(
  callable|string|array $Callback,
  bool $Terminate = true,
  array $Arguments = []
): mixed
```

## Parameters

- `$Callback`
  > The callback to be executed. Literally the callback passed to ::MATCH.
- `$Terminate`
  > If true, the ejection will be terminated after calling `$Callback`.
- `$Arguments`
  > An array of arguments to be passed to the callback.

## Returns

The returned value of the callback.

## Errors/Exceptions

@See the `Router::MATCH` method for the exceptions.

## Examples

@@TODO
