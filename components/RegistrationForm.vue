<template>
  <div class="registration-form">
    <h2>Enregistrez-vous pour jouer</h2>
    <form @submit.prevent="submitForm">
      <div class="form-group">
        <label for="firstName">Prénom</label>
        <input 
          id="firstName"
          v-model="form.firstName"
          type="text"
          class="form-input"
          required
        />
      </div>
      
      <div class="form-group">
        <label for="lastName">Nom</label>
        <input 
          id="lastName"
          v-model="form.lastName"
          type="text"
          class="form-input"
          required
        />
      </div>
      
      <div class="form-group">
        <label for="phone">Téléphone</label>
        <input 
          id="phone"
          v-model="form.phone"
          type="tel"
          class="form-input"
          placeholder="+225 XX XX XX XX XX"
          required
        />
      </div>
      
      <div class="form-group">
        <label for="email">Email</label>
        <input 
          id="email"
          v-model="form.email"
          type="email"
          class="form-input"
        />
      </div>
      
      <div class="form-group">
        <label for="agreement">
          <input 
            id="agreement"
            v-model="form.agreement"
            type="checkbox"
          />
          J'accepte les conditions générales
        </label>
      </div>
      
      <button 
        type="submit" 
        class="btn" 
        :disabled="isSubmitting"
      >
        {{ isSubmitting ? 'Traitement...' : 'Jouer maintenant' }}
      </button>
      
      <div v-if="error" class="error">{{ error }}</div>
      <div v-if="success" class="success">Enregistrement réussi !</div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useSupabase } from '~/composables/useSupabase';

const emit = defineEmits(['registered']);

const form = reactive({
  firstName: '',
  lastName: '',
  phone: '',
  email: '',
  agreement: false
});

const isSubmitting = ref(false);
const error = ref('');
const success = ref(false);

let supabase;

try {
  const supabaseInstance = useSupabase();
  supabase = supabaseInstance.supabase;
} catch (err) {
  console.error('Error initializing Supabase:', err);
  // We'll handle the missing Supabase case in the functions below
}

async function submitForm() {
  // Reset state
  error.value = '';
  success.value = false;
  
  // Validate form
  if (!form.firstName || !form.lastName || !form.phone) {
    error.value = 'Veuillez remplir tous les champs obligatoires.';
    return;
  }
  
  if (!form.agreement) {
    error.value = 'Vous devez accepter les conditions générales.';
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    if (!supabase) {
      console.warn('Supabase not available, using mock registration');
      await mockRegistration();
      return;
    }
    
    // Register the participant in Supabase
    const { data, error: apiError } = await supabase
      .from('participant')
      .insert({
        first_name: form.firstName,
        last_name: form.lastName,
        phone: form.phone,
        email: form.email || null
      })
      .select();
      
    if (apiError) throw apiError;
    
    // Success! Show success message and reset form
    success.value = true;
    console.log('Registration successful:', data);
    emit('registered', data[0].id);
    
    // Reset form
    resetForm();
  } catch (err) {
    console.error('Error submitting form:', err);
    error.value = 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.';
    isSubmitting.value = false;
  }
}

async function mockRegistration() {
  // Simulate network delay
  await new Promise(resolve => setTimeout(resolve, 1000));
  
  // Simulate successful registration
  const mockId = Math.floor(Math.random() * 1000) + 1;
  success.value = true;
  console.log('Mock registration successful with ID:', mockId);
  
  // Emit the registered event with mock ID
  emit('registered', mockId);
  
  // Reset form
  resetForm();
}

function resetForm() {
  form.firstName = '';
  form.lastName = '';
  form.phone = '';
  form.email = '';
  form.agreement = false;
  isSubmitting.value = false;
}
</script>

<style scoped>
.registration-form {
  max-width: 500px;
  margin: 0 auto;
  padding: 30px;
  background-color: white;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

h2 {
  text-align: center;
  margin-bottom: 25px;
  color: var(--primary-color);
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.btn {
  width: 100%;
  margin-top: 10px;
}

.error {
  color: red;
  margin-top: 10px;
}

.success {
  color: green;
  margin-top: 10px;
}
</style>
