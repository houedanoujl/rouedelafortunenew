-- =========================================================================
-- FONCTIONS UTILITAIRES POUR LA GESTION DE LA BASE DE DONNÉES
-- =========================================================================

-- Fonction pour désactiver temporairement les contraintes de clé étrangère
CREATE OR REPLACE FUNCTION disable_foreign_keys() RETURNS VOID AS $$
BEGIN
  EXECUTE 'SET session_replication_role = replica;';
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Fonction pour réactiver les contraintes de clé étrangère
CREATE OR REPLACE FUNCTION enable_foreign_keys() RETURNS VOID AS $$
BEGIN
  EXECUTE 'SET session_replication_role = DEFAULT;';
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Fonction pour vider une table
CREATE OR REPLACE FUNCTION truncate_table(table_name TEXT) RETURNS VOID AS $$
BEGIN
  EXECUTE format('TRUNCATE TABLE %I CASCADE', table_name);
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Fonction pour mettre à jour le tableau won_date
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

-- Fonction pour initialiser les valeurs remaining à partir des entrées
CREATE OR REPLACE FUNCTION initialize_remaining_values() RETURNS VOID AS $$
BEGIN
  -- Initialiser la colonne remaining à partir de total_quantity
  UPDATE prize SET remaining = total_quantity WHERE remaining IS NULL;

  -- Mettre à jour la colonne remaining pour les prix déjà gagnés
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
END;
$$ LANGUAGE plpgsql;

-- Accorder les droits d'exécution aux rôles spécifiques
GRANT EXECUTE ON FUNCTION disable_foreign_keys() TO anon, authenticated, service_role;
GRANT EXECUTE ON FUNCTION enable_foreign_keys() TO anon, authenticated, service_role;
GRANT EXECUTE ON FUNCTION truncate_table(TEXT) TO anon, authenticated, service_role;
GRANT EXECUTE ON FUNCTION update_won_dates() TO authenticated, service_role;
GRANT EXECUTE ON FUNCTION initialize_remaining_values() TO authenticated, service_role;
