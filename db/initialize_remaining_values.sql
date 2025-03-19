-- Script pour initialiser les valeurs de la colonne remaining et won_date
-- Compatible avec Supabase
-- Ce script doit être exécuté après l'ajout des colonnes remaining et won_date

-- Initialiser la colonne remaining à partir de total_quantity
UPDATE prize SET remaining = total_quantity WHERE remaining IS NULL;

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

-- Initialiser won_date comme un tableau JSON vide s'il est NULL
UPDATE prize SET won_date = '[]'::JSONB WHERE won_date IS NULL;

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

-- Supprimer la fonction après utilisation
DROP FUNCTION update_won_dates();
