<?php

require_once __DIR__ . '/../utils/JWT.php';

class Auth {
    
    // Verify JWT token from request
    public static function verify() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        
        // Extract token from "Bearer <token>"
        $authHeader = $headers['Authorization'];
        $parts = explode(' ', $authHeader);
        
        if (count($parts) !== 2 || $parts[0] !== 'Bearer') {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token format']);
            exit;
        }
        
        $token = $parts[1];
        $payload = JWT::decode($token);
        
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
            exit;
        }
        
        return $payload;
    }
}
?>