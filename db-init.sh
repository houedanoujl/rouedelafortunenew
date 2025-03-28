#!/bin/bash
set -e

echo "🔍 Initializing database with all SQL files in /db directory..."

# Attendre que PostgreSQL soit prêt
until PGPASSWORD=postgres psql -h postgres -U postgres -c '\q'; do
  echo "🕒 PostgreSQL is unavailable - waiting..."
  sleep 1
done

echo "✅ PostgreSQL is up - executing SQL files"

# Exécuter les fichiers SQL dans un ordre spécifique
PGPASSWORD=postgres psql -h postgres -U postgres -d postgres << EOF
-- Exécuter les scripts d'initialisation de base
\i /db/all_migrations_combined.sql
EOF

# Charger les données
PGPASSWORD=postgres psql -h postgres -U postgres -d postgres << EOF
-- Exécuter les scripts d'insertion de données
\i /db/prize_rows.sql
\i /db/contest_rows.sql
\i /db/participant_rows.sql
\i /db/entry_rows.sql
\i /db/prize_distribution_rows.sql
\i /db/qr_codes_rows.sql
EOF

echo "🎉 Database initialization completed successfully!"
