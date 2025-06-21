<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../api/controllers/AuthorController.php';
require_once __DIR__ . '/../api/core/Database.php';

class AuthorControllerTest extends TestCase {
    private $pdoMock;
    private $stmtMock;
    private $authorController;

    protected function setUp(): void {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->authorController = $this->getMockBuilder(AuthorController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPdo'])
            ->getMock();

        $this->authorController->method('getPdo')->willReturn($this->pdoMock);

        $ref = new ReflectionClass(AuthorController::class);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->authorController, $this->pdoMock);
    }

    public function testGetAllAuthorsReturnsArray() {
        $authors = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']
        ];

        $this->stmtMock->method('fetchAll')->willReturn($authors);
        $this->pdoMock->method('query')->willReturn($this->stmtMock);

        $result = $this->authorController->getAllAuthors();
        $this->assertIsArray($result);
        $this->assertEquals('John Doe', $result[0]['name']);
    }

    public function testInsertAuthorReturnsLastInsertId() {
        $this->stmtMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->pdoMock->method('lastInsertId')->willReturn('10');

        $result = $this->authorController->insertAuthor('Jane Smith', 'jane@example.com');
        $this->assertEquals(10, $result);
    }

    public function testGetBooksByAuthorIdReturnsArray() {
        $books = [
            ['id' => 1, 'title' => 'Sample Book', 'author_id' => 1]
        ];

        $this->stmtMock->method('fetchAll')->willReturn($books);
        $this->stmtMock->method('execute')->willReturn(true);
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);

        $result = $this->authorController->getBooksByAuthorId(1);
        $this->assertIsArray($result);
        $this->assertEquals('Sample Book', $result[0]['title']);
    }
}
