import { createClient } from '@supabase/supabase-js';

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
  // Récupérer les variables d'environnement ou utiliser des valeurs par défaut
  const supabaseUrl = process.env.SUPABASE_URL || 'https://qwlzxerivnbuxejqxjyu.supabase.co';
  const supabaseKey = process.env.SUPABASE_KEY || 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InF3bHp4ZXJpdm5idXhlanF4anl1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzczODUxMTIsImV4cCI6MjA1Mjk2MTExMn0.0zbZQwhTjx0YfE-j18vHDM1rPmmOT9PLznVjNzE2Fhk';
  
  // Vérifier si les identifiants Supabase sont disponibles
  const hasValidCredentials = supabaseUrl && supabaseKey && 
                             supabaseUrl.startsWith('https://') && 
                             supabaseKey.length > 20;
  
  // Afficher les valeurs dans la console
  console.log('Supabase config:', { 
    url: supabaseUrl, 
    key: supabaseKey ? `${supabaseKey.substring(0, 10)}...` : 'missing', // Ne pas afficher la clé complète
    valid: hasValidCredentials
  });
  
  // Créer un client Supabase, qu'il soit réel ou mock
  let supabase;
  
  try {
    if (hasValidCredentials) {
      supabase = createClient(supabaseUrl, supabaseKey);
      console.log('Supabase client created successfully');
    } else {
      console.log('Invalid Supabase credentials, using mock client');
      throw new Error('Invalid Supabase credentials');
    }
  } catch (error) {
    console.error('Error creating Supabase client:', error);
    supabase = createMockClient();
  }
  
  return {
    supabase,
    isReal: hasValidCredentials,
    config: {
      url: supabaseUrl,
      key: supabaseKey ? `${supabaseKey.substring(0, 10)}...` : 'missing', // Ne pas afficher la clé complète
      valid: hasValidCredentials
    }
  };
};

// Créer un client Supabase simulé pour le développement
function createMockClient() {
  console.log('Creating mock Supabase client for development');
  
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
          },
          single: async () => {
            await delay(500);
            const items = mockData[table]?.filter((item: Record<string, any>) => item[column] === value) || [];
            return { 
              data: items.length > 0 ? items[0] : null, 
              error: null 
            };
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
      insert: (items: any[]) => ({
        select: async () => {
          await delay(700);
          const newItems = items.map((item, index) => ({
            ...item,
            id: Date.now() + index
          }));
          
          mockData[table] = [...(mockData[table] || []), ...newItems];
          console.log(`Mock: Inserted ${newItems.length} items into ${table}`, newItems);
          return { data: newItems, error: null };
        }
      }),
      update: (item: Record<string, any>) => ({
        eq: (column: string, value: any) => ({
          select: async () => {
            await delay(700);
            const index = mockData[table]?.findIndex((i: Record<string, any>) => i[column] === value);
            if (index !== -1 && mockData[table]) {
              mockData[table][index] = { ...mockData[table][index], ...item };
              console.log(`Mock: Updated item in ${table} where ${column}=${value}`, mockData[table][index]);
              return { data: [mockData[table][index]], error: null };
            }
            return { data: null, error: null };
          },
          execute: async () => {
            await delay(700);
            const index = mockData[table]?.findIndex((i: Record<string, any>) => i[column] === value);
            if (index !== -1 && mockData[table]) {
              mockData[table][index] = { ...mockData[table][index], ...item };
              console.log(`Mock: Updated item in ${table} where ${column}=${value}`, mockData[table][index]);
              return { data: mockData[table][index], error: null };
            }
            return { data: null, error: null };
          }
        })
      })
    })
  };
}
