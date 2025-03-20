-- Initial database schema setup
-- This will be executed when the local Supabase instance is first started

-- Create tables if they don't exist yet
CREATE TABLE IF NOT EXISTS participant (
  id SERIAL PRIMARY KEY,
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  phone TEXT,
  email TEXT
);

CREATE TABLE IF NOT EXISTS prize (
  id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  description TEXT,
  total_quantity INTEGER DEFAULT 1,
  remaining INTEGER,
  won_date JSONB DEFAULT '[]'
);

CREATE TABLE IF NOT EXISTS contest (
  id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  start_date TIMESTAMP WITH TIME ZONE,
  end_date TIMESTAMP WITH TIME ZONE,
  status TEXT DEFAULT 'ACTIVE'
);

CREATE TABLE IF NOT EXISTS entry (
  id SERIAL PRIMARY KEY,
  participant_id INTEGER REFERENCES participant(id),
  contest_id INTEGER REFERENCES contest(id),
  entry_date TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  result TEXT DEFAULT 'EN ATTENTE',
  prize_id INTEGER REFERENCES prize(id)
);

-- Import the QR code tracking migration
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

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_qr_codes_tracking_id ON qr_codes(tracking_id);

-- Create functions
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

-- Create triggers
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

-- Create a function to manage remaining prize quantities
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

-- Create trigger for prize tracking
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

-- Create RPC function for QR code scanning
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

-- Set permissions
GRANT EXECUTE ON FUNCTION scan_qr_code(TEXT) TO anon;
