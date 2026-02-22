<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Candidate;
use App\Models\Party;
use App\Services\FileUploader;

class CandidateController
{
    private $candidate;
    private $party;
    private $uploader;

    public function __construct(Candidate $candidate, Party $party, FileUploader $uploader)
    {
        $this->candidate = $candidate;
        $this->party = $party;
        $this->uploader = $uploader;
    }

    public function index(Request $request, Response $response)
    {
        $title = 'Candidates';
        $candidates = $this->candidate->getAll();
        
        ob_start();
        include __DIR__ . '/../Views/candidates/index.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    public function create(Request $request, Response $response)
    {
        $title = 'Add Candidate';
        $parties = $this->party->getAll();
        
        ob_start();
        include __DIR__ . '/../Views/candidates/create.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        if (isset($files['picture']) && $files['picture']->getError() === UPLOAD_ERR_OK) {
            $data['picture'] = $this->uploader->upload($files['picture']);
        }
        
        $this->candidate->create($data);
        
        return $response
            ->withHeader('Location', '/candidates')
            ->withStatus(302);
    }

    public function edit(Request $request, Response $response, $args)
    {
        $title = 'Edit Candidate';
        $candidate = $this->candidate->getById($args['id']);
        $parties = $this->party->getAll();
        
        if (!$candidate) {
            $response->getBody()->write('Candidate not found');
            return $response->withStatus(404);
        }
        
        ob_start();
        include __DIR__ . '/../Views/candidates/edit.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    public function update(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        $candidate = $this->candidate->getById($args['id']);

        if (!$candidate) {
            $response->getBody()->write('Candidate not found');
            return $response->withStatus(404);
        }

        if (isset($files['picture']) && $files['picture']->getError() === UPLOAD_ERR_OK) {
            $this->uploader->delete($candidate['picture']);
            $data['picture'] = $this->uploader->upload($files['picture']);
        }

        $this->candidate->update($args['id'], $data);
        
        return $response
            ->withHeader('Location', '/candidates')
            ->withStatus(302);
    }

    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $candidate = $this->candidate->getById($id);
        
        if (!$candidate) {
            $response->getBody()->write('Candidate not found');
            return $response->withStatus(404);
        }
        
        $this->candidate->delete($id);
        
        return $response
            ->withHeader('Location', '/candidates')
            ->withStatus(302);
    }
}
