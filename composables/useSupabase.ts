import { createClient } from '@supabase/supabase-js';
import { useRuntimeConfig } from '#imports';

// Initialize the Supabase client
export const useSupabase = () => {
  const config = useRuntimeConfig();
  
  const supabaseUrl = config.public.supabaseUrl;
  const supabaseKey = config.public.supabaseKey;
  
  // Check if Supabase credentials are available
  if (!supabaseUrl || !supabaseKey) {
    console.warn('Supabase credentials are missing. Using mock functionality.');
    
    // Return a mock Supabase client for development
    return {
      supabase: {
        from: (table: string) => ({
          insert: (data: any) => Promise.resolve({ data: [{ id: 1 }], error: null }),
          select: () => Promise.resolve({ data: [{ id: 1 }], error: null }),
          limit: () => Promise.resolve({ data: [{ id: 1 }], error: null })
        })
      }
    };
  }
  
  // Create and return the actual Supabase client
  const supabase = createClient(supabaseUrl, supabaseKey);
  
  return { supabase };
};
