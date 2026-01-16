<?php
require_once __DIR__ . '/../config/database.php';

class Channel {
    private $db;
    private $collection = 'channels';

    public function __construct() {
        $this->db = new Database();
    }

    // Create a new channel
    public function create($name, $isPrivate, $createdBy) {
        $channel = [
            '_id' => new MongoDB\BSON\ObjectId(),
            'name' => $name,
            'is_private' => $isPrivate,
            'created_by' => $createdBy,
            'members' => [$createdBy], // Creator is automatically a member
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];

        $result = $this->db->insert($this->collection, $channel);
        
        if ($result) {
            return ['success' => true, 'channel_id' => (string)$channel['_id']];
        }
        return ['success' => false, 'message' => 'Failed to create channel'];
    }

    // Get all channels for a user
    public function getChannelsForUser($userId) {
        // Get public channels and private channels where user is a member
        $channels = $this->db->find($this->collection, [
            '$or' => [
                ['is_private' => false],
                ['members' => $userId]
            ]
        ]);
        return $channels;
    }

    // Get channel by ID
    public function findById($id) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($id);
            return $this->db->findOne($this->collection, ['_id' => $objectId]);
        } catch (Exception $e) {
            return null;
        }
    }

    // Update channel
    public function update($id, $data) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($id);
            return $this->db->update($this->collection, ['_id' => $objectId], $data);
        } catch (Exception $e) {
            return false;
        }
    }

    // Delete channel
    public function delete($id) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($id);
            return $this->db->delete($this->collection, ['_id' => $objectId]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Add user to channel
    public function addMember($channelId, $userId) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($channelId);
            $channel = $this->db->findOne($this->collection, ['_id' => $objectId]);
            
            if (!$channel) return false;
            
            $members = $channel->members;
            if (!in_array($userId, $members)) {
                $members[] = $userId;
                return $this->db->update($this->collection, ['_id' => $objectId], ['members' => $members]);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Remove user from channel
    public function removeMember($channelId, $userId) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($channelId);
            $channel = $this->db->findOne($this->collection, ['_id' => $objectId]);
            
            if (!$channel) return false;
            
            $members = array_values(array_filter($channel->members, function($m) use ($userId) {
                return $m !== $userId;
            }));
            
            return $this->db->update($this->collection, ['_id' => $objectId], ['members' => $members]);
        } catch (Exception $e) {
            return false;
        }
    }

    // Check if user has access to channel
    public function hasAccess($channelId, $userId) {
        $channel = $this->findById($channelId);
        if (!$channel) return false;
        
        // Public channels are accessible to all
        if (!$channel->is_private) return true;
        
        // Private channels only accessible to members
        return in_array($userId, $channel->members);
    }
}
?>