<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/middleware/Auth.php';
require_once $projectRoot . '/models/Invitation.php';
require_once $projectRoot . '/models/Channel.php';
require_once $projectRoot . '/models/User.php';
require_once $projectRoot . '/utils/Email.php';

$invitationModel = new Invitation();
$channelModel = new Channel();
$userModel = new User();
$email = new Email();

$user = Auth::verify();
$userId = $user['user_id'];
$username = $user['username'];

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    if ($method === 'POST') {
        // Send invitation
        if (!isset($data['channel_id']) || !isset($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        $channelId = $data['channel_id'];
        $invitedEmail = $data['email'];
        
        // Check if channel exists and user is creator
        $channel = $channelModel->findById($channelId);
        if (!$channel) {
            echo json_encode(['success' => false, 'message' => 'Channel not found']);
            exit;
        }
        
        if ($channel->created_by !== $userId) {
            echo json_encode(['success' => false, 'message' => 'Only channel creator can send invitations']);
            exit;
        }
        
        // Check if user exists
        $invitedUser = $userModel->findByEmail($invitedEmail);
        $invitedUserId = $invitedUser ? (string)$invitedUser->_id : null;
        $invitedName = $invitedUser ? $invitedUser->username : $invitedEmail;
        
        // Create invitation
        $result = $invitationModel->create($channelId, $userId, $invitedEmail, $invitedUserId);
        
        if ($result['success']) {
            // Send email
            $emailSent = $email->sendChannelInvitation(
                $invitedEmail,
                $invitedName,
                $channel->name,
                $username,
                $result['token']
            );
            
            if ($emailSent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Invitation sent successfully to ' . $invitedEmail
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Invitation created but email failed to send. Invitation link can be shared manually.',
                    'token' => $result['token']
                ]);
            }
        } else {
            echo json_encode($result);
        }
        
    } elseif ($method === 'GET') {
        // Get pending invitations for current user
        $userInfo = $userModel->findById($userId);
        if (!$userInfo) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        
        $invitations = $invitationModel->getPendingInvitations($userInfo->email);
        
        $formattedInvitations = array_map(function($inv) use ($channelModel, $userModel) {
            $channel = $channelModel->findById($inv->channel_id);
            $inviter = $userModel->findById($inv->inviter_user_id);
            
            return [
                'id' => (string)$inv->_id,
                'channel_name' => $channel ? $channel->name : 'Unknown',
                'inviter_name' => $inviter ? $inviter->username : 'Unknown',
                'token' => $inv->token,
                'created_at' => $inv->created_at->toDateTime()->format('Y-m-d H:i:s'),
                'expires_at' => $inv->expires_at->toDateTime()->format('Y-m-d H:i:s')
            ];
        }, $invitations);
        
        echo json_encode(['success' => true, 'invitations' => $formattedInvitations]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>