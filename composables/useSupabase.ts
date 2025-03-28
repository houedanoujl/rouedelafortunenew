// Remplacer l'ancien composable useSupabase par une redirection vers useAPI
// pour maintenir la compatibilité avec le code existant
import { useAPI } from './useAPI';

// Types pour les données mockées (conservées pour référence)
interface MockParticipant {
  id: number;
  first_name: string;
  last_name: string;
  phone: string;
  email?: string;
}

interface MockPrize {
  id: number;
  name: string;
  description: string;
}

interface MockEntry {
  id: number;
  participant_id: number;
  contest_id: number;
  result: string;
  prize_id?: number;
}

interface MockDatabase {
  participant: MockParticipant[];
  entry: MockEntry[];
  prize: MockPrize[];
  [key: string]: any; // Pour permettre l'indexation par chaîne
}

// Initialize the Supabase client (now redirected to API)
export const useSupabase = () => {
  // Utiliser notre nouvelle API
  const api = useAPI();
  
  console.log('Utilisation de l\'API locale au lieu de Supabase');
  
  return {
    supabase: api.supabase, // api.supabase imite l'interface de Supabase
    isReal: true,
    config: {
      url: '/api',
      key: 'local-api',
      valid: true
    }
  };
};
