
INSERT INTO authors (name, email) VALUES 
('George Orwell', 'george.orwell@example.com'),
('Jane Austen', 'jane.austen@example.com'),
('Fyodor Dostoevsky', 'fyodor.d@example.com');

INSERT INTO categories (name, description) VALUES 
('Dystopia', 'Distopyan ve politik romanlar'),
('Classic Literature', 'Klasik edebi eserler'),
('Psychological Fiction', 'Psikolojik derinlikli romanlar');

INSERT INTO books (title, isbn, author_id, category_id, publication_year, page_count, is_available) VALUES 
('1984', '9780451524935', 1, 1, 1949, 328, TRUE),
('Animal Farm', '9780451526342', 1, 1, 1945, 112, TRUE),
('Pride and Prejudice', '9780141439518', 2, 2, 1813, 279, TRUE),
('Crime and Punishment', '9780140449136', 3, 3, 1866, 671, FALSE),
('The Idiot', '9780140447927', 3, 3, 1869, 656, TRUE);
