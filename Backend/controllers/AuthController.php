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

    // Search users by username or email
    public function searchUsers($query, $currentUserId) {
        try {
            $users = $this->userModel->searchUsers($query, $currentUserId);
            
            // Format users for response
            $formattedUsers = [];
            foreach ($users as $user) {
                $formattedUsers[] = [
                    'id' => (string)$user->_id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'avatar' => isset($user->avatar) ? $user->avatar : null,
                    'created_at' => isset($user->created_at) ? $user->created_at : null
                ];
            }

            return [
                'success' => true,
                'users' => $formattedUsers,
                'count' => count($formattedUsers)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    // Get user profile by ID
    public function getUserProfile($userId) {
        try {
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }

            return [
                'success' => true,
                'user' => [
                    'id' => (string)$user->_id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'avatar' => isset($user->avatar) ? $user->avatar : null,
                    'created_at' => isset($user->created_at) ? $user->created_at : null
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    // List all users except current user
    public function listUsers($currentUserId) {
        try {
            // Use existing getAllUsers method
            $users = $this->userModel->getAllUsers();
            
            // Format users for response and exclude current user
            $formattedUsers = [];
            foreach ($users as $user) {
                // Skip current user
                if ((string)$user->_id === $currentUserId) {
                    continue;
                }
                
                $formattedUsers[] = [
                    'id' => (string)$user->_id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'avatar' => isset($user->avatar) ? $user->avatar : null,
                    'created_at' => isset($user->created_at) ? $user->created_at : null
                ];
            }

            return [
                'success' => true,
                'users' => $formattedUsers,
                'count' => count($formattedUsers)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
?>