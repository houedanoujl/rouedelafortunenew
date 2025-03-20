<template>
  <div class="qrcode-container">
    <h3 v-if="title" class="qrcode-title">{{ title }}</h3>
    <div class="qrcode-image" ref="qrContainer"></div>
    <p v-if="scanCount !== null" class="scan-count">
      Scans: {{ scanCount }}
    </p>
    <button v-if="showDownloadButton" class="download-button" @click="downloadQRCode">
      {{ downloadButtonText }}
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import QRCode from 'qrcode';
import { useSupabase } from '~/composables/useSupabase';

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  title: {
    type: String,
    default: ''
  },
  size: {
    type: Number,
    default: 200
  },
  showDownloadButton: {
    type: Boolean,
    default: true
  },
  downloadButtonText: {
    type: String,
    default: 'Télécharger le QR Code'
  },
  trackingId: {
    type: String,
    default: null
  }
});

const qrContainer = ref(null);
const scanCount = ref(null);
const qrCodeDataUrl = ref(null);
const { supabase } = useSupabase();

// Fonction pour générer une URL avec les données du participant et du lot
function generateQRCodeURL() {
  // Créer un objet contenant les données à encoder
  const qrData = {
    participant: {
      id: props.data.participant?.id,
      firstName: props.data.participant?.first_name,
      lastName: props.data.participant?.last_name,
      phone: props.data.participant?.phone
    },
    prize: props.data.prize ? {
      id: props.data.prize.id,
      name: props.data.prize.name,
      description: props.data.prize.description || ''
    } : null,
    result: props.data.result,
    tracking: props.trackingId || generateTrackingId(),
    timestamp: new Date().toISOString()
  };

  // Convertir en chaîne JSON
  return JSON.stringify(qrData);
}

// Générer un ID de suivi unique si non fourni
function generateTrackingId() {
  return 'qr_' + Math.random().toString(36).substring(2, 15) + 
         Math.random().toString(36).substring(2, 15);
}

// Générer le QR code avec les données
async function generateQRCode() {
  if (!qrContainer.value) return;
  
  try {
    const url = await QRCode.toDataURL(generateQRCodeURL(), {
      width: props.size,
      margin: 2,
      errorCorrectionLevel: 'H'
    });
    
    qrCodeDataUrl.value = url;
    
    // Créer une image et l'ajouter au conteneur
    qrContainer.value.innerHTML = '';
    const img = document.createElement('img');
    img.src = url;
    img.alt = 'QR Code pour votre lot';
    img.width = props.size;
    img.height = props.size;
    qrContainer.value.appendChild(img);
    
    // Si un ID de suivi est fourni, enregistrer le QR code dans la base de données
    if (props.trackingId) {
      saveQRCodeToDatabase(props.trackingId);
      fetchScanCount(props.trackingId);
    }
  } catch (error) {
    console.error('Erreur lors de la génération du QR code:', error);
  }
}

// Enregistrer le QR code dans la base de données pour le suivi
async function saveQRCodeToDatabase(trackingId) {
  if (!trackingId) return;
  
  try {
    const { error } = await supabase
      .from('qr_codes')
      .upsert({
        tracking_id: trackingId,
        participant_id: props.data.participant?.id,
        prize_id: props.data.prize?.id,
        created_at: new Date().toISOString(),
        scan_count: 0,
        last_scanned: null
      }, {
        onConflict: 'tracking_id'
      });
      
    if (error) {
      console.error('Erreur lors de l\'enregistrement du QR code:', error);
    }
  } catch (error) {
    console.error('Erreur lors de la connexion à la base de données:', error);
  }
}

// Récupérer le nombre de scans
async function fetchScanCount(trackingId) {
  if (!trackingId) return;
  
  try {
    const { data, error } = await supabase
      .from('qr_codes')
      .select('scan_count')
      .eq('tracking_id', trackingId)
      .single();
      
    if (error) {
      console.error('Erreur lors de la récupération du nombre de scans:', error);
    } else if (data) {
      scanCount.value = data.scan_count;
    }
  } catch (error) {
    console.error('Erreur lors de la connexion à la base de données:', error);
  }
}

// Télécharger le QR code
function downloadQRCode() {
  if (!qrCodeDataUrl.value) return;
  
  const link = document.createElement('a');
  link.href = qrCodeDataUrl.value;
  link.download = `qrcode-${props.data.participant?.last_name || 'participant'}-${new Date().getTime()}.png`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Générer le QR code lorsque le composant est monté
onMounted(() => {
  generateQRCode();
});

// Regénérer le QR code si les données changent
watch(() => props.data, () => {
  generateQRCode();
}, { deep: true });
</script>

<style scoped>
.qrcode-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 1.5rem 0;
  padding: 1.5rem;
  background-color: white;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.qrcode-title {
  margin-bottom: 1rem;
  font-size: 1.5rem;
  font-weight: 600;
  color: #333;
}

.qrcode-image {
  margin: 0.5rem 0;
  padding: 1rem;
  background-color: white;
  border-radius: 8px;
  border: 1px solid #eee;
}

.scan-count {
  margin-top: 0.5rem;
  font-size: 0.9rem;
  color: #666;
}

.download-button {
  margin-top: 1rem;
  padding: 0.6rem 1.5rem;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.download-button:hover {
  background-color: #45a049;
}
</style>
