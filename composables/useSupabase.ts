import { createClient } from '@supabase/supabase-js';
import { useRuntimeConfig } from '#imports';

// Types pour les données mockées
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

// Initialize the Supabase client
export const useSupabase = () => {
  const config = useRuntimeConfig();
  console.log('Runtime config:', config);
  console.log('Process env:', process.env);
  // ...
  const supabaseUrl = config.public.supabaseUrl as string;
  const supabaseKey = config.public.supabaseKey as string;
  
  // Vérifier si les identifiants Supabase sont disponibles
  const hasValidCredentials = 
    supabaseUrl && 
    supabaseKey && 
    supabaseUrl !== 'https://example.supabase.co' && 
    supabaseKey !== 'your-supabase-anon-key';
  
  // Afficher les valeurs des variables d'environnement
  console.log('Supabase config:', { 
    url: supabaseUrl, 
    key: supabaseKey,
    valid: hasValidCredentials
  });
  
  // Créer un client Supabase, qu'il soit réel ou mock
  let supabase;
  
  if (hasValidCredentials) {
    try {
      supabase = createClient(supabaseUrl, supabaseKey);
    } catch (error) {
      supabase = createMockClient();
    }
  } else {
    supabase = createMockClient();
  }
  
  return {
    supabase,
    isReal: hasValidCredentials
  };
};

// Créer un client Supabase simulé pour le développement
function createMockClient() {
  // Valeurs simulées pour les tables
  const mockData: MockDatabase = {
    participant: [],
    entry: [],
    prize: [
      { id: 1, name: 'Smartphone', description: 'Un smartphone dernière génération' },
      { id: 2, name: 'Tablette', description: 'Une tablette tactile' },
      { id: 3, name: 'Casque audio', description: 'Un casque audio sans fil' },
      { id: 4, name: 'Bon d\'achat', description: 'Un bon d\'achat de 50€' },
      { id: 5, name: 'Clé USB', description: 'Une clé USB 32Go' }
    ]
  };
  
  // Simuler un délai réseau
  const delay = (ms: number) => new Promise(resolve => setTimeout(resolve, ms));
  
  // Client simulé
  return {
    from: (table: string) => ({
      select: (columns?: string) => ({
        execute: async () => {
          await delay(500);
          return { data: mockData[table] || [], error: null };
        },
        eq: (column: string, value: any) => ({
          execute: async () => {
            await delay(500);
            const data = mockData[table]?.filter((item: Record<string, any>) => item[column] === value) || [];
            return { data, error: null };
          }
        }),
        single: async () => {
          await delay(500);
          return { 
            data: mockData[table]?.length > 0 ? mockData[table][0] : null, 
            error: null 
          };
        }
      }),
      insert: (item: any[]) => ({
        select: async () => {
          await delay(700);
          const newItem = { ...item[0], id: Date.now() };
          mockData[table] = [...(mockData[table] || []), newItem];
          return { data: [newItem], error: null };
        }
      }),
      update: (item: Record<string, any>) => ({
        eq: (column: string, value: any) => ({
          execute: async () => {
            await delay(700);
            const index = mockData[table]?.findIndex((i: Record<string, any>) => i[column] === value);
            if (index !== -1 && mockData[table]) {
              mockData[table][index] = { ...mockData[table][index], ...item };
              return { data: mockData[table][index], error: null };
            }
            return { data: null, error: null };
          }
        })
      })
    })
  };
}
