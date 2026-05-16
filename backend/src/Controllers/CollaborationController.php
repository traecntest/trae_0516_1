<?php
namespace App\Controllers;

use App\Core\Database;
use Ramsey\Uuid\Uuid;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class CollaborationController {
    public function join(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $data = json_decode($request->getBody()->getContents(), true);
        $db = Database::getInstance();
        
        $existing = $db->fetch(
            'SELECT id FROM collaborations WHERE drawing_id = ? AND user_id = ?',
            [$args['drawingId'], $user['id']]
        );
        
        if ($existing) {
            $db->update('collaborations', ['status' => 'active', 'session_id' => $data['session_id']], ['id' => $existing['id']]);
        } else {
            $collabData = [
                'id' => Uuid::uuid4()->toString(),
                'drawing_id' => $args['drawingId'],
                'user_id' => $user['id'],
                'session_id' => $data['session_id'],
                'status' => 'active'
            ];
            $db->insert('collaborations', $collabData);
        }
        
        $response->getBody()->write(json_encode(['message' => 'Joined collaboration']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function leave(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $db = Database::getInstance();
        
        $db->update('collaborations', ['status' => 'inactive'], [
            'drawing_id' => $args['drawingId'],
            'user_id' => $user['id']
        ]);
        
        $response->getBody()->write(json_encode(['message' => 'Left collaboration']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getUsers(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $db = Database::getInstance();
        
        $users = $db->fetchAll(
            'SELECT u.id, u.username, u.full_name, c.status, c.session_id 
            FROM collaborations c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.drawing_id = ? AND c.status = ?',
            [$args['drawingId'], 'active']
        );
        
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $data = json_decode($request->getBody()->getContents(), true);
        $db = Database::getInstance();
        
        $drawing = $db->fetch('SELECT id FROM drawings WHERE id = ?', [$args['drawingId']]);
        if (!$drawing) {
            $response->getBody()->write(json_encode(['error' => 'Drawing not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        if (isset($data['json_data'])) {
            $db->update('drawings', ['json_data' => $data['json_data']], ['id' => $args['drawingId']]);
        }
        
        $response->getBody()->write(json_encode(['message' => 'Update received']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
?>