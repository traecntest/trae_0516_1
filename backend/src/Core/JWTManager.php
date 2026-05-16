<?php
namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTManager {
    private $secret;
    private $expiration;

    public function __construct() {
        $config = require '/mnt/github/trae_0516_1/config/app.php';
        $this->secret = $config['jwt_secret'];
        $this->expiration = $config['session_timeout'];
    }

    public function generateToken($userData) {
        $payload = [
            'iss' => 'cad-collab-api',
            'aud' => 'cad-collab-frontend',
            'iat' => time(),
            'exp' => time() + $this->expiration,
            'data' => $userData
        ];
        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function extractToken($headers) {
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
?>