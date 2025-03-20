-- =========================================================================
-- NETTOYAGE FINAL ET OPTIMISATIONS
-- =========================================================================

-- Suppression des fonctions temporaires utilisées pour l'initialisation
DROP FUNCTION IF EXISTS update_won_dates();
DROP FUNCTION IF EXISTS initialize_remaining_values();

-- Création d'un index sur la colonne result de entry pour accélérer les requêtes
CREATE INDEX IF NOT EXISTS idx_entry_result ON entry(result);

-- Analyse des tables pour optimiser les performances des requêtes
ANALYZE participant;
ANALYZE prize;
ANALYZE contest;
ANALYZE entry;
ANALYZE qr_codes;
