<template>
  <div class="registration-form-container">
    <h2 class="form-title">{{ t('registration.title') }}</h2>
    <p class="form-description">
      {{ t('registration.description') }}
    </p>

    <form @submit.prevent="registerParticipant" class="registration-form">
      <div class="form-group">
        <label for="firstName">{{ t('registration.fields.firstName.label') }} <span class="required">*</span></label>
        <input 
          type="text" 
          id="firstName" 
          v-model="firstName" 
          :placeholder="t('registration.fields.firstName.placeholder')"
          required
          :disabled="isLoading"
        >
      </div>

      <div class="form-group">
        <label for="lastName">{{ t('registration.fields.lastName.label') }} <span class="required">*</span></label>
        <input 
          type="text" 
          id="lastName" 
          v-model="lastName" 
          :placeholder="t('registration.fields.lastName.placeholder')"
          required
          :disabled="isLoading"
        >
      </div>

      <div class="form-group">
        <label for="phone">{{ t('registration.fields.phone.label') }} <span class="required">*</span></label>
        <input 
          type="tel" 
          id="phone" 
          v-model="phone" 
          :placeholder="t('registration.fields.phone.placeholder')"
          required
          :disabled="isLoading"
        >
      </div>

      <div class="form-group">
        <label for="email">{{ t('registration.fields.email.label') }}</label>
        <input 
          type="email" 
          id="email" 
          v-model="email" 
          :placeholder="t('registration.fields.email.placeholder')"
          :disabled="isLoading"
        >
      </div>

      <div class="form-group checkbox-group">
        <input 
          type="checkbox" 
          id="agreeTerms" 
          v-model="agreeTerms"
          :disabled="isLoading"
        >
        <label for="agreeTerms">
          {{ t('registration.fields.terms.label') }} <span class="required">*</span>
          <button type="button" class="terms-link" @click="showTermsModal = true">
            {{ t('registration.fields.terms.viewLink') }}
          </button>
        </label>
      </div>
      
      <div class="terms-links-container">
        <button 
          class="btn btn-link terms-footer-link" 
          type="button" 
          data-bs-toggle="modal" 
          data-bs-target="#termsModal"
        >
          {{ t('footer.terms') }}
        </button>
        <button 
          class="btn btn-link terms-footer-link" 
          type="button" 
          data-bs-toggle="modal" 
          data-bs-target="#privacyModal"
        >
          {{ t('footer.privacy') }}
        </button>
      </div>

      <!-- Modal pour les conditions générales et politique de confidentialité -->
      <div v-if="showTermsModal" class="modal-overlay">
        <div class="modal-container">
          <div class="modal-header">
            <h3>{{ t('registration.termsModal.title') }}</h3>
            <button type="button" class="close-btn" @click="showTermsModal = false">&times;</button>
          </div>
          <div class="modal-content">
            <div class="modal-tabs">
              <button 
                :class="['tab-btn', { active: activeTab === 'terms' }]" 
                @click="activeTab = 'terms'"
              >
                {{ t('registration.termsModal.tabTerms') }}
              </button>
              <button 
                :class="['tab-btn', { active: activeTab === 'privacy' }]" 
                @click="activeTab = 'privacy'"
              >
                {{ t('registration.termsModal.tabPrivacy') }}
              </button>
            </div>
            <div class="tab-content">
              <div v-if="activeTab === 'terms'" class="terms-content">
                <h4>{{ t('registration.termsModal.termsTitle') }}</h4>
                <div v-html="t('registration.termsModal.termsContent')"></div>
              </div>
              <div v-if="activeTab === 'privacy'" class="privacy-content">
                <h4>{{ t('registration.termsModal.privacyTitle') }}</h4>
                <div v-html="t('registration.termsModal.privacyContent')"></div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn" @click="showTermsModal = false">
              {{ t('registration.termsModal.closeButton') }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>

      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <button type="submit" class="btn submit-btn" :disabled="isLoading || !isFormValid">
        <span v-if="isLoading">
          <svg class="spinner" viewBox="0 0 50 50">
            <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
          </svg>
          {{ t('registration.buttons.loading') }}
        </span>
        <span v-else>
          {{ t('registration.buttons.submit') }}
        </span>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRuntimeConfig } from '#app';

const { t } = useI18n();
const emit = defineEmits(['participant-registered']);

// Éléments du formulaire
const firstName = ref('');
const lastName = ref('');
const phone = ref('');
const email = ref('');
const agreeTerms = ref(false);

// États
const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const showTermsModal = ref(false);
const activeTab = ref('terms');

// Configuration
const config = useRuntimeConfig();
const mockMode = config.public.mockMode;

