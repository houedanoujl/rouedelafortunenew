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
import { useSupabase } from '~/composables/useSupabase';
import { useTranslation } from '~/composables/useTranslation';
import { useParticipantCheck } from '~/composables/useParticipantCheck';

const emit = defineEmits(['participant-registered']);
const { t } = useTranslation();

const firstName = ref('');
const lastName = ref('');
const phone = ref('');
const email = ref('');
const agreeTerms = ref(false);
const showTermsModal = ref(false);
const activeTab = ref('terms');

const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// Utiliser le composable pour la vérification des participants
const { checkParticipantByPhone, participantState } = useParticipantCheck();

// Vérifier si Supabase est disponible
let supabase;
let mockMode = false;
let supabaseConfig;

try {
  const supabaseInstance = useSupabase();
  supabase = supabaseInstance.supabase;
  mockMode = !supabaseInstance.isReal;
  supabaseConfig = supabaseInstance.config;
  console.log('Supabase instance in RegistrationForm:', {
    isReal: supabaseInstance.isReal,
    config: supabaseInstance.config
  });
} catch (err) {
  console.error('Error initializing Supabase in RegistrationForm:', err);
  mockMode = true;
}

// Validation des données du formulaire
const isFormValid = computed(() => {
  return firstName.value.trim() !== '' 
      && lastName.value.trim() !== '' 
      && phone.value.trim() !== ''
      && agreeTerms.value === true;
});

// Enregistrement du participant
async function registerParticipant() {
  if (!isFormValid.value) {
    errorMessage.value = t('registration.messages.required');
    return;
  }
  
  errorMessage.value = '';
  successMessage.value = '';
  isLoading.value = true;
  
  try {
    // Données du participant avec validation et nettoyage
    const participantData = {
      first_name: firstName.value.trim(),
      last_name: lastName.value.trim(),
      phone: formatPhoneNumber(phone.value.trim()), // Formatage du numéro de téléphone
      email: email.value.trim() || null,
      created_at: new Date().toISOString(), // Ajouter la date de création
      updated_at: new Date().toISOString()  // Ajouter la date de mise à jour
    };
    
    console.log('Participant data to register:', participantData);
    console.log('Using Supabase config:', supabaseConfig);
    
    // Si Supabase n'est pas disponible ou en mode mock, utiliser les données simulées
    if (mockMode || !supabase) {
      console.log('Using mock mode for registration');
      // Simuler un délai de traitement
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Créer un ID simulé basé sur l'horodatage
      const mockId = Date.now();
      
      // Afficher le message de succès
      successMessage.value = t('registration.messages.demoSuccess');
      
      // Émettre l'événement avec les données du participant
      emit('participant-registered', {
        id: mockId,
        ...participantData
      });
      
      // Stocker le numéro de téléphone dans le stockage local
      localStorage.setItem('participant_phone', participantData.phone);
      
      // Réinitialiser le formulaire
      resetForm();
      
      isLoading.value = false;
      return;
    }
    
    // Vérifier d'abord si le participant existe déjà avec ce numéro de téléphone
    console.log('Checking for existing participant with phone:', participantData.phone);
    
    // Utiliser notre composable pour vérifier si le participant existe
    try {
      const existingParticipant = await checkParticipantByPhone(participantData.phone);
      
      if (existingParticipant) {
        console.log('Found existing participant:', existingParticipant);
      } else {
        console.log('No existing participant found, creating new one');
      }
      
      let participant;
      
      if (existingParticipant) {
        // Mettre à jour les informations du participant existant
        console.log('Updating existing participant with ID:', existingParticipant.id);
        const { data: updatedParticipant, error: updateError } = await supabase
          .from('participant')
          .update({
            first_name: participantData.first_name,
            last_name: participantData.last_name,
            email: participantData.email,
            updated_at: participantData.updated_at
          })
          .eq('id', existingParticipant.id)
          .select();
        
        if (updateError) {
          console.error('Update error:', updateError);
          throw updateError;
        }
        
        participant = updatedParticipant?.[0] || existingParticipant;
      } else {
        // Enregistrer le nouveau participant dans Supabase
        console.log('Inserting new participant');
        const { data: newParticipant, error: insertError } = await supabase
          .from('participant')
          .insert([participantData])
          .select();
        
        if (insertError) {
          console.error('Insert error:', insertError);
          throw insertError;
        }
        
        // Vérifier que nous avons obtenu des données
        if (!newParticipant || newParticipant.length === 0) {
          console.error('No data received from database after insert');
          throw new Error('Aucune donnée reçue de la base de données');
        }
        
        participant = newParticipant[0];
      }
      
      // Stocker le numéro de téléphone dans le stockage local
      localStorage.setItem('participant_phone', participantData.phone);
      
      // Message de succès
      successMessage.value = t('registration.messages.success');
      console.log('Registration successful, participant:', participant);
      
      // Émettre l'événement avec les données du participant
      emit('participant-registered', participant);
      
      // Réinitialiser le formulaire
      resetForm();
    } catch (participantCheckError) {
      console.error('Error checking participant:', participantCheckError);
      throw participantCheckError;
    }
  } catch (error) {
    console.error('Registration error:', error);
    
    // Déterminer le type d'erreur pour afficher un message approprié
    if (error.code) {
      // Erreurs Supabase avec des codes
      switch (error.code) {
        case 'PGRST301':
        case 'PGRST302':
          errorMessage.value = t('errors.databaseAccess');
          break;
        case '23505': // Code PostgreSQL pour violation de contrainte unique
          errorMessage.value = t('errors.duplicateEntry');
          break;
        case '23514': // Code PostgreSQL pour violation de contrainte de validation
          errorMessage.value = t('errors.invalidData');
          break;
        case '42P01': // Table inexistante
          errorMessage.value = t('errors.supabase') + ': Table non trouvée';
          break;
        case '42501': // Erreur de permission
          errorMessage.value = t('errors.supabase') + ': Permissions insuffisantes';
          break;
        default:
          if (error.message && error.message.includes('network')) {
            errorMessage.value = t('registration.messages.networkError');
          } else {
            errorMessage.value = t('registration.messages.error') + (error.message ? ': ' + error.message : '');
          }
      }
    } else if (error.message) {
      // Erreurs avec un message mais sans code
      if (error.message.toLowerCase().includes('network') || error.message.toLowerCase().includes('connexion')) {
        errorMessage.value = t('registration.messages.networkError');
      } else if (error.message.toLowerCase().includes('validation') || error.message.toLowerCase().includes('invalid')) {
        errorMessage.value = t('registration.messages.validationError');
      } else if (error.message.toLowerCase().includes('database') || error.message.toLowerCase().includes('base de données')) {
        errorMessage.value = t('registration.messages.databaseError');
      } else {
        errorMessage.value = t('registration.messages.error') + ': ' + error.message;
      }
    } else {
      // Erreur inconnue
      errorMessage.value = t('registration.messages.unknownError');
    }
    
    // Ajouter des informations de débogage dans la console
    console.log('Supabase config utilisée lors de l\'erreur:', supabaseConfig);
  } finally {
    isLoading.value = false;
  }
}

// Fonction pour formater le numéro de téléphone
function formatPhoneNumber(phone) {
  // Supprimer tous les caractères non numériques
  let cleaned = phone.replace(/\D/g, '');
  
  // S'assurer que le numéro commence par le code pays (par défaut +225 pour la Côte d'Ivoire)
  if (!cleaned.startsWith('225') && cleaned.length <= 10) {
    cleaned = '225' + cleaned;
  }
  
  // Retourner le numéro formaté
  return cleaned;
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
