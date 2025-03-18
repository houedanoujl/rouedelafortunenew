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
          <path d="M15 0L30 20H0L15 0Z" fill="#e63946" />
          <rect x="12" y="20" width="6" height="30" fill="#e63946" />
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
                <stop offset="0%" stop-color="#34D399" />
                <stop offset="100%" stop-color="#059669" />
              </linearGradient>
              <linearGradient id="loseGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#F87171" />
                <stop offset="100%" stop-color="#DC2626" />
              </linearGradient>
              <radialGradient id="centerGradient" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                <stop offset="0%" stop-color="#fff" />
                <stop offset="100%" stop-color="#f1faee" />
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
                  fill="white" 
                  font-weight="bold"
                  font-size="14"
                  text-shadow="1px 1px 2px rgba(0,0,0,0.5)"
                >
                  {{ sector.won ? 'GAGNÉ' : 'PERDU' }}
                </text>
              </template>
              <!-- Cercle central -->
              <circle cx="0" cy="0" r="40" fill="#1d3557" stroke="#fff" stroke-width="2" />
              <text x="0" y="5" text-anchor="middle" fill="white" font-weight="bold" font-size="14">DINOR</text>
              <!-- Décorations sur le bord externe -->
              <template v-for="i in 12" :key="`dot-${i}`">
                <circle 
                  :cx="170 * Math.cos((i * 30 - 15) * Math.PI / 180)" 
                  :cy="170 * Math.sin((i * 30 - 15) * Math.PI / 180)" 
                  r="5" 
                  fill="#FFD166"
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
import { ref, onMounted, computed } from 'vue';
import { useSupabase } from '~/composables/useSupabase';
import gsap from 'gsap';

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

const emit = defineEmits(['gameCompleted']);

let supabase;
let mockMode = false;

try {
  const supabaseInstance = useSupabase();
  supabase = supabaseInstance.supabase;
  mockMode = !supabaseInstance.isReal;
} catch (err) {
  console.error('Error initializing Supabase:', err);
  mockMode = true;
}

// References
const wheelRef = ref(null);
const wheelSectorsRef = ref(null);

// State
const isSpinning = ref(false);
const showResult = ref(false);
const result = ref({ won: false, prizeId: null });
const qrCodeUrl = ref('');
const hasPlayed = ref(false);
const alreadyPlayed = ref(false);

// Sectors (12 segments with win/lose status)
const sectors = ref([
  { won: true }, { won: false }, { won: false }, 
  { won: false }, { won: true }, { won: false }, 
  { won: false }, { won: true }, { won: false }, 
  { won: false }, { won: true }, { won: false }
]);

// Vérifier si le participant a déjà joué
onMounted(async () => {
  console.log('GSAP Version:', gsap.version);
  await checkIfParticipantAlreadyPlayed();
});

async function checkIfParticipantAlreadyPlayed() {
  if (!props.participantId) return;
  
  try {
    if (mockMode || !supabase) {
      // Simuler que le participant n'a pas joué (pour le premier tour)
      alreadyPlayed.value = false;
      return;
    }
    
    const { data, error } = await supabase
      .from('entry')
      .select('id')
      .eq('participant_id', props.participantId)
      .eq('contest_id', props.contestId);
      
    if (error) {
      console.error('Error checking participant entries:', error);
      return;
    }
    
    alreadyPlayed.value = data && data.length > 0;
    console.log('Participant already played:', alreadyPlayed.value);
  } catch (err) {
    console.error('Error checking if participant already played:', err);
  }
}

// Helper function to create SVG arcs for wheel sectors
function describeArc(x, y, radius, startAngle, endAngle) {
  const start = polarToCartesian(x, y, radius, endAngle);
  const end = polarToCartesian(x, y, radius, startAngle);
  const largeArcFlag = endAngle - startAngle <= 180 ? 0 : 1;
  
  return [
    "M", start.x, start.y,
    "A", radius, radius, 0, largeArcFlag, 0, end.x, end.y,
    "L", x, y,
    "Z"
  ].join(" ");
}

function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
  const angleInRadians = (angleInDegrees - 90) * Math.PI / 180;
  return {
    x: centerX + (radius * Math.cos(angleInRadians)),
    y: centerY + (radius * Math.sin(angleInRadians))
  };
}

