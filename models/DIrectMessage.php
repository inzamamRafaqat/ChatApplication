<?php
require_once __DIR__ . '/../config/database.php';

class DirectMessage {
    private $db;
    private $collection = 'direct_messages';

    public function __construct() {
        $this->db = new Database();
    }

    // Create conversation ID (always same regardless of sender/receiver order)
    private function getConversationId($userId1, $userId2) {
        $ids = [$userId1, $userId2];
        sort($ids);
        return implode('_', $ids);
    }

    // Send direct message
    public function send($fromUserId, $toUserId, $content, $fromUsername) {
        $message = [
            '_id' => new MongoDB\BSON\ObjectId(),
            'conversation_id' => $this->getConversationId($fromUserId, $toUserId),
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'from_username' => $fromUsername,
            'content' => $content,
            'is_read' => false,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];

        $result = $this->db->insert($this->collection, $message);
        
        if ($result) {
            return ['success' => true, 'message_id' => (string)$message['_id']];
        }
        return ['success' => false, 'message' => 'Failed to send message'];
    }

    // Get conversation between two users
    public function getConversation($userId1, $userId2, $limit = 50) {
        $conversationId = $this->getConversationId($userId1, $userId2);
        
        $messages = $this->db->find(
            $this->collection,
            ['conversation_id' => $conversationId],
            ['sort' => ['created_at' => -1], 'limit' => $limit]
        );
        
        return array_reverse($messages);
    }

    // Get all conversations for a user
    public function getUserConversations($userId) {
        $messages = $this->db->find(
            $this->collection,
            [
                '$or' => [
                    ['from_user_id' => $userId],
                    ['to_user_id' => $userId]
                ]
            ],
            ['sort' => ['created_at' => -1]]
        );

        // Group by conversation and get last message
        $conversations = [];
        foreach ($messages as $msg) {
            $convId = $msg->conversation_id;
            if (!isset($conversations[$convId])) {
                $conversations[$convId] = $msg;
            }
        }

        return array_values($conversations);
    }

    // Mark messages as read
    public function markAsRead($conversationId, $userId) {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update(
                [
                    'conversation_id' => $conversationId,
                    'to_user_id' => $userId,
                    'is_read' => false
                ],
                ['$set' => ['is_read' => true]],
                ['multi' => true]
            );
            
            $manager = $this->db->getCollection('direct_messages')['manager'];
            $namespace = $this->db->getCollection('direct_messages')['namespace'];
            $manager->executeBulkWrite($namespace, $bulk);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Get unread count
    public function getUnreadCount($userId) {
        try {
            $command = new MongoDB\Driver\Command([
                'count' => 'direct_messages',
                'query' => [
                    'to_user_id' => $userId,
                    'is_read' => false
                ]
            ]);
            
            $manager = $this->db->getCollection('direct_messages')['manager'];
            $cursor = $manager->executeCommand('chat_system', $command);
            $result = $cursor->toArray()[0];
            
            return $result->n;
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>