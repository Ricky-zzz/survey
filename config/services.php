<?php

use App\Services\FileUploader;

/** @var \DI\Container $container */

$container->set(FileUploader::class, function ($c) {
    $config = $c->get('config');
    return new FileUploader($config['upload_path']);
});
