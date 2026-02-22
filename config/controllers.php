<?php

use App\Controllers\CandidateController;
use App\Controllers\PartyController;
use App\Controllers\HomeController;
use App\Services\FileUploader;

/** @var \DI\Container $container */

$container->set('CandidateController', function ($c) {
    return new CandidateController(
        $c->get('CandidateModel'),
        $c->get('PartyModel'),
        $c->get(FileUploader::class)
    );
});

$container->set('PartyController', function ($c) {
    return new PartyController($c->get('PartyModel'));
});

$container->set('HomeController', function ($c) {
    return new HomeController(
        $c->get('PartyModel'),
        $c->get('CandidateModel')
    );
});