// Vérifier si le formulaire est valide
const isFormValid = computed(() => {
  return firstName.value.trim() !== ''
      && lastName.value.trim() !== ''
      && phone.value.trim() !== ''
      && agreeTerms.value === true;
});

// Vérifier si un participant existe déjà avec ce numéro de téléphone
async function checkParticipantByPhone(phoneNumber) {
  try {
    console.log(`Recherche du participant avec le téléphone: ${phoneNumber}`);
    
    // Si on est en mode mock, on simule
    if (mockMode) {
      await new Promise(resolve => setTimeout(resolve, 500));
      return null; // Simuler aucun participant existant
    }
    
    const formattedPhone = formatPhoneNumber(phoneNumber);
    
    // Utiliser l'API côté serveur pour vérifier le participant
    const response = await fetch('/api/participants', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        first_name: firstName.value,
        last_name: lastName.value,
        phone: formattedPhone,
        email: email.value
      })
    });
    
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
    }
    
    const data = await response.json();
    
    if (data.success && data.isExisting) {
      console.log('Participant existant trouvé:', data.participant);
      return data.participant;
    }
    
    if (data.success && !data.isExisting) {
      console.log('Nouveau participant créé:', data.participant);
      return data.participant;
    }
    
    return null;
  } catch (error) {
    console.error('Erreur lors de la vérification du participant:', error);
    throw error;
  }
}

