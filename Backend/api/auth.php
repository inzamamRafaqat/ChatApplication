<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../middleware/Auth.php';

$controller = new AuthController();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

// Get the action from URL parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($action) {
        case 'register':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            $result = $controller->register($data);
            echo json_encode($result);
            break;

        case 'login':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            $result = $controller->login($data);
            echo json_encode($result);
            break;

        case 'me':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            $user = Auth::verify();
            $result = $controller->me($user['user_id']);
            echo json_encode($result);
            break;

        case 'search':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            // Verify authentication
            $currentUser = Auth::verify();
            
            $query = isset($_GET['q']) ? trim($_GET['q']) : '';
            
            if (empty($query)) {
                echo json_encode(['success' => false, 'message' => 'Search query is required']);
                exit;
            }
            
            $result = $controller->searchUsers($query, $currentUser['user_id']);
            echo json_encode($result);
            break;

        case 'profile':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            // Verify authentication
            $currentUser = Auth::verify();
            
            $userId = isset($_GET['id']) ? $_GET['id'] : $currentUser['user_id'];
            $result = $controller->getUserProfile($userId);
            echo json_encode($result);
            break;

        case 'list':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                exit;
            }
            
            // Verify authentication
            $currentUser = Auth::verify();
            
            $result = $controller->listUsers($currentUser['user_id']);
            echo json_encode($result);
            break;

        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Action not found']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>