import { defineEventHandler, createError, readBody, H3Event } from 'h3';
import { query } from '~/server/utils/postgres';

export default defineEventHandler(async (event: H3Event) => {
  try {
    // Récupérer les données du corps de la requête
    const { firstName, lastName, phone, email } = await readBody(event);
    
    // Vérifier que les données requises sont présentes
    if (!firstName || !lastName || !phone) {
      throw createError({
        statusCode: 400,
        statusMessage: 'Les champs prénom, nom et téléphone sont obligatoires'
      });
    }
    
    // Vérifier si le participant existe déjà
    const checkResult = await query(
      'SELECT * FROM participant WHERE phone = $1',
      [phone]
    );
    
    if (checkResult.rows.length > 0) {
      // Le participant existe déjà, retourner ses informations
      return {
        success: true,
        message: 'Participant déjà inscrit',
        data: checkResult.rows[0],
        isNew: false
      };
    }
    
    // Créer un nouveau participant
    const insertResult = await query(
      'INSERT INTO participant (first_name, last_name, phone, email, created_at) VALUES ($1, $2, $3, $4, NOW()) RETURNING *',
      [firstName, lastName, phone, email || null]
    );
    
    // Retourner les informations du nouveau participant
    return {
      success: true,
      message: 'Inscription réussie',
      data: insertResult.rows[0],
      isNew: true
    };
    
  } catch (error) {
    console.error('Erreur lors de l\'inscription du participant:', error);
    throw createError({
      statusCode: 500,
      statusMessage: 'Erreur lors de l\'inscription du participant',
      data: error
    });
  }
});
