import { createPool } from 'mysql2/promise';
import { defineEventHandler, createError, readBody } from 'h3';

export default defineEventHandler(async (event) => {
  try {
    // Récupérer les données du corps de la requête
    const body = await readBody(event);
    
    // Vérifier que les données nécessaires sont présentes
    if (!body.name) {
      throw createError({
        statusCode: 400,
        statusMessage: 'Le nom du prix est requis'
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

    // Extraire les champs du prix à partir du corps de la requête
    const { name, description, total_quantity, remaining } = body;
    
    // Construire la requête d'insertion
    const [result] = await pool.execute(
      'INSERT INTO prize (name, description, total_quantity, remaining) VALUES (?, ?, ?, ?)',
      [name, description, total_quantity, remaining || total_quantity]
    );
    
    // Récupérer l'ID du prix inséré
    const insertId = result.insertId;
    
    // Récupérer le prix inséré
    const [rows] = await pool.execute(
      'SELECT * FROM prize WHERE id = ?',
      [insertId]
    );

    return {
      success: true,
      data: rows[0]
    };
  } catch (error) {
    console.error('Erreur lors de la création du prix:', error);
    if (error.statusCode) {
      throw error;
    }
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de la création du prix',
      data: error
    });
  }
});
