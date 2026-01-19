<?php
class GridFS {
    private $manager;
    private $dbName = 'chat_system';
    
    public function __construct() {
        $this->manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    }
    
    // Upload file to GridFS
    public function upload($filePath, $filename, $metadata = []) {
        try {
            // Read file content
            $fileContent = file_get_contents($filePath);
            $fileSize = strlen($fileContent);
            
            // Generate file ID
            $fileId = new MongoDB\BSON\ObjectId();
            
            // Calculate number of chunks (256KB per chunk)
            $chunkSize = 261120; // 255 KB
            $numChunks = ceil($fileSize / $chunkSize);
            
            // Insert file document into fs.files collection
            $fileDoc = [
                '_id' => $fileId,
                'length' => $fileSize,
                'chunkSize' => $chunkSize,
                'uploadDate' => new MongoDB\BSON\UTCDateTime(),
                'filename' => $filename,
                'metadata' => array_merge($metadata, [
                    'contentType' => $this->getMimeType($filePath)
                ])
            ];
            
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert($fileDoc);
            $this->manager->executeBulkWrite($this->dbName . '.fs.files', $bulk);
            
            // Insert chunks into fs.chunks collection
            for ($i = 0; $i < $numChunks; $i++) {
                $chunk = substr($fileContent, $i * $chunkSize, $chunkSize);
                
                $chunkDoc = [
                    '_id' => new MongoDB\BSON\ObjectId(),
                    'files_id' => $fileId,
                    'n' => $i,
                    'data' => new MongoDB\BSON\Binary($chunk, MongoDB\BSON\Binary::TYPE_GENERIC)
                ];
                
                $bulk = new MongoDB\Driver\BulkWrite;
                $bulk->insert($chunkDoc);
                $this->manager->executeBulkWrite($this->dbName . '.fs.chunks', $bulk);
            }
            
            return [
                'success' => true,
                'file_id' => (string)$fileId,
                'filename' => $filename
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }
    
    // Download file from GridFS
    public function download($fileId) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($fileId);
            
            // Get file document
            $query = new MongoDB\Driver\Query(['_id' => $objectId]);
            $cursor = $this->manager->executeQuery($this->dbName . '.fs.files', $query);
            $files = $cursor->toArray();
            
            if (empty($files)) {
                return ['success' => false, 'message' => 'File not found'];
            }
            
            $file = $files[0];
            
            // Get all chunks
            $query = new MongoDB\Driver\Query(
                ['files_id' => $objectId],
                ['sort' => ['n' => 1]]
            );
            $cursor = $this->manager->executeQuery($this->dbName . '.fs.chunks', $query);
            $chunks = $cursor->toArray();
            
            // Combine chunks
            $content = '';
            foreach ($chunks as $chunk) {
                $content .= $chunk->data->getData();
            }
            
            return [
                'success' => true,
                'content' => $content,
                'filename' => $file->filename,
                'contentType' => $file->metadata->contentType ?? 'application/octet-stream',
                'size' => $file->length
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Download failed: ' . $e->getMessage()
            ];
        }
    }
    
    // Delete file from GridFS
    public function delete($fileId) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($fileId);
            
            // Delete file document
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->delete(['_id' => $objectId]);
            $this->manager->executeBulkWrite($this->dbName . '.fs.files', $bulk);
            
            // Delete all chunks
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->delete(['files_id' => $objectId]);
            $this->manager->executeBulkWrite($this->dbName . '.fs.chunks', $bulk);
            
            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ];
        }
    }
    
    // Get file info
    public function getFileInfo($fileId) {
        try {
            $objectId = new MongoDB\BSON\ObjectId($fileId);
            
            $query = new MongoDB\Driver\Query(['_id' => $objectId]);
            $cursor = $this->manager->executeQuery($this->dbName . '.fs.files', $query);
            $files = $cursor->toArray();
            
            if (!empty($files)) {
                $file = $files[0];
                return [
                    'success' => true,
                    'file' => [
                        'id' => (string)$file->_id,
                        'filename' => $file->filename,
                        'size' => $file->length,
                        'uploadDate' => $file->uploadDate->toDateTime()->format('Y-m-d H:i:s'),
                        'contentType' => $file->metadata->contentType ?? 'unknown'
                    ]
                ];
            }
            
            return ['success' => false, 'message' => 'File not found'];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    // Get MIME type
    private function getMimeType($filepath) {
        if (!file_exists($filepath)) {
            return 'application/octet-stream';
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        return $mimeType ?: 'application/octet-stream';
    }
}
?>