<?php

require_once __DIR__ . '/../config/database.php';

class TypingIndicator {
    private $db;
    private $collection = 'typing_indicators';

    public function __construct() {
        $this->db = new Database();
    }

    // Set user typing status
    public function setTyping($userId, $channelId, $username) {
        try {
            // Remove old typing indicator for this user
            $this->db->delete($this->collection, ['user_id' => $userId, 'channel_id' => $channelId]);
            
            // Add new one
            $indicator = [
                '_id' => new MongoDB\BSON\ObjectId(),
                'user_id' => $userId,
                'channel_id' => $channelId,
                'username' => $username,
                'expires_at' => time() + 5 // 5 seconds timeout
            ];
            
            return $this->db->insert($this->collection, $indicator);
        } catch (Exception $e) {
            return false;
        }
    }

    // Get who's typing in a channel
    public function getTypingUsers($channelId) {
        $currentTime = time();
        $typing = $this->db->find($this->collection, ['channel_id' => $channelId]);
        
        $activeTyping = [];
        foreach ($typing as $indicator) {
            if ($indicator->expires_at > $currentTime) {
                $activeTyping[] = $indicator->username;
            } else {
                // Clean up expired indicators
                $this->db->delete($this->collection, ['_id' => $indicator->_id]);
            }
        }
        
        return $activeTyping;
    }

    // Remove typing indicator
    public function removeTyping($userId, $channelId) {
        return $this->db->delete($this->collection, ['user_id' => $userId, 'channel_id' => $channelId]);
    }
}
?>