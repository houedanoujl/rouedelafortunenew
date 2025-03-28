import { Pool } from 'pg';

// Création d'un pool de connexions PostgreSQL
const pool = new Pool({
  host: process.env.POSTGRES_HOST || 'postgres',
  user: process.env.POSTGRES_USER || 'postgres',
  password: process.env.POSTGRES_PASSWORD || 'postgres',
  database: process.env.POSTGRES_DATABASE || 'rouedelafortune',
  port: parseInt(process.env.POSTGRES_PORT || '5432'),
  max: 20, // Nombre maximum de clients dans le pool
  idleTimeoutMillis: 30000, // Temps d'inactivité avant de fermer un client
  connectionTimeoutMillis: 2000 // Temps d'attente pour une connexion
});

// Fonction pour exécuter une requête avec des paramètres
export async function query(text: string, params?: any[]) {
  const start = Date.now();
  try {
    const res = await pool.query(text, params);
    const duration = Date.now() - start;
    console.log('Requête exécutée en', duration, 'ms');
    return res;
  } catch (error) {
    console.error('Erreur lors de l\'exécution de la requête:', error);
    throw error;
  }
}

// Fonction pour obtenir un client du pool
export async function getClient() {
  const client = await pool.connect();
  const query = client.query;
  const release = client.release;
  
  // Intercepter la méthode query pour ajouter des logs
  client.query = (...args: any[]) => {
    client.lastQuery = args[0];
    return query.apply(client, args);
  };
  
  // Intercepter la méthode release pour ajouter des logs
  client.release = () => {
    client.query = query;
    client.release = release;
    return release.apply(client);
  };
  
  return client;
}

// Exporter le pool pour un usage direct si nécessaire
export default pool;
