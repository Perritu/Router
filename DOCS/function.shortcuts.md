# Router shortcuts

Alias functions for the `Router::MATCH` method.

The shortcuts are:

- `Router::ANY`
- `Router::DELETE`
- `Router::GET`
- `Router::HEAD`
- `Router::OPTIONS`
- `Router::PATCH`
- `Router::POST`
- `Router::PUT`

## Description

```php
Router::{ANY|DELETE|GET|HEAD|OPTIONS|PATCH|POST|PUT}(
  string|array $Criteria,
  callable|string|array $Callback,
  bool $Terminate = true
): mixed
```

## Parameters

> See the `Router::MATCH` method for the parameters.

The only exception is the `$MethodBit` parameter. The shortcuts use the
named bitwise constants to specify the HTTP verbs to be handled.
