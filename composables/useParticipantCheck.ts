import { ref, reactive } from 'vue';
import { useSupabase } from './useSupabase';

// Types pour les données
interface Participant {
  id: number;
  first_name: string;
  last_name: string;
  phone: string;
  email?: string;
  created_at?: string;
  updated_at?: string;
}

interface Prize {
  id: number;
  name: string;
  description?: string;
  total_quantity?: number;
}

interface Entry {
  id: number;
  participant_id: number;
  contest_id: number;
  entry_date?: string;
  created_at?: string;
  result: 'GAGNÉ' | 'PERDU';
  prize_id?: number;
  prize?: Prize;
  updated_at?: string;
}

export function useParticipantCheck() {
  const { supabase, isReal, config } = useSupabase();
  
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  
  const participantState = reactive({
    isRegistered: false,
    participant: null as Participant | null,
    hasPlayed: false,
    playedRecently: false,  // Nouvelle propriété pour indiquer si le participant a joué récemment
    lastPlayDate: null as Date | null,  // Date de la dernière participation
    daysUntilNextPlay: 0,  // Nombre de jours avant de pouvoir rejouer
    gameResult: null as Entry | null
  });

  /**
   * Vérifie si un participant existe avec le numéro de téléphone donné
   * @param phone - Numéro de téléphone à vérifier
   */
  async function checkParticipantByPhone(phone: string): Promise<Participant | null> {
    if (!phone) {
      error.value = "Numéro de téléphone requis";
      return null;
    }

    isLoading.value = true;
    error.value = null;
    
    try {
      console.log('Vérification du participant avec le téléphone:', phone);
      console.log('Utilisation de la configuration Supabase:', config);
      
      // Si en mode mock, simuler un délai
      if (!isReal) {
        await new Promise(resolve => setTimeout(resolve, 800));
        participantState.isRegistered = false;
        participantState.participant = null;
        participantState.hasPlayed = false;
        participantState.playedRecently = false;
        participantState.lastPlayDate = null;
        participantState.daysUntilNextPlay = 0;
        participantState.gameResult = null;
        return null;
      }
      
      // Rechercher le participant par téléphone
      let result;
      try {
        const query = supabase
          .from('participant')
          .select('*')
          .eq('phone', phone);
          
        // Vérifier si la méthode execute existe (client mock)
        if ('execute' in query) {
          result = await query.execute();
        } else {
          result = await query;
        }
      } catch (err) {
        console.error('Erreur lors de la requête Supabase:', err);
        throw err;
      }
      
      if (result.error) {
        console.error('Erreur lors de la recherche du participant:', result.error);
        throw result.error;
      }
      
      // Vérifier si un participant a été trouvé
      const participants = result.data as Participant[] | null;
      if (!participants || participants.length === 0) {
        // Participant non trouvé
        participantState.isRegistered = false;
        participantState.participant = null;
        participantState.hasPlayed = false;
        participantState.playedRecently = false;
        participantState.lastPlayDate = null;
        participantState.daysUntilNextPlay = 0;
        participantState.gameResult = null;
        return null;
      }
      
      const participant = participants[0];
      
      // Participant trouvé, vérifier s'il a déjà joué
      participantState.isRegistered = true;
      participantState.participant = participant;
      
      let entriesResult;
      try {
        const query = supabase
          .from('entry')
          .select('*, prize:prize_id(*)')
          .eq('participant_id', participant.id);
          
        // Vérifier si la méthode execute existe (client mock)
        if ('execute' in query) {
          entriesResult = await query.execute();
        } else {
          entriesResult = await query;
        }
      } catch (err) {
        console.error('Erreur lors de la requête Supabase:', err);
        throw err;
      }
      
      if (entriesResult.error) {
        console.error('Erreur lors de la recherche des participations:', entriesResult.error);
        throw entriesResult.error;
      }
      
      const entries = entriesResult.data as Entry[] | null;
      if (entries && entries.length > 0) {
        // Le participant a déjà joué
        participantState.hasPlayed = true;
        participantState.gameResult = entries[0]; // Prendre la première participation
        
        // Vérifier si le participant a joué récemment (dans les 7 derniers jours)
        const latestEntry = entries[0];
        const entryDate = latestEntry.created_at 
          ? new Date(latestEntry.created_at) 
          : new Date(); // Utiliser la date actuelle si pas de date d'entrée
        
        participantState.lastPlayDate = entryDate;
        
        // Vérifier si le participant peut rejouer (après 7 jours)
        const canPlayAgain = checkIfCanPlayAgain(entryDate);
        participantState.playedRecently = !canPlayAgain.canPlay;
        participantState.daysUntilNextPlay = canPlayAgain.daysRemaining;
        
        console.log('État de participation:', {
          derniereDate: entryDate,
          peutRejouer: !participantState.playedRecently,
          joursRestants: participantState.daysUntilNextPlay
        });
      } else {
        participantState.hasPlayed = false;
        participantState.playedRecently = false;
        participantState.lastPlayDate = null;
        participantState.daysUntilNextPlay = 0;
        participantState.gameResult = null;
      }
      
      return participant;
    } catch (error: any) {
      console.error('Erreur lors de la vérification du participant:', error);
      participantState.isRegistered = false;
      participantState.participant = null;
      participantState.hasPlayed = false;
      participantState.playedRecently = false;
      participantState.lastPlayDate = null;
      participantState.daysUntilNextPlay = 0;
      participantState.gameResult = null;
      throw error;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Vérifie si un participant a déjà joué
   * @param participantId - ID du participant à vérifier
   */
  async function checkIfParticipantHasPlayed(participantId: number): Promise<boolean> {
    if (!participantId) {
      error.value = "ID de participant requis";
      return false;
    }

    isLoading.value = true;
    error.value = null;
    
    try {
      console.log('Vérification si le participant a déjà joué:', participantId);
      
      // Si en mode mock, simuler un délai
      if (!isReal) {
        await new Promise(resolve => setTimeout(resolve, 800));
        participantState.hasPlayed = false;
        participantState.playedRecently = false;
        participantState.lastPlayDate = null;
        participantState.daysUntilNextPlay = 0;
        participantState.gameResult = null;
        return false;
      }
      
      let entriesResult;
      try {
        const query = supabase
          .from('entry')
          .select('*, prize:prize_id(*)')
          .eq('participant_id', participantId);
          
        // Vérifier si la méthode execute existe (client mock)
        if ('execute' in query) {
          entriesResult = await query.execute();
        } else {
          entriesResult = await query;
        }
      } catch (err) {
        console.error('Erreur lors de la requête Supabase:', err);
        throw err;
      }
      
      if (entriesResult.error) {
        console.error('Erreur lors de la recherche des participations:', entriesResult.error);
        throw entriesResult.error;
      }
      
      const entries = entriesResult.data as Entry[] | null;
      if (entries && entries.length > 0) {
        // Le participant a déjà joué
        participantState.hasPlayed = true;
        participantState.gameResult = entries[0]; // Prendre la première participation
        
        // Vérifier si le participant a joué récemment (dans les 7 derniers jours)
        const latestEntry = entries[0];
        const entryDate = latestEntry.created_at 
          ? new Date(latestEntry.created_at) 
          : new Date(); // Utiliser la date actuelle si pas de date d'entrée
        
        participantState.lastPlayDate = entryDate;
        
        // Vérifier si le participant peut rejouer (après 7 jours)
        const canPlayAgain = checkIfCanPlayAgain(entryDate);
        participantState.playedRecently = !canPlayAgain.canPlay;
        participantState.daysUntilNextPlay = canPlayAgain.daysRemaining;
        
        return true;
      } else {
        participantState.hasPlayed = false;
        participantState.playedRecently = false;
        participantState.lastPlayDate = null;
        participantState.daysUntilNextPlay = 0;
        participantState.gameResult = null;
        return false;
      }
    } catch (error: any) {
      console.error('Erreur lors de la vérification des participations:', error);
      participantState.hasPlayed = false;
      participantState.playedRecently = false;
      participantState.lastPlayDate = null;
      participantState.daysUntilNextPlay = 0;
      participantState.gameResult = null;
      throw error;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Vérifie si le participant peut rejouer basé sur la date de sa dernière participation
   * @param lastPlayDate - Date de la dernière participation
   * @param daysToWait - Nombre de jours à attendre avant de pouvoir rejouer (par défaut 7 jours)
   * @returns Un objet indiquant si le participant peut jouer et le nombre de jours restants
   */
  function checkIfCanPlayAgain(lastPlayDate: Date, daysToWait = 7): { canPlay: boolean, daysRemaining: number } {
    const now = new Date();
    const timeDiff = now.getTime() - lastPlayDate.getTime();
    const daysPassed = Math.floor(timeDiff / (1000 * 3600 * 24));
    const daysRemaining = Math.max(0, daysToWait - daysPassed);
    
    return {
      canPlay: daysPassed >= daysToWait,
      daysRemaining
    };
  }

  return {
    isLoading,
    error,
    participantState,
    checkParticipantByPhone,
    checkIfParticipantHasPlayed,
    checkIfCanPlayAgain
  };
}
