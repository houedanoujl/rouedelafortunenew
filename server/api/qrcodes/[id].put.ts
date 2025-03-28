import { createPool } from 'mysql2/promise';
import { defineEventHandler, createError, readBody } from 'h3';

export default defineEventHandler(async (event) => {
  try {
    // Récupérer l'ID depuis les paramètres de route
    const id = event.context.params?.id;

    // Vérifier si l'ID est fourni
    if (!id) {
      throw createError({
        statusCode: 400,
        statusMessage: 'ID du code QR requis'
      });
    }

    // Récupérer les données du corps de la requête
    const body = await readBody(event);
    
    // Vérifier que les données à mettre à jour sont présentes
    if (Object.keys(body).length === 0) {
      throw createError({
        statusCode: 400,
        statusMessage: 'Aucune donnée à mettre à jour'
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

    // Vérifier si le code QR existe
    const [existingRows] = await pool.execute(
      'SELECT * FROM qr_code WHERE id = ?',
      [id]
    );

    if (!Array.isArray(existingRows) || existingRows.length === 0) {
      throw createError({
        statusCode: 404,
        statusMessage: 'Code QR non trouvé'
      });
    }

    // Construire la requête de mise à jour dynamiquement
    const fields = Object.keys(body).filter(key => body[key] !== undefined);
    const values = fields.map(field => body[field]);
    
    const updateQuery = `UPDATE qr_code SET ${fields.map(field => `${field} = ?`).join(', ')} WHERE id = ?`;
    
    // Ajouter l'ID à la fin des valeurs
    values.push(id);
    
    // Exécuter la requête de mise à jour
    await pool.execute(updateQuery, values);
    
    // Récupérer le code QR mis à jour
    const [updatedRows] = await pool.execute(
      'SELECT * FROM qr_code WHERE id = ?',
      [id]
    );

    return {
      success: true,
      data: updatedRows[0]
    };
  } catch (error) {
    console.error('Erreur lors de la mise à jour du code QR:', error);
    if (error.statusCode) {
      throw error;
    }
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de la mise à jour du code QR',
      data: error
    });
  }
});
