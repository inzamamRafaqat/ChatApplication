<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/models/User.php';
require_once $projectRoot . '/models/PasswordReset.php';
require_once $projectRoot . '/utils/Email.php';

$userModel = new User();
$passwordResetModel = new PasswordReset();
$email = new Email();

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email'])) {
            echo json_encode(['success' => false, 'message' => 'Email is required']);
            exit;
        }
        
        $userEmail = $data['email'];
        
        // Check if user exists
        $user = $userModel->findByEmail($userEmail);
        
        if (!$user) {
            // For security, don't reveal if email exists or not
            echo json_encode([
                'success' => true,
                'message' => 'If an account exists with this email, you will receive a password reset link shortly.'
            ]);
            exit;
        }
        
        // Create reset token and temp password
        $result = $passwordResetModel->createResetToken($userEmail, (string)$user->_id);
        
        if ($result['success']) {
            // Send email
            $emailSent = $email->sendPasswordReset(
                $userEmail,
                $user->username,
                $result['temp_password'],
                $result['token']
            );
            
            if ($emailSent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Password reset instructions have been sent to your email.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send email. Please try again later.'
                ]);
            }
        } else {
            echo json_encode($result);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>