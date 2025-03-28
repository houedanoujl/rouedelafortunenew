import { createPool } from 'mysql2/promise';
import { defineEventHandler, createError } from 'h3';

export default defineEventHandler(async (event) => {
  try {
    // Récupérer l'ID depuis les paramètres de route
    const id = event.context.params?.id;

    // Vérifier si l'ID est fourni
    if (!id) {
      throw createError({
        statusCode: 400,
        statusMessage: 'ID du prix requis'
      });
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

    // Exécution de la requête pour trouver le prix par ID
    const [rows] = await pool.execute(
      'SELECT * FROM prize WHERE id = ?',
      [id]
    );

    // Vérifier si le prix existe
    if (Array.isArray(rows) && rows.length === 0) {
      throw createError({
        statusCode: 404,
        statusMessage: 'Prix non trouvé'
      });
    }

    return {
      success: true,
      data: Array.isArray(rows) ? rows[0] : rows
    };
  } catch (error) {
    console.error('Erreur lors de la recherche du prix:', error);
    if (error.statusCode) {
      throw error;
    }
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de la recherche du prix',
      data: error
    });
  }
});
