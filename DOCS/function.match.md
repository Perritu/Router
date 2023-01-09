# Router::MATCH

(PHP 8)
Perform code call wheen a request matches with the given conditions.

## Description

```php
public Router::MATCH(
  int    $Verb,
  string $Criteria,
  mixed  $Callback,
  int    $EvalFlags = Router::E_FLAT_I,
  bool   $Terminate = true
): mixed
```

## Parameters

- `Verb`
  Bitwise representation of the desited HTTP verbs to be handled.

  The possible values are: `Router::GET`, `Router::HEAD`, `Router::POST`,
  `Router::PUT`, `Router::DELETE`, `Router::CONNECT`, `Router::OPTIONS`,
  `Router::TRACE` and `Router::PATCH`. Each flag responds to each type of HTTP
  verb.

  Multiple options can be used using the or `|` operator.
- `Criteria`
  String to be used in the matching process.
  See [Router::Evaluate][].
- `Callback`
  Either a [callable][] or a `Path\To\Public@method` to be called.

  Note than the path is evaluated from the very root, and pre-pending
  `Router::ClassPrefix` value, which is empty by default.
- `EvalFlags`
  Bitwise flags to be used during the matching process.
  See [Router::Evaluate][].

  The possible values are:
  - `Router::E_FLAT`
    Perform a plain text evaluation to the requested path.
  - `Router::E_FLAT_I`
    Same as `Router::E_FLAT`, but case insensitively.
  - `Router::E_PREG`
    Perform the evaluation using [preg_match][].
  - `Router::E_PREG_I`
    Same as `Router::E_PREG`, but case insensitively.

- `Terminate`
  Flag to perform execution shutdown after `Callback` finishes it's performing.

## Return values

Return true if `$Callback` was called and performed it's execution succesfully
(nothing thrown), false if no call was performed, and throw if `$Callback` does
it.

> # **Warning**
> This function may return boolean `false`, but may also return a non-boolean
> which evaluates to `false`. Use of the identique `===` operator is advised
> when handling returned value of this function

## Examples

**Example #1. Define a static `/about` handler for `GET` HTTP verb.**

```php
Router::MATCH(Router::GET, '/about', function(){
  echo "<h1>About</h1>";
});
```

In this case, `/about` will be the same if the request is performed with
`/ABOUT`, or case-variants like `/About`. This can be prevented by using the
`Router::E_FLAT` flag.

```php
Router::MATCH(Router::GET, '/about', function(){
  // ...
}, Router::E_FLAT); // Note. The default value is `Router::E_FLAT_I`.
```

`$Callback` can also receive parametters if the evaluation is performed with
PREG engine instead of plain check. To do so, use the flags `Router::E_PREG` or
`Router::E_PREG_I`.

```php
Router::MATCH(Router::GET, '\/area\/(\d+)', function($AreaId){
  $AreaId = @intval($AreaId);
  if (0 <= $AreaId) $AreaId = "with invalid parametters";

  echo "You requested the area $AreaId!";
}, Router::E_PREG_I); // Or `Router::E_PREG` if don't want '/Area' nor '/AREA'.
```

**Example #2. Handling Multiple HTTP verbs.**

```php
Router::MATCH(Router::GET | Router::POST | Router::PUT, '/actions', function(){
  // This function handles GET, POST and PUT requests fot '/actions'.
});
```

In this example, the operator or `|` is used to define multiple type of HTTP
verbs to be handled by `$Callback`.

**Example #3. Issuing a payload to an API.**

```php
Router::MATCH(Router::GET, '\/ytURL\/([\w\-\+_]+)', function($slug){
  return [
    "url" => "https://youtu.be/$slug",
  ];
}, Router::E_PREG);
```

The returned value will be treated as an API response if the type is an array,
and in this case, the answare will be formatted according to the response
requested by the client using the value of the header 'Content-type', taking a
value from the following:
- JSON for `application/json`.
- XML for `application/xml` or `text/xml`.

If no header is found, or it's value is none of the accepted values, JSON will
be used.

**Example #4. Using a `Path\To\Public@method` string.**

```php
Router::MATCH(Router::GET, '/status', 'App\Status@getReport');
```

This will call the `getReport` public method and do the same behavoir as if it
was a `function`. If the method is not a satic method, the parent class will be
instanced with no parametters.

## Throws

This function can throw an `EXCEPTION` if `$Callback` is neither a `callable`
nor a valid string.

This function throw any throwable throwed by `$Callback`, or if a valid string,
by the `__constructor` of it's class if not a satic method.

This function throw a `EXCEPTION` if `$Verb` or `$EvalFlags` are set outide of
its allowed values.

## Final notes

Router aliases can be used if only one HTTP verb is desired to be handled by
`$Callback`.

[Router::Evaluate]:function.evaluate.md
[preg_match]:https://www.php.net/manual-lookup.php?pattern=function.preg_match
[callable]:https://www.php.net/manual-lookup.php?pattern=language.types.callable
