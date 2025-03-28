import { createPool } from 'mysql2/promise';
import { defineEventHandler, createError, readBody } from 'h3';

export default defineEventHandler(async (event) => {
  try {
    // Récupérer les données du corps de la requête
    const body = await readBody(event);
    
    // Vérifier que les données nécessaires sont présentes
    if (!body.code || !body.contest_id) {
      throw createError({
        statusCode: 400,
        statusMessage: 'Le code QR et l\'ID du concours sont requis'
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

    // Extraire les champs du QR code à partir du corps de la requête
    const { code, contest_id, is_used = false } = body;
    
    // Construire la requête d'insertion
    const [result] = await pool.execute(
      'INSERT INTO qr_code (code, contest_id, is_used) VALUES (?, ?, ?)',
      [code, contest_id, is_used]
    );
    
    // Récupérer l'ID du QR code inséré
    const insertId = result.insertId;
    
    // Récupérer le QR code inséré
    const [rows] = await pool.execute(
      'SELECT * FROM qr_code WHERE id = ?',
      [insertId]
    );

    return {
      success: true,
      data: rows[0]
    };
  } catch (error) {
    console.error('Erreur lors de la création du code QR:', error);
    if (error.statusCode) {
      throw error;
    }
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de la création du code QR',
      data: error
    });
  }
});
