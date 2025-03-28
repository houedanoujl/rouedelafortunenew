import { createClient } from '@supabase/supabase-js'

// Configuration pour le client Supabase côté serveur
const supabaseUrl = process.env.SUPABASE_URL || 'http://localhost:8000'
const supabaseKey = process.env.SUPABASE_SERVICE_KEY || 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZS1kZW1vIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImV4cCI6MTk4MzgxMjk5Nn0.EGIM96RAZx35lJzdJsyH-qQwv8Hdp7fsn3W0YpN81IU'

// Création du client Supabase avec les options appropriées
export const supabase = createClient(supabaseUrl, supabaseKey, {
  auth: {
    autoRefreshToken: false,
    persistSession: false
  }
})

// Fonction pour créer une connexion à Supabase avec des paramètres personnalisés
export const createSupabaseClient = (customUrl?: string, customKey?: string) => {
  return createClient(
    customUrl || supabaseUrl,
    customKey || supabaseKey,
    {
      auth: {
        autoRefreshToken: false,
        persistSession: false
      }
    }
  )
}
