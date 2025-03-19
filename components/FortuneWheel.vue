<template>
  <div class="wheel-container">
    <div v-if="alreadyPlayed" class="already-played-message">
      <div class="alert alert-info">
        <h3>Vous avez déjà participé</h3>
        <p>Chaque participant ne peut jouer qu'une seule fois. Merci de votre participation!</p>
      </div>
    </div>
    
    <template v-else>
      <div class="wheel-marker">
        <svg width="30" height="50" viewBox="0 0 30 50" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M15 50L30 30H0L15 50Z" fill="#87664Bff" />
          <rect x="12" y="0" width="6" height="30" fill="#87664Bff" />
        </svg>
      </div>
      
      <div class="wheel-outer-ring">
        <div ref="wheelRef" class="wheel">
          <svg width="100%" height="100%" viewBox="0 0 400 400">
            <defs>
              <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                <feDropShadow dx="0" dy="0" stdDeviation="15" flood-color="#000" flood-opacity="0.3"/>
              </filter>
              <linearGradient id="winGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#FCFEFFff" />
                <stop offset="100%" stop-color="#FBFDFEff" />
              </linearGradient>
              <linearGradient id="loseGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#D45D56" />
                <stop offset="100%" stop-color="#BD2B23ff" />
              </linearGradient>
              <radialGradient id="centerGradient" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                <stop offset="0%" stop-color="#FCFEFFff" />
                <stop offset="100%" stop-color="#FBFDFEff" />
              </radialGradient>
            </defs>
            <g ref="wheelSectorsRef" transform="translate(200, 200)">
              <circle cx="0" cy="0" r="180" fill="url(#centerGradient)" stroke="#ccc" stroke-width="2" filter="url(#shadow)" />
              <template v-for="(sector, index) in sectors" :key="index">
                <path 
                  :d="describeArc(0, 0, 180, index * 30, (index + 1) * 30)" 
                  :fill="sector.won ? 'url(#winGradient)' : 'url(#loseGradient)'" 
                  stroke="#fff" 
                  stroke-width="2"
                  :style="{ filter: 'brightness(' + (1 - index * 0.02) + ')' }"
                />
                <text 
                  :transform="`rotate(${index * 30 + 15}) translate(0, -130) rotate(-${index * 30 + 15})`" 
                  text-anchor="middle" 
                  fill="black" 
                  font-weight="bold"
                  font-size="14"
                  font-family="'EB Garamond', serif"
                >
                  {{ sector.won ? 'GAGNÉ' : 'PERDU' }}
                </text>
              </template>
              <!-- Cercle central -->
              <circle cx="0" cy="0" r="40" fill="#87664Bff" stroke="#FBFDFEff" stroke-width="2" />
              <text x="0" y="5" text-anchor="middle" fill="#FCFEFFff" font-weight="bold" font-size="14">DINOR</text>
              <!-- Décorations sur le bord externe -->
              <template v-for="i in 12" :key="`dot-${i}`">
                <circle 
                  :cx="170 * Math.cos((i * 30 - 15) * Math.PI / 180)" 
                  :cy="170 * Math.sin((i * 30 - 15) * Math.PI / 180)" 
                  r="5" 
                  fill="#87664Bff"
                />
              </template>
            </g>
          </svg>
        </div>
      </div>
      
      <button 
        class="btn spin-button" 
        @click="spinWheel" 
        :disabled="isSpinning || hasPlayed"
      >
        <span v-if="isSpinning">Roue en mouvement...</span>
        <span v-else-if="hasPlayed">Vous avez déjà joué</span>
        <span v-else>Tourner la roue</span>
      </button>
      
      <div v-if="showResult" class="result-container" :class="{ 'win': result.won, 'lose': !result.won }">
        <h2 :class="result.won ? 'win-message' : 'lose-message'">
          {{ result.won ? 'Félicitations ! Vous avez GAGNÉ !' : 'Dommage ! Vous avez PERDU !' }}
        </h2>
        <div v-if="result.won" class="prize-info">
          <p>Un SMS vous sera envoyé avec les détails pour récupérer votre lot.</p>
          <div v-if="qrCodeUrl" class="qr-code">
            <img :src="qrCodeUrl" alt="QR Code pour récupérer votre lot" />
          </div>
        </div>
        <div v-else>
          <p>Vous pourrez retenter votre chance lors du prochain jeu.</p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import gsap from 'gsap';
