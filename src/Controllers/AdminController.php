<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\Admin;

class AdminController
{
    private $adminModel;

    public function __construct(Admin $adminModel)
    {
        $this->adminModel = $adminModel;
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * GET /admin/login
     * Show login form
     */
    public function loginForm(Request $request, Response $response)
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['admin_id'])) {
            return $response
                ->withHeader('Location', '/admin/surveys')
                ->withStatus(302);
        }

        return $this->render($response, 'admin/login');
    }

    /**
     * POST /admin/login
     * Handle login
     */
    public function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (!isset($data['username']) || !isset($data['password'])) {
            return $this->render($response, 'admin/login', [
                'error' => 'Username and password are required'
            ]);
        }

        // Get admin by username
        $admin = $this->adminModel->getByUsername($data['username']);

        if (!$admin || !$this->adminModel->verifyPassword($admin['id'], $data['password'])) {
            return $this->render($response, 'admin/login', [
                'error' => 'Invalid username or password'
            ]);
        }

        // Set session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        return $response
            ->withHeader('Location', '/admin/surveys')
            ->withStatus(302);
    }

    /**
     * GET /admin/logout
     * Handle logout
     */
    public function logout(Request $request, Response $response)
    {
        session_destroy();
        
        return $response
            ->withHeader('Location', '/')
            ->withStatus(302);
    }

    /**
     * Render helper method
     */
    private function render(Response $response, $template, $data = [])
    {
        ob_start();
        extract($data);
        include __DIR__ . "/../Views/{$template}.php";
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Render 404 error
     */
    private function renderNotFound(Response $response)
    {
        return $response
            ->withStatus(404)
            ->withHeader('Content-Type', 'text/html');
    }
}
