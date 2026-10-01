# Router::MATCH

(php 8 >= 8.5)

Router::MATCH — Perform a route evaluation and return the matching results.

## Description

```php
public static function MATCH(
  int $bitMethod,
  string|array $cCriteria,
  callable|string|array|null $cCallback = null,
  bool $bTerminate = true,
): array|false
```

Evaluates route criteria against the current path using flat or regex matching, and optionally executes a callback if a match is found.

## Parameters

- bitMethod — Bitwise flag indicating the HTTP method to check against `Router::$MethodBit`. Use bitwise constants such as `Router::GET`, `Router::POST`, `Router::ANY`, etc.
- cCriteria — String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules. Supports the following matching modes:
  - `Router::FLAT` and `Router::IFLAT` to compare direct string paths.
  - `Router::PREG` and `Router::IPREG` to perform regular expression matching. Do not include the delimiter slashes.
- cCallback — Optional callback to execute upon successful match. Can be a callable, fully qualified class method string (e.g., `'Path\To\Class::Method'`), or an array of `[class, method]`. Pass `null` to skip execution.
- bTerminate — Whether to terminate script execution after running the callback, preventing other routes from executing. Defaults to `true`.

## Return values

Returns an array `[true, $matches]` on a successful match without callback execution, `[true, $result]` on a successful match with callback execution, or `false` if no match is found or the HTTP method does not match.

## Examples

### Match a static path with GET method

```php
$oRouter = new Router();
$result = $oRouter->MATCH(Router::GET, '/about');
if ($result !== false) {
  echo "Matched the /about path";
}
```

### Match with a callback

```php
$oRouter = new Router();
$oRouter->MATCH(Router::POST, '/api/users', function() {
  echo "Creating a new user";
});
```

### Match using regex with case-insensitive flag

```php
$oRouter = new Router();
$oRouter->MATCH(
  Router::GET,
  ['^/users/(\d+)$', Router::IPREG],
  function($userId) {
    echo "User ID: " . $userId;
  }
);
```

### Match any HTTP method

```php
$oRouter = new Router();
$oRouter->MATCH(Router::ANY, '/contact', function() {
  echo "Contact page";
});
```

### Match with class method callback

```php
$oRouter = new Router();
$oRouter->MATCH(Router::GET, '/', 'Application\Controllers\Home::index');
```

### Match without terminating further routes

```php
$oRouter = new Router();
$oRouter->MATCH(Router::GET, '/api/data', function() {
  echo "API data endpoint";
}, false); // Allow other routes to match

$oRouter->MATCH(Router::GET, '/api/data', function() {
  echo "This will also execute";
}, false);
```

## See Also

- [Router aliases](router.aliases.md) — Convenient shortcuts for specific HTTP methods
- [Router::init](router.init.md) — Initialize the router
