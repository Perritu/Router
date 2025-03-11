# Router::MATCH

Launch the defined callback if given criteria matches the request.

## Description

```php
public static function MATCH(
  int $MethodBit,
  string|array $Criteria,
  callable|string|array $Callback,
  bool $Terminate = true
): mixed
```

## Parameters

- `$MethodBit`
  > The bitwise representation of the desired HTTP verbs to be handled.
- `$Criteria`
  > Criteria to be used.
  >
  > The criteria can be a standalone string or an array where the first
  > element is the criteria and the second is the method to process it.
  >
  > For instance, a criteria is checked in flat case-insensitive mode.
- `$Callback`
  > String or callable to be executed. Using an array of two elements
  > can be convenient to use a class name as the first element and the
  > method name as the second.
- `$Terminate`
  > If true, the ejection will be terminated after calling `$Callback`.

## Returns

If `$Callback` is executed, the return value is returned. Otherwise,
`null` is returned.

## Errors/Exceptions

If using a string as the callback, an `Exception` is thrown if the
class or the method does not exist, or if the method is not public.

## Examples

**Handling index with GET and POST**

```php
Router::MATCH(
  Router::GET | Router::POST,
  '/',
  function () { echo 'Hello world!'; },
);
```

The `$MethodBit` parameter can be mixed using the OR bitwise operator `|`
to handle multiple HTTP methods.

**Using a class name as the callback**

```php
Router::MATCH(
  Router::GET,
  '/',
  [Index::class, 'index'],
);
Router::MATCH(
  Router::GET,
  '/',
  'Path\To\Index::index',
);
Router::MATCH(
  Router::GET,
  '/',
  'Path/To/Index@index',
);
```

You can use a string or an array of two elements to specify the callback.

When using a string, you can separate the class name and the method name
with either `::` or `@`.

**Using a regular expression as the criteria**

```php
Router::MATCH(
  Router::GET,
  ['\/Ticket\/(\d+)\/?', Router::PREG]
  function (int $TicketID) { echo "Ticket #$TicketID"; },
);
```

When using a regular expression, groupings can be used to extract data from
the requested path. In this case, the `$Criteria` parameter is an array
to specify the regular expression and the method to process it.

Valid processing flags are:
- `Router::FLAT`: Check the criteria against the request path as-is.
- `Router::PREG`: Use the criteria as a regular expression to match the request.
- `Router::IFLAT` and `Router::IPREG`: Case-insensitive variants.

**Not terminating the ejection after calling the callback**

```php
Router::MATCH(
  Router::GET,
  '/',
  function () { echo 'Hello world!'; },
  false,
);
```

By default, the php execution is terminated after the callback is called.
by setting `$Terminate` to `false`, the execution will continue and the
router will return the callback return value.

## @TODO

Move the flags usage to the `Criteria` parameter. Pending review.
