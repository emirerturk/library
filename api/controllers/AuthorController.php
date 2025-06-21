<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Response.php';
class AuthorController{

    private $pdo;

    public function __construct() {
        $this->pdo = (new Database())->getConnection();
    }
    public function getPdo() {
        return $this->pdo;
    }
    public function getAllAuthors() {
        $stmt = $this->pdo->query("SELECT * FROM authors");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function insertAuthor($name, $email) {
        $stmt = $this->pdo->prepare("INSERT INTO authors (name,email, created_at) VALUES (:name, :email, NOW())");
        $stmt->execute([
                ':name' => $name,
                ':email' => $email
            ]);
        return $this->pdo->lastInsertId();
    }
    
    public function getBooksByAuthorId($authorId) {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE author_id = :author_id");
        $stmt->execute([':author_id' => $authorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}