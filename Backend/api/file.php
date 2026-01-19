<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$projectRoot = dirname(__DIR__);
require_once $projectRoot . '/utils/GridFS.php';

// Get file ID from URL
$fileId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$fileId) {
    http_response_code(400);
    die('File ID required');
}

$gridFS = new GridFS();

try {
    $result = $gridFS->download($fileId);
    
    if ($result['success']) {
        // Set appropriate headers
        header('Content-Type: ' . $result['contentType']);
        header('Content-Length: ' . $result['size']);
        header('Content-Disposition: inline; filename="' . $result['filename'] . '"');
        header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
        
        // Output file content
        echo $result['content'];
        exit;
    } else {
        http_response_code(404);
        die('File not found: ' . $result['message']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    die('Error retrieving file: ' . $e->getMessage());
}
?>