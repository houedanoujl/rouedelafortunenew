import { compare } from 'bcryptjs';
import { H3Event } from 'h3';
import { query } from '~/server/utils/postgres';

export default defineEventHandler(async (event: H3Event) => {
  try {
    // Récupérer les données d'identification depuis le corps de la requête
    const { username, password } = await readBody(event);
    
    // Vérifier que les données requises sont présentes
    if (!username || !password) {
      return {
        success: false,
        message: 'Les identifiants sont incomplets'
      };
    }
    
    // Rechercher l'utilisateur admin dans la base de données PostgreSQL
    const result = await query(
      'SELECT * FROM admin_user WHERE username = $1',
      [username]
    );
    
    // Vérifier si l'utilisateur existe
    if (result.rows.length === 0) {
      return {
        success: false,
        message: 'Identifiants incorrects'
      };
    }
    
    const user = result.rows[0];
    
    // Vérifier si le mot de passe correspond
    const passwordMatch = await compare(password, user.password_hash);
    if (!passwordMatch) {
      return {
        success: false,
        message: 'Identifiants incorrects'
      };
    }
    
    // Mettre à jour la date de dernière connexion
    await query(
      'UPDATE admin_user SET last_login = NOW() WHERE id = $1',
      [user.id]
    );
    
    // Authentification réussie
    return {
      success: true,
      user: {
        id: user.id,
        username: user.username
      }
    };
    
  } catch (error) {
    console.error('Erreur lors de l\'authentification:', error);
    return {
      success: false,
      message: 'Une erreur est survenue lors de l\'authentification'
    };
  }
});
