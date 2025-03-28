import { ref } from 'vue'
import Cookies from 'js-cookie'

// État d'authentification
const isAuthenticated = ref(false)
const user = ref<{ username: string } | null>(null)
const error = ref<string | null>(null)

export function useAuth() {
  // Vérifier si l'utilisateur est déjà authentifié (via API)
  const checkAuth = async () => {
    try {
      // Vérifier si un cookie existe d'abord pour éviter des appels API inutiles
      const token = Cookies.get('auth_token')
      if (!token) {
        isAuthenticated.value = false
        user.value = null
        return false
      }
      
      // Appeler l'API de vérification d'authentification
      const response = await fetch('/api/auth/check')
      const data = await response.json()
      
      if (data.isAuthenticated) {
        isAuthenticated.value = true
        user.value = data.user
        return true
      } else {
        isAuthenticated.value = false
        user.value = null
        return false
      }
    } catch (err) {
      console.error('Erreur lors de la vérification d\'authentification:', err)
      isAuthenticated.value = false
      user.value = null
      return false
    }
  }

  // Fonction de connexion
  const login = async (username: string, password: string) => {
    try {
      error.value = null
      
      // Appeler l'API de connexion
      const response = await fetch('/api/auth/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username, password })
      })
      
      const data = await response.json()
      
      if (data.success) {
        isAuthenticated.value = true
        user.value = data.user
        
        // Créer un cookie de session (expire après 24h)
        Cookies.set('auth_token', 'admin_session_token', { expires: 1 })
        Cookies.set('username', username, { expires: 1 })
        
        return true
      } else {
        error.value = data.message || 'Identifiants incorrects. Veuillez réessayer.'
        return false
      }
    } catch (err) {
      console.error('Erreur lors de la connexion:', err)
      error.value = 'Une erreur est survenue lors de la connexion. Veuillez réessayer.'
      return false
    }
  }

  // Fonction de déconnexion
  const logout = async () => {
    try {
      // Appeler l'API de déconnexion
      await fetch('/api/auth/logout', {
        method: 'POST'
      })
    } catch (err) {
      console.error('Erreur lors de la déconnexion:', err)
    } finally {
      // Toujours nettoyer l'état local même si l'API échoue
      isAuthenticated.value = false
      user.value = null
      Cookies.remove('auth_token')
      Cookies.remove('username')
    }
  }

  // Vérifier l'authentification au chargement
  if (process.client) {
    checkAuth()
  }

  return {
    isAuthenticated,
    user,
    error,
    login,
    logout,
    checkAuth
  }
}
