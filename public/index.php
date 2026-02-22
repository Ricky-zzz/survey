<?php

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Medoo\Medoo;

// configs
$dbConfig = require __DIR__ . '/../config/database.php';
$appConfig = require __DIR__ . '/../config/config.php';

// container make and register
$container = new Container();
$container->set('config', $appConfig);
$container->set('db', function () use ($dbConfig) {
    return new Medoo($dbConfig);
});

require __DIR__ . '/../config/models.php';
require __DIR__ . '/../config/services.php';
require __DIR__ . '/../config/controllers.php';

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Content-Type', 'text/html; charset=utf-8');
});

require __DIR__ . '/../config/routes.php';

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$app->run();
