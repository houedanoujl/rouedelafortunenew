-- =========================================================================
-- CRÉATION DU SCHÉMA DE BASE - Roue de la Fortune
-- =========================================================================

-- Création de la table participant
CREATE TABLE IF NOT EXISTS participant (
  id SERIAL PRIMARY KEY,
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  phone TEXT,
  email TEXT,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Création de la table prize (lots)
CREATE TABLE IF NOT EXISTS prize (
  id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  description TEXT,
  total_quantity INTEGER DEFAULT 1,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Création de la table contest (concours)
CREATE TABLE IF NOT EXISTS contest (
  id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  description TEXT,
  start_date TIMESTAMP WITH TIME ZONE,
  end_date TIMESTAMP WITH TIME ZONE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  status TEXT DEFAULT 'ACTIVE'
);

-- Création de la table entry (participation)
CREATE TABLE IF NOT EXISTS entry (
  id SERIAL PRIMARY KEY,
  participant_id INTEGER REFERENCES participant(id),
  contest_id INTEGER REFERENCES contest(id),
  entry_date TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  result TEXT DEFAULT 'EN ATTENTE',
  prize_id INTEGER REFERENCES prize(id),
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Création des indexes essentiels
CREATE INDEX IF NOT EXISTS idx_entry_participant_id ON entry(participant_id);
CREATE INDEX IF NOT EXISTS idx_entry_contest_id ON entry(contest_id);
CREATE INDEX IF NOT EXISTS idx_entry_prize_id ON entry(prize_id);
CREATE INDEX IF NOT EXISTS idx_entry_result ON entry(result);
