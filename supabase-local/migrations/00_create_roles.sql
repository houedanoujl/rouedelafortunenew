-- =========================================================================
-- CRÉATION DES RÔLES SUPABASE NÉCESSAIRES
-- =========================================================================

-- Vérifie si le rôle anon existe, sinon le crée
DO
$$
BEGIN
  IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'anon') THEN
    CREATE ROLE anon;
    RAISE NOTICE 'Rôle anon créé avec succès';
  ELSE
    RAISE NOTICE 'Le rôle anon existe déjà';
  END IF;
END
$$;

-- Vérifie si le rôle authenticated existe, sinon le crée
DO
$$
BEGIN
  IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'authenticated') THEN
    CREATE ROLE authenticated;
    RAISE NOTICE 'Rôle authenticated créé avec succès';
  ELSE
    RAISE NOTICE 'Le rôle authenticated existe déjà';
  END IF;
END
$$;

-- Vérifie si le rôle service_role existe, sinon le crée
DO
$$
BEGIN
  IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'service_role') THEN
    CREATE ROLE service_role;
    RAISE NOTICE 'Rôle service_role créé avec succès';
  ELSE
    RAISE NOTICE 'Le rôle service_role existe déjà';
  END IF;
END
$$;

-- Accorder les privilèges nécessaires
GRANT USAGE ON SCHEMA public TO anon;
GRANT USAGE ON SCHEMA public TO authenticated;
GRANT USAGE ON SCHEMA public TO service_role;

GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO service_role;

-- Message de confirmation
DO
$$
BEGIN
  RAISE NOTICE 'Configuration des rôles Supabase terminée';
END
$$;
