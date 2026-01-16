<?php
// Database configuration
class Database {
    private $connection;
    private $db;

    public function __construct() {
        try {
            // Connect to MongoDB
            $this->connection = new MongoDB\Driver\Manager("mongodb://localhost:27017");
            $this->db = "chat_system";
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getCollection($collectionName) {
        return [
            'manager' => $this->connection,
            'namespace' => $this->db . '.' . $collectionName
        ];
    }

    public function insert($collection, $document) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($document);
        $result = $this->connection->executeBulkWrite($this->db . '.' . $collection, $bulk);
        return $result->getInsertedCount() > 0;
    }

    public function find($collection, $filter = [], $options = []) {
        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor = $this->connection->executeQuery($this->db . '.' . $collection, $query);
        return $cursor->toArray();
    }

    public function findOne($collection, $filter) {
        $options = ['limit' => 1];
        $result = $this->find($collection, $filter, $options);
        return !empty($result) ? $result[0] : null;
    }

    public function update($collection, $filter, $update) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update($filter, ['$set' => $update], ['multi' => false]);
        $result = $this->connection->executeBulkWrite($this->db . '.' . $collection, $bulk);
        return $result->getModifiedCount() > 0;
    }

    public function delete($collection, $filter) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->delete($filter, ['limit' => 1]);
        $result = $this->connection->executeBulkWrite($this->db . '.' . $collection, $bulk);
        return $result->getDeletedCount() > 0;
    }
}
?>