# Router::Mount

(PHP 8)
Perform a routing subset to a specific subrouting.

## :warning: DRAFT FUNCTION :warning:

This feature is not yet finished, and its implementation may be erratic.

Avoid using it for now.

## Description

```php
public Mount(
  string   $Prefix,
  callable $Callback,
  callable $notFound = null
): bool
```

## Parameters

- `Prefix`
  Subroute to be mounted.
- `Callback`
  Callable to be called when the subroute matches.
- `notFound`
  Callable to call if no route inside of `Callback` is called.
- `PrependPrefix`
  If true, `$Prefix` will be prepend to each `$Criteria` in `Callback` routing
  calls.

## Return values

Return true if `Callback` was called.

## Examples

@TODO
