<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'core/Response.php';
require_once 'controllers/BookController.php';
require_once 'controllers/AuthorController.php';
require_once 'controllers/CategoryController.php';
require_once 'helper/Validation.php';
require_once 'core/Database.php';
require_once 'core/Request.php';
require_once 'core/Logger.php';
Logger::getLogger()->info('API is starting...');

$request = new Request($_SERVER);

$bookController = new BookController();
$authorController = new AuthorController();
$categoryController = new CategoryController();

function route($method, $uriPattern, $callback) {
    global $request;
    $methodMatch = $request->isMethod($method);
    $uriMatch = false;

    if (str_contains($uriPattern, ':id')) {
        // /api/books/:id
        $pattern = preg_replace('/:[^\/]+/', '([^\/]+)', $uriPattern);
        $pattern = '#^' . $pattern . '$#';
        if (preg_match($pattern, $request->getUri(), $matches)) {
            $uriMatch = true;
            array_shift($matches);
            $callback(...$matches);
            return true;
        }
    } else {
        $uriMatch = $request->isAction($uriPattern);
        if ($uriMatch) {
            $callback();
            return true;
        }
    }

    return false;
}

// GET Requests
if ($request->isMethod('get')) {
    // pagination
    if (route('GET', '/api/books', function() use ($bookController) {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        $totalCount = $bookController->getBooksCount();
        $totalPages = max(1, ceil($totalCount / $limit));

        if ($page > $totalPages) {
            $books = [];
        } else {
            $books = $bookController->getBooksPaginated($limit, $offset);
        }

        Response::success([
            'items' => $books,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'per_page' => $limit,
                'total_items' => $totalCount
            ]
        ]);
    })) {}

    // search
    elseif (route('GET', '/api/books/search', function() use ($bookController) {
        $q = $_GET['q'] ?? '';
        if ($q === '') {
            Response::error('Arama sorgusu boş olamaz.', 400);
            return;
        }
        $books = $bookController->searchBooks($q);
        Response::success($books);
    })) {}

    //get book
    elseif (route('GET', '/api/books/:id', function($id) use ($bookController) {
        if (!Validation::validatePositiveInt($id)) {
            Response::error('Geçersiz kitap ID.', 400);
            return;
        }
        $book = $bookController->getBookById($id);
        if ($book) {
            Response::success($book);
        } else {
            Response::error('Kitap bulunamadı.', 404);
        }
    })) {}

    // author list
    elseif (route('GET', '/api/authors', function() use ($authorController) {
        $authors = $authorController->getAllAuthors();
        Response::success($authors);
    })) {}

    // author books
    elseif (route('GET', '/api/authors/:id/books', function($id) use ($authorController) {
        if (!Validation::validatePositiveInt($id)) {
            Response::error('Geçersiz yazar ID.', 400);
            return;
        }
        $books = $authorController->getBooksByAuthorId($id);
        Response::success($books);
    })) {}

    // categories
    elseif (route('GET', '/api/categories', function() use ($categoryController) {
        $categories = $categoryController->getAllCategories();
        Response::success($categories);
    })) {}

    else {
        Response::error('Böyle bir endpoint bulunamadı.', 404);
    }
}

