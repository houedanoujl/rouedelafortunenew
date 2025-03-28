import { createPool } from 'mysql2/promise';
import { defineEventHandler, createError, getQuery } from 'h3';

export default defineEventHandler(async (event) => {
  try {
    // Récupérer les paramètres de requête
    const query = getQuery(event);
    const filters = {};
    
    // Construire la liste des filtres à partir des paramètres de requête
    for (const [key, value] of Object.entries(query)) {
      if (key !== 'sort' && key !== 'order') {
        filters[key] = value;
      }
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

    // Construire la requête SQL
    let sql = 'SELECT * FROM qr_code';
    const params = [];
    
    // Ajouter les conditions WHERE si des filtres sont spécifiés
    if (Object.keys(filters).length > 0) {
      sql += ' WHERE ';
      sql += Object.entries(filters)
        .map(([key, _]) => `${key} = ?`)
        .join(' AND ');
      
      params.push(...Object.values(filters));
    }
    
    // Ajouter ORDER BY si spécifié
    if (query.sort) {
      sql += ` ORDER BY ${query.sort} ${query.order === 'asc' ? 'ASC' : 'DESC'}`;
    }

    // Exécution de la requête
    const [rows] = await pool.execute(sql, params);

    return {
      success: true,
      data: rows
    };
  } catch (error) {
    console.error('Erreur lors de la récupération des codes QR:', error);
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de la récupération des codes QR',
      data: error
    });
  }
});
