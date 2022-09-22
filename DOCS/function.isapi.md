# Router::IsApi

(PHP 8)
Try to guess if the current request is expecting an API response.

## Description

```php
public Router::IsApi(): bool
```

## Return values

Return true if the current request has a `Content-Type` header with a known API
`mime-type` value.

## Examples

**Return HTML if call was not performed by an API.**
```php
Router::MATCH(Router::POST | Router::PATCH, '/foo/bar', function(){
  file_put_contents('/tmp/foo/bar.baz', file_get_contents('PHP://stdin'));

  // If caller expect an API response, return an array.
  if(Router::IsApi())
  return ['Success' => true];

  // If caller is NOT expecting an API response, just return void (or `null`).
  echo "Received successfully";
}
```


## Final notes

This function is dependant of the headers, so it should not be used if the
application is behind a reverse-proxy that stripes the `Content-Type` header.
