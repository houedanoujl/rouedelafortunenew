import { createPool } from 'mysql2/promise';
import { defineEventHandler, createError, readBody } from 'h3';

export default defineEventHandler(async (event) => {
  try {
    // Récupérer les données du corps de la requête
    const body = await readBody(event);
    
    // Vérifier que les données nécessaires sont présentes
    if (!body.participant_id || !body.contest_id || !body.result) {
      throw createError({
        statusCode: 400,
        statusMessage: 'L\'ID du participant, l\'ID du concours et le résultat sont requis'
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

    // Débuter une transaction pour garantir l'intégrité des données
    const connection = await pool.getConnection();
    await connection.beginTransaction();

    try {
      // Extraire les champs de l'entrée à partir du corps de la requête
      const { participant_id, contest_id, result, prize_id, entry_date } = body;
      
      // Si un prix est gagné, vérifier et mettre à jour la quantité restante
      if (prize_id && result === 'GAGNÉ') {
        // Récupérer les informations sur le prix
        const [prizeRows] = await connection.execute(
          'SELECT * FROM prize WHERE id = ?',
          [prize_id]
        );
        
        if (!Array.isArray(prizeRows) || prizeRows.length === 0) {
          throw createError({
            statusCode: 404,
            statusMessage: 'Prix non trouvé'
          });
        }
        
        const prize = prizeRows[0];
        
        // Vérifier si des prix sont encore disponibles
        if (prize.remaining <= 0) {
          throw createError({
            statusCode: 400,
            statusMessage: 'Plus de prix disponibles'
          });
        }
        
        // Mettre à jour la quantité restante
        await connection.execute(
          'UPDATE prize SET remaining = remaining - 1 WHERE id = ?',
          [prize_id]
        );
      }
      
      // Construire la requête d'insertion pour l'entrée
      const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
      const entryDate = entry_date || now;
      
      const [result1] = await connection.execute(
        'INSERT INTO entry (participant_id, contest_id, result, prize_id, entry_date, created_at) VALUES (?, ?, ?, ?, ?, ?)',
        [participant_id, contest_id, result, prize_id || null, entryDate, now]
      );
      
      // Récupérer l'ID de l'entrée insérée
      const insertId = result1.insertId;
      
      // Récupérer l'entrée insérée avec les informations sur le prix
      const [rows] = await connection.execute(`
        SELECT e.*, p.id as prize_id, p.name as prize_name, p.description as prize_description 
        FROM entry e
        LEFT JOIN prize p ON e.prize_id = p.id
        WHERE e.id = ?
      `, [insertId]);
      
      // Formatter les résultats
      let formattedEntry = null;
      if (Array.isArray(rows) && rows.length > 0) {
        const row = rows[0];
        const { prize_name, prize_description, ...entry } = row;
        
        // Si un prix existe, créer un objet prize
        const prize = row.prize_id ? {
          id: row.prize_id,
          name: prize_name,
          description: prize_description
        } : null;
        
        formattedEntry = {
          ...entry,
          prize
        };
      }
      
      // Valider la transaction
      await connection.commit();

      return {
        success: true,
        data: formattedEntry
      };
    } catch (error) {
      // En cas d'erreur, annuler la transaction
      await connection.rollback();
      throw error;
    } finally {
      // Libérer la connexion
      connection.release();
    }
  } catch (error) {
    console.error('Erreur lors de la création de l\'entrée:', error);
    if (error.statusCode) {
      throw error;
    }
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de la création de l\'entrée',
      data: error
    });
  }
});