// Animation de la roue améliorée
async function spinWheel() {
  console.log('Spinning wheel...');
  console.log('wheelSectorsRef:', wheelSectorsRef.value);
  
  if (isSpinning.value || hasPlayed.value || alreadyPlayed.value) return;
  
  isSpinning.value = true;
  showResult.value = false;
  
  // Déterminer le secteur gagnant (aléatoire ou prédéterminé)
  const randomSectorIndex = Math.floor(Math.random() * 12);
  const wonSpin = sectors.value[randomSectorIndex].won;
  
  // Calcul de la rotation (au moins 8 rotations complètes + le secteur aléatoire)
  const fullRotations = 8 + Math.random() * 4; // Entre 8 et 12 rotations complètes
  const extraDegrees = randomSectorIndex * 30;
  const totalRotation = fullRotations * 360 + extraDegrees;
  
  console.log('Target rotation:', totalRotation);
  
  try {
    // Sélectionner l'élément SVG directement en cas de problème avec la référence
    const wheelElement = wheelSectorsRef.value || document.querySelector('g[transform="translate(200, 200)"]');
    
    if (!wheelElement) {
      console.error('Wheel element not found!');
      isSpinning.value = false;
      return;
    }
    
    console.log('Wheel element found:', wheelElement);
    
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
      onComplete: () => handleSpinComplete(wonSpin)
    });
    
    // Ajout d'un effet sonore (simulation)
    const clickCount = Math.floor(totalRotation / 15); // Un clic tous les 15 degrés
    let currentClick = 0;
    
    const tickInterval = setInterval(() => {
      if (currentClick >= clickCount) {
        clearInterval(tickInterval);
        return;
      }
      
      // Simuler un son de clic (à remplacer par un vrai son si nécessaire)
      console.log('tick');
      
      currentClick++;
      
      // Ralentir la fréquence des clics progressivement
      if (currentClick > clickCount * 0.7) {
        clearInterval(tickInterval);
        const slowTickInterval = setInterval(() => {
          if (currentClick >= clickCount) {
            clearInterval(slowTickInterval);
            return;
          }
          console.log('tick-slow');
          currentClick++;
        }, 200);
      }
    }, 50);
  } catch (error) {
    console.error('Error during wheel animation:', error);
    isSpinning.value = false;
  }
}

async function handleSpinComplete(won) {
  isSpinning.value = false;
  hasPlayed.value = true; // Marquer que le participant a joué
  
  // Animation pour l'affichage du résultat
  setTimeout(() => {
    // Set the result object
    result.value = {
      won,
      prizeId: won ? determinePrize() : null
    };
    
    // If won, generate QR code
    if (won) {
      qrCodeUrl.value = `/assets/images/qr-code-placeholder.svg`; // Using SVG placeholder
    }
    
    // Save result to database
    saveEntryToDatabase(won, result.value.prizeId);
    
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

async function determinePrize() {
  // If Supabase is not available, return mock data
  if (mockMode || !supabase) {
    console.warn('Supabase not available, using mock prize');
    return Math.floor(Math.random() * 5) + 1;
  }
  
  // Logic to determine which prize to award
  try {
    const { data, error } = await supabase
      .from('prize')
      .select('id')
      .limit(1);
      
    if (error) throw error;
    
    return data.length > 0 ? data[0].id : Math.floor(Math.random() * 5) + 1;
  } catch (error) {
    console.error('Error determining prize:', error);
    return Math.floor(Math.random() * 5) + 1;
  }
}

async function saveEntryToDatabase(won, prizeId) {
  // If Supabase is not available, just log for demo
  if (mockMode || !supabase) {
    console.warn('Supabase not available, mock entry saved:', {
      participant_id: props.participantId,
      contest_id: props.contestId,
      result: won ? 'GAGNÉ' : 'PERDU',
      prize_id: prizeId
    });
    return;
  }
  
  try {
    const { data, error } = await supabase
      .from('entry')
      .insert({
        participant_id: props.participantId,
        contest_id: props.contestId,
        result: won ? 'GAGNÉ' : 'PERDU',
        prize_id: prizeId
      });
      
    if (error) throw error;
    
    console.log('Entry saved:', data);
  } catch (error) {
    console.error('Error saving entry:', error);
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
  background: linear-gradient(145deg, #e63946, #c1121f);
  box-shadow: 0 4px 10px rgba(230, 57, 70, 0.4);
  transition: all 0.3s ease;
}

.spin-button:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(230, 57, 70, 0.5);
}

.spin-button:active:not(:disabled) {
  transform: translateY(1px);
  box-shadow: 0 2px 5px rgba(230, 57, 70, 0.4);
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
  color: #059669;
  font-size: 24px;
  font-weight: 700;
}

.lose-message {
  color: #DC2626;
  font-size: 24px;
  font-weight: 700;
}

.result-container.win {
  border-left: 5px solid #059669;
}

.result-container.lose {
  border-left: 5px solid #DC2626;
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
