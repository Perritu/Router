# Router::__constructor

(PHP 8)
Inits a new instance and sets the path and http verb requested.

## Description

```php
public Router::__constructor(
  string $Path = null,
  string $Verb = null
)
```

## Parameters

- `Path`
  Path used to perform the request.
- `Verb`
  HTTP Verb used to perform the request.

## Examples

**Bare example to use custom variables.**

```php
new Router($Caller->Path, $Caller->Method);
```
