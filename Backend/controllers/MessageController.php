<?php

require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Channel.php';

class MessageController {
    private $messageModel;
    private $channelModel;

    public function __construct() {
        $this->messageModel = new Message();
        $this->channelModel = new Channel();
    }

   
public function create($data, $userId, $username) {
    if (!isset($data['channel_id']) || !isset($data['content'])) {
        return ['success' => false, 'message' => 'Missing required fields'];
    }

    // Check if user has access to channel
    if (!$this->channelModel->hasAccess($data['channel_id'], $userId)) {
        return ['success' => false, 'message' => 'Access denied to this channel'];
    }

    // Get file info if provided
    $fileUrl = isset($data['file_url']) ? $data['file_url'] : null;
    $fileType = isset($data['file_type']) ? $data['file_type'] : null;
    $fileName = isset($data['file_name']) ? $data['file_name'] : null;

    // Create message with file info
    $message = [
        '_id' => new MongoDB\BSON\ObjectId(),
        'channel_id' => $data['channel_id'],
        'user_id' => $userId,
        'username' => $username,
        'content' => $data['content'],
        'file_url' => $fileUrl,
        'file_type' => $fileType,  // Store file type (image/document)
        'file_name' => $fileName,   // Store original filename
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];

    $db = new Database();
    $result = $db->insert('messages', $message);
    
    if ($result) {
        return ['success' => true, 'message_id' => (string)$message['_id']];
    }
    return ['success' => false, 'message' => 'Failed to create message'];
}

// Updated getByChannel method
public function getByChannel($channelId, $userId) {
    // Check if user has access to channel
    if (!$this->channelModel->hasAccess($channelId, $userId)) {
        return ['success' => false, 'message' => 'Access denied to this channel'];
    }

    $messages = $this->messageModel->getByChannel($channelId);
    
    // Include all file info in formatted messages
    $formattedMessages = array_map(function($message) {
        return [
            'id' => (string)$message->_id,
            'channel_id' => $message->channel_id,
            'user_id' => $message->user_id,
            'username' => $message->username,
            'content' => $message->content,
            'file_url' => isset($message->file_url) ? $message->file_url : null,
            'file_type' => isset($message->file_type) ? $message->file_type : null,
            'file_name' => isset($message->file_name) ? $message->file_name : null,
            'created_at' => $message->created_at->toDateTime()->format('Y-m-d H:i:s')
        ];
    }, $messages);

    return ['success' => true, 'messages' => $formattedMessages];
}

    // Update message
    public function update($messageId, $data, $userId) {
        if (!isset($data['content'])) {
            return ['success' => false, 'message' => 'Content is required'];
        }

        $result = $this->messageModel->update($messageId, $userId, $data['content']);
        
        return $result 
            ? ['success' => true, 'message' => 'Message updated']
            : ['success' => false, 'message' => 'Failed to update message'];
    }

    // Delete message
    public function delete($messageId, $userId) {
        $result = $this->messageModel->delete($messageId, $userId);
        
        return $result 
            ? ['success' => true, 'message' => 'Message deleted']
            : ['success' => false, 'message' => 'Failed to delete message'];
    }
}
?>