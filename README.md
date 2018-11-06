# Scissor HMVC PHP Framework

Simple light HMVC framework for PHP


## Composer

Install scissor using [Composer](http://getcomposer.org/)

```bash
composer require craftsware/scissor
```
Instantiate scissor in your index.php and run.
```php
// index.php

// PSR-4 Autoload
require '../vendor/autoload.php';

// Scissor
$app = new Craftsware\Scissor;

$app->run();

```
