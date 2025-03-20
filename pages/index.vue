<template>
  <div>
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
        <div class="alert alert-waiting">
          <h3 class="waiting-title">{{ t('app.messages.waitTitle') }}</h3>
          <div class="waiting-content">
            <p>{{ t('app.messages.waitMessage', { days: participantState.daysUntilNextPlay }) }}</p>
            <p v-if="participantState.lastPlayDate">
              {{ t('app.messages.lastPlayed', { date: formatDate(participantState.lastPlayDate) }) }}
            </p>
            <p v-if="participantState.lastPlayDate">
              {{ t('app.messages.comeBackSoon', { nextDate: formatDate(getNextPlayDate(participantState.lastPlayDate)) }) }}
            </p>
          </div>
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
            
            <!-- Bouton pour afficher les détails du lot dans une modale -->
            <button 
              class="btn btn-info mt-2 mb-4"
              @click="showPrizeDetails = true"
            >
              <i class="icon-info-circle"></i> {{ t('app.buttons.viewPrizeDetails') }}
            </button>
            
            <div class="qr-info">
              <p>{{ t('app.gameResult.scanQrCode') }}</p>
              <QRCodeGenerator
                :participant-id="participantId"
                :prize-id="gameResult?.prize?.id"
                size="200"
                :tracking-id="qrTrackingId"
              />
            </div>
          </div>
          
          <div class="game-over-actions">
            <button 
              class="btn btn-primary" 
              @click="resetGame"
              :title="t('app.buttons.newParticipant')"
            >
              {{ t('app.buttons.newParticipant') }}
            </button>
            
            <button
              v-if="gameResult?.result === 'GAGNÉ'"
              class="btn btn-download"
              @click="downloadQRCodeAsPdf"
              :title="t('qrCode.downloadPdf')"
            >
              <i class="icon-download"></i> {{ t('qrCode.downloadPdf') }}
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Fenêtre modale pour afficher les détails du lot -->
    <div v-if="showPrizeDetails" class="modal-overlay" @click="showPrizeDetails = false">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h2>{{ t('modal.prizeDetails') }}</h2>
          <button class="modal-close" @click="showPrizeDetails = false">&times;</button>
        </div>
        <div class="modal-body">
          <div v-if="gameResult?.prize" class="prize-details">
            <h3>{{ gameResult.prize.name }}</h3>
            
            <div v-if="gameResult.prize.description" class="prize-description">
              <h4>{{ t('modal.description') }} :</h4>
              <p>{{ gameResult.prize.description }}</p>
            </div>
            
            <div class="prize-value" v-if="gameResult.prize.value">
              <h4>{{ t('modal.value') }} :</h4>
              <p>{{ gameResult.prize.value }} FCFA</p>
            </div>
            
            <div class="redemption-info">
              <h4>{{ t('modal.howToRedeem') }} :</h4>
              <p>{{ t('modal.redeemInstructions') }}</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" @click="showPrizeDetails = false">{{ t('modal.close') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useTranslation } from '~/composables/useTranslation';
import { useParticipantCheck } from '~/composables/useParticipantCheck';
import { useSupabase } from '~/composables/useSupabase';
// Suppression de l'import du composable usePdfGenerator qui cause des problèmes
// import { usePdfGenerator } from '~/composables/usePdfGenerator';
import QRCodeGenerator from '~/components/QRCodeGenerator.vue';

const { t } = useTranslation();
const { checkParticipantByPhone, checkIfParticipantHasPlayed, participantState, isLoading: checkingParticipant } = useParticipantCheck();
const { supabase } = useSupabase();
// Suppression de l'utilisation de usePdfGenerator
// const { generatePdfFromElement } = usePdfGenerator();

const showForm = ref(true);
const participantId = ref(null);
const participantName = ref('');
const gameComplete = ref(false);
const gameResult = ref(null);
const initialCheckComplete = ref(false);
const checkingStoredParticipant = ref(false);
const isCheckingParticipant = ref(true);
const qrCodeData = ref(null);
const qrTrackingId = ref('');
const showPrizeDetails = ref(false);