import { useSupabase } from '~/composables/useSupabase';
import { useParticipantCheck } from '~/composables/useParticipantCheck';
import { useTranslation } from '~/composables/useTranslation';

const emit = defineEmits(['gameCompleted']);
const { t } = useTranslation();

const props = defineProps({
  participantId: {
    type: Number,
    required: true
  },
  contestId: {
    type: Number,
    default: 1
  }
});

const wheelRef = ref(null);
const wheelSectorsRef = ref(null);
const isSpinning = ref(false);
const hasPlayed = ref(false);
const alreadyPlayed = ref(false);
const showResult = ref(false);
const result = ref({ won: false, prizeId: null });
const qrCodeUrl = ref('');
const participant = ref(null);

// Secteurs de la roue (6 gagnants, 6 perdants alternés)
const sectors = ref([
  { name: t('fortuneWheel.prizes.tv'), won: true },
  { name: t('fortuneWheel.prizes.tryAgain'), won: false },
  { name: t('fortuneWheel.prizes.smartphone'), won: true },
  { name: t('fortuneWheel.prizes.tryAgain'), won: false },
  { name: t('fortuneWheel.prizes.voucher50'), won: true },
  { name: t('fortuneWheel.prizes.tryAgain'), won: false },
  { name: t('fortuneWheel.prizes.airpods'), won: true },
  { name: t('fortuneWheel.prizes.tryAgain'), won: false },
  { name: t('fortuneWheel.prizes.watch'), won: true },
  { name: t('fortuneWheel.prizes.tryAgain'), won: false },
  { name: t('fortuneWheel.prizes.voucher20'), won: true },
  { name: t('fortuneWheel.prizes.tryAgain'), won: false }
]);

// Vérifier si Supabase est disponible
let supabase;
let mockMode = false;

try {
  const { supabase: supabaseInstance, isReal } = useSupabase();
  supabase = supabaseInstance;
  mockMode = !isReal;
  console.log('FortuneWheel: Mode mock =', mockMode);
} catch (err) {
  console.error('Error initializing Supabase in FortuneWheel:', err);
  mockMode = true;
}

// Fonctions pour gérer les cookies
function setCookie(name, value, days) {
  const date = new Date();
  date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
  const expires = "; expires=" + date.toUTCString();
  document.cookie = name + "=" + value + expires + "; path=/; SameSite=Strict";
}

function getCookie(name) {
  const nameEQ = name + "=";
  const ca = document.cookie.split(';');
  for (let i = 0; i < ca.length; i++) {
    let c = ca[i];
    while (c.charAt(0) === ' ') c = c.substring(1, c.length);
    if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
  }
  return null;
}

const { checkIfParticipantHasPlayed, participantState, saveParticipationInCookie } = useParticipantCheck();

onMounted(async () => {
  checkParticipantExists();
});

// Vérifier si le participant existe dans la base de données
async function checkParticipantExists() {
  if (!props.participantId) {
    return;
  }

  try {
    // Vérifier si le participant a déjà joué
    await checkIfParticipantAlreadyPlayed();
  } catch (error) {
    console.error('Error checking participant:', error);
  }
}

// Vérifier si le participant a déjà joué
async function checkIfParticipantAlreadyPlayed() {
  try {
    const hasPlayed = await checkIfParticipantHasPlayed(props.participantId);
    
    if (hasPlayed && participantState.playedRecently) {
      alreadyPlayed.value = true;
      
      if (participantState.gameResult) {
        result.value = {
          won: participantState.gameResult.result === 'GAGNÉ',
          prizeId: participantState.gameResult.prize_id
        };
      }
    } else {
      alreadyPlayed.value = false;
    }
  } catch (error) {
    console.error('Error checking if participant already played:', error);
    alreadyPlayed.value = false;
  }
}

// Helper function to create SVG arcs for wheel sectors
function describeArc(x, y, radius, startAngle, endAngle) {
  const start = polarToCartesian(x, y, radius, endAngle);
  const end = polarToCartesian(x, y, radius, startAngle);
  const largeArcFlag = endAngle - startAngle <= 180 ? "0" : "1";
  return `M ${start.x} ${start.y} A ${radius} ${radius} 0 ${largeArcFlag} 0 ${end.x} ${end.y} L ${x} ${y} Z`;
}

