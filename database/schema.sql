CREATE DATABASE c2c_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;



CREATE TABLE users (
    user_id            INT AUTO_INCREMENT PRIMARY KEY,
    username           VARCHAR(50)  UNIQUE NOT NULL,
    email              VARCHAR(100) UNIQUE NOT NULL,
    password           VARCHAR(255) NOT NULL,
    phone              VARCHAR(20)  DEFAULT '',
    user_role          ENUM('buyer','seller','admin') DEFAULT 'buyer',
    preferred_language VARCHAR(10)  DEFAULT 'en',
    is_active          TINYINT(1)   DEFAULT 1,
    date_registered    DATETIME     DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE seller_profiles (
    seller_id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id              INT NOT NULL,
    business_name        VARCHAR(100) DEFAULT '',
    business_description TEXT,
    location             VARCHAR(150) DEFAULT '',
    rating               DECIMAL(3,2) DEFAULT 0.00,
    total_sales          INT          DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE products (
    product_id       INT AUTO_INCREMENT PRIMARY KEY,
    seller_id        INT           NOT NULL,
    product_name     VARCHAR(150)  NOT NULL,
    description      TEXT,
    category         VARCHAR(50)   DEFAULT '',
    price            DECIMAL(10,2) NOT NULL,
    quantity         INT           DEFAULT 1,
    condition_status VARCHAR(20)   DEFAULT 'used',
    image_url        VARCHAR(255)  DEFAULT '',
    location         VARCHAR(150)  DEFAULT '',
    is_available     TINYINT(1)    DEFAULT 1,
    date_listed      DATETIME      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES seller_profiles(seller_id) ON DELETE CASCADE
);

-- ============================================================
-- ORDERS
-- ============================================================
CREATE TABLE orders (
    order_id         INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id         INT           NOT NULL,
    product_id       INT           NOT NULL,
    seller_id        INT           NOT NULL,
    quantity         INT           DEFAULT 1,
    total_price      DECIMAL(10,2) NOT NULL,
    payment_method   VARCHAR(50)   DEFAULT 'cash_on_delivery',
    payment_status   ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    delivery_method  VARCHAR(50)   DEFAULT 'collection',
    delivery_address TEXT,
    order_status     ENUM('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
    order_date       DATETIME      DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id)   REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (seller_id)  REFERENCES seller_profiles(seller_id)
);


CREATE TABLE reviews (
    review_id    INT  AUTO_INCREMENT PRIMARY KEY,
    order_id     INT  NOT NULL,
    buyer_id     INT  NOT NULL,
    seller_id    INT  NOT NULL,
    product_id   INT  NOT NULL,
    review_title VARCHAR(150) DEFAULT '',
    rating       INT  CHECK (rating BETWEEN 1 AND 5),
    review_text  TEXT,
    review_date  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_review_per_order (order_id),
    FOREIGN KEY (order_id)   REFERENCES orders(order_id),
    FOREIGN KEY (buyer_id)   REFERENCES users(user_id),
    FOREIGN KEY (seller_id)  REFERENCES seller_profiles(seller_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);


CREATE TABLE messages (
    message_id   INT AUTO_INCREMENT PRIMARY KEY,
    sender_id    INT  NOT NULL,
    receiver_id  INT  NOT NULL,
    product_id   INT  DEFAULT NULL,
    message_text TEXT NOT NULL,
    is_read      TINYINT(1) DEFAULT 0,
    sent_date    DATETIME   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(user_id),
    FOREIGN KEY (receiver_id) REFERENCES users(user_id)
);


CREATE TABLE transactions (
    transaction_id     INT AUTO_INCREMENT PRIMARY KEY,
    order_id           INT           NOT NULL,
    payment_method     VARCHAR(50)   DEFAULT '',
    amount             DECIMAL(10,2) NOT NULL,
    transaction_status ENUM('pending','completed','failed','refunded') DEFAULT 'pending',
    transaction_date   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);


CREATE TABLE cart (
    cart_id    INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id   INT NOT NULL,
    product_id INT NOT NULL,
    quantity   INT DEFAULT 1,
    added_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id)   REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);


INSERT INTO users (username, email, password, phone, user_role, is_active)
VALUES (
    'admin',
    'admin@masabtrade.co.za',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '0000000000',
    'admin',
    1
);
