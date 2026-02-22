<?php

use App\Models\Candidate;
use App\Models\Party;

/** @var \DI\Container $container */

$container->set('CandidateModel', function ($c) {
    return new Candidate($c->get('db'), $c->get('config'));
});

$container->set('PartyModel', function ($c) {
    return new Party($c->get('db'));
});
