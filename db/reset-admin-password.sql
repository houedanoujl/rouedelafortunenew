-- Script de réinitialisation du mot de passe administrateur
-- Nouveau mot de passe: admin123

-- Mise à jour du mot de passe pour l'utilisateur 'houedanou' (mot de passe: admin123)
-- Le hash bcrypt du mot de passe 'admin123'
UPDATE `admin_user` 
SET `password_hash` = '$2a$10$DaOmUQTh9nJLGRIW.X1X7ue4aRr9l9VQ1i/mhPZxNuK8F.qeJkn5q', 
    `updated_at` = CURRENT_TIMESTAMP 
WHERE `username` = 'houedanou';

-- Confirmation que le mot de passe a été modifié
SELECT 'Mot de passe réinitialisé avec succès pour l\'utilisateur houedanou' AS 'Résultat';
