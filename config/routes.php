<?php

use App\Middleware\AdminAuthMiddleware;

// PUBLIC ROUTES
$app->get('/', 'SurveyController:publicIndex');
$app->get('/surveys/{id}/take', 'RespondentController:takeSurvey');
$app->post('/surveys/{id}/submit', 'RespondentController:submitSurvey');
$app->get('/surveys/{id}/thank-you', 'RespondentController:thankYou');

// ADMIN ROUTES 
$app->get('/admin/login', 'AdminController:loginForm');
$app->post('/admin/login', 'AdminController:login');
$app->get('/admin/logout', 'AdminController:logout');

// ADMIN ROUTES 
$app->group('/admin', function($group) {
    // Dashboard redirect
    $group->get('', function ($request, $response) {
        return $response->withHeader('Location', '/admin/surveys')->withStatus(302);
    });
    
    // Survey management
    $group->get('/surveys', 'SurveyController:index');
    $group->get('/surveys/create', 'SurveyController:createForm');
    $group->post('/surveys', 'SurveyController:store');
    $group->get('/surveys/{id}/edit', 'SurveyController:editForm');
    $group->post('/surveys/{id}', 'SurveyController:update');
    $group->post('/surveys/{id}/delete', 'SurveyController:delete');
    
    // Results & Respondents
    $group->get('/surveys/{id}/results', 'ResponseController:viewResults');
    $group->get('/surveys/{id}/respondents', 'ResponseController:viewRespondents');
    $group->get('/surveys/{id}/respondents/{rid}', 'ResponseController:viewRespondent');
    
    // Sections (AJAX)
    $group->post('/surveys/{id}/sections', 'SectionController:store');
    $group->post('/surveys/{id}/sections/{sid}', 'SectionController:update');
    $group->post('/surveys/{id}/sections/{sid}/delete', 'SectionController:delete');
    
    // Questions (AJAX)
    $group->post('/surveys/{id}/questions', 'QuestionController:store');
    $group->post('/surveys/{id}/questions/{qid}', 'QuestionController:update');
    $group->post('/surveys/{id}/questions/{qid}/delete', 'QuestionController:delete');
    $group->post('/surveys/{id}/questions/{qid}/options', 'QuestionController:storeOption');
    $group->post('/surveys/{id}/questions/{qid}/options/{oid}/delete', 'QuestionController:deleteOption');
    
})->add(AdminAuthMiddleware::class);

