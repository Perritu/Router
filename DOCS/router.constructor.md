# Router::__construct

(php 8 >= 8.5, Uri\Rfc3986\Uri >= *)

Router::__construct — Initiates the router and instances the Router for Object-oriented programming.

## Description

```php
public function __construct(
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
$oRouter = new Router();
```

### Overwrite the requested host and port

```php
$oRouter = new Router(null, null, 'Router.localhost', 80);
```

### Simple hello routine

```php
$oRouter = new Router();
$oRouter->GET('/', function(){
  echo "Hello world!";
});
```
