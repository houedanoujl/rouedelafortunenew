-- Script pour vérifier les commentaires existants dans la base de données
-- Exécuter avec: psql -h localhost -U postgres -d postgres -f check-db-comments.sql

-- Commentaires sur les tables
SELECT c.relname AS table_name, 
       pg_catalog.obj_description(c.oid, 'pg_class') AS table_comment
FROM pg_catalog.pg_class c
    JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
WHERE c.relkind = 'r'    -- Tables uniquement (pas de vues, etc.)
  AND n.nspname = 'public'  -- Schéma public uniquement
  AND pg_catalog.obj_description(c.oid, 'pg_class') IS NOT NULL;

-- Commentaires sur les colonnes
SELECT c.relname AS table_name,
       a.attname AS column_name,
       pg_catalog.col_description(c.oid, a.attnum) AS column_comment
FROM pg_catalog.pg_class c
    JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
    JOIN pg_catalog.pg_attribute a ON a.attrelid = c.oid
WHERE c.relkind = 'r'    -- Tables uniquement
  AND n.nspname = 'public'  -- Schéma public uniquement
  AND a.attnum > 0        -- Colonnes système exclues
  AND NOT a.attisdropped  -- Colonnes supprimées exclues
  AND pg_catalog.col_description(c.oid, a.attnum) IS NOT NULL;

-- Commentaires sur les index
SELECT c.relname AS index_name,
       pg_catalog.obj_description(c.oid, 'pg_class') AS index_comment
FROM pg_catalog.pg_class c
    JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
WHERE c.relkind = 'i'    -- Index uniquement
  AND n.nspname = 'public'  -- Schéma public uniquement
  AND pg_catalog.obj_description(c.oid, 'pg_class') IS NOT NULL;
