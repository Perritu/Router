# Router::ForwardCallback

(php 8 >= 8.5)

Router::ForwardCallback — Internal protected method to process and dispatch callbacks.

## Description

```php
protected static function ForwardCallback(
  callable|string|array $cCallback,
  array $aArguments = []
): mixed
```

Forwards a callback to the specified target, handling multiple callback formats including callables, fully qualified class method strings, and array notation. This is a protected static method and is only accessible from classes extending the Router class.

## Access Level

**Protected** — This method is not directly accessible from outside the Router class. It can only be called from within the Router class itself or from classes that extend Router. It is used internally by `Router::MATCH()` and `Router::MapNamespace()` to execute route callbacks.

## Parameters

- cCallback — The callback to be executed. Supports three formats:
  - A callable (closure, function reference, array with instance/class and method)
  - A fully qualified class method string in format `'Path\To\Class::Method'` or `'Path\To\Class@Method'`
  - An array in format `[class, method]` where both elements are strings
- aArguments — An array of arguments to pass to the callback. Defaults to an empty array.

## Return Values

Returns the result of the callback execution. The return type is mixed as different callbacks may return different types.

## Exceptions

- **InvalidArgumentException** — Thrown if an array callback does not contain exactly two string elements, or if the callback array format is invalid.
- **RuntimeException** — Thrown if:
  - The callback points to a non-existent class
  - The specified method is not found on the class
  - The method is not declared as public
  - Invocation of the callback fails

## Behavior

The method performs the following steps:

1. If the callback is a string in format `Class::Method` or `Class@Method`:
   - Parses the string to extract class and method names
   - Applies any configured class prefix if the class is not fully qualified
   - Converts the string callback to array format `[class, method]`

2. If the callback is an array:
   - Validates it contains exactly 2 elements, both strings
   - Verifies the class exists
   - Uses reflection to verify the method exists and is public
   - If the method is not static, instantiates the class

3. Finally executes the callback with the provided arguments using `call_user_func_array()`

## Examples

### Extending Router with protected method access

```php
namespace MyApp;

use Perritu\Router\Router;

class CustomRouter extends Router
{
  public function executeCustomCallback($callback, $args = [])
  {
    // Can now call the protected ForwardCallback method
    return self::ForwardCallback($callback, $args);
  }
}

// Usage
$router = new CustomRouter();
$result = $router->executeCustomCallback(function($id) {
  return "User: " . $id;
}, [42]);
```

### Callable format

```php
$callback = function($userId, $action) {
  return "User $userId performed: $action";
};

// Called internally by Router::MATCH when a closure is provided
$result = static::ForwardCallback($callback, [1, 'login']);
```

### Class method string format

```php
// String notation with double colon
$callback = 'Application\Controllers\Users::retrieve';
// or with @ notation
$callback = 'Application\Controllers\Users@retrieve';

// Called internally, applies class prefix if configured
$result = static::ForwardCallback($callback, [123]);
```

### Array format

```php
$callback = ['Application\Controllers\Users', 'delete'];

// Called internally
$result = static::ForwardCallback($callback, [42]);
```

## Error Handling

### Handling invalid array format

```php
try {
  // Invalid: more than 2 elements
  static::ForwardCallback(['Class', 'method', 'extra']);
} catch (InvalidArgumentException $e) {
  echo "Invalid callback format: " . $e->getMessage();
}
```

### Handling non-existent class

```php
try {
  static::ForwardCallback('NonExistent\Class::method', []);
} catch (RuntimeException $e) {
  echo "Class not found: " . $e->getMessage();
}
```

### Handling non-public methods

```php
try {
  // Method exists but is not public
  static::ForwardCallback('Application\Controllers\Users::privateMethod', []);
} catch (RuntimeException $e) {
  echo "Cannot invoke non-public method: " . $e->getMessage();
}
```

## Notes

- This method is protected and only accessible from within the Router class or extending classes
- It is called internally by `Router::MATCH()` and `Router::MapNamespace()` when executing matched route callbacks
- The class prefix (`Router::$ClassPrefix`) is applied during callback resolution for string-based callbacks
- Both static and instance methods are supported; instance methods will be called on a new instance of the class
- Callback arguments passed through the `$aArguments` parameter are passed directly to the callback using `call_user_func_array()`

## See Also

- [Router::MATCH](router.match.md) — Route evaluation that uses ForwardCallback internally
- [Router::MapNamespace](router.mapnamespace.md) — Namespace mapping that uses ForwardCallback internally
- [Router aliases](router.aliases.md) — HTTP method aliases that use ForwardCallback
