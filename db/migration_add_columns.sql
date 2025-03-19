-- Ajouter la colonne 'remaining' à la table 'prize' pour suivre le nombre de lots restants
ALTER TABLE "public"."prize" ADD COLUMN "remaining" integer;

-- Mettre à jour les valeurs de 'remaining' pour correspondre à 'total_quantity' pour les lots existants
UPDATE "public"."prize" SET "remaining" = "total_quantity";

-- Ajouter la colonne 'won_date' à la table 'entry' pour enregistrer la date de gain
ALTER TABLE "public"."entry" ADD COLUMN "won_date" timestamp with time zone;

-- Mettre à jour les entrées existantes pour avoir une date de gain (si nécessaire)
UPDATE "public"."entry" SET "won_date" = "created_at" WHERE "result" = 'GAGNÉ';

-- Créer une vue pour l'administration des lots
CREATE OR REPLACE VIEW "public"."prize_admin_view" AS
SELECT 
    p.id AS prize_id,
    p.name AS prize_name,
    p.description AS prize_description,
    p.total_quantity AS total_quantity,
    p.remaining AS remaining_quantity,
    (SELECT COUNT(*) FROM "public"."entry" e WHERE e.prize_id = p.id AND e.result = 'GAGNÉ') AS total_won,
    (SELECT COUNT(*) FROM "public"."entry" e WHERE e.prize_id = p.id AND e.result = 'GAGNÉ' AND e.won_date::date = current_date) AS won_today,
    (SELECT COUNT(*) FROM "public"."entry" e WHERE e.prize_id = p.id AND e.result = 'GAGNÉ' AND e.won_date::date >= date_trunc('week', current_date)) AS won_this_week,
    (SELECT COUNT(*) FROM "public"."entry" e WHERE e.prize_id = p.id AND e.result = 'GAGNÉ' AND e.won_date::date >= date_trunc('month', current_date)) AS won_this_month
FROM 
    "public"."prize" p
ORDER BY 
    p.id;

-- Créer une fonction TRIGGER pour mettre à jour 'remaining' lorsqu'un lot est gagné
CREATE OR REPLACE FUNCTION update_prize_remaining()
RETURNS TRIGGER AS $$
BEGIN
    -- Si le résultat est 'GAGNÉ' et qu'un prix est attribué, décrémenter 'remaining'
    IF NEW.result = 'GAGNÉ' AND NEW.prize_id IS NOT NULL THEN
        -- Mettre à jour la date de gain
        NEW.won_date = CURRENT_TIMESTAMP;
        
        -- Décrémenter le nombre de lots restants
        UPDATE "public"."prize" 
        SET "remaining" = GREATEST(0, "remaining" - 1) 
        WHERE "id" = NEW.prize_id;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Créer le TRIGGER pour appeler la fonction lors de l'insertion d'une nouvelle entrée
DROP TRIGGER IF EXISTS trigger_update_prize_remaining ON "public"."entry";
CREATE TRIGGER trigger_update_prize_remaining
BEFORE INSERT ON "public"."entry"
FOR EACH ROW
EXECUTE FUNCTION update_prize_remaining();
