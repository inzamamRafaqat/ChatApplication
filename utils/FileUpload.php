<?php
class FileUpload {
    private $uploadDir = __DIR__ . '/../public/uploads/';
    private $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $allowedDocs = ['pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx'];
    private $maxSize = 5 * 1024 * 1024; // 5MB

    public function __construct() {
        // Create upload directory if not exists
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    // Upload file
    public function upload($file) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'No file uploaded'];
        }

        // Check file size
        if ($file['size'] > $this->maxSize) {
            return ['success' => false, 'message' => 'File too large (max 5MB)'];
        }

        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Determine file type
        $fileType = 'document';
        if (in_array($extension, $this->allowedImages)) {
            $fileType = 'image';
        } elseif (!in_array($extension, $this->allowedDocs)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'file_url' => '/uploads/' . $filename,
                'file_type' => $fileType,
                'original_name' => $file['name']
            ];
        }

        return ['success' => false, 'message' => 'Failed to upload file'];
    }

    // Upload base64 image
    public function uploadBase64($base64String, $filename = null) {
        if (empty($base64String)) {
            return ['success' => false, 'message' => 'No data provided'];
        }

        // Remove data:image/xxx;base64, prefix if exists
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
            $extension = $matches[1];
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
        } else {
            $extension = 'png';
        }

        // Decode base64
        $imageData = base64_decode($base64String);
        if ($imageData === false) {
            return ['success' => false, 'message' => 'Invalid base64 data'];
        }

        // Generate filename
        if (!$filename) {
            $filename = uniqid() . '_' . time() . '.' . $extension;
        }

        $filepath = $this->uploadDir . $filename;

        // Save file
        if (file_put_contents($filepath, $imageData)) {
            return [
                'success' => true,
                'file_url' => '/uploads/' . $filename,
                'file_type' => 'image'
            ];
        }

        return ['success' => false, 'message' => 'Failed to save file'];
    }

    // Delete file
    public function delete($fileUrl) {
        $filename = basename($fileUrl);
        $filepath = $this->uploadDir . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
}
?>