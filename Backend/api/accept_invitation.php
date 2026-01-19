<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/models/Invitation.php';
require_once $projectRoot . '/models/Channel.php';
require_once $projectRoot . '/models/User.php';

$invitationModel = new Invitation();
$channelModel = new Channel();
$userModel = new User();

// Get token from URL
$token = isset($_GET['token']) ? $_GET['token'] : null;

if (!$token) {
    die("Invalid invitation link");
}

// Check if user is logged in
session_start();
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// If not logged in, redirect to login with return URL
if (!$userId) {
    // Store token in session
    $_SESSION['pending_invitation_token'] = $token;
    header("Location: /ChatApplication/Frontend/Src/Screens/login.php?redirect=accept_invitation");
    exit;
}

// Accept the invitation
$result = $invitationModel->accept($token, $userId);

if ($result['success']) {
    // Add user to channel
    $channelModel->addMember($result['channel_id'], $userId);
    
    // Redirect to chat
    header("Location: /ChatApplication/Frontend/Screens/index.php?success=invitation_accepted&channel_id=" . $result['channel_id']);
    exit;
} else {
    // Show error page
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Invitation Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-md">
            <div class="text-center">
                <div class="text-6xl mb-4">❌</div>
                <h1 class="text-2xl font-bold mb-4 text-red-600">Invitation Error</h1>
                <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($result['message']); ?></p>
                <a href="/ChatApplication/Frontend/Screens/index.php" 
                   class="inline-block px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Go to Chat
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>