// Fonction pour télécharger le QR code en format PDF
async function downloadQRCodeAsPdf() {
  // Si les données du participant et du prix ne sont pas disponibles, les récupérer
  if (!qrCodeData.value) {
    try {
      const { data: participant } = await supabase
        .from('participant')
        .select('*')
        .eq('id', participantId.value)
        .single();
      
      const { data: prize } = await supabase
        .from('prize')
        .select('*')
        .eq('id', gameResult.value.prize?.id)
        .single();
      
      qrCodeData.value = {
        participant,
        prize
      };
    } catch (error) {
      console.error('Erreur lors de la récupération des données pour le PDF:', error);
    }
  }

  // Attendre que le DOM soit mis à jour et que le QR code soit rendu
  await nextTick();
  
  // Petit délai pour s'assurer que le QR code est bien rendu
  await new Promise(resolve => setTimeout(resolve, 500));

  // Créer une mise en page d'impression stylée
  const printContent = document.createElement('div');
  printContent.style.cssText = 'width:100%;height:100%;position:fixed;top:0;left:0;background:white;padding:20px;z-index:9999;font-family:Arial,sans-serif;';
  
  // Créer un conteneur pour le contenu
  const container = document.createElement('div');
  container.style.cssText = 'max-width:800px;margin:0 auto;padding:20px;border:1px solid #ddd;border-radius:10px;text-align:center;background-color:#fff;box-shadow:0 2px 10px rgba(0,0,0,0.1);';
  
  // Obtenir le QR code
  let qrContainer = document.querySelector('.qrcode-container');
  
  // Si le QR code n'est pas trouvé, attendre un peu plus longtemps et réessayer
  if (!qrContainer) {
    console.log('QR Code non trouvé, nouvelle tentative après délai...');
    await new Promise(resolve => setTimeout(resolve, 1000));
    qrContainer = document.querySelector('.qrcode-container');
  }
  
  if (!qrContainer) {
    console.error('QR Code non trouvé après plusieurs tentatives');
    alert('Impossible de générer le PDF. Veuillez réessayer.');
    return;
  }
  
  // Cloner le contenu du QR code
  const qrClone = qrContainer.cloneNode(true);
  qrClone.style.margin = '20px auto';
  qrClone.style.maxWidth = '200px';
  
  // Ajouter le titre
  const title = document.createElement('h1');
  title.textContent = t('qrCode.title');
  title.style.cssText = 'text-align:center;margin:20px 0 10px;color:#333;font-size:24px;';
  
  // Ajouter le sous-titre
  const subtitle = document.createElement('h2');
  subtitle.textContent = t('qrCode.pdfSubtitle');
  subtitle.style.cssText = 'text-align:center;margin:0 0 20px;color:#666;font-size:18px;font-weight:normal;';
  
  // Ajouter les informations du participant
  if (qrCodeData.value?.participant) {
    const participant = qrCodeData.value.participant;
    const participantBox = document.createElement('div');
    participantBox.style.cssText = 'background-color:#f9f9f9;border-radius:8px;padding:15px;margin:15px 0;text-align:left;';
    
    const participantTitle = document.createElement('h3');
    participantTitle.textContent = 'Informations du participant';
    participantTitle.style.cssText = 'margin-top:0;color:#4caf50;border-bottom:1px solid #eee;padding-bottom:5px;';
    
    const participantName = document.createElement('p');
    participantName.innerHTML = '<strong>Nom:</strong> ' + participant.first_name + ' ' + participant.last_name;
    
    const participantPhone = document.createElement('p');
    participantPhone.innerHTML = '<strong>Téléphone:</strong> ' + participant.phone;
    
    participantBox.appendChild(participantTitle);
    participantBox.appendChild(participantName);
    participantBox.appendChild(participantPhone);
    
    container.appendChild(participantBox);
  }
  
  // Ajouter les informations du prix
  if (qrCodeData.value?.prize) {
    const prize = qrCodeData.value.prize;
    const prizeBox = document.createElement('div');
    prizeBox.style.cssText = 'background-color:#f9f9f9;border-radius:8px;padding:15px;margin:15px 0;text-align:left;';
    
    const prizeTitle = document.createElement('h3');
    prizeTitle.textContent = 'Détails du lot';
    prizeTitle.style.cssText = 'margin-top:0;color:#4caf50;border-bottom:1px solid #eee;padding-bottom:5px;';
    
    const prizeName = document.createElement('p');
    prizeName.innerHTML = '<strong>Lot:</strong> ' + prize.name;
    
    prizeBox.appendChild(prizeTitle);
    prizeBox.appendChild(prizeName);
    
    if (prize.description) {
      const prizeDesc = document.createElement('p');
      prizeDesc.innerHTML = '<strong>Description:</strong> ' + prize.description;
      prizeBox.appendChild(prizeDesc);
    }
    
    container.appendChild(prizeBox);
  }
  
  // Ajouter le pied de page
  const footer = document.createElement('div');
  footer.style.cssText = 'margin-top:30px;font-size:12px;color:#999;font-style:italic;text-align:center;';
  
  const footerText1 = document.createElement('p');
  footerText1.textContent = 'Présentez ce QR code pour récupérer votre lot. Valable une fois uniquement.';
  
  const footerText2 = document.createElement('p');
  footerText2.textContent = 'Généré le ' + new Date().toLocaleDateString();
  
  footer.appendChild(footerText1);
  footer.appendChild(footerText2);
  
  // Assembler le contenu
  container.appendChild(title);
  container.appendChild(subtitle);
  container.appendChild(qrClone);
  container.appendChild(footer);
  
  // Ajouter un bouton "Annuler" (ne sera pas affiché lors de l'impression)
  const cancelButton = document.createElement('button');
  cancelButton.textContent = 'Annuler';
  cancelButton.style.cssText = 'position:fixed;top:10px;right:10px;padding:8px 16px;background:#e53935;color:white;border:none;border-radius:4px;cursor:pointer;font-size:14px;z-index:10000;';
  cancelButton.setAttribute('class', 'no-print');
  cancelButton.onclick = function() {
    document.body.removeChild(printContent);
  };
  
  // Ajouter un bouton "Imprimer" (ne sera pas affiché lors de l'impression)
  const printButton = document.createElement('button');
  printButton.textContent = 'Imprimer / Enregistrer PDF';
  printButton.style.cssText = 'padding:10px 20px;background:#4285f4;color:white;border:none;border-radius:4px;cursor:pointer;font-size:16px;margin:20px auto;display:block;';
  printButton.setAttribute('class', 'no-print');
  printButton.onclick = function() {
    window.print();
  };
  
  // Ajouter un style pour masquer les éléments non imprimables
  const printStyle = document.createElement('style');
  printStyle.textContent = '@media print { .no-print { display: none !important; } }';
  
  // Ajouter tous les éléments à la page
  printContent.appendChild(container);
  printContent.appendChild(cancelButton);
  printContent.appendChild(printButton);
  printContent.appendChild(printStyle);
  document.body.appendChild(printContent);
  
  // Auto-imprimer après un délai court (si on le souhaite)
  /*
  setTimeout(() => {
    window.print();
  }, 500);
  */
}

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
  
  // Générer un ID de suivi pour le QR code
  qrTrackingId.value = `qr-${data.id}-${Date.now()}`;
  
  // Sauvegarder les informations du participant
  localStorage.setItem('participantId', data.id.toString());
  localStorage.setItem('participantName', `${data.first_name} ${data.last_name}`);
  localStorage.setItem('participantPhone', data.phone);
  
  // Vérifier si le participant a déjà joué
  try {
    const hasPlayed = await checkIfParticipantHasPlayed(data.id);
    if (hasPlayed) {
      // Si le participant a déjà joué, afficher le résultat
      console.log('Le participant a déjà joué:', participantState.gameResult);
      showForm.value = false;
      gameComplete.value = true;
      gameResult.value = participantState.gameResult;
    }
  } catch (error) {
    console.error('Erreur lors de la vérification si le participant a déjà joué:', error);
  }
}

