<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Party;

class PartyController
{
    private $party;

    public function __construct(Party $party)
    {
        $this->party = $party;
    }

    public function index(Request $request, Response $response)
    {
        $title = 'Political Parties';
        $parties = $this->party->getAll();
        
        ob_start();
        include __DIR__ . '/../Views/parties/index.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    public function create(Request $request, Response $response)
    {
        $title = 'Add Party';
        
        ob_start();
        include __DIR__ . '/../Views/parties/create.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getParsedBody();
        
        $this->party->create($data);
        
        return $response
            ->withHeader('Location', '/parties')
            ->withStatus(302);
    }

    public function edit(Request $request, Response $response, $args)
    {
        $title = 'Edit Party';
        $party = $this->party->getById($args['id']);
        
        if (!$party) {
            $response->getBody()->write('Party not found');
            return $response->withStatus(404);
        }
        
        ob_start();
        include __DIR__ . '/../Views/parties/edit.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }

    public function update(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();
        $id = $args['id'];
        
        $party = $this->party->getById($id);
        
        if (!$party) {
            $response->getBody()->write('Party not found');
            return $response->withStatus(404);
        }
        
        $this->party->update($id, $data);
        
        return $response
            ->withHeader('Location', '/parties')
            ->withStatus(302);
    }

    public function delete(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $party = $this->party->getById($id);
        
        if (!$party) {
            $response->getBody()->write('Party not found');
            return $response->withStatus(404);
        }
        
        $this->party->delete($id);
        
        return $response
            ->withHeader('Location', '/parties')
            ->withStatus(302);
    }
}
