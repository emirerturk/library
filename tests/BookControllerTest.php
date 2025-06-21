<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../api/core/Database.php';
require_once __DIR__ . '/../api/controllers/BookController.php';

class BookControllerTest extends TestCase {
    private $pdoMock;
    private $bookController;

    protected function setUp(): void {
        $this->pdoMock = $this->createMock(PDO::class);

        $this->bookController = $this->getMockBuilder(BookController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPdo'])
            ->getMock();

        $this->bookController->method('getPdo')->willReturn($this->pdoMock);

        $ref = new ReflectionClass(BookController::class);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->bookController, $this->pdoMock);
    }

    public function testGetAllBooksReturnsArray() {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('fetchAll')->willReturn([
            ['id' => 1, 'title' => 'Test Kitap']
        ]);

        $this->pdoMock->method('query')->willReturn($stmtMock);

        $result = $this->bookController->getAllBooks();
        $this->assertIsArray($result);
        $this->assertEquals('Test Kitap', $result[0]['title']);
    }

    public function testInsertBookReturnsLastInsertId() {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);

        $this->pdoMock->method('prepare')->willReturn($stmtMock);
        $this->pdoMock->method('lastInsertId')->willReturn('42');

        $result = $this->bookController->insertBook(
            'Kitap Adı', '9781234567890', 1, 2, 2023, 300, true
        );

        $this->assertEquals(42, $result);
    }

    public function testDeleteBookReturnsAffectedRows() {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('rowCount')->willReturn(1);

        $this->pdoMock->method('prepare')->willReturn($stmtMock);

        $result = $this->bookController->deleteBook(1);
        $this->assertEquals(1, $result);
    }
}
