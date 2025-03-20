-- =========================================================================
-- AJOUT DE DESCRIPTIONS DÉTAILLÉES POUR LES TABLES ET COLONNES
-- =========================================================================

-- =========================================================================
-- TABLE: participant
-- =========================================================================
COMMENT ON TABLE participant IS 'Stocke les informations sur les participants au concours de la Roue de la Fortune. Chaque entrée représente une personne unique qui s''est inscrite pour participer.';
COMMENT ON COLUMN participant.id IS 'Identifiant unique auto-incrémenté pour chaque participant';
COMMENT ON COLUMN participant.first_name IS 'Prénom du participant';
COMMENT ON COLUMN participant.last_name IS 'Nom de famille du participant';
COMMENT ON COLUMN participant.phone IS 'Numéro de téléphone du participant, utilisé pour le contacter en cas de gain';
COMMENT ON COLUMN participant.email IS 'Adresse email du participant (optionnelle)';
COMMENT ON COLUMN participant.created_at IS 'Date et heure de création de l''enregistrement du participant';
COMMENT ON COLUMN participant.updated_at IS 'Date et heure de la dernière modification des informations du participant';

-- =========================================================================
-- TABLE: prize (lots)
-- =========================================================================
COMMENT ON TABLE prize IS 'Contient tous les lots disponibles pour le concours de la Roue de la Fortune. Ces lots peuvent être attribués aux participants qui gagnent.';
COMMENT ON COLUMN prize.id IS 'Identifiant unique auto-incrémenté pour chaque lot';
COMMENT ON COLUMN prize.name IS 'Nom du lot, affiché aux participants';
COMMENT ON COLUMN prize.description IS 'Description détaillée du lot et de ses caractéristiques';
COMMENT ON COLUMN prize.total_quantity IS 'Nombre total d''exemplaires disponibles de ce lot';
COMMENT ON COLUMN prize.created_at IS 'Date et heure de création de l''enregistrement du lot';
COMMENT ON COLUMN prize.updated_at IS 'Date et heure de la dernière modification des informations du lot';

-- =========================================================================
-- TABLE: contest (concours)
-- =========================================================================
COMMENT ON TABLE contest IS 'Définit les différentes sessions ou périodes de concours de la Roue de la Fortune. Permet d''organiser les participations en différentes périodes ou événements.';
COMMENT ON COLUMN contest.id IS 'Identifiant unique auto-incrémenté pour chaque concours';
COMMENT ON COLUMN contest.name IS 'Nom du concours ou de la session';
COMMENT ON COLUMN contest.description IS 'Description détaillée du concours, règles ou informations supplémentaires';
COMMENT ON COLUMN contest.start_date IS 'Date et heure de début du concours';
COMMENT ON COLUMN contest.end_date IS 'Date et heure de fin du concours';
COMMENT ON COLUMN contest.created_at IS 'Date et heure de création de l''enregistrement du concours';
COMMENT ON COLUMN contest.updated_at IS 'Date et heure de la dernière modification des informations du concours';
COMMENT ON COLUMN contest.status IS 'Statut actuel du concours (ACTIVE, CLOSED, PENDING, etc.)';

-- =========================================================================
-- TABLE: entry (participation)
-- =========================================================================
COMMENT ON TABLE entry IS 'Enregistre chaque participation d''un participant à un concours. Trace les résultats et les lots gagnés lors de chaque tentative.';
COMMENT ON COLUMN entry.id IS 'Identifiant unique auto-incrémenté pour chaque participation';
COMMENT ON COLUMN entry.participant_id IS 'Référence au participant qui a effectué cette tentative (clé étrangère vers participant.id)';
COMMENT ON COLUMN entry.contest_id IS 'Référence au concours auquel cette participation est associée (clé étrangère vers contest.id)';
COMMENT ON COLUMN entry.entry_date IS 'Date et heure exactes de la participation';
COMMENT ON COLUMN entry.result IS 'Résultat de la participation (EN ATTENTE, GAGNÉ, PERDU, etc.)';
COMMENT ON COLUMN entry.prize_id IS 'Référence au lot gagné, si applicable (clé étrangère vers prize.id)';
COMMENT ON COLUMN entry.created_at IS 'Date et heure de création de l''enregistrement de participation';

-- =========================================================================
-- TABLE: qr_codes
-- =========================================================================
COMMENT ON TABLE qr_codes IS 'Stocke les informations sur les codes QR générés pour les lots gagnés, permettant le suivi des scans et la validation des lots.';
COMMENT ON COLUMN qr_codes.id IS 'Identifiant unique auto-incrémenté pour chaque code QR';
COMMENT ON COLUMN qr_codes.tracking_id IS 'Identifiant unique de suivi associé au code QR, utilisé pour l''identifier lors des scans';
COMMENT ON COLUMN qr_codes.participant_id IS 'Référence au participant propriétaire de ce code QR (clé étrangère vers participant.id)';
COMMENT ON COLUMN qr_codes.prize_id IS 'Référence au lot associé à ce code QR (clé étrangère vers prize.id)';
COMMENT ON COLUMN qr_codes.created_at IS 'Date et heure de création du code QR';
COMMENT ON COLUMN qr_codes.scan_count IS 'Nombre total de fois que ce code QR a été scanné';
COMMENT ON COLUMN qr_codes.last_scanned IS 'Date et heure du dernier scan de ce code QR';
COMMENT ON COLUMN qr_codes.scan_history IS 'Historique complet des scans au format JSON, incluant l''horodatage, l''adresse IP et l''agent utilisateur';

-- Ajouter des commentaires sur les index si nécessaire
COMMENT ON INDEX idx_entry_participant_id IS 'Index pour accélérer les recherches de participations par identifiant de participant';
COMMENT ON INDEX idx_entry_contest_id IS 'Index pour accélérer les recherches de participations par identifiant de concours';
COMMENT ON INDEX idx_entry_prize_id IS 'Index pour accélérer les recherches de participations par identifiant de lot';
COMMENT ON INDEX idx_entry_result IS 'Index pour accélérer les recherches de participations par résultat';
COMMENT ON INDEX idx_qr_codes_tracking_id IS 'Index pour accélérer les recherches de codes QR par identifiant de suivi';
