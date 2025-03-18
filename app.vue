<template>
  <div class="container">
    <header class="header">
      <h1>Roue de la Fortune DINOR</h1>
      <p>Inscrivez-vous et tentez votre chance pour gagner des lots !</p>
    </header>
    
    <main>
      <div v-if="!participantId" class="registration-section">
        <RegistrationForm @registered="handleRegistration" />
      </div>
      
      <div v-else class="wheel-section">
        <FortuneWheel :participant-id="participantId" :contest-id="1" />
        
        <div class="restart-container">
          <button class="btn restart-btn" @click="restart">
            Recommencer
          </button>
        </div>
      </div>
    </main>
    
    <footer class="footer">
      <p>&copy; 2025 DINOR - Tous droits réservés</p>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';

// Gérer l'inscription du participant
const participantId = ref(null);

function handleRegistration(id) {
  console.log('Participant registered with ID:', id);
  participantId.value = id;
}

function restart() {
  participantId.value = null;
}
</script>

<style>
/* Global styles */
:root {
  --primary-color: #e63946;
  --secondary-color: #1d3557;
  --accent-color: #f1faee;
  --success-color: #2a9d8f;
  --error-color: #e76f51;
  --background-color: #f8f9fa;
  --text-color: #212529;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Roboto', 'Segoe UI', sans-serif;
  line-height: 1.6;
  color: var(--text-color);
  background-color: var(--background-color);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.header {
  text-align: center;
  margin-bottom: 40px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e1e4e8;
}

.header h1 {
  color: var(--primary-color);
  font-size: 2.5rem;
  margin-bottom: 10px;
}

main {
  min-height: 70vh;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.registration-section,
.wheel-section {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
}

.footer {
  margin-top: 40px;
  text-align: center;
  padding-top: 20px;
  border-top: 1px solid #e1e4e8;
  color: #6c757d;
  font-size: 0.9rem;
}

.btn {
  display: inline-block;
  padding: 12px 24px;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn:hover {
  opacity: 0.9;
  transform: translateY(-2px);
}

.btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  transform: none;
}

.restart-container {
  display: flex;
  justify-content: center;
  margin-top: 30px;
}

.restart-btn {
  background-color: var(--secondary-color);
}

@media (max-width: 768px) {
  .header h1 {
    font-size: 2rem;
  }
}
</style>
