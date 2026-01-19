<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/models/PasswordReset.php';
require_once $projectRoot . '/models/User.php';

$passwordResetModel = new PasswordReset();
$userModel = new User();

// Get token from URL
$token = isset($_GET['token']) ? $_GET['token'] : null;

if (!$token) {
    showError("Invalid password reset link");
    exit;
}

// Verify token
$result = $passwordResetModel->verifyToken($token);

if ($result['success']) {
    // Update user's password
    $updateResult = $userModel->updatePassword($result['user_id'], $result['hashed_password']);
    
    if ($updateResult) {
        // Show success page
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Password Reset Successful</title>
            <script src="https://cdn.tailwindcss.com"></script>
        </head>
        <body class="bg-gray-100 flex items-center justify-center min-h-screen">
            <div class="bg-white p-8 rounded-lg shadow-md max-w-md">
                <div class="text-center">
                    <div class="text-6xl mb-4">✅</div>
                    <h1 class="text-2xl font-bold mb-4 text-green-600">Password Reset Successful!</h1>
                    <p class="text-gray-600 mb-6">Your password has been updated. You can now login with your new temporary password.</p>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <p class="text-sm text-yellow-700">
                            <strong>⚠️ Important:</strong> Please change your temporary password after logging in for security.
                        </p>
                    </div>
                    <a href="/ChatApplication/Frontend/Src/Screens/login.php" 
                       class="inline-block px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Go to Login
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        showError("Failed to update password. Please try again.");
    }
} else {
    showError($result['message']);
}

function showError($message) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Password Reset Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-md">
            <div class="text-center">
                <div class="text-6xl mb-4">❌</div>
                <h1 class="text-2xl font-bold mb-4 text-red-600">Password Reset Error</h1>
                <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($message); ?></p>
                <a href="/ChatApplication/Frontend/Src/Screens/login.php" 
                   class="inline-block px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Back to Login
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>