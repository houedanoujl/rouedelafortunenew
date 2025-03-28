import { H3Event } from 'h3';
import { query } from '~/server/utils/postgres';

export default defineEventHandler(async (event: H3Event) => {
  try {
    // Récupérer les cookies
    const cookies = parseCookies(event);
    const authToken = cookies.auth_token;
    const username = cookies.username;
    
    // Vérifier si le token est présent
    if (!authToken || !username) {
      return {
        isAuthenticated: false,
        user: null
      };
    }
    
    // Vérifier si l'utilisateur existe dans la base de données PostgreSQL
    const result = await query(
      'SELECT * FROM admin_user WHERE username = $1',
      [username]
    );
    
    // Si l'utilisateur n'existe pas, retourner non authentifié
    if (result.rows.length === 0) {
      return {
        isAuthenticated: false,
        user: null
      };
    }
    
    return {
      isAuthenticated: true,
      user: {
        username,
        id: result.rows[0].id
      }
    };
    
  } catch (error) {
    console.error('Erreur lors de la vérification d\'authentification:', error);
    return {
      isAuthenticated: false,
      user: null,
      error: 'Une erreur est survenue lors de la vérification'
    };
  }
});
