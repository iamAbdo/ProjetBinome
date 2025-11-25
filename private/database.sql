Drop database if exists binome_shop;
CREATE DATABASE IF NOT EXISTS binome_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE binome_shop;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NULL,
  name VARCHAR(255) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  hashed_pass VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  client_name VARCHAR(255) NOT NULL,
  client_phone VARCHAR(50),
  client_address VARCHAR(255),
  client_email VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  phone VARCHAR(50),
  email VARCHAR(255),
  message TEXT,
  date_contact TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  qty INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB;



INSERT INTO categories (name) VALUES
('Categorie 1'), ('Categorie 2'), ('Categorie 3')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO products (category_id, name, price, image, description) VALUES
((SELECT id FROM categories WHERE name='Categorie 1'), 'Produit 1', 4500, 'https://placehold.co/600x400?text=Produit+1', 'Un produit incontournable pour votre quotidien.'),
((SELECT id FROM categories WHERE name='Categorie 1'), 'Produit 2', 3200, 'https://placehold.co/600x400?text=Produit+2', 'Une solution simple et efficace.'),
((SELECT id FROM categories WHERE name='Categorie 2'), 'Produit 3', 6200, 'https://placehold.co/600x400?text=Produit+3', 'Nouvelle collection saisonnière.'),
((SELECT id FROM categories WHERE name='Categorie 3'), 'Produit 4', 2500, 'https://placehold.co/600x400?text=Produit+4', 'Le bon plan du moment.')
ON DUPLICATE KEY UPDATE price = VALUES(price);

INSERT INTO admins (email, hashed_pass)
VALUES ('test@test.dz', '$2y$10$/l4FaFBO1j/uv3RyxjvZ..C7fkXuWpx8XaFP2JcwW2GMKfz4BP9LW')
ON DUPLICATE KEY UPDATE email = email;


