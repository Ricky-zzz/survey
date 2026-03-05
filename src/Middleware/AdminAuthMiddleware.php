<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AdminAuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, RequestHandler $handler): Response
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if admin is logged in
        if (!isset($_SESSION['admin_id'])) {
            $response = new \Slim\Psr7\Response();
            
            // For AJAX requests, return JSON error
            if ($request->hasHeader('X-Requested-With') && 
                $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                $response->getBody()->write(json_encode([
                    'status' => 'error',
                    'message' => 'Authentication required'
                ]));
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(401);
            }
            
            // For regular requests, redirect to login
            return $response
                ->withHeader('Location', '/admin/login')
                ->withStatus(302);
        }

        // User is authenticated, continue to the next middleware/controller
        return $handler->handle($request);
    }
}