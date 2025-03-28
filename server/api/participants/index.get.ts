import { defineEventHandler, createError, getQuery } from 'h3';
import { query } from '~/server/utils/postgres';

export default defineEventHandler(async (event) => {
  try {
    // Récupérer les paramètres de requête
    const queryParams = getQuery(event);
    const phone = queryParams.phone as string | undefined;

    // Vérifier si le numéro de téléphone est fourni
    if (!phone) {
      return {
        success: true,
        data: []
      };
    }

    // Rechercher le participant par téléphone avec PostgreSQL
    const result = await query(
      'SELECT * FROM participant WHERE phone = $1',
      [phone]
    );

    return {
      success: true,
      data: result.rows
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
