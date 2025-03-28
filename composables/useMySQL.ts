import { ref } from 'vue';
import { useRuntimeConfig } from '#app';
import mysql from 'mysql2/promise';

// Pool de connexions MySQL
let pool: any = null;

// Types pour les données
export interface Participant {
  id: number;
  first_name: string;
  last_name: string;
  phone: string;
  email?: string;
  created_at?: string;
  updated_at?: string;
}

export interface Prize {
  id: number;
  name: string;
  description: string;
  type?: string;
  value?: number;
  image_url?: string;
  stock?: number;
}

export interface Entry {
  id: number;
  participant_id: number;
  contest_id: number;
  result: string;
  prize_id?: number;
  played_at?: string;
  qr_code?: string;
  claimed?: boolean;
  won_date?: string;
}

// Initialisation du pool de connexions MySQL
const initPool = async () => {
  if (pool) return pool;
  
  try {
    const config = useRuntimeConfig();
    
    // URL de connexion depuis les variables d'environnement
    const dbUrl = config.public.databaseUrl || 'mysql://user:password@mysql:3306/rouedelafortune';
    
    // Parse l'URL pour obtenir les paramètres de connexion
    const urlPattern = /mysql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/(.+)/;
    const match = dbUrl.match(urlPattern);
    
    if (!match) {
      console.error('Format d\'URL de base de données invalide');
      return null;
    }
    
    const [, user, password, host, port, database] = match;
    
    // Créer le pool de connexions
    pool = mysql.createPool({
      host,
      port: Number(port),
      user,
      password,
      database,
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0
    });
    
    console.log('Pool de connexions MySQL créé avec succès');
    return pool;
  } catch (error) {
    console.error('Erreur lors de l\'initialisation du pool MySQL:', error);
    return null;
  }
};

// Hook principal
export const useMySQL = () => {
  const isConnected = ref(false);
  
  const connect = async () => {
    try {
      const poolInstance = await initPool();
      if (poolInstance) {
        const [rows] = await poolInstance.execute('SELECT 1');
        isConnected.value = rows && rows.length > 0;
        console.log('Connexion MySQL établie:', isConnected.value);
      }
    } catch (error) {
      console.error('Erreur de connexion MySQL:', error);
      isConnected.value = false;
    }
    
    return isConnected.value;
  };
  
  // Méthode pour exécuter une requête
  const query = async (sql: string, params: any[] = []) => {
    try {
      const poolInstance = await initPool();
      if (!poolInstance) {
        throw new Error('Pool MySQL non initialisé');
      }
      
      const [rows] = await poolInstance.execute(sql, params);
      return { data: rows, error: null };
    } catch (error) {
      console.error('Erreur lors de l\'exécution de la requête:', error);
      return { data: null, error };
    }
  };
  
  // API compatible avec Supabase pour les opérations CRUD
  const from = (table: string) => {
    return {
      select: (columns = '*') => {
        return {
          execute: async () => {
            return await query(`SELECT ${columns} FROM ${table}`);
          },
          eq: (column: string, value: any) => {
            return {
              execute: async () => {
                return await query(`SELECT ${columns} FROM ${table} WHERE ${column} = ?`, [value]);
              },
              single: async () => {
                const { data, error } = await query(`SELECT ${columns} FROM ${table} WHERE ${column} = ? LIMIT 1`, [value]);
                return { data: data && data.length > 0 ? data[0] : null, error };
              }
            };
          },
          single: async () => {
            const { data, error } = await query(`SELECT ${columns} FROM ${table} LIMIT 1`);
            return { data: data && data.length > 0 ? data[0] : null, error };
          }
        };
      },
      insert: (items: any[]) => {
        return {
          select: async () => {
            if (!items || items.length === 0) {
              return { data: null, error: new Error('Aucun élément à insérer') };
            }
            
            try {
              // Préparer les colonnes et valeurs pour l'insertion
              const columns = Object.keys(items[0]);
              const placeholders = Array(columns.length).fill('?').join(', ');
              const values = items.map(item => columns.map(col => item[col]));
              
              // Exécuter l'insertion
              const poolInstance = await initPool();
              if (!poolInstance) {
                throw new Error('Pool MySQL non initialisé');
              }
              
              const insertPromises = values.map(async (valueSet) => {
                const [result] = await poolInstance.execute(
                  `INSERT INTO ${table} (${columns.join(', ')}) VALUES (${placeholders})`, 
                  valueSet
                );
                
                if (result && result.insertId) {
                  // Récupérer l'élément inséré
                  const [inserted] = await poolInstance.execute(
                    `SELECT * FROM ${table} WHERE id = ?`, 
                    [result.insertId]
                  );
                  
                  return inserted && inserted.length > 0 ? inserted[0] : null;
                }
                
                return null;
              });
              
              const insertedItems = await Promise.all(insertPromises);
              return { data: insertedItems.filter(Boolean), error: null };
            } catch (error) {
              console.error(`Erreur lors de l'insertion dans ${table}:`, error);
              return { data: null, error };
            }
          }
        };
      },
      update: (updates: Record<string, any>) => {
        return {
          eq: (column: string, value: any) => {
            return {
              select: async () => {
                try {
                  // Préparer les colonnes et valeurs pour la mise à jour
                  const entries = Object.entries(updates);
                  const setClause = entries.map(([col]) => `${col} = ?`).join(', ');
                  const values = [...entries.map(([, val]) => val), value];
                  
                  // Exécuter la mise à jour
                  const { error } = await query(
                    `UPDATE ${table} SET ${setClause} WHERE ${column} = ?`, 
                    values
                  );
                  
                  if (error) throw error;
                  
                  // Récupérer l'élément mis à jour
                  const { data, error: selectError } = await query(
                    `SELECT * FROM ${table} WHERE ${column} = ?`, 
                    [value]
                  );
                  
                  if (selectError) throw selectError;
                  
                  return { data, error: null };
                } catch (error) {
                  console.error(`Erreur lors de la mise à jour dans ${table}:`, error);
                  return { data: null, error };
                }
              }
            };
          }
        };
      }
    };
  };
  
  return {
    db: { connect, query, from },
    isConnected,
    isReal: true  // Toujours vrai car c'est une vraie connexion à MySQL
  };
};
