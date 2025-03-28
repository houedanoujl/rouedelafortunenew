import { defineEventHandler, createError, getQuery, H3Event } from 'h3';
import { query } from '~/server/utils/postgres';

export default defineEventHandler(async (event: H3Event) => {
  try {
    // Récupérer les paramètres de requête
    const queryParams = getQuery(event);
    const filters: Record<string, any> = {};
    
    // Construire la liste des filtres à partir des paramètres de requête
    for (const [key, value] of Object.entries(queryParams)) {
      if (key !== 'sort' && key !== 'order') {
        filters[key] = value;
      }
    }

    // Construire la requête SQL pour PostgreSQL
    let sql = 'SELECT * FROM prize';
    const params = [];
    let paramIndex = 1;
    
    // Ajouter les conditions WHERE si des filtres sont spécifiés
    if (Object.keys(filters).length > 0) {
      sql += ' WHERE ';
      sql += Object.entries(filters)
        .map(([key, _]) => {
          return `${key} = $${paramIndex++}`;
        })
        .join(' AND ');
      
      params.push(...Object.values(filters));
    }
    
    // Ajouter ORDER BY si spécifié
    if (queryParams.sort) {
      sql += ` ORDER BY ${queryParams.sort} ${queryParams.order === 'asc' ? 'ASC' : 'DESC'}`;
    }

    // Exécution de la requête avec PostgreSQL
    const result = await query(sql, params);

    return {
      success: true,
      data: result.rows
    };
  } catch (error) {
    console.error('Erreur lors de la récupération des prix:', error);
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de la récupération des prix',
      data: error
    });
  }
});
