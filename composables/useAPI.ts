import { ref } from 'vue';

// Types pour les données
interface Participant {
  id: number;
  first_name: string;
  last_name: string;
  phone: string;
  email?: string;
  created_at?: string;
  updated_at?: string;
}

interface Prize {
  id: number;
  name: string;
  description?: string;
  total_quantity?: number;
  remaining?: number;
  won_date?: string;
}

interface Entry {
  id: number;
  participant_id: number;
  contest_id: number;
  entry_date?: string;
  created_at?: string;
  result: 'GAGNÉ' | 'PERDU';
  prize_id?: number;
  prize?: Prize;
  updated_at?: string;
}

interface QRCode {
  id: number;
  code: string;
  contest_id: number;
  is_used: boolean;
  created_at?: string;
  updated_at?: string;
}

// Simuler la structure de retour Supabase pour faciliter la migration
interface APIResponse<T> {
  data: T | null;
  error: Error | null;
}

export const useAPI = () => {
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  // Fonction générique pour effectuer les appels API
  async function fetchAPI<T>(url: string, options?: RequestInit): Promise<APIResponse<T>> {
    try {
      const response = await fetch(url, {
        ...options,
        headers: {
          'Content-Type': 'application/json',
          ...(options?.headers || {})
        }
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({ message: response.statusText }));
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      return { data: result.data, error: null };
    } catch (err) {
      console.error('API Error:', err);
      return { data: null, error: err as Error };
    }
  }

  // API Participants
  const participants = {
    // Sélectionner tous les participants
    select: async (): Promise<APIResponse<Participant[]>> => {
      return await fetchAPI<Participant[]>('/api/participants');
    },
    // Rechercher un participant par une condition
    where: (field: string, value: any) => {
      return {
        single: async (): Promise<APIResponse<Participant>> => {
          return await fetchAPI<Participant>(`/api/participants?${field}=${encodeURIComponent(value)}`);
        },
        get: async (): Promise<APIResponse<Participant[]>> => {
          return await fetchAPI<Participant[]>(`/api/participants?${field}=${encodeURIComponent(value)}`);
        }
      };
    },
    // Insérer un nouveau participant
    insert: async (participant: Omit<Participant, 'id'>): Promise<APIResponse<Participant>> => {
      return await fetchAPI<Participant>('/api/participants', {
        method: 'POST',
        body: JSON.stringify(participant)
      });
    },
    // Mettre à jour un participant
    update: (data: Partial<Participant>) => {
      return {
        where: (field: string, value: any) => {
          return {
            execute: async (): Promise<APIResponse<Participant>> => {
              return await fetchAPI<Participant>(`/api/participants/${value}`, {
                method: 'PUT',
                body: JSON.stringify(data)
              });
            }
          };
        }
      };
    }
  };

  // API Entries (participations)
  const entries = {
    // Sélectionner toutes les participations
    select: async (columns?: string): Promise<APIResponse<Entry[]>> => {
      return await fetchAPI<Entry[]>('/api/entries');
    },
    // Rechercher des participations par une condition
    where: (field: string, value: any) => {
      return {
        order: (column: string, options?: { ascending: boolean }) => {
          const direction = options?.ascending ? 'asc' : 'desc';
          return {
            execute: async (): Promise<APIResponse<Entry[]>> => {
              return await fetchAPI<Entry[]>(`/api/entries?${field}=${encodeURIComponent(value)}&sort=${column}&order=${direction}`);
            }
          };
        },
        execute: async (): Promise<APIResponse<Entry[]>> => {
          return await fetchAPI<Entry[]>(`/api/entries?${field}=${encodeURIComponent(value)}`);
        }
      };
    },
    // Insérer une nouvelle participation
    insert: async (entry: Omit<Entry, 'id'>): Promise<APIResponse<Entry>> => {
      return await fetchAPI<Entry>('/api/entries', {
        method: 'POST',
        body: JSON.stringify(entry)
      });
    }
  };

  // API Prix
  const prizes = {
    // Sélectionner tous les prix
    select: async (): Promise<APIResponse<Prize[]>> => {
      return await fetchAPI<Prize[]>('/api/prizes');
    },
    // Rechercher des prix par une condition
    where: (field: string, value: any) => {
      return {
        execute: async (): Promise<APIResponse<Prize[]>> => {
          return await fetchAPI<Prize[]>(`/api/prizes?${field}=${encodeURIComponent(value)}`);
        },
        single: async (): Promise<APIResponse<Prize>> => {
          return await fetchAPI<Prize>(`/api/prizes/${value}`);
        }
      };
    },
    // Insérer un nouveau prix
    insert: async (prize: Omit<Prize, 'id'>): Promise<APIResponse<Prize>> => {
      return await fetchAPI<Prize>('/api/prizes', {
        method: 'POST',
        body: JSON.stringify(prize)
      });
    },
    // Mettre à jour un prix
    update: (data: Partial<Prize>) => {
      return {
        where: (field: string, value: any) => {
          return {
            execute: async (): Promise<APIResponse<Prize>> => {
              return await fetchAPI<Prize>(`/api/prizes/${value}`, {
                method: 'PUT',
                body: JSON.stringify(data)
              });
            }
          };
        }
      };
    }
  };

  // API QR Codes
  const qrCodes = {
    // Sélectionner tous les codes QR
    select: async (): Promise<APIResponse<QRCode[]>> => {
      return await fetchAPI<QRCode[]>('/api/qrcodes');
    },
    // Rechercher des codes QR par une condition
    where: (field: string, value: any) => {
      return {
        execute: async (): Promise<APIResponse<QRCode[]>> => {
          return await fetchAPI<QRCode[]>(`/api/qrcodes?${field}=${encodeURIComponent(value)}`);
        },
        single: async (): Promise<APIResponse<QRCode>> => {
          const response = await fetchAPI<QRCode[]>(`/api/qrcodes?${field}=${encodeURIComponent(value)}`);
          return {
            data: response.data && response.data.length > 0 ? response.data[0] : null,
            error: response.error
          };
        }
      };
    },
    // Insérer un nouveau code QR
    insert: async (qrCode: Omit<QRCode, 'id'>): Promise<APIResponse<QRCode>> => {
      return await fetchAPI<QRCode>('/api/qrcodes', {
        method: 'POST',
        body: JSON.stringify(qrCode)
      });
    },
    // Mettre à jour un code QR
    update: (data: Partial<QRCode>) => {
      return {
        where: (field: string, value: any) => {
          return {
            execute: async (): Promise<APIResponse<QRCode>> => {
              return await fetchAPI<QRCode>(`/api/qrcodes/${value}`, {
                method: 'PUT',
                body: JSON.stringify(data)
              });
            }
          };
        }
      };
    }
  };

  // API pour les requêtes personnalisées
  const custom = {
    fetch: async <T>(url: string, options?: RequestInit): Promise<APIResponse<T>> => {
      return await fetchAPI<T>(url, options);
    }
  };

  return {
    from: (table: string) => {
      switch (table) {
        case 'participant':
          return participants;
        case 'entry':
          return entries;
        case 'prize':
          return prizes;
        case 'qr_code':
          return qrCodes;
        default:
          throw new Error(`Table inconnue: ${table}`);
      }
    },
    // Compatibilité avec l'ancienne API
    supabase: {
      from: (table: string) => {
        switch (table) {
          case 'participant':
            return participants;
          case 'entry':
            return entries;
          case 'prize':
            return prizes;
          case 'qr_code':
            return qrCodes;
          default:
            throw new Error(`Table inconnue: ${table}`);
        }
      }
    },
    custom,
    isLoading,
    error,
    isReal: true // Toujours vrai puisque c'est notre vraie API
  };
};
