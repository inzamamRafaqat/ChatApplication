<?php

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/FileUpload.php';

class UserController {
    private $userModel;
    private $fileUpload;

    public function __construct() {
        $this->userModel = new User();
        $this->fileUpload = new FileUpload();
    }

    // Update profile
    public function updateProfile($userId, $data) {
        // Handle avatar upload if provided
        if (isset($data['avatar']) && strpos($data['avatar'], 'data:image') === 0) {
            $uploadResult = $this->fileUpload->uploadBase64($data['avatar']);
            if ($uploadResult['success']) {
                $data['avatar'] = $uploadResult['file_url'];
            }
        }

        $result = $this->userModel->updateProfile($userId, $data);
        
        return $result 
            ? ['success' => true, 'message' => 'Profile updated']
            : ['success' => false, 'message' => 'Failed to update profile'];
    }

    // Get user profile
    public function getProfile($userId) {
        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        return [
            'success' => true,
            'user' => [
                'id' => (string)$user->_id,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
                'bio' => $user->bio ?? '',
                'status' => $user->status ?? 'offline',
                'is_admin' => $user->is_admin ?? false
            ]
        ];
    }

    // Search users
    public function searchUsers($query) {
        $users = $this->userModel->search($query);
        
        $formattedUsers = array_map(function($user) {
            return [
                'id' => (string)$user->_id,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
                'status' => $user->status ?? 'offline'
            ];
        }, $users);

        return ['success' => true, 'users' => $formattedUsers];
    }

    // Get all users
    public function getAllUsers() {
        $users = $this->userModel->getAllUsers();
        
        $formattedUsers = array_map(function($user) {
            return [
                'id' => (string)$user->_id,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $user->avatar ?? null,
                'status' => $user->status ?? 'offline',
                'is_admin' => $user->is_admin ?? false
            ];
        }, $users);

        return ['success' => true, 'users' => $formattedUsers];
    }

    // Update status
    public function updateStatus($userId, $status) {
        $result = $this->userModel->updateStatus($userId, $status);
        
        return $result 
            ? ['success' => true, 'message' => 'Status updated']
            : ['success' => false, 'message' => 'Failed to update status'];
    }

    // Make admin (admin only)
    public function makeAdmin($requestingUserId, $targetUserId) {
        $requestingUser = $this->userModel->findById($requestingUserId);
        
        if (!$requestingUser || !($requestingUser->is_admin ?? false)) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        $result = $this->userModel->makeAdmin($targetUserId);
        
        return $result 
            ? ['success' => true, 'message' => 'User promoted to admin']
            : ['success' => false, 'message' => 'Failed to promote user'];
    }

    // Get online users
    public function getOnlineUsers() {
        $users = $this->userModel->getOnlineUsers();
        
        $formattedUsers = array_map(function($user) {
            return [
                'id' => (string)$user->_id,
                'username' => $user->username,
                'avatar' => $user->avatar ?? null
            ];
        }, $users);

        return ['success' => true, 'users' => $formattedUsers];
    }
}
?>