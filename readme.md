# Perritu/Router

A lightweight, simple, yet blazing fast PHP router.

![Packagist Version][]
![Packagist PHP Version][]
![Packagist License][]
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/f42059a4dc754afd8f6ff26cded3c50e)](https://app.codacy.com/gh/Perritu/Router/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

[![FOSSA Status][]](https://app.fossa.com/projects/git%2Bgithub.com%2FPerritu%2FRouter?ref=badge_shield)

## Features

- Supports for `DELETE`, `GET`, `HEAD`, `OPTIONS`, `PATCH`, `POST` and `PUT`
  request methods.
- Routing shortcuts.
  - `Router::DELETE()`, `Router::GET()`, `Router::HEAD()`, etc.
- Static and dynamic PCRE-based routing.
- Custom `Path` and/or `HTTP-Method` call override.
- Use of `Path\to\public::method` callback.
- Subrouting / route prefixes.
- Subnamespace / namespace prefixes.
- Namespace mapping.

## Requirements

Perritu/Router can run just fine out-of-the-box (even without Composer).

The best way to implement is, of course, through composer, yet, it can be
implemented without it.

All you need is PHP 8.1 or greater and any URL rewriting technique.

## Installation.

There are 2 ways of install.

- Using [composer]. (Recomended)
  - `composer require perritu/router`
- Direct download.
  - Downlad and place the `Router.php` file outside your publicly accessible
    directory, so any call must be performed throug your code flow.
  - Do a `require_once` import from your code flow.
  ```php
  require_once(PROJECT_ROOT .'/include/perritu/router.php');
  ```

## Usage

Once imported, do a `use` statement to start using the router, then, start with
your routing definitions.

Bare example:

```php
// Require statement
require_once('../vendor/autoload.php'); // Or router.php if not using composer.

use Perritu\Router\Router;

// Router definition
Router::GET('/', function() { echo 'Hello World!'; });
```

You can read the documentation in the [DOCS].

## External links.

Codacy: https://app.codacy.com/gh/Perritu/Router/dashboard

Packagist: https://packagist.org/packages/perritu/router

FOSSA: https://app.fossa.com/projects/git%2Bgithub.com%2FPerritu%2FRouter?ref=badge_large

[DOCS]:DOCS/Class.md

[composer]:https://getcomposer.org/download/
[Packagist Version]:https://img.shields.io/packagist/v/perritu/router?style=flat-square
[Packagist PHP Version]:https://img.shields.io/packagist/dependency-v/perritu/router/php?style=flat-square
[Packagist License]:https://img.shields.io/packagist/l/perritu/router?style=flat-square
[FOSSA Status]:https://app.fossa.com/api/projects/git%2Bgithub.com%2FPerritu%2FRouter.svg?type=large