function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
  const angleInRadians = (angleInDegrees - 90) * Math.PI / 180.0;
  return {
    x: centerX + (radius * Math.cos(angleInRadians)),
    y: centerY + (radius * Math.sin(angleInRadians))
  };
}

// Animation de la roue améliorée
async function spinWheel() {
  if (isSpinning.value || hasPlayed.value || alreadyPlayed.value) return;
  
  isSpinning.value = true;
  showResult.value = false;
  
  // Déterminer si c'est gagné ou perdu (prédéterminé par la configuration des lots disponibles)
  const { won, sectorIndex } = await determineWinningOutcome();
  
  // Calcul de la rotation (au moins 8 rotations complètes + le secteur aléatoire)
  const fullRotations = 8 + Math.random() * 4; // Entre 8 et 12 rotations complètes
  const extraDegrees = sectorIndex * 30;
  const totalRotation = fullRotations * 360 + extraDegrees;
  
  try {
    // Sélectionner l'élément SVG directement en cas de problème avec la référence
    const wheelElement = wheelSectorsRef.value || document.querySelector('g[transform="translate(200, 200)"]');
    
    if (!wheelElement) {
      isSpinning.value = false;
      return;
    }
    
    // Définir la propriété transform directement
    gsap.set(wheelElement, { rotation: 0, transformOrigin: "50% 50%" });
    
    // Effet de rebond lors du ralentissement avec gsap
    const timeline = gsap.timeline();
    
    // Animation initiale avec accélération
    timeline.to(wheelElement, {
      rotation: totalRotation - 30, // Un peu moins que la rotation totale
      duration: 4,
      ease: "power2.inOut"
    });
    
    // Ralentissement final avec effet de rebond
    timeline.to(wheelElement, {
      rotation: totalRotation,
      duration: 1.5,
      ease: "elastic.out(1, 0.3)",
      onComplete: () => handleSpinComplete(won)
    });
  } catch (error) {
    isSpinning.value = false;
  }
}

async function handleSpinComplete(won) {
  isSpinning.value = false;
  hasPlayed.value = true; // Marquer que le participant a joué
  
  // Animation pour l'affichage du résultat
  setTimeout(async () => {
    // Déterminer le prix si gagné
    const prizeId = won ? await determinePrize() : null;
    
    // Set the result object
    result.value = {
      won,
      prizeId
    };
    
    // If won, generate QR code
    if (won) {
      qrCodeUrl.value = `/assets/images/qr-code-placeholder.svg`; // Using SVG placeholder
    }
    
    // Récupérer les informations du participant pour le cookie
    let participantInfo = null;
    if (!mockMode && supabase) {
      try {
        const { data } = await supabase
          .from('participant')
          .select('*')
          .eq('id', props.participantId)
          .single();
        
        participantInfo = data;
      } catch (error) {
        console.error('Error fetching participant info for cookie:', error);
      }
    }
    
    // Save result to database
    const gameResult = await saveEntryToDatabase(won, result.value.prizeId);
    
    // Enregistrer le résultat dans un cookie si le participant existe
    if (participantInfo) {
      saveParticipationInCookie(participantInfo.phone, participantInfo, gameResult);
    }
    
    // Show result with animation
    showResult.value = true;
    
    // Animate result container
    const resultElement = document.querySelector('.result-container');
    if (resultElement) {
      gsap.fromTo(resultElement, 
        { opacity: 0, y: 20 }, 
        { opacity: 1, y: 0, duration: 0.5, ease: "power2.out" }
      );
    }
    
    // Émettre un événement pour indiquer que le jeu est terminé
    emit('gameCompleted', {
      participantId: props.participantId,
      result: won ? 'GAGNÉ' : 'PERDU',
      prizeId: result.value.prizeId
    });
  }, 500);
}

