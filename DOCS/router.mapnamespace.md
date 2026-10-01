# Router::MapNamespace

(php 8 >= 8.5)

Router::MapNamespace — Mount a namespace so it behaves as a virtual directory.

## Description

```php
public static function MapNamespace(
  string $cNamespace,
  string $cBasePath,
  int $bitMethods = self::ANY,
  bool $bTerminate = true
): void
```

Maps a namespace and base path to dynamically route requests to classes within that namespace. This allows you to organize your route handlers into a namespace structure where incoming requests are automatically matched to corresponding class methods.

## Parameters

- cNamespace — A string representing the namespace prefix (e.g., `'Application\Controllers'`). Classes within this namespace will be dynamically instantiated based on the request path.
- cBasePath — A string defining the base path prefix to match against incoming requests (e.g., `'/api'`). Requests matching this path will be routed to the specified namespace.
- bitMethods — An integer bitmask specifying which HTTP methods to apply this namespace mapping to. Use constants such as `Router::GET`, `Router::POST`, `Router::ANY`, etc. Defaults to `Router::ANY`.
- bTerminate — A boolean indicating whether to terminate script execution after successfully forwarding to a class method. Defaults to `true`.

## Return Values

Returns `void`. Does not return a value; the method either forwards to the matched class method or returns silently if no match is found.

## Behavior

The method performs the following steps:

1. Checks if the current request method matches the specified `bitMethods`.
2. Verifies that the incoming request path starts with the `cBasePath`.
3. Extracts the remaining path after the base path and converts it into a class path structure.
4. Constructs a fully qualified class name by combining the namespace and the extracted path.
5. Attempts to instantiate the class and call the method matching the HTTP method name.
6. The method must be public to be invoked.
7. If `bTerminate` is `true`, prevents other routes from being processed.

## Examples

### Basic namespace mapping for API endpoints

```php
$oRouter = new Router();
$oRouter->MapNamespace('Application\Controllers', '/api');

// Procedural programming
Router::MapNamespace('Application\Controllers', '/api');
```

With this setup, a GET request to `/api/users` would:
- Extract the remaining path: `users`
- Construct the class name: `Application\Controllers\Users`
- Call the `GET` method on that class (if it exists and is public)

### Namespace mapping for specific HTTP method

```php
$oRouter = new Router();
$oRouter->MapNamespace('Application\Api', '/v1', Router::GET | Router::POST);

// Procedural programming
Router::MapNamespace('Application\Api', '/v1', Router::GET | Router::POST);
```

This maps only GET and POST requests to the `/v1` namespace.

### Nested namespace structure

```php
$oRouter = new Router();
$oRouter->MapNamespace('App\Api\V2\Controllers', '/api/v2');

// Procedural programming
Router::MapNamespace('App\Api\V2\Controllers', '/api/v2');
```

A request to `/api/v2/users/profile` would map to:
- Class: `App\Api\V2\Controllers\Users\Profile`
- Method: The HTTP method name (e.g., `GET`, `POST`)

### Multiple namespace mappings

```php
$oRouter = new Router();
$oRouter->MapNamespace('App\Admin', '/admin', Router::ANY);
$oRouter->MapNamespace('App\Api', '/api', Router::ANY);
$oRouter->MapNamespace('App\Web', '/', Router::ANY);

// Procedural programming
Router::MapNamespace('App\Admin', '/admin', Router::ANY);
Router::MapNamespace('App\Api', '/api', Router::ANY);
Router::MapNamespace('App\Web', '/', Router::ANY);
```

This creates a routing hierarchy where requests are matched from specific to general paths.

## Notes

- The class must exist within the specified namespace.
- The method name must match the HTTP method name in uppercase (e.g., `GET`, `POST`, `DELETE`).
- The method being called must be declared as `public`.
- If no matching class or method is found, the mapping silently returns without error.
- The extracted path components are converted to class names by replacing non-alphanumeric characters with backslashes, creating nested namespace paths.

## See Also

- [Router::MATCH](router.match.md) — Perform a route evaluation with explicit criteria
- [Router aliases](router.aliases.md) — Convenient shortcuts for specific HTTP methods
- [Router::init](router.init.md) — Initialize the router
