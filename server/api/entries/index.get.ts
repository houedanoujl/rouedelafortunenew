import { createPool } from 'mysql2/promise';
import { defineEventHandler, createError, getQuery } from 'h3';

export default defineEventHandler(async (event) => {
  try {
    // Récupérer les paramètres de requête
    const query = getQuery(event);
    const participantId = query.participant_id as string | undefined;

    // Vérifier si l'ID du participant est fourni
    if (!participantId) {
      return {
        success: true,
        data: []
      };
    }

    // Créer une connexion à la base de données MySQL
    const pool = createPool({
      host: process.env.MYSQL_HOST || 'mysql',
      user: process.env.MYSQL_USER || 'root',
      password: process.env.MYSQL_PASSWORD || 'root',
      database: process.env.MYSQL_DATABASE || 'rouedelafortune',
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0
    });

    // Exécution de la requête pour trouver les entrées par ID de participant
    // Jointure avec la table prize pour récupérer les informations sur les prix
    const [rows] = await pool.execute(`
      SELECT e.*, p.id as prize_id, p.name as prize_name, p.description as prize_description 
      FROM entry e
      LEFT JOIN prize p ON e.prize_id = p.id
      WHERE e.participant_id = ?
      ORDER BY e.created_at DESC
    `, [participantId]);

    // Formater les résultats pour correspondre à la structure attendue par le client
    let formattedRows = [];
    if (Array.isArray(rows)) {
      formattedRows = rows.map(row => {
        // Convertir une entry avec des infos de prize en entry avec un objet prize imbriqué
        const { prize_name, prize_description, ...entry } = row;
        
        // Si un prix existe, créer un objet prize
        const prize = row.prize_id ? {
          id: row.prize_id,
          name: prize_name,
          description: prize_description
        } : null;
        
        return {
          ...entry,
          prize
        };
      });
    }

    return {
      success: true,
      data: formattedRows
    };
  } catch (error) {
    console.error('Erreur lors de la recherche des entrées:', error);
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de la recherche des entrées',
      data: error
    });
  }
});