// Déterminer si le participant va gagner ou perdre en fonction des lots disponibles
async function determineWinningOutcome() {
  // Si mode mock ou pas de Supabase, retourner un résultat aléatoire
  if (mockMode || !supabase) {
    const sectorIndex = Math.floor(Math.random() * 12);
    return { 
      won: sectors.value[sectorIndex].won,
      sectorIndex
    };
  }
  
  try {
    // Vérifier s'il y a des lots disponibles avec remaining > 0
    const { data, error } = await supabase
      .from('prize')
      .select('id, remaining')
      .gt('remaining', 0);
    
    if (error) throw error;
    
    // S'il n'y a pas de lots disponibles (remaining > 0), le participant ne peut pas gagner
    const canWin = data && data.length > 0;
    
    // Si le participant peut gagner, sélectionner un secteur gagnant, sinon un secteur perdant
    let sectorIndex;
    if (canWin) {
      // Sélectionner un secteur gagnant (indices pairs: 0, 2, 4, 6, 8, 10)
      const winningSectors = [0, 2, 4, 6, 8, 10];
      sectorIndex = winningSectors[Math.floor(Math.random() * winningSectors.length)];
    } else {
      // Sélectionner un secteur perdant (indices impairs: 1, 3, 5, 7, 9, 11)
      const losingSectors = [1, 3, 5, 7, 9, 11];
      sectorIndex = losingSectors[Math.floor(Math.random() * losingSectors.length)];
    }
    
    return {
      won: canWin && sectors.value[sectorIndex].won,
      sectorIndex
    };
    
  } catch (error) {
    console.error('Error determining winning outcome:', error);
    // En cas d'erreur, retourner un résultat aléatoire
    const sectorIndex = Math.floor(Math.random() * 12);
    return { 
      won: sectors.value[sectorIndex].won,
      sectorIndex
    };
  }
}

async function determinePrize() {
  // If Supabase is not available, return mock data
  if (mockMode || !supabase) {
    return Math.floor(Math.random() * 5) + 1;
  }
  
  // Logic to determine which prize to award
  try {
    const { data, error } = await supabase
      .from('prize')
      .select('id, remaining')
      .gt('remaining', 0)
      .order('id')
      .limit(10);
      
    if (error) throw error;
    
    // S'il y a des lots disponibles avec remaining > 0, en sélectionner un aléatoirement
    if (data && data.length > 0) {
      const randomIndex = Math.floor(Math.random() * data.length);
      return data[randomIndex].id;
    }
    
    // Si aucun lot n'est disponible, retourner null (ne devrait pas arriver normalement)
    return null;
  } catch (error) {
    console.error('Error determining prize:', error);
    return null;
  }
}

async function saveEntryToDatabase(won, prizeId) {
  // If Supabase is not available, just log for demo
  if (mockMode || !supabase) {
    console.log('Mock mode: saving entry to database', { won, prizeId });
    return {
      id: Date.now(),
      participant_id: props.participantId,
      contest_id: props.contestId,
      result: won ? 'GAGNÉ' : 'PERDU',
      prize_id: prizeId,
      created_at: new Date().toISOString()
    };
  }
  
  try {
    const entryData = {
      participant_id: props.participantId,
      contest_id: props.contestId,
      result: won ? 'GAGNÉ' : 'PERDU',
      prize_id: prizeId,
      entry_date: new Date().toISOString()
    };
    
    // Créer une transaction pour insérer l'entrée et mettre à jour le prix si nécessaire
    if (won && prizeId) {
      // 1. Insérer l'entrée
      const { data: entryData, error: entryError } = await supabase
        .from('entry')
        .insert(entryData)
        .select();
        
      if (entryError) throw entryError;
      
      // 2. Mettre à jour le prix (décrémenter remaining et ajouter la date)
      const currentDate = new Date().toISOString();
      
      // Récupérer d'abord les données actuelles du prix
      const { data: prizeData, error: prizeSelectError } = await supabase
        .from('prize')
        .select('remaining, won_date')
        .eq('id', prizeId)
        .single();
        
      if (prizeSelectError) throw prizeSelectError;
      
      // Mettre à jour le prix
      // Conversion du tableau won_date JSON en array JavaScript
      const currentWonDates = Array.isArray(prizeData.won_date) ? prizeData.won_date : [];
      const updatedWonDates = [...currentWonDates, currentDate];
      
      const { error: prizeUpdateError } = await supabase
        .from('prize')
        .update({ 
          remaining: Math.max(0, (prizeData.remaining || 0) - 1),
          won_date: updatedWonDates
        })
        .eq('id', prizeId);
        
      if (prizeUpdateError) throw prizeUpdateError;
      
      return entryData && entryData.length > 0 ? entryData[0] : {
        ...entryData,
        created_at: currentDate
      };
    } else {
      // Si ce n'est pas un gain, simplement insérer l'entrée
      const { data, error } = await supabase
        .from('entry')
        .insert(entryData)
        .select();
        
      if (error) throw error;
      
      return data && data.length > 0 ? data[0] : entryData;
    }
  } catch (error) {
    console.error('Error saving entry to database:', error);
    return {
      id: Date.now(),
      participant_id: props.participantId,
      contest_id: props.contestId,
      result: won ? 'GAGNÉ' : 'PERDU',
      prize_id: prizeId,
      created_at: new Date().toISOString()
    };
  }
}
</script>

