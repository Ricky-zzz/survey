<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Survey;
use App\Models\Response as ResponseModel;
use App\Models\Respondent;
use App\Models\File;

class ResponseController
{
    private $surveyModel;
    private $responseModel;
    private $respondentModel;
    private $fileModel;

    public function __construct(
        Survey $surveyModel,
        ResponseModel $responseModel,
        Respondent $respondentModel,
        File $fileModel
    ) {
        $this->surveyModel = $surveyModel;
        $this->responseModel = $responseModel;
        $this->respondentModel = $respondentModel;
        $this->fileModel = $fileModel;
    }

    /**
     * GET /admin/surveys/{id}/results
     * View survey results and analytics
     */
    public function viewResults(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $survey = $this->surveyModel->getWithSections($surveyId);

        if (!$survey) {
            return $this->renderNotFound($response);
        }

        try {
            // Get summary stats
            $stats = [
                'total_responses' => $this->respondentModel->getSubmittedCount($surveyId),
                'total_files' => $this->fileModel->getCountBySurvey($surveyId),
                'total_storage' => $this->formatBytes($this->fileModel->getTotalSizeBySurvey($surveyId))
            ];

            // Get respondents with their responses
            $respondents = $this->respondentModel->getBySurvey($surveyId);
            
            // Enrich respondents with their response data
            foreach ($respondents as &$respondent) {
                $respondent['responses'] = $this->responseModel->getByRespondent($respondent['id']);
                $respondent['files'] = $this->fileModel->getByRespondent($respondent['id']);
            }

            // Get question-level analytics
            $analytics = [];
            foreach ($survey['sections'] as $section) {
                foreach ($section['questions'] as $question) {
                    $analytics[$question['id']] = $this->getQuestionAnalytics($question);
                }
            }

            return $this->render($response, 'admin/results', [
                'survey' => $survey,
                'stats' => $stats,
                'respondents' => $respondents,
                'analytics' => $analytics
            ]);
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage());
        }
    }

    /**
     * GET /admin/surveys/{id}/respondents
     * View list of respondents
     */
    public function viewRespondents(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $survey = $this->surveyModel->getById($surveyId);

        if (!$survey) {
            return $this->renderNotFound($response);
        }

        try {
            // Get pagination params
            $page = (int)($request->getQueryParams()['page'] ?? 1);
            $limit = 20;
            $offset = ($page - 1) * $limit;

            // Get respondents with pagination
            $respondents = $this->respondentModel->getBySurvey($surveyId, $limit, $offset);
            $total = $this->respondentModel->getSubmittedCount($surveyId);
            $totalPages = ceil($total / $limit);

            return $this->render($response, 'admin/respondents', [
                'survey' => $survey,
                'respondents' => $respondents,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'total' => $total
            ]);
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage());
        }
    }

    /**
     * GET /admin/respondents/{id}
     * View single respondent and their responses
     */
    public function viewRespondent(Request $request, Response $response, $args)
    {
        $respondentId = $args['id'];
        $respondent = $this->respondentModel->getWithResponsesAndFiles($respondentId);

        if (!$respondent) {
            return $this->renderNotFound($response);
        }

        try {
            $survey = $this->surveyModel->getById($respondent['survey_id']);
            
            // Format responses by section for easier display
            $responsesBySection = $this->groupResponsesBySection($respondent['responses'], $survey);

            return $this->render($response, 'admin/respondent-detail', [
                'respondent' => $respondent,
                'survey' => $survey,
                'responsesBySection' => $responsesBySection,
                'files' => $respondent['files']
            ]);
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage());
        }
    }

    /**
     * GET /admin/surveys/{id}/export
     * Export survey results as CSV or JSON
     */
    public function exportResults(Request $request, Response $response, $args)
    {
        $surveyId = $args['id'];
        $format = $request->getQueryParams()['format'] ?? 'csv';

        $survey = $this->surveyModel->getWithSections($surveyId);
        if (!$survey) {
            return $this->renderNotFound($response);
        }

        try {
            $respondents = $this->respondentModel->getBySurvey($surveyId);

            if ($format === 'json') {
                return $this->exportJSON($response, $survey, $respondents);
            } else {
                return $this->exportCSV($response, $survey, $respondents);
            }
        } catch (\Exception $e) {
            return $this->renderWithError($response, $e->getMessage());
        }
    }

    /**
     * Get analytics for a specific question
     */
    private function getQuestionAnalytics($question)
    {
        $analytics = [
            'id' => $question['id'],
            'text' => $question['question_text'],
            'type' => $question['type']
        ];

        // Type-specific analytics
        if ($question['type'] === 'scale' || $question['type'] === 'yesno') {
            // Get average and distribution for scale/yesno
            $analytics['average'] = $this->responseModel->getAverageForQuestion($question['id']);
            $analytics['statistics'] = $this->responseModel->getStatisticsForQuestion($question['id']);
        } else if ($question['type'] === 'multiple_choice') {
            // Get distribution of choices
            $analytics['statistics'] = $this->responseModel->getStatisticsForQuestion($question['id']);
        }
        // For text and file_upload, just count responses

        $responseCount = count($this->responseModel->getByQuestion($question['id']));
        $analytics['response_count'] = $responseCount;

        return $analytics;
    }

    /**
     * Group responses by section for easier display
     */
    private function groupResponsesBySection($responses, $survey)
    {
        $grouped = [];

        foreach ($survey['sections'] as $section) {
            $grouped[$section['id']] = [
                'title' => $section['title'],
                'responses' => []
            ];

            foreach ($section['questions'] as $question) {
                $response = array_filter($responses, fn($r) => $r['question_id'] == $question['id']);
                
                if (!empty($response)) {
                    $grouped[$section['id']]['responses'][$question['id']] = [
                        'text' => $question['question_text'],
                        'type' => $question['type'],
                        'answer' => reset($response)['answer_value']
                    ];
                }
            }
        }

        return $grouped;
    }

    /**
     * Export results as CSV
     */
    private function exportCSV(Response $response, $survey, $respondents)
    {
        $filename = "survey_{$survey['id']}_results_" . date('Y-m-d_His') . '.csv';

        // Build CSV headers
        $headers = ['Respondent ID', 'submitted_at'];
        
        foreach ($survey['sections'] as $section) {
            foreach ($section['questions'] as $question) {
                $headers[] = "[{$section['title']}] {$question['question_text']}";
            }
        }

        // Build CSV rows
        $csv = fopen('php://memory', 'r+');
        fputcsv($csv, $headers);

        foreach ($respondents as $respondent) {
            $row = [$respondent['id'], $respondent['submitted_at']];
            
            $responses = $this->responseModel->getByRespondent($respondent['id']);
            
            foreach ($survey['sections'] as $section) {
                foreach ($section['questions'] as $question) {
                    $answer = '';
                    
                    foreach ($responses as $response) {
                        if ($response['question_id'] == $question['id']) {
                            $answer = $response['answer_value'];
                            break;
                        }
                    }
                    
                    $row[] = $answer;
                }
            }
            
            fputcsv($csv, $row);
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        $response->getBody()->write($content);
        return $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export results as JSON
     */
    private function exportJSON(Response $response, $survey, $respondents)
    {
        $filename = "survey_{$survey['id']}_results_" . date('Y-m-d_His') . '.json';

        $exportData = [
            'survey' => [
                'id' => $survey['id'],
                'name' => $survey['name'],
                'exported_at' => date('Y-m-d H:i:s')
            ],
            'respondents' => array_map(function($respondent) {
                return [
                    'id' => $respondent['id'],
                    'submitted_at' => $respondent['submitted_at'],
                    'responses' => $this->responseModel->getByRespondent($respondent['id'])
                ];
            }, $respondents)
        ];

        $response->getBody()->write(json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    // Helper methods
    private function render(Response $response, $template, $data = [])
    {
        ob_start();
        extract($data);
        include __DIR__ . "/../Views/{$template}.php";
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    private function renderWithError(Response $response, $message)
    {
        $response->getBody()->write("Error: {$message}");
        return $response->withStatus(500);
    }

    private function renderNotFound(Response $response)
    {
        $response->getBody()->write('Survey not found');
        return $response->withStatus(404);
    }

    private function jsonResponse(Response $response, $data, $statusCode = 200)
    {
        $response->getBody()->write(json_encode($data));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }
}
