# Router::init / Router::__construct

(PHP 8)
Initialize the router. You can use this method to override the path and method
that will be used by the router.

## Description

```php
public static Router::init(?string $Path = null, ?string $Method = null): string;
```

## Parameters

- `Path`
  > The path to be used by the router. Is the path for the current request.
- `Method`
  > The method to be used by the router. Is the string representation of the
  > HTTP method used by the request. <br>
  > Valid values are: GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS.

## Returns

The `::init` method returns a string that represents the path to the class,
technically, allow the return value to be used as a chain.

In the other hand, the `__construct` method returns the class instance itself.

## Errors/Exceptions

Emits an `\Exception` if the `$Method` parameter is not a valid HTTP method.

## Examples

**Usage to override the path without affecting the method used for the request:**

```php
  Router::init('/api/v1/users');
```

Here, the `$Method` parameter is not provided, so the router will use the
default

**Usage to override the method without affecting the path:**

```php
  Router::init(null, 'GET');
```

The `$Path` parameter can be `null` to use the default path.
