<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/JWT.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Register a new user
    public function register($data) {
        if (!isset($data['username']) || !isset($data['email']) || !isset($data['password'])) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }

        $result = $this->userModel->create($data['username'], $data['email'], $data['password']);
        return $result;
    }

    // Login user
    public function login($data) {
        if (!isset($data['email']) || !isset($data['password'])) {
            return ['success' => false, 'message' => 'Missing email or password'];
        }

        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        if (!$this->userModel->verifyPassword($user, $data['password'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // Create JWT token
        $payload = [
            'user_id' => (string)$user->_id,
            'email' => $user->email,
            'username' => $user->username
        ];
        
        $token = JWT::encode($payload);

        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => (string)$user->_id,
                'username' => $user->username,
                'email' => $user->email
            ]
        ];
    }

    // Get current user info
    public function me($userId) {
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        return [
            'success' => true,
            'user' => [
                'id' => (string)$user->_id,
                'username' => $user->username,
                'email' => $user->email
            ]
        ];
    }
}
?>