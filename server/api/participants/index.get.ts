import { createPool } from 'mysql2/promise';
import { defineEventHandler, createError, getQuery } from 'h3';

export default defineEventHandler(async (event) => {
  try {
    // Récupérer les paramètres de requête
    const query = getQuery(event);
    const phone = query.phone as string | undefined;

    // Vérifier si le numéro de téléphone est fourni
    if (!phone) {
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

    // Exécution de la requête pour trouver le participant par téléphone
    const [rows] = await pool.execute(
      'SELECT * FROM participant WHERE phone = ?',
      [phone]
    );

    return {
      success: true,
      data: rows
    };
  } catch (error) {
    console.error('Erreur lors de la recherche du participant:', error);
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de la recherche du participant',
      data: error
    });
  }
});
