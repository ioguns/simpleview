# SimpleView - View for PHP

The simpleview library provides developers a simple library to complement to render php files.

## Install

To install with composer:

```sh
composer require ioguns/simpleview
```

Requires PHP 8.0 or newer.

## Usage

Here's a basic usage example:

```php
<?php

require '/path/to/vendor/autoload.php';

$view = new \IOguns\SimpleView\View();
$view->setDirectory(__DIR__ . '/layouts/');
$view->setView('child');
echo $view->render();
```