# Router::init

(php 8 >= 8.5, Uri\Rfc3986\Uri >= *)

Router::init — Initiates the router and return the class string for Procedural Programming.

## Description

```php
public function init(
  ?string $cPath = null,
  ?string $cMethod = null,
  ?string $cHost = null,
  ?int    $uPort = null
)
```

Calls the `Router::init` method and returns an instance for Object-oriented programming.

## Parameters

- cPath — A string for the current requested path. Use `null` to infer from `$_SERVER['REQUEST_URI']`.
- cMethod — A string for the current request method. Use `null` to infer from `$_SERVER['REQUEST_METHOD']`.
- cHost — A string for the current request host. Use `null` to infer from `$_SERVER['HTTP_HOST']`.
- uPort — An unsigned integer for the current request post. Use `null` to infer from `$_SERVER['SERVER_PORT']`.

## Examples

### Initiate with the `$_SERVER` values

```php
Router::init();
```

### Overwrite the requested host and port

```php
Router::init(null, null, 'Router.localhost', 80);
```
