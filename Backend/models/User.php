<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/Bcrypt.php';

class User {
    private $db;
    private $collection = 'users';

    public function __construct() {
        $this->db = new Database();
    }

    // Create a new user with profile
    public function create($username, $email, $password, $avatar = null, $bio = '') {
        $existing = $this->db->findOne($this->collection, ['email' => $email]);
        if ($existing) {
            return ['success' => false, 'message' => 'User already exists'];
        }

        $user = [
            '_id' => new MongoDB\BSON\ObjectId(),
            'username' => $username,
            'email' => $email,
            'password' => Bcrypt::hash($password),
            'avatar' => $avatar ?: 'https://ui-avatars.com/api/?name=' . urlencode($username),
            'bio' => $bio,
            'status' => 'online',
            'last_seen' => new MongoDB\BSON\UTCDateTime(),
            'is_admin' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];

        $result = $this->db->insert($this->collection, $user);
        
        if ($result) {
            return ['success' => true, 'user_id' => (string)$user['_id']];
        }
        return ['success' => false, 'message' => 'Failed to create user'];
    }

    // Update user profile
    public function updateProfile($userId, $data) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($userId);
            $updateData = [];
            
            if (isset($data['username'])) $updateData['username'] = $data['username'];
            if (isset($data['bio'])) $updateData['bio'] = $data['bio'];
            if (isset($data['avatar'])) $updateData['avatar'] = $data['avatar'];
            
            return $this->db->update($this->collection, ['_id' => $objectId], $updateData);
        } catch (Exception $e) {
            return false;
        }
    }

    // Update user status
    public function updateStatus($userId, $status) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($userId);
            return $this->db->update($this->collection, ['_id' => $objectId], [
                'status' => $status,
                'last_seen' => new MongoDB\BSON\UTCDateTime()
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Make user admin
    public function makeAdmin($userId) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($userId);
            return $this->db->update($this->collection, ['_id' => $objectId], ['is_admin' => true]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Search users
    public function search($query) {
        try {
            $regex = new MongoDB\BSON\Regex($query, 'i');
            return $this->db->find($this->collection, [
                '$or' => [
                    ['username' => $regex],
                    ['email' => $regex]
                ]
            ], ['projection' => ['password' => 0]]);
        } catch (Exception $e) {
            return [];
        }
    }

    public function findByEmail($email) {
        return $this->db->findOne($this->collection, ['email' => $email]);
    }

    public function findById($id) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($id);
            return $this->db->findOne($this->collection, ['_id' => $objectId]);
        } catch (Exception $e) {
            return null;
        }
    }

    public function verifyPassword($user, $password) {
        return Bcrypt::verify($password, $user->password);
    }

    public function getAllUsers() {
        return $this->db->find($this->collection, [], ['projection' => ['password' => 0]]);
    }

    // Get online users
    public function getOnlineUsers() {
        return $this->db->find($this->collection, ['status' => 'online'], ['projection' => ['password' => 0]]);
    }
   
    public function searchUsers($query, $currentUserId) {
        try {
            // Create regex pattern for case-insensitive search
            $regex = new MongoDB\BSON\Regex($query, 'i');
            
            // Search by username or email, exclude current user
            $filter = [
                '$or' => [
                    ['username' => $regex],
                    ['email' => $regex]
                ],
                '_id' => ['$ne' => new MongoDB\BSON\ObjectId($currentUserId)]
            ];

            $options = [
                'limit' => 20,
                'sort' => ['username' => 1],
                'projection' => ['password' => 0] // Exclude password field
            ];

            // Use your Database wrapper's find method
            return $this->db->find($this->collection, $filter, $options);
        } catch (Exception $e) {
            throw new Exception("Failed to search users: " . $e->getMessage());
        }
    }
   
    // Update password - FIXED VERSION
    public function updatePassword($userId, $hashedPassword) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($userId);
            $result = $this->db->update($this->collection, ['_id' => $objectId], [
                'password' => $hashedPassword
            ]);
            
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    
}
?>