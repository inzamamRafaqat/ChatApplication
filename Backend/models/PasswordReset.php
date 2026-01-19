<?php

require_once __DIR__ . '/../config/database.php';

class PasswordReset {
    private $db;
    private $collection;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getDatabase();
        $this->collection = $this->db->password_resets;
    }
    
    // Create password reset token
    public function createResetToken($email, $userId) {
        // Generate random token
        $token = bin2hex(random_bytes(32));
        
        // Generate temporary password (8 characters)
        $tempPassword = $this->generateTempPassword();
        
        // Hash the temporary password
        $hashedPassword = password_hash($tempPassword, PASSWORD_BCRYPT);
        
        // Delete any existing reset tokens for this email
        $this->collection->deleteMany(['email' => $email]);
        
        // Create reset record
        $resetData = [
            'email' => $email,
            'user_id' => $userId,
            'token' => $token,
            'temp_password' => $tempPassword, // Store plain text to send in email
            'hashed_password' => $hashedPassword,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'expires_at' => new MongoDB\BSON\UTCDateTime(strtotime('+1 hour') * 1000),
            'used' => false
        ];
        
        $result = $this->collection->insertOne($resetData);
        
        if ($result->getInsertedCount() > 0) {
            return [
                'success' => true,
                'token' => $token,
                'temp_password' => $tempPassword
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to create reset token'];
    }
    
    // Verify and use reset token
    public function verifyToken($token) {
        $reset = $this->collection->findOne([
            'token' => $token,
            'used' => false,
            'expires_at' => ['$gt' => new MongoDB\BSON\UTCDateTime()]
        ]);
        
        if (!$reset) {
            return ['success' => false, 'message' => 'Invalid or expired reset token'];
        }
        
        // Mark as used
        $this->collection->updateOne(
            ['_id' => $reset->_id],
            ['$set' => ['used' => true]]
        );
        
        return [
            'success' => true,
            'user_id' => $reset->user_id,
            'hashed_password' => $reset->hashed_password
        ];
    }
    
    // Generate random temporary password
    private function generateTempPassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
        $password = '';
        $charsLength = strlen($chars);
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $charsLength - 1)];
        }
        
        return $password;
    }
    
    // Clean up expired tokens (optional - can run as cron job)
    public function cleanupExpired() {
        $this->collection->deleteMany([
            'expires_at' => ['$lt' => new MongoDB\BSON\UTCDateTime()]
        ]);
    }
}
?>