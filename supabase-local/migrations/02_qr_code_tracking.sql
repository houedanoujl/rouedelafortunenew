-- =========================================================================
-- CRÉATION DE LA TABLE QR_CODES POUR LE SUIVI DES SCANS
-- =========================================================================

-- Créer la table qr_codes si elle n'existe pas encore
CREATE TABLE IF NOT EXISTS qr_codes (
  id SERIAL PRIMARY KEY,
  tracking_id TEXT UNIQUE NOT NULL,
  participant_id INTEGER REFERENCES participant(id),
  prize_id INTEGER REFERENCES prize(id),
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  scan_count INTEGER DEFAULT 0,
  last_scanned TIMESTAMP WITH TIME ZONE,
  scan_history JSONB DEFAULT '[]'::JSONB
);

-- Créer un index sur l'identifiant de suivi pour accélérer les recherches
CREATE INDEX IF NOT EXISTS idx_qr_codes_tracking_id ON qr_codes(tracking_id);

-- Fonction pour incrémenter le compteur de scans et enregistrer l'historique des scans
CREATE OR REPLACE FUNCTION increment_scan_count()
RETURNS TRIGGER AS $$
BEGIN
  -- Mettre à jour le nombre de scans et la date du dernier scan
  NEW.scan_count := OLD.scan_count + 1;
  NEW.last_scanned := NOW();
  
  -- Ajouter la nouvelle date de scan à l'historique
  NEW.scan_history := OLD.scan_history || jsonb_build_object(
    'timestamp', to_jsonb(NEW.last_scanned),
    'ip', to_jsonb(current_setting('request.headers', true)::json->>'x-forwarded-for'),
    'user_agent', to_jsonb(current_setting('request.headers', true)::json->>'user-agent')
  );
  
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Créer le trigger pour incrémenter le compteur de scans
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_trigger WHERE tgname = 'qr_code_scan_trigger'
  ) THEN
    CREATE TRIGGER qr_code_scan_trigger
    BEFORE UPDATE OF scan_count ON qr_codes
    FOR EACH ROW
    EXECUTE FUNCTION increment_scan_count();
  END IF;
END $$;

-- Créer une API REST pour les scans de QR code
CREATE OR REPLACE FUNCTION scan_qr_code(tracking_id TEXT)
RETURNS JSONB AS $$
DECLARE
  result JSONB;
BEGIN
  -- Vérifier si le QR code existe
  IF NOT EXISTS (SELECT 1 FROM qr_codes WHERE tracking_id = scan_qr_code.tracking_id) THEN
    RETURN jsonb_build_object(
      'success', false,
      'message', 'QR code non trouvé',
      'tracking_id', tracking_id
    );
  END IF;
  
  -- Incrémenter le compteur de scans
  UPDATE qr_codes
  SET scan_count = scan_count + 1
  WHERE tracking_id = scan_qr_code.tracking_id
  RETURNING jsonb_build_object(
    'success', true,
    'tracking_id', tracking_id,
    'scan_count', scan_count,
    'last_scanned', to_jsonb(last_scanned)
  ) INTO result;
  
  RETURN result;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Ajout de la politique de sécurité pour permettre l'accès public à la fonction scan_qr_code
GRANT EXECUTE ON FUNCTION scan_qr_code(TEXT) TO anon;
