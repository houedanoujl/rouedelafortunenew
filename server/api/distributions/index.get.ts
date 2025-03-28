import { defineEventHandler, createError, getQuery, H3Event } from 'h3';
import { query } from '~/server/utils/postgres';

export default defineEventHandler(async (event: H3Event) => {
  try {
    // Récupérer les paramètres de requête
    const queryParams = getQuery(event);
    const weekNumber = queryParams.week ? parseInt(queryParams.week as string) : null;
    const distributed = queryParams.distributed !== undefined ? 
      queryParams.distributed === 'true' : null;
    
    // Construire la requête SQL pour récupérer les distributions
    let sql = `
      SELECT pd.*, p.name as prize_name, p.description as prize_description, 
             part.first_name, part.last_name, part.phone
      FROM prize_distribution pd
      LEFT JOIN prize p ON pd.prize_id = p.id
      LEFT JOIN participant part ON pd.participant_id = part.id
    `;
    
    const params = [];
    let paramIndex = 1;
    const conditions = [];
    
    // Ajouter les filtres à la requête
    if (weekNumber !== null) {
      conditions.push(`pd.week_number = $${paramIndex++}`);
      params.push(weekNumber);
    }
    
    if (distributed !== null) {
      conditions.push(`pd.distributed = $${paramIndex++}`);
      params.push(distributed);
    }
    
    // Ajouter les conditions WHERE si nécessaire
    if (conditions.length > 0) {
      sql += ' WHERE ' + conditions.join(' AND ');
    }
    
    // Ajouter ORDER BY pour trier par date de création
    sql += ' ORDER BY pd.created_at DESC';
    
    // Exécuter la requête
    const result = await query(sql, params);
    
    return {
      success: true,
      data: result.rows
    };
  } catch (error) {
    console.error('Erreur lors de la récupération des distributions de prix:', error);
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de la récupération des distributions de prix',
      data: error
    });
  }
});
