<?php

// PUBLIC ROUTES
$app->get('/', 'SurveyController:publicIndex');
$app->get('/surveys/{id}/take', 'RespondentController:takeSurvey');
$app->post('/surveys/{id}/submit', 'RespondentController:submitSurvey');
$app->get('/surveys/{id}/thank-you', 'RespondentController:thankYou');

// ADMIN ROUTES - Authentication
$app->get('/admin/login', 'AdminController:loginForm');
$app->post('/admin/login', 'AdminController:login');
$app->get('/admin/logout', 'AdminController:logout');

// ADMIN ROUTES - Surveys
$app->get('/admin/surveys', 'SurveyController:index');
$app->get('/admin/surveys/create', 'SurveyController:createForm');
$app->post('/admin/surveys', 'SurveyController:store');
$app->get('/admin/surveys/{id}/edit', 'SurveyController:editForm');
$app->post('/admin/surveys/{id}', 'SurveyController:update');
$app->post('/admin/surveys/{id}/delete', 'SurveyController:delete');

// ADMIN ROUTES - Results & Respondents
$app->get('/admin/surveys/{id}/results', 'ResponseController:viewResults');
$app->get('/admin/surveys/{id}/respondents', 'ResponseController:viewRespondents');
$app->get('/admin/surveys/{id}/respondents/{rid}', 'ResponseController:viewRespondent');

// ADMIN ROUTES - Sections (AJAX)
$app->post('/admin/surveys/{id}/sections', 'SectionController:store');
$app->post('/admin/surveys/{id}/sections/{sid}', 'SectionController:update');
$app->post('/admin/surveys/{id}/sections/{sid}/delete', 'SectionController:delete');

// ADMIN ROUTES - Questions (AJAX)
$app->post('/admin/surveys/{id}/questions', 'QuestionController:store');
$app->post('/admin/surveys/{id}/questions/{qid}', 'QuestionController:update');
$app->post('/admin/surveys/{id}/questions/{qid}/delete', 'QuestionController:delete');
$app->post('/admin/surveys/{id}/questions/{qid}/options', 'QuestionController:storeOption');
$app->post('/admin/surveys/{id}/questions/{qid}/options/{oid}/delete', 'QuestionController:deleteOption');

