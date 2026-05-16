<?php
namespace App\Middleware;

use App\Core\JWTManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthMiddleware {
    public function __invoke(Request $request, RequestHandler $handler): Response {
        $headers = $request->getHeaders();
        $jwtManager = new JWTManager();
        $token = $jwtManager->extractToken($headers);

        if (!$token) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $userData = $jwtManager->validateToken($token);
        if (!$userData) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'Invalid token']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $request = $request->withAttribute('user', $userData);
        return $handler->handle($request);
    }
}
?>