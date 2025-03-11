# Router::USE

Use a given namespace to handle the request in given conditions

## Description

```php
public static function USE(
  string $Namespace,
  string $MountPoint,
  int $MethodBit = self::ANY,
  bool $Terminate = true
): void
```

Using a namespace emulates a directory structure, where the classes
in the namespace are mapped like files in the directory.

The way to resolve the request is by using a method name that matches
the request method used to request the resource. This must be in
UPPERCASE and must be a public method. It doesn't matter if the
method is static or not.

## Parameters

- `$Namespace`
  > The namespace to be used. It must be a valid PHP namespace.
- `$MountPoint`
  > The point where the namespace will be mounted. Is the beginning
  > part of the request path that will be resolved by the namespace.
- `$MethodBit`
  > The bitwise representation of the desired HTTP verbs to be handled.
- `$Terminate`
  > If true, the ejection will be terminated after a successful call.

## Returns

`Router::USE` doesn't return anything.

## Errors/Exceptions

Unlike `Router::MATCH`, this method doesn't throw exceptions. If a class can't
be found or has no public method that matches the request, the route is treated
as not matched an the code flow continues.

## Examples

@@TODO
