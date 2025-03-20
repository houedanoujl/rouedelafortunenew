-- =========================================================================
-- DONNÉES D'EXEMPLE POUR TEST ET DÉVELOPPEMENT
-- =========================================================================

-- Insertion d'un concours de test
INSERT INTO contest (id, name, description, start_date, end_date, created_at, updated_at, status) 
VALUES 
(1, 'Jeu du Jeudi d''Or', 'Concours hebdomadaire avec lots à gagner', 
'2025-03-19 15:40:13+00', '2025-03-21 15:40:19+00', 
'2025-03-19 15:40:25+00', '2025-03-19 15:40:28+00', 'ACTIVE')
ON CONFLICT (id) DO NOTHING;

-- Insertion de quelques lots de test
INSERT INTO prize (id, name, description, total_quantity, remaining)
VALUES
(1, 'Smartphone', 'Smartphone dernière génération avec 128Go de stockage', 5, 5),
(2, 'Tablette', 'Tablette tactile 10 pouces', 3, 3),
(3, 'Casque audio', 'Casque audio sans fil avec réduction de bruit', 10, 10),
(4, 'Bon d''achat 50€', 'Bon d''achat à utiliser dans nos magasins partenaires', 20, 20),
(5, 'Clé USB 32Go', 'Clé USB haute vitesse de 32Go', 30, 30)
ON CONFLICT (id) DO NOTHING;

-- Insertion de quelques participants de test
INSERT INTO participant (id, first_name, last_name, phone, email)
VALUES
(1, 'Jean', 'Dupont', '+33612345678', 'jean.dupont@example.com'),
(2, 'Marie', 'Martin', '+33623456789', 'marie.martin@example.com'),
(3, 'Pierre', 'Durand', '+33634567890', 'pierre.durand@example.com'),
(4, 'Sophie', 'Dubois', '+33645678901', 'sophie.dubois@example.com'),
(5, 'Paul', 'Moreau', '+33656789012', 'paul.moreau@example.com')
ON CONFLICT (id) DO NOTHING;

-- Insertion d'exemples de participations
INSERT INTO entry (participant_id, contest_id, entry_date, result, prize_id)
VALUES
(1, 1, '2025-03-19 16:00:00+00', 'GAGNÉ', 3),
(2, 1, '2025-03-19 16:15:00+00', 'PERDU', NULL),
(3, 1, '2025-03-19 16:30:00+00', 'GAGNÉ', 4),
(4, 1, '2025-03-19 16:45:00+00', 'GAGNÉ', 5),
(5, 1, '2025-03-19 17:00:00+00', 'PERDU', NULL);

-- Insertion d'exemples de QR codes
INSERT INTO qr_codes (tracking_id, participant_id, prize_id, created_at, scan_count)
VALUES
('QR-12345-ABCDE', 1, 3, '2025-03-19 16:05:00+00', 0),
('QR-23456-BCDEF', 3, 4, '2025-03-19 16:35:00+00', 0),
('QR-34567-CDEFG', 4, 5, '2025-03-19 16:50:00+00', 0);

-- Mettre à jour les valeurs remaining et won_date
SELECT initialize_remaining_values();
SELECT update_won_dates();
