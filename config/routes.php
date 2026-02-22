<?php

$app->get('/', 'HomeController:index');

// Candidate routes group
$app->group('/candidates', function ($group) {
    $group->get('', 'CandidateController:index');
    $group->get('/create', 'CandidateController:create');
    $group->post('', 'CandidateController:store');
    $group->get('/{id}/edit', 'CandidateController:edit');
    $group->post('/{id}', 'CandidateController:update');
    $group->post('/{id}/delete', 'CandidateController:delete');
});

// Party routes group
$app->group('/parties', function ($group) {
    $group->get('', 'PartyController:index');
    $group->get('/create', 'PartyController:create');
    $group->post('', 'PartyController:store');
    $group->get('/{id}/edit', 'PartyController:edit');
    $group->post('/{id}', 'PartyController:update');
    $group->post('/{id}/delete', 'PartyController:delete');
});