// Lorsque le jeu est terminé, afficher le résultat et permettre de redémarrer
async function onGameCompleted(data) {
  gameComplete.value = true;
  gameResult.value = data;
  
  if (data.result === 'GAGNÉ') {
    // Attendre que les données soient mises à jour avant d'essayer d'accéder au QR code
    await nextTick();
    
    // Récupérer les données du participant et du prix pour le PDF
    try {
      const { data: participant } = await supabase
        .from('participant')
        .select('*')
        .eq('id', participantId.value)
        .single();
      
      const { data: prize } = await supabase
        .from('prize')
        .select('*')
        .eq('id', data.prize?.id)
        .single();
      
      qrCodeData.value = {
        participant,
        prize
      };
    } catch (error) {
      console.error('Erreur lors de la récupération des données:', error);
    }
  }
  
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
        // Si le participant n'existe pas, réinitialiser
        resetGame();
      }
    }
  } catch (error) {
    console.error('Erreur lors de la vérification du participant stocké:', error);
  } finally {
    checkingStoredParticipant.value = false;
    initialCheckComplete.value = true;
    isCheckingParticipant.value = false;
  }
}

// Formater la date pour l'affichage
function formatDate(date) {
  if (!date) return '';
  
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  
  if (typeof date === 'string') {
    return new Date(date).toLocaleDateString(undefined, options);
  }
  
  return date.toLocaleDateString(undefined, options);
}

