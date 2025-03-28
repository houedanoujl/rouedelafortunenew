import { ref } from 'vue'
import bcrypt from 'bcryptjs'
import Cookies from 'js-cookie'

// Informations d'authentification prédéfinies (ne pas utiliser en production)
// En production, cela devrait être stocké de manière sécurisée dans la base de données
const ADMIN_USERNAME = 'houedanou'
// Mot de passe haché pour 'karniellla'
const ADMIN_PASSWORD_HASH = '$2a$10$J6JVK2.aDnlX1SHWN5dMZeP.vMbmLDjQoQP5ZWR.WE0ND5a6/owkW'

// État d'authentification
const isAuthenticated = ref(false)
const user = ref<string | null>(null)

export function useAuth() {
  // Vérifier si l'utilisateur est déjà authentifié (via cookie)
  const checkAuth = () => {
    const token = Cookies.get('auth_token')
    if (token) {
      isAuthenticated.value = true
      user.value = Cookies.get('username') || null
    }
    return isAuthenticated.value
  }

  // Fonction de connexion
  const login = async (username: string, password: string) => {
    // Vérifier les identifiants
    if (username === ADMIN_USERNAME && await bcrypt.compare(password, ADMIN_PASSWORD_HASH)) {
      isAuthenticated.value = true
      user.value = username
      
      // Créer un cookie de session (expire après 24h)
      Cookies.set('auth_token', 'admin_session_token', { expires: 1 })
      Cookies.set('username', username, { expires: 1 })
      
      return true
    }
    return false
  }

  // Fonction de déconnexion
  const logout = () => {
    isAuthenticated.value = false
    user.value = null
    Cookies.remove('auth_token')
    Cookies.remove('username')
  }

  // Si l'utilisateur est authentifié au chargement
  checkAuth()

  return {
    isAuthenticated,
    user,
    login,
    logout,
    checkAuth
  }
}
