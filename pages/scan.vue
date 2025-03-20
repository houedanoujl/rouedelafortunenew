<template>
  <div class="scan-page">
    <div class="scan-container">
      <h1 class="page-title">{{ t('qrCode.scanTitle') }}</h1>
      
      <!-- Scanner d'URL pour les appareils non compatibles avec la caméra -->
      <div class="url-scanner">
        <div class="input-group">
          <input 
            type="text" 
            v-model="trackingUrl" 
            :placeholder="t('qrCode.enterUrl')" 
            class="form-control"
          />
          <button @click="handleUrlScan" class="btn-primary">
            {{ t('qrCode.scanBtn') }}
          </button>
        </div>
      </div>
      
      <!-- Résultat du scan -->
      <div v-if="scanResult" class="scan-result">
        <div class="result-card" :class="{ success: scanResult.success, error: !scanResult.success }">
          <h2>{{ scanResult.success ? t('qrCode.scanSuccess') : t('qrCode.scanError') }}</h2>
          
          <div v-if="scanResult.success" class="result-details">
            <div v-if="qrData" class="participant-info">
              <h3>{{ t('qrCode.participantInfo') }}</h3>
              <p v-if="qrData.participant">
                <strong>{{ t('registration.fields.name') }}:</strong> 
                {{ qrData.participant.firstName }} {{ qrData.participant.lastName }}
              </p>
              <p v-if="qrData.participant">
                <strong>{{ t('registration.fields.phone') }}:</strong> 
                {{ qrData.participant.phone }}
              </p>
              
              <div v-if="qrData.prize" class="prize-info">
                <h3>{{ t('qrCode.prizeInfo') }}</h3>
                <p>
                  <strong>{{ t('qrCode.prizeName') }}:</strong> 
                  {{ qrData.prize.name }}
                </p>
                <p v-if="qrData.prize.description">
                  <strong>{{ t('qrCode.prizeDescription') }}:</strong> 
                  {{ qrData.prize.description }}
                </p>
              </div>
            </div>
            
            <div class="scan-stats">
              <p class="scan-count">
                <strong>{{ t('qrCode.scanCount') }}:</strong> 
                {{ scanResult.scan_count }}
              </p>
              <p v-if="scanResult.last_scanned" class="last-scanned">
                <strong>{{ t('qrCode.lastScanned') }}:</strong> 
                {{ formatDate(new Date(scanResult.last_scanned)) }}
              </p>
            </div>
          </div>
          
          <div v-else class="error-message">
            <p>{{ scanResult.message }}</p>
          </div>
          
          <button @click="resetScan" class="btn-secondary">
            {{ t('qrCode.scanAgain') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useSupabase } from '~/composables/useSupabase';
import { useTranslation } from '~/composables/useTranslation';

const { t } = useTranslation();
const { supabase } = useSupabase();

const trackingUrl = ref('');
const scanResult = ref(null);
const qrData = ref(null);

// Fonction pour scanner un QR code à partir d'une URL
async function handleUrlScan() {
  if (!trackingUrl.value) return;
  
  try {
    // Extraire l'ID de suivi de l'URL
    const trackingId = extractTrackingId(trackingUrl.value);
    
    if (!trackingId) {
      scanResult.value = {
        success: false,
        message: t('qrCode.invalidUrl')
      };
      return;
    }
    
    // Appeler l'API pour incrémenter le compteur de scans
    const { data, error } = await supabase
      .rpc('scan_qr_code', { tracking_id: trackingId });
    
    if (error) {
      console.error('Erreur lors du scan:', error);
      scanResult.value = {
        success: false,
        message: t('qrCode.scanError'),
        error: error.message
      };
      return;
    }
    
    scanResult.value = data;
    
    // Récupérer les données du QR code si disponibles
    if (data.success) {
      await fetchQRCodeData(trackingId);
    }
  } catch (error) {
    console.error('Erreur lors du traitement du scan:', error);
    scanResult.value = {
      success: false,
      message: t('qrCode.scanError'),
      error: error.message
    };
  }
}

// Fonction pour extraire l'ID de suivi à partir de l'URL ou du texte scanné
function extractTrackingId(url) {
  try {
    // Si c'est une chaine JSON, essayer de l'analyser
    if (url.startsWith('{') && url.endsWith('}')) {
      const data = JSON.parse(url);
      return data.tracking;
    }
    
    // Sinon, considérer que c'est un ID de suivi direct
    return url.trim();
  } catch (error) {
    console.error('Erreur lors de l\'extraction de l\'ID de suivi:', error);
    return null;
  }
}

// Récupérer les données du QR code à partir de l'ID de suivi
async function fetchQRCodeData(trackingId) {
  try {
    // Récupérer le QR code avec les relations participant et prize
    const { data, error } = await supabase
      .from('qr_codes')
      .select(`
        tracking_id,
        participant:participant_id(id, first_name, last_name, phone),
        prize:prize_id(id, name, description)
      `)
      .eq('tracking_id', trackingId)
      .single();
    
    if (error) {
      console.error('Erreur lors de la récupération des données du QR code:', error);
      return;
    }
    
    // Convertir les données au format attendu par l'application
    if (data) {
      qrData.value = {
        participant: data.participant ? {
          id: data.participant.id,
          firstName: data.participant.first_name,
          lastName: data.participant.last_name,
          phone: data.participant.phone
        } : null,
        prize: data.prize || null,
        tracking: data.tracking_id
      };
    }
  } catch (error) {
    console.error('Erreur lors de la récupération des données:', error);
  }
}

// Réinitialiser le scan pour permettre un nouveau scan
function resetScan() {
  scanResult.value = null;
  qrData.value = null;
  trackingUrl.value = '';
}

// Formater une date pour l'affichage
function formatDate(date) {
  if (!date) return '';
  
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  
  return date.toLocaleDateString(undefined, options);
}
</script>

<style scoped>
.scan-page {
  padding: 2rem;
  max-width: 800px;
  margin: 0 auto;
}

.page-title {
  font-size: 2rem;
  margin-bottom: 2rem;
  text-align: center;
  color: #333;
}

.scan-container {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.url-scanner {
  width: 100%;
  max-width: 500px;
  margin-bottom: 2rem;
}

.input-group {
  display: flex;
  margin-bottom: 1rem;
}

.form-control {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px 0 0 4px;
  font-size: 1rem;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 0 4px 4px 0;
  cursor: pointer;
  font-size: 1rem;
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background-color: #607d8b;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 1.5rem;
  font-size: 1rem;
}

.scan-result {
  width: 100%;
  max-width: 600px;
}

.result-card {
  background-color: white;
  border-radius: 8px;
  padding: 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  margin-top: 1rem;
}

.result-card.success {
  border-left: 4px solid #4caf50;
}

.result-card.error {
  border-left: 4px solid #f44336;
}

.result-details {
  margin-top: 1.5rem;
}

.participant-info, .prize-info {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background-color: #f9f9f9;
  border-radius: 4px;
}

.scan-stats {
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #e8f5e9;
  border-radius: 4px;
}

.scan-count {
  font-size: 1.2rem;
  color: #4caf50;
}

.error-message {
  color: #f44336;
  margin-top: 1rem;
}
</style>
