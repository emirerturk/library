<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Response.php';
class CategoryController{

    private $pdo;

    public function __construct() {
        $this->pdo = (new Database())->getConnection();
    }
    public function getPdo() {
        return $this->pdo;
    }
    public function getAllCategories() {
        $stmt = $this->pdo->query("SELECT * FROM categories");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function insertCategory($name,$description = null) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name,description, created_at) VALUES (:name, :description, NOW())");
        $stmt->execute([':name' => $name, ':description' => $description]);
        return $this->pdo->lastInsertId();
    }
}
