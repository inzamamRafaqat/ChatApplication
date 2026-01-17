<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/middleware/Auth.php';

Auth::verify();

// Upload to Backend/public/uploads folder
$uploadDir = $projectRoot . '/public/uploads/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

try {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
            exit;
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Return correct URL path
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            
            echo json_encode([
                'success' => true,
                'file_url' => $protocol . '://' . $host . '/ChatApplication/Backend/public/uploads/' . $filename,
                'file_type' => in_array($extension, ['jpg','jpeg','png','gif','webp']) ? 'image' : 'document',
                'filename' => $filename
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload file. Check directory permissions.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file provided']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>