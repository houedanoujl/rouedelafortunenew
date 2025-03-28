import { useAuth } from '~/composables/useAuth'

export default defineNuxtRouteMiddleware((to, from) => {
  const { isAuthenticated, checkAuth } = useAuth()
  
  // Vérifier l'authentification ou rediriger vers la page de login
  if (!checkAuth() && to.path !== '/admin') {
    return navigateTo('/admin')
  }
})
