import { useMySQL } from './useMySQL';
import { useSupabase } from './useSupabase';
import { useRuntimeConfig } from '#app';

// Composable pour fournir un accès unifié à la base de données
export const useDatabase = () => {
  const config = useRuntimeConfig();
  const useMySQL2 = config.public.useMySQL;
  
  // Déterminer quel composable utiliser
  if (useMySQL2) {
    const { db, isConnected, isReal, type } = useMySQL();
    console.log('Utilisation du connecteur MySQL');
    return {
      database: db,
      isConnected,
      isReal,
      type
    };
  } else {
    const { supabase, isReal } = useSupabase();
    console.log('Utilisation du connecteur Supabase');
    return {
      database: supabase,
      isConnected: true,
      isReal,
      type: 'supabase'
    };
  }
};
