-- Migration pour ajouter les colonnes 'remaining' et 'won_date' à la table prize
-- Compatible avec Supabase

-- Ajouter la colonne 'remaining' si elle n'existe pas
ALTER TABLE prize ADD COLUMN IF NOT EXISTS remaining INTEGER;

-- Mettre à jour 'remaining' avec la valeur de total_quantity pour les entrées existantes
UPDATE prize SET remaining = total_quantity WHERE remaining IS NULL;

-- Ajouter la colonne 'won_date' si elle n'existe pas
ALTER TABLE prize ADD COLUMN IF NOT EXISTS won_date JSONB DEFAULT '[]';

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