<style scoped>
.wheel-container {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 40px 0;
  padding-bottom: 40px;
  font-family: 'EB Garamond', serif;
}

.wheel-outer-ring {
  position: relative;
  width: 380px;
  height: 380px;
  border-radius: 50%;
  background: linear-gradient(145deg, #f1faee, #e2e6de);
  box-shadow: 
    0 10px 30px rgba(0, 0, 0, 0.1),
    inset 0 -5px 15px rgba(0, 0, 0, 0.1),
    inset 0 5px 15px rgba(255, 255, 255, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 15px;
  margin-bottom: 30px;
}

.wheel {
  position: relative;
  width: 350px;
  height: 350px;
  border-radius: 50%;
  overflow: hidden;
  transition: transform 0.2s ease;
  transform-origin: center center;
  box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
}

.wheel-marker {
  position: absolute;
  width: 30px;
  height: 50px;
  top: -30px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
  filter: drop-shadow(0 5px 5px rgba(0, 0, 0, 0.3));
}

.spin-button {
  margin-top: 20px;
  min-width: 200px;
  background: linear-gradient(145deg, #87664Bff, #755743);
  box-shadow: 0 4px 10px rgba(135, 102, 75, 0.4);
  transition: all 0.3s ease;
  color: #FCFEFFff;
}

.spin-button:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(135, 102, 75, 0.5);
}

.spin-button:active:not(:disabled) {
  transform: translateY(1px);
  box-shadow: 0 2px 5px rgba(135, 102, 75, 0.4);
}

.spin-button:disabled {
  background: linear-gradient(145deg, #ccc, #999);
  cursor: not-allowed;
  transform: none;
  opacity: 0.7;
}

.result-container {
  text-align: center;
  margin-top: 30px;
  padding: 20px;
  border-radius: 10px;
  background-color: white;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  opacity: 0;
}

.win-message {
  color: #87664Bff;
  font-size: 24px;
  font-weight: 700;
}

.lose-message {
  color: #BD2B23ff;
  font-size: 24px;
  font-weight: 700;
}

.result-container.win {
  border-left: 5px solid #87664Bff;
}

.result-container.lose {
  border-left: 5px solid #BD2B23ff;
}

.prize-info {
  margin-top: 20px;
}

.qr-code {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  padding: 10px;
  background-color: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.qr-code img {
  max-width: 200px;
  transition: transform 0.3s ease;
}

.qr-code img:hover {
  transform: scale(1.05);
}

.already-played-message {
  margin: 30px 0;
  max-width: 500px;
  text-align: center;
}

.alert {
  padding: 20px;
  border-radius: 10px;
  background-color: #f8f9fa;
  border-left: 5px solid #0dcaf0;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.alert-info {
  background-color: #e7f5ff;
}

.alert h3 {
  color: #0b5ed7;
  margin-bottom: 10px;
}

/* Responsive styles */
@media (max-width: 768px) {
  .wheel-outer-ring {
    width: 320px;
    height: 320px;
    padding: 10px;
  }
  
  .wheel {
    width: 300px;
    height: 300px;
  }
}

@media (max-width: 480px) {
  .wheel-outer-ring {
    width: 280px;
    height: 280px;
    padding: 10px;
  }
  
  .wheel {
    width: 260px;
    height: 260px;
  }
}
</style>
