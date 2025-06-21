<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../api/controllers/CategoryController.php';

class CategoryControllerTest extends TestCase
{
    private $pdoMock;
    private $stmtMock;
    private $categoryController;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->categoryController = $this->getMockBuilder(CategoryController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPdo'])
            ->getMock();

        $this->categoryController->method('getPdo')->willReturn($this->pdoMock);

        $ref = new ReflectionClass(CategoryController::class);
        $prop = $ref->getProperty('pdo');
        $prop->setAccessible(true);
        $prop->setValue($this->categoryController, $this->pdoMock);
    }

    public function testGetAllCategoriesReturnsArray()
    {
        $expectedCategories = [
            ['id' => 1, 'name' => 'Fiction', 'description' => 'Fictional books'],
            ['id' => 2, 'name' => 'Science', 'description' => 'Scientific books'],
        ];

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedCategories);

        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with("SELECT * FROM categories")
            ->willReturn($this->stmtMock);

        $result = $this->categoryController->getAllCategories();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Fiction', $result[0]['name']);
    }

    public function testInsertCategoryReturnsLastInsertId()
    {
        $name = "History";
        $description = "Historical books";

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([':name' => $name, ':description' => $description])
            ->willReturn(true);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with("INSERT INTO categories (name,description, created_at) VALUES (:name, :description, NOW())")
            ->willReturn($this->stmtMock);

        $this->pdoMock->expects($this->once())
            ->method('lastInsertId')
            ->willReturn('42');

        $result = $this->categoryController->insertCategory($name, $description);

        $this->assertEquals(42, $result);
    }
}
