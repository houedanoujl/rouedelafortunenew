-- =========================================================================
-- CRÉATION DES VUES ADMINISTRATIVES
-- =========================================================================

-- Vue pour l'administration des lots
CREATE OR REPLACE VIEW prize_admin_view AS
SELECT 
    p.id AS prize_id,
    p.name AS prize_name,
    p.description AS prize_description,
    p.total_quantity AS total_quantity,
    p.remaining AS remaining_quantity,
    (SELECT COUNT(*) FROM entry e WHERE e.prize_id = p.id AND e.result = 'GAGNÉ') AS total_won,
    (SELECT CASE 
        WHEN jsonb_array_length(p.won_date) > 0 THEN 
            (p.won_date->0)::text::timestamp with time zone 
        ELSE NULL 
    END) as first_won_date
FROM 
    prize p
ORDER BY 
    p.id;

-- Vue pour la distribution des prix
CREATE OR REPLACE VIEW admin_prize_distribution AS
SELECT
    p.id AS prize_id,
    p.name AS prize_name,
    p.description AS prize_description,
    p.total_quantity,
    p.remaining,
    (p.total_quantity - p.remaining) AS distributed,
    CASE WHEN p.total_quantity > 0 
        THEN ROUND(((p.total_quantity - p.remaining)::numeric / p.total_quantity::numeric) * 100, 2)
        ELSE 0
    END AS percent_distributed,
    CASE 
        WHEN jsonb_array_length(p.won_date) > 0 THEN 
            (p.won_date->0)::text::timestamp with time zone 
        ELSE NULL 
    END as first_won_date,
    CASE 
        WHEN jsonb_array_length(p.won_date) > 0 THEN 
            (p.won_date->-1)::text::timestamp with time zone 
        ELSE NULL 
    END as last_won_date,
    jsonb_array_length(p.won_date) AS won_dates_count
FROM
    prize p
ORDER BY
    distributed DESC, p.name;
