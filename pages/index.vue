<template>
  <div class="container">
    <header>
      <img src="/assets/images/dinor-logo.svg" alt="DINOR" class="logo" />
      <h1>Roue de la Fortune DINOR</h1>
      <p>Scannez le QR code sur un produit DINOR et tentez votre chance pour gagner !</p>
    </header>
    
    <main>
      <div v-if="!participantId">
        <RegistrationForm @registration-complete="handleRegistrationComplete" />
      </div>
      
      <div v-else>
        <FortuneWheel :participant-id="participantId" />
      </div>
    </main>
    
    <footer>
      <p>&copy; {{ new Date().getFullYear() }} DINOR. Tous droits réservés.</p>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const participantId = ref(null);

function handleRegistrationComplete(id) {
  participantId.value = id;
}
</script>

<style scoped>
header {
  text-align: center;
  margin-bottom: 40px;
  padding: 20px 0;
}

.logo {
  max-width: 150px;
  margin-bottom: 15px;
}

h1 {
  color: var(--primary-color);
  font-size: 2.5rem;
  margin-bottom: 15px;
}

main {
  min-height: 60vh;
}

footer {
  text-align: center;
  margin-top: 60px;
  padding: 20px 0;
  border-top: 1px solid #ddd;
  color: #666;
}

@media (max-width: 768px) {
  h1 {
    font-size: 2rem;
  }
}
</style>
