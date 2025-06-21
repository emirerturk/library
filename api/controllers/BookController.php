<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Response.php';
class BookController{

    private $pdo;

    public function __construct() {
        $this->pdo = (new Database())->getConnection();
    }

    public function getAllBooks() {
        $stmt = $this->pdo->query("SELECT * FROM books");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertBook($title, $isbn, $authorId, $categoryId, $year, $pageCount, $isAvailable = true) {
        $sql = "INSERT INTO books (title, isbn, author_id, category_id, publication_year, page_count, is_available, created_at)
                VALUES (:title, :isbn, :author_id, :category_id, :year, :page_count, :is_available, NOW())";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':title' => $title,
            ':isbn' => $isbn,
            ':author_id' => $authorId,
            ':category_id' => $categoryId,
            ':year' => $year,
            ':page_count' => $pageCount,
            ':is_available' => $isAvailable
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateBook($id, $title, $isbn, $authorId, $categoryId, $year, $pageCount, $isAvailable) {
        $sql = "UPDATE books SET 
                    title = :title,
                    isbn = :isbn,
                    author_id = :author_id,
                    category_id = :category_id,
                    publication_year = :year,
                    page_count = :page_count,
                    is_available = :is_available
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':isbn' => $isbn,
            ':author_id' => $authorId,
            ':category_id' => $categoryId,
            ':year' => $year,
            ':page_count' => $pageCount,
            ':is_available' => $isAvailable
        ]);
        return $stmt->rowCount();
    }

    public function deleteBook($id) {
        $stmt = $this->pdo->prepare("DELETE FROM books WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    public function getBookById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchBooks($query) {
        $stmt = $this->pdo->prepare("SELECT * FROM books WHERE title LIKE :query OR isbn LIKE :query");
        $stmt->execute([':query' => "%$query%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBooksPaginated(int $limit, int $offset) {
        $sql = "SELECT * FROM books ORDER BY id LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getBooksCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as count FROM books");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }
}