## Perritu/Router

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/741da1c4dcac47dea1f3097f5972d865)](https://app.codacy.com/gh/Perritu/Router?utm_source=github.com&utm_medium=referral&utm_content=Perritu/Router&utm_campaign=Badge_Grade_Settings)

A lightweight, simple, yet powerfull routing library for PHP.

### Features

- Supports for `DELETE`, `GET`, `HEAD`, `OPTIONS`, `PATCH`, `POST` and `PUT`
  request verbs.
- Routing shortcuts.
  - `Router::DELETE()`, `Router::GET()`, `Router::HEAD()`, etc.
- Static and dynamic PCRE-based routing.
- Custom `Path` and/or `HTTP-Method` call override.
- Use of `Path\to\public@method` callback.
- Array to API responses.
- Subrouting / route prefixes.
- Subnamespace / namespace prefixes.

### Requirements

Perritu/Router can run just fine out-of-the-box (even without Composer).

The best way to implement is, of course, through composer, yet, it can be
implemented without it.

All you need is PHP 8.1 or greater and any URL rewriting technique.

### Installation.

There are 2 ways of install.
- Using [composer](https://getcomposer.org/download/). (Recomended)
  - `composer require perritu/router`
- Direct download.
  - Downlad and place the `Router.php` file outside your publicly accessible
    directory, so any call must be performed throug your code flow.
  - Do a `require_once` import from your code flow.
  ```php
  require_once(PROJECT_ROOT .'/include/perritu/router.php');
  ```

### Usage

Once imported, do a `use` statement to start using the router, then, start with
your routing definitions.

Bare example:
```php
// Require statement
require_once('../vendor/autoload.php'); // Or router.php if not using composer.

use Perritu\Router\Router as R;

R::MATCH(R::ANY, '.*', function(){
  if(R::IsApi())
  return ['Hello world!'];

  echo '<h1>Hello world!</h1>';
}, R::E_PREG);
```

You can read the documentation in the [DOCS](DOCS/Class.md).
