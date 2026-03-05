<?php

use App\Models\Admin;
use App\Models\Survey;
use App\Models\Section;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Respondent;
use App\Models\Response;
use App\Models\File;

/** @var \DI\Container $container */


// Survey system models
$container->set('AdminModel', function ($c) {
    return new Admin($c->get('db'));
});

$container->set('SurveyModel', function ($c) {
    return new Survey($c->get('db'));
});

$container->set('SectionModel', function ($c) {
    return new Section($c->get('db'));
});

$container->set('QuestionModel', function ($c) {
    return new Question($c->get('db'));
});

$container->set('QuestionOptionModel', function ($c) {
    return new QuestionOption($c->get('db'));
});

$container->set('RespondentModel', function ($c) {
    return new Respondent($c->get('db'));
});

$container->set('ResponseModel', function ($c) {
    return new Response($c->get('db'));
});

$container->set('FileModel', function ($c) {
    return new File($c->get('db'));
});

