<template>
  <div class="app-container">
    <header class="header">
      <div class="logo">
        <img src="/assets/images/dinor-logo.svg" alt="DINOR Logo" class="logo-image" />
        <h1>{{ t('app.title') }}</h1>
      </div>
      <div class="header-info">
        <p class="tagline">{{ t('app.subtitle') }}</p>
      </div>
    </header>
    
    <main class="main-content">
      <!-- Afficher un indicateur de chargement pendant la vérification initiale -->
      <div v-if="isCheckingParticipant" class="flex flex-col items-center justify-center h-64">
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-primary mb-4"></div>
        <p class="text-lg text-gray-700">{{ t('app.messages.loading') }}</p>
      </div>
      
      <div v-else-if="!initialCheckComplete || checkingParticipant" class="loading-container">
        <div class="spinner-container">
          <svg class="spinner" viewBox="0 0 50 50">
            <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
          </svg>
        </div>
        <p>{{ t('app.messages.loading') }}</p>
      </div>
      
      <div v-else>
        <div v-if="participantId && participantName" class="participant-info">
          <div class="participant-badge">
            <span class="participant-name">{{ participantName }}</span>
            <span class="participant-status">{{ t('app.messages.participantInfo') }}</span>
          </div>
        </div>
        
        <!-- Message d'attente si le participant a joué récemment -->
        <div v-if="participantState.playedRecently" class="waiting-message">
          <div class="alert alert-warning">
            <h3>{{ t('app.messages.waitTitle') }}</h3>
            <p>{{ t('app.messages.waitMessage', { days: participantState.daysUntilNextPlay }) }}</p>
            <p v-if="participantState.lastPlayDate">
              {{ t('app.messages.lastPlayed', { date: formatDate(participantState.lastPlayDate) }) }}
            </p>
            <button class="btn btn-secondary mt-4" @click="resetGame">
              {{ t('app.buttons.tryAnotherParticipant') }}
            </button>
          </div>
        </div>
        
        <div v-else-if="showForm" class="form-container">
          <RegistrationForm @participant-registered="onParticipantRegistered" />
        </div>
        
        <div v-else-if="!gameComplete" class="wheel-container">
          <FortuneWheel 
            :participantId="participantId" 
            @game-completed="onGameCompleted" 
          />
        </div>
        
        <div v-else class="game-results">
          <div class="result-card" :class="gameResult.result === 'GAGNÉ' ? 'win' : 'lose'">
            <h2>{{ t('fortuneWheel.messages.congratulations') }}</h2>
            <p class="result-text">{{ t('app.messages.gameCompleted') }} {{ gameResult.result }}!</p>
            
            <div v-if="gameResult.result === 'GAGNÉ'" class="win-details">
              <p>{{ t('app.messages.prizeWon') }}</p>
              <p v-if="gameResult.prize" class="prize-name">{{ gameResult.prize.name }}</p>
            </div>
            
            <div class="game-over-actions">
              <button 
                class="btn btn-primary" 
                @click="resetGame"
                :title="t('app.buttons.newParticipant')"
              >
                {{ t('app.buttons.newParticipant') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
    
    <footer class="footer">
      <p>{{ t('app.footer.copyright') }}</p>
      <p class="small">{{ t('app.footer.disclaimer') }}</p>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useTranslation } from '~/composables/useTranslation';
import { useParticipantCheck } from '~/composables/useParticipantCheck';

const { t } = useTranslation();
const { checkParticipantByPhone, checkIfParticipantHasPlayed, participantState, isLoading: checkingParticipant } = useParticipantCheck();

const showForm = ref(true);
const participantId = ref(null);
const participantName = ref('');
const gameComplete = ref(false);
const gameResult = ref(null);
const initialCheckComplete = ref(false);
const checkingStoredParticipant = ref(false);
const isCheckingParticipant = ref(true);

// Montrer le formulaire d'inscription au début
function resetGame() {
  showForm.value = true;
  participantId.value = null;
  participantName.value = '';
  gameComplete.value = false;
  gameResult.value = null;
  
  // Effacer les données du localStorage
  localStorage.removeItem('participantId');
  localStorage.removeItem('participantName');
  localStorage.removeItem('participantPhone');
}

// Lorsqu'un participant s'inscrit, passer à l'étape de la roue
async function onParticipantRegistered(data) {
  participantId.value = data.id;
  participantName.value = `${data.first_name} ${data.last_name}`;
  showForm.value = false;
  
  // Sauvegarder les informations du participant
  localStorage.setItem('participantId', data.id.toString());
  localStorage.setItem('participantName', `${data.first_name} ${data.last_name}`);
  localStorage.setItem('participantPhone', data.phone);
  
  // Vérifier si le participant a déjà joué
  try {
    const hasPlayed = await checkIfParticipantHasPlayed(data.id);
    if (hasPlayed) {
      // Si le participant a déjà joué, afficher le résultat
      gameComplete.value = true;
      gameResult.value = participantState.gameResult;
    }
  } catch (error) {
    console.error('Erreur lors de la vérification si le participant a déjà joué:', error);
  }
}

// Lorsque le jeu est terminé, afficher le résultat et permettre de redémarrer
function onGameCompleted(data) {
  gameComplete.value = true;
  gameResult.value = data;
  
  // Si une erreur est survenue (comme participant non trouvé), réinitialiser le jeu
  if (data.result === 'ERROR') {
    setTimeout(() => {
      resetGame();
    }, 3000); // Réinitialiser après 3 secondes pour laisser le temps de lire le message
  }
}

// Vérifier si l'utilisateur a déjà un ID de participant dans le localStorage
// et vérifier son statut dans la base de données
async function checkStoredParticipant() {
  checkingStoredParticipant.value = true;
  
  try {
    const savedParticipantId = localStorage.getItem('participantId');
    const savedParticipantName = localStorage.getItem('participantName');
    const savedParticipantPhone = localStorage.getItem('participantPhone');
    
    console.log('Données du participant stockées:', { 
      id: savedParticipantId, 
      name: savedParticipantName,
      phone: savedParticipantPhone
    });
    
    if (savedParticipantId && savedParticipantName && savedParticipantPhone) {
      // Vérifier si le participant existe toujours dans la base de données
      const participant = await checkParticipantByPhone(savedParticipantPhone);
      
      if (participant) {
        console.log('Participant trouvé dans la base de données:', participant);
        
        // Mettre à jour les informations avec celles de la base de données
        participantId.value = participant.id;
        participantName.value = `${participant.first_name} ${participant.last_name}`;
        
        if (participantState.hasPlayed) {
          // Si le participant a déjà joué, afficher le résultat
          console.log('Le participant a déjà joué:', participantState.gameResult);
          showForm.value = false;
          gameComplete.value = true;
          gameResult.value = participantState.gameResult;
        } else {
          // Si le participant n'a pas encore joué, afficher la roue
          console.log('Le participant n\'a pas encore joué');
          showForm.value = false;
          gameComplete.value = false;
        }
      } else {
        console.log('Participant non trouvé dans la base de données, réinitialisation');
        // Si le participant n'existe pas dans la base de données, réinitialiser
        resetGame();
      }
    }
  } catch (error) {
    console.error('Erreur lors de la vérification du participant stocké:', error);
    // En cas d'erreur, afficher le formulaire d'inscription
    resetGame();
  } finally {
    checkingStoredParticipant.value = false;
    initialCheckComplete.value = true;
    isCheckingParticipant.value = false;
  }
}

// Formater la date pour l'affichage
function formatDate(date) {
  if (!date) return '';
  
  // Options pour le format de date
  const options = { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  
  // Utiliser l'API Intl pour formater la date en français
  return new Intl.DateTimeFormat('fr-FR', options).format(date);
}

// Exécuter la vérification au chargement de la page
onMounted(() => {
  checkStoredParticipant();
});
</script>

<style>
/* Styles globaux */
:root {
  --primary-color: #e63946;
  --secondary-color: #1d3557;
  --light-color: #f1faee;
  --accent-color: #a8dadc;
  --dark-color: #457b9d;
  --win-color: #059669;
  --lose-color: #DC2626;
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  line-height: 1.6;
  color: var(--secondary-color);
  background-color: var(--light-color);
  min-height: 100vh;
}

.btn {
  display: inline-block;
  padding: 12px 24px;
  border: none;
  border-radius: 30px;
  font-weight: 700;
  font-size: 16px;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
  text-decoration: none;
}

.btn-primary {
  background-color: var(--primary-color);
}

.btn-primary:hover {
  background-color: #d62636;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-primary:active {
  transform: translateY(1px);
}

.btn-secondary {
  background-color: var(--dark-color);
}

.btn-secondary:hover {
  background-color: #3d6d8a;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Structure */
.app-container {
  max-width: 1200px;
  min-height: 100vh;
  margin: 0 auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
}

.header {
  padding: 20px 0;
  border-bottom: 2px solid rgba(0, 0, 0, 0.1);
  margin-bottom: 40px;
  text-align: center;
}

.logo {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 10px;
}

.logo-image {
  height: 60px;
  margin-right: 20px;
}

.logo h1 {
  font-size: 28px;
  color: var(--primary-color);
  margin: 0;
}

.header-info {
  max-width: 600px;
  margin: 0 auto;
}

.tagline {
  font-size: 18px;
  color: var(--dark-color);
  font-style: italic;
}

.main-content {
  flex: 1;
  padding: 20px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.participant-info {
  width: 100%;
  margin-bottom: 30px;
  display: flex;
  justify-content: center;
}

.participant-badge {
  background-color: white;
  border-radius: 50px;
  padding: 10px 20px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.participant-name {
  font-weight: 700;
  font-size: 18px;
  color: var(--secondary-color);
}

.participant-status {
  font-size: 14px;
  color: var(--dark-color);
}

.form-container, .wheel-container {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
}

.game-results {
  width: 100%;
  max-width: 600px;
  margin: 30px auto;
}

.result-card {
  background-color: white;
  border-radius: 10px;
  padding: 30px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.result-card.win {
  border-left: 6px solid var(--win-color);
}

.result-card.lose {
  border-left: 6px solid var(--lose-color);
}

.result-text {
  font-size: 24px;
  font-weight: 700;
  margin: 20px 0;
}

.win .result-text {
  color: var(--win-color);
}

.lose .result-text {
  color: var(--lose-color);
}

.game-over-actions {
  margin-top: 30px;
}

.win-details {
  margin: 20px 0;
  padding: 15px;
  background-color: #f0fff4;
  border-radius: 8px;
}

.footer {
  margin-top: 40px;
  padding: 20px 0;
  border-top: 2px solid rgba(0, 0, 0, 0.1);
  text-align: center;
  font-size: 14px;
  color: var(--dark-color);
}

.footer .small {
  font-size: 12px;
  margin-top: 5px;
  opacity: 0.7;
}

/* Responsive */
@media (max-width: 768px) {
  .logo-image {
    height: 50px;
    margin-right: 15px;
  }
  
  .logo h1 {
    font-size: 24px;
  }
  
  .tagline {
    font-size: 16px;
  }
}

@media (max-width: 480px) {
  .logo {
    flex-direction: column;
  }
  
  .logo-image {
    margin-right: 0;
    margin-bottom: 15px;
  }
  
  .logo h1 {
    font-size: 22px;
  }
  
  .header-info {
    padding: 0 10px;
  }
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  text-align: center;
}

.spinner-container {
  margin-bottom: 20px;
}

.spinner {
  animation: rotate 2s linear infinite;
  width: 50px;
  height: 50px;
}

.spinner .path {
  stroke: var(--primary-color);
  stroke-linecap: round;
  animation: dash 1.5s ease-in-out infinite;
}

@keyframes rotate {
  100% {
    transform: rotate(360deg);
  }
}

@keyframes dash {
  0% {
    stroke-dasharray: 1, 150;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -35;
  }
  100% {
    stroke-dasharray: 90, 150;
    stroke-dashoffset: -124;
  }
}

.prize-name {
  font-size: 1.5rem;
  font-weight: bold;
  margin-top: 10px;
  color: var(--win-color);
}

.waiting-message {
  width: 100%;
  max-width: 600px;
  margin: 30px auto;
  text-align: center;
}

.alert {
  background-color: #f0fff4;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.alert-warning {
  border-left: 6px solid #ffc107;
}
</style>
