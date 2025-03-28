import { H3Event } from 'h3';

export default defineEventHandler(async (event: H3Event) => {
  try {
    // Effacer les cookies d'authentification
    // Dans Nuxt, nous devons utiliser un cookie vide avec une date d'expiration passée
    setCookie(event, 'auth_token', '', {
      maxAge: -1,
      path: '/'
    });
    
    setCookie(event, 'username', '', {
      maxAge: -1,
      path: '/'
    });
    
    return {
      success: true,
      message: 'Déconnexion réussie'
    };
    
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error);
    return {
      success: false,
      message: 'Une erreur est survenue lors de la déconnexion'
    };
  }
});
