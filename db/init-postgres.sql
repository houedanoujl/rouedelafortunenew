-- Fichier d'initialisation pour PostgreSQL

-- Activation de l'extension pour les UUID
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Création des tables
CREATE TABLE IF NOT EXISTS "contest" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(255) NOT NULL,
  "start_date" TIMESTAMP NOT NULL,
  "end_date" TIMESTAMP NOT NULL,
  "status" VARCHAR(50) DEFAULT 'active',
  "description" TEXT,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "prize" (
  "id" SERIAL PRIMARY KEY,
  "name" VARCHAR(255) NOT NULL,
  "description" TEXT,
  "type" VARCHAR(50) DEFAULT 'product',
  "value" DECIMAL(10,2),
  "image_url" VARCHAR(512),
  "stock" INTEGER DEFAULT 0,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS "prize_distribution" (
  "id" SERIAL PRIMARY KEY,
  "contest_id" INTEGER NOT NULL,
  "prize_id" INTEGER NOT NULL,
  "quantity" INTEGER NOT NULL DEFAULT 1,
  "start_date" TIMESTAMP,
  "end_date" TIMESTAMP,
  "remaining" INTEGER,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("contest_id") REFERENCES "contest"("id"),
  FOREIGN KEY ("prize_id") REFERENCES "prize"("id")
);

CREATE TABLE IF NOT EXISTS "participant" (
  "id" SERIAL PRIMARY KEY,
  "first_name" VARCHAR(255) NOT NULL,
  "last_name" VARCHAR(255) NOT NULL,
  "phone" VARCHAR(50) NOT NULL,
  "email" VARCHAR(255),
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE ("phone")
);

CREATE TABLE IF NOT EXISTS "entry" (
  "id" SERIAL PRIMARY KEY,
  "participant_id" INTEGER NOT NULL,
  "contest_id" INTEGER NOT NULL,
  "prize_id" INTEGER,
  "result" VARCHAR(50) NOT NULL DEFAULT 'pending',
  "played_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "qr_code" VARCHAR(512),
  "claimed" BOOLEAN DEFAULT FALSE,
  "won_date" TIMESTAMP,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("participant_id") REFERENCES "participant"("id"),
  FOREIGN KEY ("contest_id") REFERENCES "contest"("id"),
  FOREIGN KEY ("prize_id") REFERENCES "prize"("id")
);

CREATE TABLE IF NOT EXISTS "qr_code" (
  "id" SERIAL PRIMARY KEY,
  "entry_id" INTEGER NOT NULL,
  "code" VARCHAR(255) NOT NULL,
  "scanned" BOOLEAN DEFAULT FALSE,
  "scanned_at" TIMESTAMP,
  "scanned_by" VARCHAR(255),
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY ("entry_id") REFERENCES "entry"("id")
);

-- Création des déclencheurs pour mettre à jour la colonne updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
   NEW.updated_at = CURRENT_TIMESTAMP;
   RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_contest_updated_at BEFORE UPDATE ON "contest" FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();
CREATE TRIGGER update_prize_updated_at BEFORE UPDATE ON "prize" FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();
CREATE TRIGGER update_distribution_updated_at BEFORE UPDATE ON "prize_distribution" FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();
CREATE TRIGGER update_participant_updated_at BEFORE UPDATE ON "participant" FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();
CREATE TRIGGER update_entry_updated_at BEFORE UPDATE ON "entry" FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();
CREATE TRIGGER update_qrcode_updated_at BEFORE UPDATE ON "qr_code" FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

-- Insertion des données de base
-- Concours
INSERT INTO "contest" ("name", "start_date", "end_date", "status", "description")
VALUES 
('DINOR Jeu Concours 2023', '2023-01-01 00:00:00', '2023-12-31 23:59:59', 'active', 'Jeu concours annuel DINOR');

-- Prix
INSERT INTO "prize" ("name", "description", "type", "value", "stock")
VALUES 
('Smartphone', 'Un smartphone dernière génération', 'product', 500.00, 5),
('Tablette', 'Une tablette tactile', 'product', 300.00, 10),
('Casque audio', 'Un casque audio sans fil', 'product', 100.00, 20),
('Bon d''achat', 'Un bon d''achat de 50€', 'voucher', 50.00, 50),
('Clé USB', 'Une clé USB 32Go', 'product', 20.00, 100);

-- Distribution des prix
INSERT INTO "prize_distribution" ("contest_id", "prize_id", "quantity", "remaining", "start_date", "end_date")
VALUES 
(1, 1, 5, 5, '2023-01-01 00:00:00', '2023-12-31 23:59:59'),
(1, 2, 10, 10, '2023-01-01 00:00:00', '2023-12-31 23:59:59'),
(1, 3, 20, 20, '2023-01-01 00:00:00', '2023-12-31 23:59:59'),
(1, 4, 50, 50, '2023-01-01 00:00:00', '2023-12-31 23:59:59'),
(1, 5, 100, 100, '2023-01-01 00:00:00', '2023-12-31 23:59:59');

-- Quelques participants d'exemple
INSERT INTO "participant" ("first_name", "last_name", "phone", "email")
VALUES 
('Jean', 'Dupont', '22501234567', 'jean.dupont@example.com'),
('Marie', 'Martin', '22507654321', 'marie.martin@example.com'),
('Paul', 'Dubois', '22509876543', 'paul.dubois@example.com');