// Calculer la date à laquelle l'utilisateur pourra rejouer
function getNextPlayDate(lastPlayDate) {
  const lastPlayDateObject = new Date(lastPlayDate);
  const nextPlayDate = new Date(lastPlayDateObject.getTime() + participantState.daysUntilNextPlay * 24 * 60 * 60 * 1000);
  return nextPlayDate;
}

// Exécuter la vérification au chargement de la page
onMounted(() => {
  checkStoredParticipant();
});
</script>

<style>
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
}

.spinner-container {
  width: 50px;
  height: 50px;
  margin-bottom: 20px;
}

.spinner {
  animation: rotate 2s linear infinite;
  z-index: 2;
  width: 50px;
  height: 50px;
}

.path {
  stroke: #1d3557;
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

.participant-info {
  margin-bottom: 2rem;
}

.participant-badge {
  display: inline-flex;
  background-color: #f1faee;
  border-radius: 50px;
  padding: 0.5rem 1rem;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.participant-name {
  font-weight: 700;
  margin-right: 0.5rem;
  color: #1d3557;
}

.participant-status {
  color: #457b9d;
  font-size: 0.9rem;
}

.waiting-message {
  max-width: 800px;
  margin: 2rem auto;
}

.alert {
  background-color: #f0fff4;
  border-radius: 10px;
  padding: 2rem;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.alert-waiting {
  background-color: #fff0f0;
  border: 2px solid #f87171;
}

.waiting-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.waiting-content {
  margin-top: 1rem;
}

.wheel-container {
  margin: 2rem auto;
  max-width: 600px;
}

.form-container {
  max-width: 800px;
  margin: 0 auto;
}

.game-results {
  display: flex;
  justify-content: center;
  margin-top: 2rem;
}

.result-card {
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  text-align: center;
  max-width: 600px;
  width: 100%;
}

.result-card.win {
  background-color: #f0fff4;
  border: 2px solid #34d399;
}

.result-card.lose {
  background-color: #fff0f0;
  border: 2px solid #f87171;
}

.result-text {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 1rem 0;
}

.prize-name {
  font-size: 2rem;
  font-weight: 800;
  color: #1d3557;
  margin: 1rem 0;
}

.win-details {
  margin: 2rem 0;
}

.game-over-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 2rem;
  align-items: center;
}

@media (min-width: 768px) {
  .game-over-actions {
    flex-direction: row;
    justify-content: center;
  }
}

.btn-download {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background-color: #4285f4;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-download:hover {
  background-color: #3367d6;
}

.icon-download {
  display: inline-block;
  width: 1rem;
  height: 1rem;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='white'%3E%3Cpath d='M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z'/%3E%3C/svg%3E");
  background-size: cover;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-container {
  background-color: #fff;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  width: 500px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.modal-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.modal-close {
  font-size: 1.5rem;
  font-weight: 700;
  cursor: pointer;
}

.modal-body {
  margin-bottom: 20px;
}

.prize-details {
  margin-bottom: 20px;
}

.prize-description {
  margin-bottom: 20px;
}

.prize-value {
  margin-bottom: 20px;
}

.redemption-info {
  margin-bottom: 20px;
}

.modal-footer {
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-footer button {
  padding: 10px 20px;
  background-color: #4285f4;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.modal-footer button:hover {
  background-color: #3367d6;
}
</style>
