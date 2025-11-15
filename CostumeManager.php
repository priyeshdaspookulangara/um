<?php
require_once 'db.php';

class CostumeManager {
    private $connection;

    public function __construct() {
        $this->connection = db_connect();
    }

    public function create($name, $description, $image_path, $colors, $pant_type, $fan_type) {
        $stmt = $this->connection->prepare(
            "INSERT INTO costumes (name, description, image_path, colors, pant_type, fan_type) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssss", $name, $description, $image_path, $colors, $pant_type, $fan_type);
        return $stmt->execute();
    }

    public function getAll($limit = 10, $offset = 0) {
        $query = "SELECT * FROM costumes ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->connection->prepare("SELECT * FROM costumes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function update($id, $name, $description, $image_path, $colors, $pant_type, $fan_type) {
        $stmt = $this->connection->prepare(
            "UPDATE costumes SET name = ?, description = ?, image_path = ?, colors = ?, pant_type = ?, fan_type = ? WHERE id = ?"
        );
        $stmt->bind_param("ssssssi", $name, $description, $image_path, $colors, $pant_type, $fan_type, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->connection->prepare("DELETE FROM costumes WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>