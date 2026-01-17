<?php
require_once __DIR__ . '/../config/database.php';

class Invitation {
    private $db;
    private $collection = 'invitations';

    public function __construct() {
        $this->db = new Database();
    }

    // Create invitation
    public function create($channelId, $inviterUserId, $invitedEmail, $invitedUserId = null) {
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        
        // Set expiration (7 days)
        $expiresAt = new MongoDB\BSON\UTCDateTime((time() + (7 * 24 * 60 * 60)) * 1000);
        
        $invitation = [
            '_id' => new MongoDB\BSON\ObjectId(),
            'channel_id' => $channelId,
            'inviter_user_id' => $inviterUserId,
            'invited_email' => $invitedEmail,
            'invited_user_id' => $invitedUserId,
            'token' => $token,
            'status' => 'pending', // pending, accepted, expired
            'expires_at' => $expiresAt,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];

        $result = $this->db->insert($this->collection, $invitation);
        
        if ($result) {
            return ['success' => true, 'token' => $token, 'invitation_id' => (string)$invitation['_id']];
        }
        return ['success' => false, 'message' => 'Failed to create invitation'];
    }

    // Find invitation by token
    public function findByToken($token) {
        return $this->db->findOne($this->collection, ['token' => $token]);
    }

    // Accept invitation
    public function accept($token, $userId) {
        try {
            $invitation = $this->findByToken($token);
            
            if (!$invitation) {
                return ['success' => false, 'message' => 'Invitation not found'];
            }
            
            // Check if expired
            $expiresAt = $invitation->expires_at->toDateTime()->getTimestamp();
            if (time() > $expiresAt) {
                $this->updateStatus($token, 'expired');
                return ['success' => false, 'message' => 'Invitation has expired'];
            }
            
            // Check if already accepted
            if ($invitation->status === 'accepted') {
                return ['success' => false, 'message' => 'Invitation already accepted'];
            }
            
            // Update status
            $this->updateStatus($token, 'accepted');
            
            return [
                'success' => true,
                'channel_id' => $invitation->channel_id,
                'message' => 'Invitation accepted successfully'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Update invitation status
    private function updateStatus($token, $status) {
        return $this->db->update($this->collection, ['token' => $token], ['status' => $status]);
    }

    // Get pending invitations for a user
    public function getPendingInvitations($email) {
        return $this->db->find($this->collection, [
            'invited_email' => $email,
            'status' => 'pending'
        ]);
    }

    // Delete invitation
    public function delete($token) {
        return $this->db->delete($this->collection, ['token' => $token]);
    }
}
?>