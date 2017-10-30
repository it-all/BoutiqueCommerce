<?php
declare(strict_types=1);

define('APP_ROOT', __DIR__ . '/../' );
require APP_ROOT . '/../vendor/autoload.php';

// in case of collision, env.php value overrides
$config = array_replace_recursive(
    require APP_ROOT . 'config/config.php',
    require APP_ROOT . 'config/env.php'
);

require __DIR__ . '/../../vendor/it-all/spaghettify/Src/init.php';

// Instantiate Slim PHP
$settings = require APP_ROOT . 'config/slim3/settings.php';
$slim = new \Slim\App($settings);

$container = $slim->getContainer();

// Set up Slim dependencies
require APP_ROOT . 'config/slim3/dependencies.php';

// remove Slim's Error Handling
unset($container['errorHandler']);
unset($container['phpErrorHandler']);

// Middleware registration
require APP_ROOT . 'config/slim3/middleware.php';

// Register routes
require APP_ROOT . 'config/slim3/routes.php';

$slim->run();
