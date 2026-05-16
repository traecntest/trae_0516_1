<?php
namespace App\Controllers;

use App\Core\Database;
use Ramsey\Uuid\Uuid;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

class DrawingController {
    public function list(Request $request, Response $response) {
        $user = $request->getAttribute('user');
        $db = Database::getInstance();
        
        $drawings = $db->fetchAll(
            'SELECT d.*, u.username as created_by_name FROM drawings d 
            JOIN users u ON d.created_by = u.id 
            WHERE d.created_by = ? 
            ORDER BY d.updated_at DESC',
            [$user['id']]
        );
        
        $response->getBody()->write(json_encode($drawings));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function get(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $db = Database::getInstance();
        
        $drawing = $db->fetch(
            'SELECT d.*, u.username as created_by_name FROM drawings d 
            JOIN users u ON d.created_by = u.id 
            WHERE d.id = ? AND d.created_by = ?',
            [$args['id'], $user['id']]
        );
        
        if (!$drawing) {
            $response->getBody()->write(json_encode(['error' => 'Drawing not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $response->getBody()->write(json_encode($drawing));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function create(Request $request, Response $response) {
        $user = $request->getAttribute('user');
        $data = json_decode($request->getBody()->getContents(), true);
        $db = Database::getInstance();
        
        $drawingData = [
            'id' => Uuid::uuid4()->toString(),
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'json_data' => $data['json_data'] ?? '{}',
            'created_by' => $user['id']
        ];
        
        $db->insert('drawings', $drawingData);
        
        $response->getBody()->write(json_encode(['id' => $drawingData['id'], 'message' => 'Drawing created']));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function update(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $data = json_decode($request->getBody()->getContents(), true);
        $db = Database::getInstance();
        
        $drawing = $db->fetch('SELECT id FROM drawings WHERE id = ? AND created_by = ?', [$args['id'], $user['id']]);
        if (!$drawing) {
            $response->getBody()->write(json_encode(['error' => 'Drawing not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $updateData = [];
        if (isset($data['title'])) $updateData['title'] = $data['title'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        if (isset($data['json_data'])) $updateData['json_data'] = $data['json_data'];
        
        $db->update('drawings', $updateData, ['id' => $args['id']]);
        
        $response->getBody()->write(json_encode(['message' => 'Drawing updated']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function delete(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $db = Database::getInstance();
        
        $drawing = $db->fetch('SELECT id FROM drawings WHERE id = ? AND created_by = ?', [$args['id'], $user['id']]);
        if (!$drawing) {
            $response->getBody()->write(json_encode(['error' => 'Drawing not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $db->delete('drawings', ['id' => $args['id']]);
        
        $response->getBody()->write(json_encode(['message' => 'Drawing deleted']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getVersions(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $db = Database::getInstance();
        
        $versions = $db->fetchAll(
            'SELECT dv.*, u.username as changed_by_name FROM drawing_versions dv 
            JOIN users u ON dv.changed_by = u.id 
            WHERE dv.drawing_id = ? 
            ORDER BY dv.version_number DESC',
            [$args['id']]
        );
        
        $response->getBody()->write(json_encode($versions));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function saveVersion(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $data = json_decode($request->getBody()->getContents(), true);
        $db = Database::getInstance();
        
        $maxVersion = $db->fetch('SELECT MAX(version_number) as max FROM drawing_versions WHERE drawing_id = ?', [$args['id']]);
        $newVersion = ($maxVersion['max'] ?? 0) + 1;
        
        $versionData = [
            'id' => Uuid::uuid4()->toString(),
            'drawing_id' => $args['id'],
            'version_number' => $newVersion,
            'json_data' => $data['json_data'] ?? '{}',
            'changed_by' => $user['id'],
            'change_description' => $data['change_description'] ?? ''
        ];
        
        $db->insert('drawing_versions', $versionData);
        
        $response->getBody()->write(json_encode(['version_number' => $newVersion, 'message' => 'Version saved']));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }

    public function getSharedUsers(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $db = Database::getInstance();
        
        $shared = $db->fetchAll(
            'SELECT sa.*, u.username, u.email FROM shared_access sa 
            JOIN users u ON sa.user_id = u.id 
            WHERE sa.drawing_id = ?',
            [$args['id']]
        );
        
        $response->getBody()->write(json_encode($shared));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function share(Request $request, Response $response, $args) {
        $user = $request->getAttribute('user');
        $data = json_decode($request->getBody()->getContents(), true);
        $db = Database::getInstance();
        
        $targetUser = $db->fetch('SELECT id FROM users WHERE email = ?', [$data['email']]);
        if (!$targetUser) {
            $response->getBody()->write(json_encode(['error' => 'User not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
        
        $shareData = [
            'id' => Uuid::uuid4()->toString(),
            'drawing_id' => $args['id'],
            'user_id' => $targetUser['id'],
            'permission' => $data['permission'] ?? 'view'
        ];
        
        $db->insert('shared_access', $shareData);
        
        $response->getBody()->write(json_encode(['message' => 'Drawing shared']));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }
}
?>