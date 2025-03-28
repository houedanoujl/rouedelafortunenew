-- Fichier d'initialisation pour MySQL - Adapté à partir des fichiers PostgreSQL existants

-- Création des tables
CREATE TABLE IF NOT EXISTS `contest` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `status` VARCHAR(50) DEFAULT 'active',
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `prize` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `type` VARCHAR(50) DEFAULT 'product',
  `value` DECIMAL(10,2),
  `image_url` VARCHAR(512),
  `stock` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `prize_distribution` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `contest_id` INT NOT NULL,
  `prize_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `start_date` DATETIME,
  `end_date` DATETIME,
  `remaining` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`contest_id`) REFERENCES `contest`(`id`),
  FOREIGN KEY (`prize_id`) REFERENCES `prize`(`id`)
);

CREATE TABLE IF NOT EXISTS `participant` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(255) NOT NULL,
  `last_name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `email` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_phone` (`phone`)
);

CREATE TABLE IF NOT EXISTS `entry` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `participant_id` INT NOT NULL,
  `contest_id` INT NOT NULL,
  `prize_id` INT,
  `result` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `played_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `qr_code` VARCHAR(512),
  `claimed` BOOLEAN DEFAULT FALSE,
  `won_date` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`participant_id`) REFERENCES `participant`(`id`),
  FOREIGN KEY (`contest_id`) REFERENCES `contest`(`id`),
  FOREIGN KEY (`prize_id`) REFERENCES `prize`(`id`)
);

CREATE TABLE IF NOT EXISTS `qr_code` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `entry_id` INT NOT NULL,
  `code` VARCHAR(255) NOT NULL,
  `scanned` BOOLEAN DEFAULT FALSE,
  `scanned_at` DATETIME,
  `scanned_by` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`entry_id`) REFERENCES `entry`(`id`)
);

-- Insertion des données de base
-- Concours
INSERT INTO `contest` (`name`, `start_date`, `end_date`, `status`, `description`)
VALUES 
('DINOR Jeu Concours 2023', '2023-01-01 00:00:00', '2023-12-31 23:59:59', 'active', 'Jeu concours annuel DINOR');

-- Prix
INSERT INTO `prize` (`name`, `description`, `type`, `value`, `stock`)
VALUES 
('Smartphone', 'Un smartphone dernière génération', 'product', 500.00, 5),
('Tablette', 'Une tablette tactile', 'product', 300.00, 10),
('Casque audio', 'Un casque audio sans fil', 'product', 100.00, 20),
('Bon d\'achat', 'Un bon d\'achat de 50€', 'voucher', 50.00, 50),
('Clé USB', 'Une clé USB 32Go', 'product', 20.00, 100);

-- Distribution des prix
INSERT INTO `prize_distribution` (`contest_id`, `prize_id`, `quantity`, `remaining`, `start_date`, `end_date`)
VALUES 
(1, 1, 5, 5, '2023-01-01 00:00:00', '2023-12-31 23:59:59'),
(1, 2, 10, 10, '2023-01-01 00:00:00', '2023-12-31 23:59:59'),
(1, 3, 20, 20, '2023-01-01 00:00:00', '2023-12-31 23:59:59'),
(1, 4, 50, 50, '2023-01-01 00:00:00', '2023-12-31 23:59:59'),
(1, 5, 100, 100, '2023-01-01 00:00:00', '2023-12-31 23:59:59');

-- Quelques participants d'exemple
INSERT INTO `participant` (`first_name`, `last_name`, `phone`, `email`)
VALUES 
('Jean', 'Dupont', '22501234567', 'jean.dupont@example.com'),
('Marie', 'Martin', '22507654321', 'marie.martin@example.com'),
('Paul', 'Dubois', '22509876543', 'paul.dubois@example.com');
