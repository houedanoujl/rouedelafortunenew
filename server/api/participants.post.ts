import mysql from 'mysql2/promise';
import { H3Event } from 'h3';

// Interface pour le participant
interface Participant {
  first_name: string;
  last_name: string;
  phone: string;
  email?: string;
}

// Fonction pour initialiser la connexion MySQL
const initMySQLConnection = async () => {
  const config = useRuntimeConfig();
  const dbUrl = config.public.databaseUrl || 'mysql://user:password@mysql:3306/rouedelafortune';
  
  // Parse l'URL pour obtenir les paramètres de connexion
  const urlPattern = /mysql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/(.+)/;
  const match = dbUrl.match(urlPattern);
  
  if (!match) {
    console.error('Format d\'URL de base de données invalide');
    return null;
  }
  
  const [, user, password, host, port, database] = match;
  
  try {
    // Créer la connexion
    const connection = await mysql.createConnection({
      host,
      port: Number(port),
      user,
      password,
      database
    });
    
    console.log('Connexion MySQL établie avec succès');
    return connection;
  } catch (error) {
    console.error('Erreur lors de la connexion à MySQL:', error);
    return null;
  }
};

// Fonction pour rechercher un participant par téléphone
const findParticipantByPhone = async (connection: any, phone: string) => {
  try {
    const [rows] = await connection.execute(
      'SELECT * FROM participant WHERE phone = ? LIMIT 1', 
      [phone]
    );
    
    if (Array.isArray(rows) && rows.length > 0) {
      return rows[0];
    }
    
    return null;
  } catch (error) {
    console.error('Erreur lors de la recherche du participant:', error);
    throw new Error('Erreur de requête MySQL');
  }
};

// Fonction pour créer un nouveau participant
const createParticipant = async (connection: any, participant: Participant) => {
  try {
    const { first_name, last_name, phone, email } = participant;
    
    const [result] = await connection.execute(
      'INSERT INTO participant (first_name, last_name, phone, email) VALUES (?, ?, ?, ?)',
      [first_name, last_name, phone, email || null]
    );
    
    if (result && result.insertId) {
      const [rows] = await connection.execute(
        'SELECT * FROM participant WHERE id = ?',
        [result.insertId]
      );
      
      if (Array.isArray(rows) && rows.length > 0) {
        return rows[0];
      }
    }
    
    return null;
  } catch (error) {
    console.error('Erreur lors de la création du participant:', error);
    throw new Error('Erreur de requête MySQL');
  }
};

// Endpoint API
export default defineEventHandler(async (event: H3Event) => {
  try {
    // Récupérer les données du corps de la requête
    const body = await readBody(event);
    
    // Valider les données
    if (!body.first_name || !body.last_name || !body.phone) {
      return {
        success: false,
        error: 'Données incomplètes'
      };
    }
    
    // Initialiser la connexion MySQL
    const connection = await initMySQLConnection();
    if (!connection) {
      return {
        success: false,
        error: 'Erreur de connexion à la base de données'
      };
    }
    
    // Vérifier si le participant existe déjà
    const existingParticipant = await findParticipantByPhone(connection, body.phone);
    
    if (existingParticipant) {
      await connection.end();
      return {
        success: true,
        participant: existingParticipant,
        isExisting: true
      };
    }
    
    // Créer un nouveau participant
    const newParticipant = await createParticipant(connection, {
      first_name: body.first_name,
      last_name: body.last_name,
      phone: body.phone,
      email: body.email
    });
    
    // Fermer la connexion
    await connection.end();
    
    if (!newParticipant) {
      return {
        success: false,
        error: 'Erreur lors de la création du participant'
      };
    }
    
    return {
      success: true,
      participant: newParticipant,
      isExisting: false
    };
    
  } catch (error) {
    console.error('Erreur dans l\'API participants:', error);
    return {
      success: false,
      error: 'Erreur serveur : ' + (error instanceof Error ? error.message : String(error))
    };
  }
});
