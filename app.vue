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
      <div v-if="participantId && participantName" class="participant-info">
        <div class="participant-badge">
          <span class="participant-name">{{ participantName }}</span>
          <span class="participant-status">{{ t('app.messages.participantInfo') }}</span>
        </div>
      </div>
      
      <div v-if="showForm" class="form-container">
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

const { t } = useTranslation();

const showForm = ref(true);
const participantId = ref(null);
const participantName = ref('');
const gameComplete = ref(false);
const gameResult = ref(null);

// Montrer le formulaire d'inscription au début
function resetGame() {
  showForm.value = true;
  participantId.value = null;
  participantName.value = '';
  gameComplete.value = false;
  gameResult.value = null;
}

// Lorsqu'un participant s'inscrit, passer à l'étape de la roue
function onParticipantRegistered(data) {
  participantId.value = data.id;
  participantName.value = `${data.first_name} ${data.last_name}`;
  showForm.value = false;
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
onMounted(() => {
  const savedParticipantId = localStorage.getItem('participantId');
  const savedParticipantName = localStorage.getItem('participantName');
  
  if (savedParticipantId && savedParticipantName) {
    // Si on a déjà un participant enregistré, passer directement à la roue
    participantId.value = parseInt(savedParticipantId);
    participantName.value = savedParticipantName;
    showForm.value = false;
  }
});

// Sauvegarder l'ID du participant dans le localStorage lors de l'inscription
watch(participantId, (newId) => {
  if (newId) {
    localStorage.setItem('participantId', newId.toString());
    localStorage.setItem('participantName', participantName.value);
  }
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
</style>
