<?php

use App\Controllers\SurveyController;
use App\Controllers\SectionController;
use App\Controllers\QuestionController;
use App\Controllers\RespondentController;
use App\Controllers\ResponseController;
use App\Services\FileUploader;

/** @var \DI\Container $container */

// Survey management controller
$container->set('SurveyController', function ($c) {
    return new SurveyController(
        $c->get('SurveyModel'),
        $c->get('SectionModel'),
        $c->get('AdminModel')
    );
});

// Section management controller
$container->set('SectionController', function ($c) {
    return new SectionController(
        $c->get('SectionModel'),
        $c->get('SurveyModel')
    );
});

// Question management controller
$container->set('QuestionController', function ($c) {
    return new QuestionController(
        $c->get('QuestionModel'),
        $c->get('QuestionOptionModel'),
        $c->get('SectionModel')
    );
});

// Respondent form & submission controller
$container->set('RespondentController', function ($c) {
    return new RespondentController(
        $c->get('SurveyModel'),
        $c->get('RespondentModel'),
        $c->get('ResponseModel'),
        $c->get('FileModel'),
        $c->get(FileUploader::class)
    );
});

// Response results & analytics controller
$container->set('ResponseController', function ($c) {
    return new ResponseController(
        $c->get('SurveyModel'),
        $c->get('ResponseModel'),
        $c->get('RespondentModel'),
        $c->get('FileModel')
    );
});
