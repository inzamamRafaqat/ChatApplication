<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../controllers/ChannelController.php';
require_once __DIR__ . '/../middleware/Auth.php';

$controller = new ChannelController();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Verify authentication
$user = Auth::verify();
$userId = $user['user_id'];

// Get ID from URL if present
$id = isset($_GET['id']) ? $_GET['id'] : null;
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'POST':
            if ($action === 'add_member' && $id) {
                $result = $controller->addMember($id, $data, $userId);
            } elseif ($action === 'remove_member' && $id) {
                $result = $controller->removeMember($id, $data, $userId);
            } else {
                $result = $controller->create($data, $userId);
            }
            echo json_encode($result);
            break;

        case 'GET':
            if ($id) {
                $result = $controller->getOne($id, $userId);
            } else {
                $result = $controller->getAll($userId);
            }
            echo json_encode($result);
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Channel ID required']);
                exit;
            }
            $result = $controller->update($id, $data, $userId);
            echo json_encode($result);
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Channel ID required']);
                exit;
            }
            $result = $controller->delete($id, $userId);
            echo json_encode($result);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>