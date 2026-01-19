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
require_once $projectRoot . '/utils/GridFS.php';

Auth::verify();

$gridFS = new GridFS();

try {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $maxSize = 16 * 1024 * 1024; // 16MB (GridFS recommended max chunk size)
        
        if ($file['size'] > $maxSize) {
            echo json_encode(['success' => false, 'message' => 'File too large (max 16MB)']);
            exit;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
            exit;
        }
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uniqueFilename = uniqid() . '_' . time() . '.' . $extension;
        
        // Determine file type
        $fileType = in_array($extension, ['jpg','jpeg','png','gif','webp']) ? 'image' : 'document';
        
        // Upload to GridFS with metadata
        $result = $gridFS->upload($file['tmp_name'], $uniqueFilename, [
            'originalName' => $file['name'],
            'fileType' => $fileType,
            'extension' => $extension,
            'size' => $file['size']
        ]);
        
        if ($result['success']) {
            // Return file ID that can be used to retrieve the file
            echo json_encode([
                'success' => true,
                'file_id' => $result['file_id'],
                'file_url' => '/ChatApplication/Backend/api/file.php?id=' . $result['file_id'],
                'file_type' => $fileType,
                'filename' => $uniqueFilename,
                'original_name' => $file['name']
            ]);
        } else {
            echo json_encode($result);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file provided']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>