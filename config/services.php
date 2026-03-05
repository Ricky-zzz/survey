<?php

use App\Services\FileUploader;
use App\Middleware\AdminAuthMiddleware;

/** @var \DI\Container $container */

$container->set(FileUploader::class, function ($c) {
    $config = $c->get('config');
    return new FileUploader($config['upload_path']);
});

$container->set(AdminAuthMiddleware::class, function ($c) {
    return new AdminAuthMiddleware();
});
