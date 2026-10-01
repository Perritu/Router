# Router Aliases

(php 8 >= 8.5)

Router Aliases — Convenient shortcuts for performing MATCH calls for a single HTTP method.

## Introduction

Router aliases provide a shorthand syntax for routing requests to specific HTTP methods. Instead of calling `Router::MATCH()` with an HTTP method constant as the first parameter, you can call the method alias directly. This makes route definitions more readable and concise.

Each alias method corresponds to a specific HTTP method and internally calls `Router::MATCH()` with the appropriate method bit constant.

## Alias Methods

The following alias methods are available:

```php
// Router::ANY,
// Router::DELETE,
// Router::GET,
// Router::HEAD,
// Router::OPTIONS,
// Router::PATCH,
// Router::POST,
// Router::PUT,
Router::QUERY(string|array $cCriteria, callable|string|array|null $cCallback = null, bool $bTerminate = true): array|false
```

## Parameters

All alias methods accept the same parameters:

- cCriteria — String criteria or array payload `[$cCriteria, $bitCriteria]` defining match rules. Supports the following matching modes:
  - `Router::FLAT` and `Router::IFLAT` to compare direct string paths.
  - `Router::PREG` and `Router::IPREG` to perform regular expression matching. Do not include the delimiter slashes.
- cCallback — Optional callback to execute upon successful match. Can be a callable, fully qualified class method string (e.g., `'Path\To\Class::Method'`), or an array of `[class, method]`. Pass `null` to skip execution.
- bTerminate — Whether to terminate script execution after running the callback, preventing other routes from executing. Defaults to `true`.

## Return Values

All alias methods return the same values as `Router::MATCH()`:

- Returns an array `[true, $matches]` on a successful match without callback execution.
- Returns an array `[true, $result]` on a successful match with callback execution.
- Returns `false` if no match is found or the HTTP method does not match.

## Alias Descriptions

### Router::ANY

Matches any HTTP method (DELETE, GET, HEAD, OPTIONS, PATCH, POST, PUT, or QUERY). Useful for routes that should handle all request methods equally.

### Router specific aliases

Matches the HTTP method that matches specific alias.

## Examples

### Basic GET route with a closure

```php
$oRouter = new Router();

$oRouter->GET('/', function() {
  echo 'Welcome to the home page';
});
```

### POST route to create a resource

```php
$oRouter = new Router();

$oRouter->POST('/users', function() {
  echo 'User created successfully';
});
```

### Using regex matching to capture URL parameters

```php
$oRouter = new Router();

$oRouter->GET(['^/users/(\d+)$', Router::PREG], function($userId) {
  echo "Viewing user: " . htmlspecialchars($userId);
});
```

### Case-insensitive regex matching

```php
$oRouter = new Router();

$oRouter->GET(['^/about/([a-z-]+)$', Router::IPREG], function($section) {
  echo "About section: " . htmlspecialchars($section);
});
```

### DELETE route with class method callback

```php
$oRouter = new Router();

$oRouter->DELETE(['^/posts/(\d+)$', Router::PREG], 'Application\Controllers\Posts::delete');
```

### HEAD route for checking resource existence

```php
$oRouter = new Router();

$oRouter->HEAD('/api/resource', function() {
  header('Content-Type: application/json');
});
```

### ANY method for a catch-all route

```php
$oRouter = new Router();

$oRouter->ANY(['/*', Router::PREG], function() {
  http_response_code(404);
  echo 'Page not found';
});
```

### Handling multiple methods for the same route

```php
$oRouter = new Router();

// Handle both GET and HEAD requests
$oRouter->GET('/api/data', 'Application\Api\Data::retrieve');
$oRouter->HEAD('/api/data', 'Application\Api\Data::retrieve');
```

### Multiple routes with non-terminating callbacks

```php
$oRouter = new Router();

// Log all requests
$oRouter->ANY(['.*', Router::PREG], function() {
  error_log('Request: ' . Router::$Path);
}, false); // Don't terminate

// Then handle specific routes
$oRouter->GET('/', function() {
  echo 'Home page';
});
```

### PATCH route for partial updates

```php
$oRouter = new Router();

$oRouter->PATCH(['^/api/items/(\d+)$', Router::PREG], function($itemId) {
  echo "Updating item: " . htmlspecialchars($itemId);
});
```

## Comparison with Router::MATCH

### Using Router::MATCH directly

```php
$oRouter->MATCH(Router::GET, '/about', function() {
  echo 'About page';
});
```

### Using the GET alias

```php
$oRouter->GET('/about', function() {
  echo 'About page';
});
```

Both approaches are functionally equivalent. The alias method is simpler and more readable, especially when dealing with multiple routes.

## Notes

- Alias methods are purely syntactic sugar for `Router::MATCH()`. They do not provide any additional functionality.
- Using aliases makes route definitions more expressive and self-documenting.
- Alias methods follow the principle of least surprise: each method name clearly indicates which HTTP method it handles.
- You can mix alias methods and `Router::MATCH()` calls within the same routing configuration.

## See Also

- [Router::MATCH](router.match.md) — Perform a route evaluation with explicit method parameter
- [Router::init](router.init.md) — Initialize the router
- [Router::MapNamespace](router.mapnamespace.md) — Mount a namespace as a virtual directory
