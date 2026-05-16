<?php
namespace App\Controllers;

use App\Core\Database;
use App\Core\JWTManager;
use Ramsey\Uuid\Uuid;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class AuthController {
    public function login(Request $request, Response $response) {
        $data = json_decode($request->getBody()->getContents(), true);
        $db = Database::getInstance();
        
        $user = $db->fetch('SELECT * FROM users WHERE email = ?', [$data['email']]);
        
        if (!$user || !password_verify($data['password'], $user['password'])) {
            $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        
        $jwtManager = new JWTManager();
        $token = $jwtManager->generateToken([
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role']
        ]);
        
        $response->getBody()->write(json_encode([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'role' => $user['role']
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function register(Request $request, Response $response) {
        $data = json_decode($request->getBody()->getContents(), true);
        $db = Database::getInstance();
        
        $existing = $db->fetch('SELECT id FROM users WHERE email = ? OR username = ?', [$data['email'], $data['username']]);
        if ($existing) {
            $response->getBody()->write(json_encode(['error' => 'User already exists']));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }
        
        $userData = [
            'id' => Uuid::uuid4()->toString(),
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'full_name' => $data['full_name'] ?? '',
            'role' => 'user'
        ];
        
        $db->insert('users', $userData);
        
        $jwtManager = new JWTManager();
        $token = $jwtManager->generateToken([
            'id' => $userData['id'],
            'username' => $userData['username'],
            'email' => $userData['email'],
            'role' => 'user'
        ]);
        
        $response->getBody()->write(json_encode([
            'token' => $token,
            'user' => [
                'id' => $userData['id'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'full_name' => $userData['full_name'],
                'role' => 'user'
            ]
        ]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function logout(Request $request, Response $response) {
        $response->getBody()->write(json_encode(['message' => 'Logged out successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function me(Request $request, Response $response) {
        $user = $request->getAttribute('user');
        $response->getBody()->write(json_encode($user));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
?>