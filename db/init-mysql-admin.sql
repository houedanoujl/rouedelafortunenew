-- Script d'initialisation MySQL pour le dashboard administratif

-- Vérification de l'existence des colonnes avant de les ajouter
-- La syntaxe ADD COLUMN IF NOT EXISTS n'est pas supportée par MySQL standard

-- Vérifier si la colonne distributed existe déjà
SET @dbname = 'rouedelafortune';
SET @tablename = 'prize_distribution';
SET @columnname = 'distributed';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " BOOLEAN DEFAULT FALSE")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Vérifier si la colonne winner_id existe déjà
SET @columnname = 'winner_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Vérifier si la colonne distribution_date existe déjà
SET @columnname = 'distribution_date';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " DATETIME NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Vérifier si la colonne week_number existe déjà
SET @columnname = 'week_number';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD COLUMN ", @columnname, " INT DEFAULT NULL")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Création d'une table pour les administrateurs (pour le dashboard)
CREATE TABLE IF NOT EXISTS `admin_user` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `last_login` DATETIME,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_username` (`username`)
);

-- Insérer l'administrateur par défaut (nom d'utilisateur: houedanou, mot de passe: nouveaumdp123)
-- Le hash bcrypt du mot de passe 'nouveaumdp123'
INSERT INTO `admin_user` (`username`, `password_hash`) 
VALUES ('houedanou', '$2a$10$1uS0PrKwFXYWT3ASG6TW/OQfmw88EFkRqdbT9dAJoNzIX5LSkOFCm')
ON DUPLICATE KEY UPDATE `password_hash` = '$2a$10$1uS0PrKwFXYWT3ASG6TW/OQfmw88EFkRqdbT9dAJoNzIX5LSkOFCm', `updated_at` = CURRENT_TIMESTAMP;

-- Actualisation des numéros de semaine dans les répartitions existantes
UPDATE `prize_distribution` 
SET `week_number` = YEARWEEK(start_date) 
WHERE `week_number` IS NULL AND start_date IS NOT NULL;

-- Initialisation des semaines pour les distributions sans date
UPDATE `prize_distribution` 
SET `week_number` = YEARWEEK(NOW()) 
WHERE `week_number` IS NULL;

-- Vérification et création des index manquants (avec vérification d'existence)
-- Pour prize_distribution.week_number
SET @dbname = 'rouedelafortune';
SET @tablename = 'prize_distribution';
SET @indexname = 'idx_prize_distribution_week';
SET @columnname = 'week_number';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD INDEX ", @indexname, "(", @columnname, ")")
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

-- Pour participant.phone
SET @tablename = 'participant';
SET @indexname = 'idx_participant_phone';
SET @columnname = 'phone';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD INDEX ", @indexname, "(", @columnname, ")")
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;

-- Pour entry.result
SET @tablename = 'entry';
SET @indexname = 'idx_entry_result';
SET @columnname = 'result';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (INDEX_NAME = @indexname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD INDEX ", @indexname, "(", @columnname, ")")
));
PREPARE createIndexIfNotExists FROM @preparedStatement;
EXECUTE createIndexIfNotExists;
DEALLOCATE PREPARE createIndexIfNotExists;
