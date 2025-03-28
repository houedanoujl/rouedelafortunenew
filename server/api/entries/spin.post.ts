import { defineEventHandler, createError, readBody, H3Event } from 'h3';
import { query } from '~/server/utils/postgres';

export default defineEventHandler(async (event: H3Event) => {
  try {
    // Récupérer les données du corps de la requête
    const { participantId, result, prizeId } = await readBody(event);
    
    // Vérifier que les données requises sont présentes
    if (!participantId || !result) {
      throw createError({
        statusCode: 400,
        statusMessage: 'Les champs participantId et result sont obligatoires'
      });
    }
    
    // Vérifier si le participant existe
    const participantResult = await query(
      'SELECT * FROM participant WHERE id = $1',
      [participantId]
    );
    
    if (participantResult.rows.length === 0) {
      throw createError({
        statusCode: 404,
        statusMessage: 'Participant non trouvé'
      });
    }
    
    // Enregistrer le résultat de la roue
    const entryResult = await query(
      'INSERT INTO entry (participant_id, result, prize_id, created_at) VALUES ($1, $2, $3, NOW()) RETURNING *',
      [participantId, result, prizeId || null]
    );
    
    // Si le résultat est "win" et qu'un prix est spécifié, mettre à jour la distribution des prix
    if (result === 'win' && prizeId) {
      // Vérifier si le prix existe
      const prizeResult = await query(
        'SELECT * FROM prize WHERE id = $1',
        [prizeId]
      );
      
      if (prizeResult.rows.length > 0) {
        // Créer une distribution de prix
        await query(
          'INSERT INTO prize_distribution (prize_id, participant_id, distributed, created_at) VALUES ($1, $2, false, NOW())',
          [prizeId, participantId]
        );
      }
    }
    
    // Retourner les informations de l'entrée
    return {
      success: true,
      message: 'Résultat enregistré avec succès',
      data: entryResult.rows[0]
    };
    
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement du résultat:', error);
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de l\'enregistrement du résultat',
      data: error
    });
  }
});