// POST Requests
elseif ($request->isMethod('post')) {
    if (route('POST', '/api/books', function() use ($bookController, $request) {
        $data = $request->getData();

        $title = Validation::validateText($data->title ?? '');
        $isbn = $data->isbn ?? '';
        $authorId = (int)($data->author_id ?? 0);
        $categoryId = (int)($data->category_id ?? 0);
        $year = isset($data->publication_year) ? (int)$data->publication_year : null;
        $pageCount = isset($data->page_count) ? (int)$data->page_count : null;

        if (!$title || !$isbn || !$authorId || !$categoryId) {
            Response::error('title, isbn, author_id ve category_id zorunludur.', 400);
            return;
        }

        if (!Validation::validateISBN($isbn)) {
            Response::error('ISBN 13 haneli olmalıdır.', 400);
            return;
        }

        if (!Validation::validatePositiveInt($authorId) || !Validation::validatePositiveInt($categoryId)) {
            Response::error('author_id ve category_id pozitif tam sayı olmalıdır.', 400);
            return;
        }

        try {
            $newBookId = $bookController->insertBook($title, $isbn, $authorId, $categoryId, $year, $pageCount, true);
            $newBook = $bookController->getBookById($newBookId);
            Response::success($newBook);
        } catch (PDOException $e) {
            Logger::getLogger()->error('Kitap eklenemedi: ' . $e->getMessage());
            Response::error('Kitap eklenemedi: ' . $e->getMessage(), 500);
        }
    })) {}

    elseif (route('POST', '/api/authors', function() use ($authorController, $request) {
        $data = $request->getData();

        if (!isset($data->name) || !isset($data->email)) {
            Response::error('name ve email zorunludur.', 400);
            return;
        }
        if (!Validation::validateText($data->name)) {
            Response::error('Geçersiz isim formatı', 400);
            return;
        }
        if (!Validation::validateText($data->email)) {
            Response::error('Geçersiz email formatı', 400);
            return;
        }
        if (!Validation::validateEmail($data->email)) {
            Response::error('Geçersiz email formatı', 400);
            return;
        }

        try {
            $authorId = $authorController->insertAuthor($data->name, $data->email);
            Response::success([
                'message' => 'Yazar başarıyla eklendi',
                'data' => $authorId
            ]);
        } catch (PDOException $e) {
            Logger::getLogger()->error('Yazar eklenemedi: ' . $e->getMessage());
            Response::error('Yazar eklenemedi: ' . $e->getMessage(), 500);
        }
    })) {}

    elseif (route('POST', '/api/categories', function() use ($categoryController, $request) {
        $data = $request->getData();

        if (!isset($data->name) || !Validation::validateText($data->name)) {
            Response::error('Kategori adı zorunludur ve geçerli bir metin olmalıdır.', 400);
            return;
        }
        if (isset($data->description) && !Validation::validateText($data->description)) {
            Response::error('Kategori açıklaması geçersiz formatta.', 400);
            return;
        }

        try {
            $categoryId = $categoryController->insertCategory($data->name, $data->description ?? '');
            Response::success([
                'message' => 'Kategori başarıyla eklendi',
                'data' => $categoryId
            ]);
        } catch (PDOException $e) {
            Logger::getLogger()->error('Kategori eklenemedi: ' . $e->getMessage());
            Response::error('Kategori eklenemedi: ' . $e->getMessage(), 500);
        }
    })) {}

    else {
        Response::error('Böyle bir endpoint bulunamadı.', 404);
    }
}

// PUT Requests
elseif ($request->isMethod('put')) {
    if (route('PUT', '/api/books/:id', function($id) use ($bookController, $request) {
        if (!Validation::validatePositiveInt($id)) {
            Response::error('Geçersiz ID parametresi', 400);
            return;
        }

        $data = $request->getData();

        if (!isset($data->title) || !Validation::validateText($data->title)) {
            Response::error('Kitap başlığı zorunludur ve geçerli bir metin olmalıdır.', 400);
            return;
        }
        if (!isset($data->isbn) || !Validation::validateISBN($data->isbn)) {
            Response::error('ISBN 13 haneli olmalıdır.', 400);
            return;
        }
        if (!isset($data->author_id) || !Validation::validatePositiveInt($data->author_id)) {
            Response::error('author_id pozitif tam sayı olmalıdır.', 400);
            return;
        }
        if (!isset($data->category_id) || !Validation::validatePositiveInt($data->category_id)) {
            Response::error('category_id pozitif tam sayı olmalıdır.', 400);
            return;
        }
        if (isset($data->publication_year) && !Validation::validatePositiveInt($data->publication_year)) {
            Response::error('publication_year pozitif tam sayı olmalıdır.', 400);
            return;
        }
        if (isset($data->page_count) && !Validation::validatePositiveInt($data->page_count)) {
            Response::error('page_count pozitif tam sayı olmalıdır.', 400);
            return;
        }
        if (isset($data->is_available) && !is_bool($data->is_available)) {
            Response::error('is_available boolean olmalıdır.', 400);
            return;
        }

        try {
            $updatedRows = $bookController->updateBook(
                $id,
                $data->title,
                $data->isbn,
                $data->author_id,
                $data->category_id,
                $data->publication_year ?? null,
                $data->page_count ?? null,
                $data->is_available ?? true
            );

            if ($updatedRows > 0) {
                Response::success([], "Kitap başarıyla güncellendi.");
            } else {
                Response::error("Kitap bulunamadı", 404);
            }
        } catch (PDOException $e) {
            Logger::getLogger()->error('Kitap güncellenemedi: ' . $e->getMessage());
            Response::error("Veritabanı hatası: " . $e->getMessage(), 500);
        }
    })) {}

    else {
        Response::error('Böyle bir endpoint bulunamadı.', 404);
    }
}

// DELETE Requests
elseif ($request->isMethod('delete')) {
    if (route('DELETE', '/api/books/:id', function($id) use ($bookController) {
        if (!Validation::validatePositiveInt($id)) {
            Response::error('Geçersiz ID parametresi', 400);
            return;
        }

        $deletedRows = $bookController->deleteBook($id);

        if ($deletedRows > 0) {
            Response::success([], "Kitap başarıyla silindi.");
        } else {
            Response::error("Kitap bulunamadı", 404);
        }
    })) {}

    else {
        Response::error('Böyle bir endpoint bulunamadı.', 404);
    }
}

else {
    Response::error('Böyle bir endpoint bulunamadı.', 404);
}
