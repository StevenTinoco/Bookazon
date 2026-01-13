
CREATE DATABASE IF NOT EXISTS online_bookstore;
USE online_bookstore;

-- =======================
-- AUTHORS TABLE
-- =======================
CREATE TABLE authors (
    author_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    bio TEXT NOT NULL
);

-- =======================
-- BOOKS TABLE
-- =======================
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(50) NOT NULL,
    list_price DECIMAL(6,2) NOT NULL,
    stock_qty INT NOT NULL,
    published_date DATE NOT NULL,
    image_file VARCHAR(255)
);

-- =======================
-- BOOK_AUTHORS TABLE (M:N)
-- =======================
CREATE TABLE book_authors (
    book_author_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    author_id INT NOT NULL,
    FOREIGN KEY (book_id) REFERENCES books(book_id),
    FOREIGN KEY (author_id) REFERENCES authors(author_id)
);

-- =======================
-- CUSTOMERS TABLE
-- =======================
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL
);

-- =======================
-- ORDERS TABLE
-- =======================
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

-- =======================
-- ORDER_ITEMS TABLE
-- =======================
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    unit_price DECIMAL(6,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (book_id) REFERENCES books(book_id)
);

-- =======================
-- REVIEWS TABLE
-- =======================
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    body TEXT NOT NULL,
    FOREIGN KEY (book_id) REFERENCES books(book_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

-- =======================
-- SAMPLE DATA (15+ RECORDS CAN BE ADDED LATER)
-- =======================

INSERT INTO authors (first_name, last_name, bio) VALUES
('George', 'Orwell', 'Author of dystopian fiction'),
('Harper', 'Lee', 'Author of To Kill a Mockingbird'),
('J.K.', 'Rowling', 'Author of Harry Potter');

INSERT INTO books (title, category, list_price, stock_qty, published_date) VALUES
('1984', 'Fiction', 12.99, 20, '1949-06-08'),
('To Kill a Mockingbird', 'Fiction', 10.99, 15, '1960-07-11'),
('Harry Potter and the Sorcerer''s Stone', 'Fantasy', 14.99, 25, '1997-06-26');

INSERT INTO book_authors (book_id, author_id) VALUES
(1,1),
(2,2),
(3,3);

INSERT INTO customers (email, password_hash, full_name, address) VALUES
('test1@email.com', 'hashedpassword1', 'Alice Reader', '123 Main St'),
('test2@email.com', 'hashedpassword2', 'Bob Buyer', '456 Oak Ave');

INSERT INTO orders (customer_id, status) VALUES
(1, 'Processing'),
(2, 'Shipped');

INSERT INTO order_items (order_id, book_id, unit_price, quantity) VALUES
(1, 1, 12.99, 1),
(2, 3, 14.99, 2);

INSERT INTO reviews (book_id, customer_id, rating, title, body) VALUES
(1, 1, 5, 'Great Book', 'Very powerful story'),
(3, 2, 4, 'Magical', 'Fun and exciting read');
