-- =========================================================================
-- MISE À JOUR DE LA GESTION DES LOTS - FICHIER SQL UNIFIÉ POUR SUPABASE
-- =========================================================================
-- Ce script combine toutes les migrations nécessaires pour ajouter et initialiser
-- les colonnes 'remaining' et 'won_date' dans la table prize
-- =========================================================================

-- PARTIE 1: MIGRATION DES COLONNES
-- -------------------------------

-- Ajouter la colonne 'remaining' si elle n'existe pas
ALTER TABLE prize ADD COLUMN IF NOT EXISTS remaining INTEGER;

-- Mettre à jour 'remaining' avec la valeur de total_quantity pour les entrées existantes
UPDATE prize SET remaining = total_quantity WHERE remaining IS NULL;

-- Sauvegarder la définition de la vue existante pour la recréer plus tard
DO $$
DECLARE 
    view_definition TEXT;
BEGIN
    -- Vérifier si la vue existe
    IF EXISTS (SELECT 1 FROM pg_views WHERE viewname = 'admin_prize_distribution') THEN
        -- Récupérer la définition de la vue
        SELECT pg_get_viewdef('admin_prize_distribution', true) INTO view_definition;
        
        -- Sauvegarder la définition dans une table temporaire
        CREATE TEMP TABLE IF NOT EXISTS saved_views (
            view_name TEXT PRIMARY KEY,
            view_definition TEXT
        );
        
        -- Insérer ou mettre à jour la définition
        INSERT INTO saved_views (view_name, view_definition)
        VALUES ('admin_prize_distribution', view_definition)
        ON CONFLICT (view_name) DO UPDATE SET view_definition = EXCLUDED.view_definition;
        
        -- Supprimer la vue existante
        EXECUTE 'DROP VIEW admin_prize_distribution';
    END IF;
END $$;

-- Supprimer la colonne 'won_date' si elle existe déjà (pour éviter les conflits de type)
DO $$
BEGIN
  IF EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name = 'prize' AND column_name = 'won_date') THEN
    ALTER TABLE prize DROP COLUMN won_date;
  END IF;
END $$;

-- Ajouter la colonne 'won_date' avec le bon type JSONB
ALTER TABLE prize ADD COLUMN won_date JSONB DEFAULT '[]';

-- PARTIE 2: INITIALISATION DES DONNÉES
-- ----------------------------------

-- Mettre à jour la colonne remaining pour les prix déjà gagnés
-- On compte le nombre d'entrées dans la table entry qui ont gagné chaque prix
WITH prize_counts AS (
  SELECT 
    prize_id, 
    COUNT(*) as won_count 
  FROM entry 
  WHERE result = 'GAGNÉ' AND prize_id IS NOT NULL 
  GROUP BY prize_id
)
UPDATE prize p
SET remaining = GREATEST(0, p.total_quantity - pc.won_count)
FROM prize_counts pc
WHERE p.id = pc.prize_id;

-- Fonction pour convertir les entrées gagnées en format JSON pour won_date
CREATE OR REPLACE FUNCTION update_won_dates() RETURNS VOID AS $$
DECLARE
  prize_record RECORD;
  entry_dates RECORD;
  date_array JSONB;
BEGIN
  -- Pour chaque prix
  FOR prize_record IN SELECT id FROM prize LOOP
    date_array := '[]'::JSONB;
    
    -- Récupérer toutes les dates de gain pour ce prix
    FOR entry_dates IN 
      SELECT entry_date 
      FROM entry 
      WHERE result = 'GAGNÉ' AND prize_id = prize_record.id 
      ORDER BY entry_date
    LOOP
      -- Ajouter chaque date au tableau JSON
      date_array := date_array || to_jsonb(entry_dates.entry_date);
    END LOOP;
    
    -- Mettre à jour le tableau de dates pour ce prix
    UPDATE prize SET won_date = date_array WHERE id = prize_record.id;
  END LOOP;
END;
$$ LANGUAGE plpgsql;

-- Exécuter la fonction pour mettre à jour won_date
SELECT update_won_dates();

-- PARTIE 3: CRÉATION DU DÉCLENCHEUR AUTOMATIQUE
-- ------------------------------------------

-- Créer une fonction de déclencheur pour maintenir automatiquement la valeur de remaining
CREATE OR REPLACE FUNCTION update_prize_remaining()
RETURNS TRIGGER AS $$
BEGIN
  -- Si une nouvelle entrée est insérée avec un résultat gagné
  IF NEW.result = 'GAGNÉ' AND NEW.prize_id IS NOT NULL THEN
    -- Décrémenter le nombre restant pour ce prix
    UPDATE prize
    SET remaining = GREATEST(0, remaining - 1)
    WHERE id = NEW.prize_id;
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Créer un déclencheur sur la table entry si ce n'est pas déjà fait
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_trigger WHERE tgname = 'update_prize_remaining_trigger'
  ) THEN
    CREATE TRIGGER update_prize_remaining_trigger
    AFTER INSERT ON entry
    FOR EACH ROW
    EXECUTE FUNCTION update_prize_remaining();
  END IF;
END $$;

-- Recréer la vue admin_prize_distribution si elle existait
DO $$
DECLARE
    original_definition TEXT;
    new_definition TEXT;
BEGIN
    -- Vérifier si nous avons sauvegardé une définition
    IF EXISTS (SELECT 1 FROM pg_tables WHERE tablename = 'saved_views') THEN
        -- Récupérer la définition originale
        SELECT view_definition INTO original_definition 
        FROM saved_views 
        WHERE view_name = 'admin_prize_distribution';
        
        IF original_definition IS NOT NULL THEN
            -- Adapter la définition pour le nouveau type JSONB
            -- Remplacer toute référence directe à won_date par une expression adaptée
            new_definition := replace(
                original_definition,
                'prize.won_date',
                'CASE WHEN jsonb_array_length(prize.won_date) > 0 THEN 
                     (prize.won_date->0)::text::timestamp with time zone 
                 ELSE NULL END as won_date'
            );
            
            -- Créer la vue avec la nouvelle définition
            EXECUTE new_definition;
        END IF;
    END IF;
END $$;

-- Supprimer la table temporaire
DROP TABLE IF EXISTS saved_views;

-- Supprimer la fonction d'initialisation après utilisation
DROP FUNCTION update_won_dates();
