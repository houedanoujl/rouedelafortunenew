<template>
  <div class="admin-prizes-container">
    <h1 class="page-title">
      {{ t('admin.prizes.title') }}
    </h1>
    
    <div class="admin-filters">
      <div class="input-group">
        <input 
          v-model="searchTerm" 
          type="text" 
          class="form-control" 
          :placeholder="t('admin.prizes.searchPlaceholder')"
        >
        <button class="btn btn-primary" @click="refreshData">
          <i class="fas fa-sync"></i> {{ t('admin.prizes.refresh') }}
        </button>
      </div>
    </div>
    
    <div v-if="loading" class="loading-container">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ t('common.loading') }}</span>
      </div>
      <p>{{ t('common.loading') }}</p>
    </div>
    
    <div v-else-if="error" class="error-container">
      <div class="alert alert-danger">
        <h4>{{ t('common.error') }}</h4>
        <p>{{ error }}</p>
      </div>
      <button class="btn btn-primary" @click="refreshData">
        {{ t('admin.prizes.tryAgain') }}
      </button>
    </div>
    
    <div v-else>
      <div class="overview-cards">
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title">{{ t('admin.prizes.totalPrizes') }}</h5>
            <p class="card-value">{{ totalPrizes }}</p>
          </div>
        </div>
        
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title">{{ t('admin.prizes.totalAwarded') }}</h5>
            <p class="card-value">{{ totalAwarded }}</p>
          </div>
        </div>
        
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title">{{ t('admin.prizes.remainingPrizes') }}</h5>
            <p class="card-value">{{ totalRemaining }}</p>
          </div>
        </div>
        
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title">{{ t('admin.prizes.todayAwarded') }}</h5>
            <p class="card-value">{{ totalAwardedToday }}</p>
          </div>
        </div>
      </div>
      
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ t('admin.prizes.id') }}</th>
              <th>{{ t('admin.prizes.name') }}</th>
              <th>{{ t('admin.prizes.description') }}</th>
              <th>{{ t('admin.prizes.totalQuantity') }}</th>
              <th>{{ t('admin.prizes.remaining') }}</th>
              <th>{{ t('admin.prizes.totalWon') }}</th>
              <th>{{ t('admin.prizes.wonToday') }}</th>
              <th>{{ t('admin.prizes.wonThisWeek') }}</th>
              <th>{{ t('admin.prizes.wonThisMonth') }}</th>
              <th>{{ t('admin.prizes.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="prize in filteredPrizes" :key="prize.prize_id">
              <td>{{ prize.prize_id }}</td>
              <td>{{ prize.prize_name }}</td>
              <td>{{ prize.prize_description }}</td>
              <td>{{ prize.total_quantity }}</td>
              <td>
                <span 
                  :class="[
                    'badge', 
                    prize.remaining_quantity <= 0 
                      ? 'text-bg-danger' 
                      : prize.remaining_quantity < prize.total_quantity * 0.25 
                        ? 'text-bg-warning' 
                        : 'text-bg-success'
                  ]"
                >
                  {{ prize.remaining_quantity }}
                </span>
              </td>
              <td>{{ prize.total_won }}</td>
              <td>{{ prize.won_today }}</td>
              <td>{{ prize.won_this_week }}</td>
              <td>{{ prize.won_this_month }}</td>
              <td>
                <button 
                  class="btn btn-sm btn-primary me-1" 
                  @click="openEditModal(prize)"
                >
                  <i class="fas fa-edit"></i>
                </button>
                <button 
                  class="btn btn-sm btn-success" 
                  @click="openWinnersModal(prize)"
                >
                  <i class="fas fa-trophy"></i>
                </button>
              </td>
            </tr>
            <tr v-if="filteredPrizes.length === 0">
              <td colspan="10" class="text-center">{{ t('admin.prizes.noPrizesFound') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <!-- Modal d'édition -->
    <div class="modal fade" id="editPrizeModal" tabindex="-1" aria-labelledby="editPrizeModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editPrizeModalLabel">{{ t('admin.prizes.editPrize') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updatePrize">
              <div class="mb-3">
                <label for="prizeName" class="form-label">{{ t('admin.prizes.name') }}</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="prizeName" 
                  v-model="editedPrize.prize_name" 
                  required
                >
              </div>
              <div class="mb-3">
                <label for="prizeDescription" class="form-label">{{ t('admin.prizes.description') }}</label>
                <textarea 
                  class="form-control" 
                  id="prizeDescription" 
                  v-model="editedPrize.prize_description"
                  rows="3" 
                ></textarea>
              </div>
              <div class="mb-3">
                <label for="prizeTotalQuantity" class="form-label">{{ t('admin.prizes.totalQuantity') }}</label>
                <input 
                  type="number" 
                  class="form-control" 
                  id="prizeTotalQuantity" 
                  v-model.number="editedPrize.total_quantity" 
                  min="0" 
                  required
                >
              </div>
              <div class="mb-3">
                <label for="prizeRemainingQuantity" class="form-label">{{ t('admin.prizes.remaining') }}</label>
                <input 
                  type="number" 
                  class="form-control" 
                  id="prizeRemainingQuantity" 
                  v-model.number="editedPrize.remaining_quantity" 
                  min="0"
                  :max="editedPrize.total_quantity" 
                  required
                >
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('common.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ t('common.save') }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modal des gagnants -->
    <div class="modal fade" id="winnersModal" tabindex="-1" aria-labelledby="winnersModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="winnersModalLabel">{{ t('admin.prizes.winners') }}: {{ selectedPrize ? selectedPrize.prize_name : '' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="loadingWinners" class="text-center my-3">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ t('common.loading') }}</span>
              </div>
              <p>{{ t('admin.prizes.loadingWinners') }}</p>
            </div>
            <div v-else-if="winnersError" class="alert alert-danger">
              {{ winnersError }}
            </div>
            <div v-else>
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>{{ t('admin.winners.id') }}</th>
                      <th>{{ t('admin.winners.name') }}</th>
                      <th>{{ t('admin.winners.phone') }}</th>
                      <th>{{ t('admin.winners.email') }}</th>
                      <th>{{ t('admin.winners.wonDate') }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="winner in winners" :key="winner.entry_id">
                      <td>{{ winner.entry_id }}</td>
                      <td>{{ winner.participant_name }}</td>
                      <td>{{ winner.participant_phone }}</td>
                      <td>{{ winner.participant_email }}</td>
                      <td>{{ formatDate(winner.won_date) }}</td>
                    </tr>
                    <tr v-if="winners.length === 0">
                      <td colspan="5" class="text-center">{{ t('admin.prizes.noWinnersFound') }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('common.close') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSupabase } from '~/composables/useSupabase';
import { useTranslation } from '~/composables/useTranslation';

// Référence à Bootstrap pour les modaux
let bootstrap = null;

const { t } = useTranslation();
const { supabase } = useSupabase();

// État
const loading = ref(true);
const error = ref(null);
const prizes = ref([]);
const searchTerm = ref('');
const editedPrize = ref({});
const selectedPrize = ref(null);
const winners = ref([]);
const loadingWinners = ref(false);
const winnersError = ref(null);

// Modaux
let editModal = null;
let winnersModal = null;

// Statistiques calculées
const totalPrizes = computed(() => {
  return prizes.value.reduce((total, prize) => total + prize.total_quantity, 0);
});

const totalAwarded = computed(() => {
  return prizes.value.reduce((total, prize) => total + prize.total_won, 0);
});

const totalRemaining = computed(() => {
  return prizes.value.reduce((total, prize) => total + prize.remaining_quantity, 0);
});

const totalAwardedToday = computed(() => {
  return prizes.value.reduce((total, prize) => total + prize.won_today, 0);
});

// Filtrage des prix
const filteredPrizes = computed(() => {
  if (!searchTerm.value) return prizes.value;
  
  const search = searchTerm.value.toLowerCase();
  return prizes.value.filter(prize => 
    prize.prize_name.toLowerCase().includes(search) || 
    prize.prize_description.toLowerCase().includes(search)
  );
});

// Récupérer tous les prix avec les statistiques
async function fetchPrizes() {
  loading.value = true;
  error.value = null;
  
  try {
    const { data, error: supabaseError } = await supabase
      .from('prize_stats_view')
      .select('*');
    
    if (supabaseError) throw supabaseError;
    
    prizes.value = data || [];
  } catch (err) {
    console.error('Erreur lors de la récupération des lots:', err);
    error.value = t('admin.prizes.errorFetchingPrizes');
  } finally {
    loading.value = false;
  }
}

// Fonction pour attendre que Bootstrap soit chargé
function waitForBootstrap() {
  return new Promise((resolve) => {
    // Si nous ne sommes pas côté client, résoudre immédiatement
    if (!process.client) {
      resolve(null);
      return;
    }
    
    // Si bootstrap est déjà disponible, résoudre immédiatement
    if (window.bootstrap) {
      resolve(window.bootstrap);
      return;
    }
    
    // Sinon, vérifier périodiquement jusqu'à ce que bootstrap soit disponible
    const checkInterval = setInterval(() => {
      if (window.bootstrap) {
        clearInterval(checkInterval);
        resolve(window.bootstrap);
      }
    }, 100);
    
    // Définir un timeout pour éviter une boucle infinie
    setTimeout(() => {
      clearInterval(checkInterval);
      console.warn('Bootstrap non chargé après 5 secondes');
      resolve(null);
    }, 5000);
  });
}

// Ouvrir la modal d'édition
async function openEditModal(prize) {
  editedPrize.value = { ...prize };
  
  // Attendre que bootstrap soit chargé
  if (process.client && !bootstrap) {
    bootstrap = await waitForBootstrap();
  }
  
  // Initialiser la modal via l'API DOM
  if (process.client && bootstrap) {
    const modalElement = document.getElementById('editPrizeModal');
    if (modalElement) {
      editModal = new bootstrap.Modal(modalElement);
      editModal.show();
    }
  }
}

// Mettre à jour un prix
async function updatePrize() {
  loading.value = true;
  
  try {
    const { error: supabaseError } = await supabase
      .from('prize')
      .update({
        name: editedPrize.value.prize_name,
        description: editedPrize.value.prize_description,
        total_quantity: editedPrize.value.total_quantity,
        remaining: editedPrize.value.remaining_quantity
      })
      .eq('id', editedPrize.value.prize_id);
    
    if (supabaseError) throw supabaseError;
    
    // Fermer la modal
    if (editModal) {
      editModal.hide();
    }
    
    // Rafraîchir les données
    await fetchPrizes();
  } catch (err) {
    console.error('Erreur lors de la mise à jour du lot:', err);
    error.value = t('admin.prizes.errorUpdatingPrize');
  } finally {
    loading.value = false;
  }
}

// Ouvrir la modal des gagnants
async function openWinnersModal(prize) {
  selectedPrize.value = prize;
  loadingWinners.value = true;
  winnersError.value = null;
  winners.value = [];
  
  // Attendre que bootstrap soit chargé
  if (process.client && !bootstrap) {
    bootstrap = await waitForBootstrap();
  }
  
  // Initialiser la modal via l'API DOM
  if (process.client && bootstrap) {
    const modalElement = document.getElementById('winnersModal');
    if (modalElement) {
      winnersModal = new bootstrap.Modal(modalElement);
      winnersModal.show();
    }
  }
  
  // Charger les gagnants
  fetchWinners(prize.prize_id);
}

// Récupérer les gagnants d'un lot
async function fetchWinners(prizeId) {
  try {
    const { data, error: supabaseError } = await supabase
      .from('winners_view')
      .select('*')
      .eq('prize_id', prizeId);
    
    if (supabaseError) throw supabaseError;
    
    winners.value = data || [];
  } catch (err) {
    console.error('Erreur lors de la récupération des gagnants:', err);
    winnersError.value = t('admin.prizes.errorFetchingWinners');
  } finally {
    loadingWinners.value = false;
  }
}

// Formater une date
function formatDate(dateString) {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date);
}

// Rafraîchir les données
function refreshData() {
  fetchPrizes();
}

// Initialisation au chargement
onMounted(async () => {
  // Attendre que Bootstrap soit chargé avant de continuer
  if (process.client) {
    bootstrap = await waitForBootstrap();
    console.log('Bootstrap disponible:', !!bootstrap);
  }
  
  // Charger les données initiales
  fetchPrizes();
});
</script>

<style>
.admin-prizes-container {
  padding: 2rem 0;
}

.page-title {
  margin-bottom: 2rem;
  color: #1d3557;
}

.admin-filters {
  margin-bottom: 2rem;
}

.overview-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.card-value {
  font-size: 2rem;
  font-weight: 700;
  margin: 1rem 0 0.5rem;
  color: #1d3557;
}

.loading-container, .error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  text-align: center;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
  margin-bottom: 1rem;
}

.winners-table {
  margin-top: 1.5rem;
}

.modal-dialog.modal-lg {
  max-width: 800px;
}
</style>
