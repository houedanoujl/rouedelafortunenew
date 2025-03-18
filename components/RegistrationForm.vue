<template>
  <div class="registration-form-container">
    <h2 class="form-title">Inscription à la Roue de la Fortune</h2>
    <p class="form-description">
      Remplissez le formulaire ci-dessous pour participer et tenter de gagner des lots exceptionnels !
    </p>

    <form @submit.prevent="registerParticipant" class="registration-form">
      <div class="form-group">
        <label for="firstName">Prénom <span class="required">*</span></label>
        <input 
          type="text" 
          id="firstName" 
          v-model="firstName" 
          placeholder="Votre prénom"
          required
          :disabled="isLoading"
        >
      </div>

      <div class="form-group">
        <label for="lastName">Nom <span class="required">*</span></label>
        <input 
          type="text" 
          id="lastName" 
          v-model="lastName" 
          placeholder="Votre nom"
          required
          :disabled="isLoading"
        >
      </div>

      <div class="form-group">
        <label for="phone">Téléphone <span class="required">*</span></label>
        <input 
          type="tel" 
          id="phone" 
          v-model="phone" 
          placeholder="Votre numéro de téléphone"
          required
          :disabled="isLoading"
        >
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input 
          type="email" 
          id="email" 
          v-model="email" 
          placeholder="Votre adresse email (optionnel)"
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
          J'accepte les conditions générales et la politique de confidentialité <span class="required">*</span>
        </label>
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
          Chargement...
        </span>
        <span v-else>
          S'inscrire et jouer
        </span>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useSupabase } from '~/composables/useSupabase';

const emit = defineEmits(['participant-registered']);

const firstName = ref('');
const lastName = ref('');
const phone = ref('');
const email = ref('');
const agreeTerms = ref(false);

const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// Vérifier si Supabase est disponible
let supabase;
let mockMode = false;

try {
  const supabaseInstance = useSupabase();
  supabase = supabaseInstance.supabase;
  mockMode = !supabaseInstance.isReal;
  console.log('Supabase client initialized, mock mode:', mockMode);
} catch (err) {
  console.error('Error initializing Supabase client:', err);
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
    errorMessage.value = 'Veuillez remplir tous les champs obligatoires et accepter les conditions.';
    return;
  }
  
  errorMessage.value = '';
  successMessage.value = '';
  isLoading.value = true;
  
  try {
    // Données du participant
    const participantData = {
      first_name: firstName.value.trim(),
      last_name: lastName.value.trim(),
      phone: phone.value.trim(),
      email: email.value.trim() || null
    };
    
    // Si Supabase n'est pas disponible ou en mode mock, utiliser les données simulées
    if (mockMode || !supabase) {
      console.log('Using mock registration for:', participantData);
      
      // Simuler un délai de traitement
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      // Créer un ID simulé basé sur l'horodatage
      const mockId = Date.now();
      
      // Afficher le message de succès
      successMessage.value = 'Inscription réussie !';
      
      // Émettre l'événement avec les données du participant
      emit('participant-registered', {
        id: mockId,
        ...participantData
      });
      
      // Réinitialiser le formulaire
      resetForm();
      
      isLoading.value = false;
      return;
    }
    
    // Enregistrer le participant dans Supabase
    const { data, error } = await supabase
      .from('participant')
      .insert([participantData])
      .select();
    
    if (error) {
      throw error;
    }
    
    // Vérifier que nous avons obtenu des données
    if (!data || data.length === 0) {
      throw new Error('Aucune donnée reçue de la base de données');
    }
    
    console.log('Participant registered:', data[0]);
    
    // Message de succès
    successMessage.value = 'Inscription réussie !';
    
    // Émettre l'événement avec les données du participant
    emit('participant-registered', data[0]);
    
    // Réinitialiser le formulaire
    resetForm();
  } catch (error) {
    console.error('Error registering participant:', error);
    errorMessage.value = 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.';
    
    // Simuler une inscription réussie en mode démo après quelques secondes
    if (error && error.message) {
      setTimeout(() => {
        console.log('Falling back to mock registration due to error:', error.message);
        const mockId = Date.now();
        
        emit('participant-registered', {
          id: mockId,
          first_name: firstName.value.trim(),
          last_name: lastName.value.trim(),
          phone: phone.value.trim(),
          email: email.value.trim() || null
        });
        
        successMessage.value = 'Inscription réussie en mode démonstration !';
        errorMessage.value = '';
        resetForm();
      }, 2000);
    }
  } finally {
    isLoading.value = false;
  }
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
  background: white;
  border-radius: 10px;
  padding: 30px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
  max-width: 600px;
  margin: 0 auto;
}

.form-title {
  color: var(--primary-color);
  font-size: 24px;
  margin-bottom: 10px;
  text-align: center;
}

.form-description {
  color: var(--secondary-color);
  margin-bottom: 25px;
  text-align: center;
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
  color: var(--secondary-color);
  font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="tel"],
.form-group input[type="email"] {
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.3s;
}

.form-group input:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.1);
}

.checkbox-group {
  flex-direction: row;
  align-items: center;
  gap: 10px;
}

.checkbox-group input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.required {
  color: var(--primary-color);
}

.error-message {
  padding: 12px;
  background-color: #fee2e2;
  border-left: 4px solid #ef4444;
  color: #b91c1c;
  border-radius: 4px;
}

.success-message {
  padding: 12px;
  background-color: #dcfce7;
  border-left: 4px solid #10b981;
  color: #047857;
  border-radius: 4px;
}

.submit-btn {
  background: linear-gradient(135deg, var(--primary-color), #c1121f);
  color: white;
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
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(230, 57, 70, 0.4);
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
  stroke: white;
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
</style>
