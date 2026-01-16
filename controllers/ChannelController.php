<?php
require_once __DIR__ . '/../models/Channel.php';

class ChannelController {
    private $channelModel;

    public function __construct() {
        $this->channelModel = new Channel();
    }

    // Create a new channel
    public function create($data, $userId) {
        if (!isset($data['name'])) {
            return ['success' => false, 'message' => 'Channel name is required'];
        }

        $isPrivate = isset($data['is_private']) ? $data['is_private'] : false;
        
        return $this->channelModel->create($data['name'], $isPrivate, $userId);
    }

    // Get all channels for current user
    public function getAll($userId) {
        $channels = $this->channelModel->getChannelsForUser($userId);
        
        $formattedChannels = array_map(function($channel) {
            return [
                'id' => (string)$channel->_id,
                'name' => $channel->name,
                'is_private' => $channel->is_private,
                'created_by' => $channel->created_by,
                'members' => $channel->members,
                'created_at' => $channel->created_at->toDateTime()->format('Y-m-d H:i:s')
            ];
        }, $channels);

        return ['success' => true, 'channels' => $formattedChannels];
    }

    // Get single channel
    public function getOne($channelId, $userId) {
        $channel = $this->channelModel->findById($channelId);
        
        if (!$channel) {
            return ['success' => false, 'message' => 'Channel not found'];
        }

        // Check access
        if (!$this->channelModel->hasAccess($channelId, $userId)) {
            return ['success' => false, 'message' => 'Access denied'];
        }

        return [
            'success' => true,
            'channel' => [
                'id' => (string)$channel->_id,
                'name' => $channel->name,
                'is_private' => $channel->is_private,
                'created_by' => $channel->created_by,
                'members' => $channel->members,
                'created_at' => $channel->created_at->toDateTime()->format('Y-m-d H:i:s')
            ]
        ];
    }

    // Update channel
    public function update($channelId, $data, $userId) {
        $channel = $this->channelModel->findById($channelId);
        
        if (!$channel) {
            return ['success' => false, 'message' => 'Channel not found'];
        }

        // Only creator can update
        if ($channel->created_by !== $userId) {
            return ['success' => false, 'message' => 'Only channel creator can update'];
        }

        $updateData = [];
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['is_private'])) $updateData['is_private'] = $data['is_private'];

        $result = $this->channelModel->update($channelId, $updateData);
        
        return $result 
            ? ['success' => true, 'message' => 'Channel updated']
            : ['success' => false, 'message' => 'Failed to update channel'];
    }

    // Delete channel
    public function delete($channelId, $userId) {
        $channel = $this->channelModel->findById($channelId);
        
        if (!$channel) {
            return ['success' => false, 'message' => 'Channel not found'];
        }

        // Only creator can delete
        if ($channel->created_by !== $userId) {
            return ['success' => false, 'message' => 'Only channel creator can delete'];
        }

        $result = $this->channelModel->delete($channelId);
        
        return $result 
            ? ['success' => true, 'message' => 'Channel deleted']
            : ['success' => false, 'message' => 'Failed to delete channel'];
    }

    // Add member to channel
    public function addMember($channelId, $data, $userId) {
        if (!isset($data['user_id'])) {
            return ['success' => false, 'message' => 'User ID is required'];
        }

        $channel = $this->channelModel->findById($channelId);
        
        if (!$channel) {
            return ['success' => false, 'message' => 'Channel not found'];
        }

        // Only creator can add members to private channels
        if ($channel->is_private && $channel->created_by !== $userId) {
            return ['success' => false, 'message' => 'Only channel creator can add members'];
        }

        $result = $this->channelModel->addMember($channelId, $data['user_id']);
        
        return $result 
            ? ['success' => true, 'message' => 'Member added']
            : ['success' => false, 'message' => 'Failed to add member'];
    }

    // Remove member from channel
    public function removeMember($channelId, $data, $userId) {
        if (!isset($data['user_id'])) {
            return ['success' => false, 'message' => 'User ID is required'];
        }

        $channel = $this->channelModel->findById($channelId);
        
        if (!$channel) {
            return ['success' => false, 'message' => 'Channel not found'];
        }

        // Only creator can remove members
        if ($channel->created_by !== $userId) {
            return ['success' => false, 'message' => 'Only channel creator can remove members'];
        }

        // Can't remove creator
        if ($data['user_id'] === $channel->created_by) {
            return ['success' => false, 'message' => 'Cannot remove channel creator'];
        }

        $result = $this->channelModel->removeMember($channelId, $data['user_id']);
        
        return $result 
            ? ['success' => true, 'message' => 'Member removed']
            : ['success' => false, 'message' => 'Failed to remove member'];
    }
}
?>