// Fonction pour enregistrer un participant
async function registerParticipant() {
  if (!isFormValid.value) {
    errorMessage.value = t('registration.messages.required');
    return;
  }
  
  isLoading.value = true;
  errorMessage.value = '';
  successMessage.value = '';
  
  try {
    // Formater le numéro de téléphone
    const formattedPhone = formatPhoneNumber(phone.value);
    
    // En mode mock, simuler un succès
    if (mockMode) {
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      successMessage.value = t('registration.messages.demoSuccess');
      const mockParticipant = {
        id: Math.floor(Math.random() * 1000),
        first_name: firstName.value,
        last_name: lastName.value,
        phone: formattedPhone,
        email: email.value,
        created_at: new Date().toISOString()
      };
      
      emit('participant-registered', mockParticipant);
      resetForm();
      return;
    }
    
    // Utiliser l'API côté serveur pour créer ou récupérer le participant
    const response = await fetch('/api/participants', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        first_name: firstName.value,
        last_name: lastName.value,
        phone: formattedPhone,
        email: email.value
      })
    });
    
    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`);
    }
    
    const data = await response.json();
    
    if (!data.success) {
      throw new Error(data.error || t('registration.messages.error'));
    }
    
    // Déterminer le message à afficher
    if (data.isExisting) {
      successMessage.value = `${t('app.messages.alreadyRegistered')} ${data.participant.first_name} ${data.participant.last_name}`;
    } else {
      successMessage.value = t('registration.messages.success');
    }
    
    // Émettre l'événement avec les données du participant
    emit('participant-registered', data.participant);
    resetForm();
    
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement:', error);
    
    // Déterminer le type d'erreur
    if (error.message.includes('network') || error.message.includes('fetch')) {
      errorMessage.value = t('registration.messages.networkError');
    } else if (error.message.includes('MySQL') || error.message.includes('database')) {
      errorMessage.value = t('registration.messages.databaseError');
    } else if (error.message.includes('validation') || error.message.includes('required')) {
      errorMessage.value = t('registration.messages.validationError');
    } else {
      errorMessage.value = `${t('registration.messages.unknownError')} (${error.message})`;
    }
  } finally {
    isLoading.value = false;
  }
}

// Fonction pour formater le numéro de téléphone
function formatPhoneNumber(phone) {
  // Supprimer tous les caractères non numériques
  let formatted = phone.replace(/\D/g, '');
  
  // Ajouter l'indicatif pays si nécessaire (par exemple +33 pour France)
  if (formatted.length === 10 && formatted.startsWith('0')) {
    formatted = '33' + formatted.substring(1);
  }
  
  return formatted;
}

// Réinitialiser le formulaire
function resetForm() {
  firstName.value = '';
  lastName.value = '';
  phone.value = '';
  email.value = '';
  agreeTerms.value = false;
}
</script>

<style scoped>
.registration-form-container {
  background: #FCFEFFff; /* White */
  border-radius: 10px;
  padding: 30px;
  box-shadow: 0 5px 20px rgba(135, 102, 75, 0.1);
  max-width: 600px;
  margin: 0 auto;
}

.form-title {
  color: #87664Bff; /* Raw Umber */
  font-size: 24px;
  margin-bottom: 10px;
  text-align: center;
  font-family: 'EB Garamond', serif;
}

.form-description {
  color: #87664Bff; /* Raw Umber */
  margin-bottom: 25px;
  text-align: center;
  font-family: 'EB Garamond', serif;
}

.registration-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 8px;
  color: #87664Bff; /* Raw Umber */
  font-weight: 500;
  font-family: 'EB Garamond', serif;
}

.form-group input[type="text"],
.form-group input[type="tel"],
.form-group input[type="email"] {
  padding: 12px 15px;
  border: 1px solid #87664Bff; /* Raw Umber */
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.3s;
  font-family: 'EB Garamond', serif;
}

.form-group input:focus {
  border-color: #BD2B23ff; /* Fire Brick */
  outline: none;
  box-shadow: 0 0 0 3px rgba(189, 43, 35, 0.1);
}

.checkbox-group {
  display: flex;
  align-items: flex-start;
}

.checkbox-group label {
  margin-left: 10px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
}

.terms-link {
  background: none;
  border: none;
  color: #BD2B23ff; /* Fire Brick */
  text-decoration: underline;
  cursor: pointer;
  padding: 0 5px;
  font-size: 0.9em;
  margin-left: 5px;
}

.required {
  color: #BD2B23ff; /* Fire Brick */
}

.error-message {
  padding: 12px;
  background-color: rgba(189, 43, 35, 0.1);
  border-left: 4px solid #BD2B23ff; /* Fire Brick */
  color: #BD2B23ff; /* Fire Brick */
  border-radius: 4px;
}

.success-message {
  padding: 12px;
  background-color: rgba(135, 102, 75, 0.1);
  border-left: 4px solid #87664Bff; /* Raw Umber */
  color: #87664Bff; /* Raw Umber */
  border-radius: 4px;
}

.submit-btn {
  background: linear-gradient(135deg, #87664Bff, #755743); /* Raw Umber gradient */
  color: #FCFEFFff; /* White */
  padding: 14px 20px;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-family: 'EB Garamond', serif;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  background: linear-gradient(135deg, #755743, #87664Bff); /* Inverted Raw Umber gradient */
  box-shadow: 0 5px 15px rgba(135, 102, 75, 0.4);
}

.submit-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Spinner pour l'état de chargement */
.spinner {
  animation: rotate 1.5s linear infinite;
  margin-right: 10px;
  width: 20px;
  height: 20px;
}

@keyframes rotate {
  100% {
    transform: rotate(360deg);
  }
}

.spinner .path {
  stroke: #FCFEFFff; /* White */
  stroke-linecap: round;
  animation: dash 1.5s ease-in-out infinite;
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

/* Responsive styles */
@media (max-width: 768px) {
  .registration-form-container {
    padding: 20px;
  }
  
  .form-title {
    font-size: 22px;
  }
  
  .form-group input[type="text"],
  .form-group input[type="tel"],
  .form-group input[type="email"] {
    padding: 10px 12px;
  }
}

@media (max-width: 480px) {
  .registration-form-container {
    padding: 15px;
  }
  
  .form-title {
    font-size: 20px;
  }
  
  .checkbox-group {
    align-items: flex-start;
  }
  
  .checkbox-group input[type="checkbox"] {
    margin-top: 3px;
  }
}

/* Styles pour la modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-container {
  background-color: #FCFEFFff; /* White */
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 5px 15px rgba(135, 102, 75, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid #eee;
  background-color: #87664Bff; /* Raw Umber */
  color: #FCFEFFff; /* White */
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #FCFEFFff; /* White */
}

.modal-content {
  flex-grow: 1;
  overflow-y: auto;
  padding: 0;
}

.modal-tabs {
  display: flex;
  border-bottom: 1px solid #eee;
}

.tab-btn {
  flex: 1;
  padding: 10px;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s;
}

.tab-btn.active {
  border-bottom: 3px solid #BD2B23ff; /* Fire Brick */
  font-weight: bold;
  color: #87664Bff; /* Raw Umber */
}

.tab-content {
  padding: 20px;
  max-height: 400px;
  overflow-y: auto;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #eee;
  display: flex;
  justify-content: flex-end;
}

.terms-footer-link {
  color: white;
  text-decoration: none;
  padding: 1em;
  font-size: 0.9em;
  margin: 0 5px;
  display: inline-block;
}

.terms-footer-link:hover {
  color: #BD2B23ff; /* Fire Brick */
  text-decoration: underline;
}

.terms-links-container {
  display: flex;
  justify-content: center;
  margin-bottom: 15px;
}
</style>
