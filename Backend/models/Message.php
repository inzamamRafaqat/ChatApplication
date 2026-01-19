<?php

require_once __DIR__ . '/../config/database.php';

class Message {
    private $db;
    private $collection = 'messages';

    public function __construct() {
        $this->db = new Database();
    }

    // Create a new message
    public function create($channelId, $userId, $content, $username, $fileUrl = null) {
        $message = [
            '_id' => new MongoDB\BSON\ObjectId(),
            'channel_id' => $channelId,
            'user_id' => $userId,
            'username' => $username,
            'content' => $content,
            'file_url' => $fileUrl, // Store the file URL
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];

        $result = $this->db->insert($this->collection, $message);
        
        if ($result) {
            return ['success' => true, 'message_id' => (string)$message['_id']];
        }
        return ['success' => false, 'message' => 'Failed to create message'];
    }

    // Get messages for a channel
    public function getByChannel($channelId, $limit = 50) {
        $messages = $this->db->find(
            $this->collection, 
            ['channel_id' => $channelId],
            ['sort' => ['created_at' => -1], 'limit' => $limit]
        );
        return array_reverse($messages);
    }

    // Update message
    public function update($id, $userId, $content) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($id);
            
            // Verify the message belongs to the user
            $message = $this->db->findOne($this->collection, ['_id' => $objectId]);
            if (!$message || $message->user_id !== $userId) {
                return false;
            }
            
            return $this->db->update($this->collection, ['_id' => $objectId], ['content' => $content]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Delete message
    public function delete($id, $userId) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($id);
            
            // Verify the message belongs to the user
            $message = $this->db->findOne($this->collection, ['_id' => $objectId]);
            if (!$message || $message->user_id !== $userId) {
                return false;
            }
            
            return $this->db->delete($this->collection, ['_id' => $objectId]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>