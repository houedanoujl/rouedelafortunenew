-- Script d'initialisation PostgreSQL pour le dashboard administratif

-- Ajout des colonnes supplémentaires à la table prize_distribution
ALTER TABLE "prize_distribution" ADD COLUMN IF NOT EXISTS "distributed" BOOLEAN DEFAULT FALSE;
ALTER TABLE "prize_distribution" ADD COLUMN IF NOT EXISTS "winner_id" INTEGER;
ALTER TABLE "prize_distribution" ADD COLUMN IF NOT EXISTS "distribution_date" TIMESTAMP;
ALTER TABLE "prize_distribution" ADD COLUMN IF NOT EXISTS "week_number" INTEGER;

-- Création d'une table pour les administrateurs (pour le dashboard)
CREATE TABLE IF NOT EXISTS "admin_user" (
  "id" SERIAL PRIMARY KEY,
  "username" VARCHAR(255) NOT NULL,
  "password_hash" VARCHAR(255) NOT NULL,
  "last_login" TIMESTAMP,
  "created_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "updated_at" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE ("username")
);

-- Création du trigger pour mettre à jour la colonne updated_at
CREATE TRIGGER update_adminuser_updated_at BEFORE UPDATE ON "admin_user" FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

-- Insérer l'administrateur par défaut (nom d'utilisateur: houedanou, mot de passe: admin123)
-- Le hash bcrypt du mot de passe 'admin123'
INSERT INTO "admin_user" ("username", "password_hash") 
VALUES ('houedanou', '$2a$10$DaOmUQTh9nJLGRIW.X1X7ue4aRr9l9VQ1i/mhPZxNuK8F.qeJkn5q')
ON CONFLICT ("username") DO UPDATE 
SET "password_hash" = '$2a$10$DaOmUQTh9nJLGRIW.X1X7ue4aRr9l9VQ1i/mhPZxNuK8F.qeJkn5q', 
    "updated_at" = CURRENT_TIMESTAMP;

-- Actualisation des numéros de semaine dans les répartitions existantes
-- En PostgreSQL, nous utilisons la fonction to_char pour extraire le numéro de semaine
UPDATE "prize_distribution" 
SET "week_number" = CAST(to_char("start_date", 'IYYY') || to_char("start_date", 'IW') AS INTEGER)
WHERE "week_number" IS NULL AND "start_date" IS NOT NULL;

-- Initialisation des semaines pour les distributions sans date
UPDATE "prize_distribution" 
SET "week_number" = CAST(to_char(CURRENT_DATE, 'IYYY') || to_char(CURRENT_DATE, 'IW') AS INTEGER)
WHERE "week_number" IS NULL;

-- Création des index pour améliorer les performances
CREATE INDEX IF NOT EXISTS "idx_prize_distribution_week" ON "prize_distribution" ("week_number");
CREATE INDEX IF NOT EXISTS "idx_participant_phone" ON "participant" ("phone");
CREATE INDEX IF NOT EXISTS "idx_entry_result" ON "entry" ("result");
