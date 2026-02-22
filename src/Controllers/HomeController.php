<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Party;
use App\Models\Candidate;

class HomeController
{
    private $party;
    private $candidate;

    public function __construct(Party $party, Candidate $candidate)
    {
        $this->party = $party;
        $this->candidate = $candidate;
    }

    public function index(Request $request, Response $response)
    {
        $title = 'Dashboard';
        $parties = $this->party->getAll();
        $candidates = $this->candidate->getAll();
        
        ob_start();
        include __DIR__ . '/../Views/home.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }
}
