<template>
  <div class="admin-prizes-container">
    <h1 class="page-title">
      {{ $t('admin.prizes.title') }}
    </h1>
    
    <div class="admin-filters">
      <div class="input-group">
        <input 
          v-model="searchTerm" 
          type="text" 
          class="form-control" 
          :placeholder="$t('admin.prizes.searchPlaceholder')"
        >
        <button class="btn btn-primary" @click="refreshData">
          <i class="fas fa-sync"></i> {{ $t('admin.prizes.refresh') }}
        </button>
      </div>
    </div>
    
    <div v-if="loading" class="loading-container">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ $t('common.loading') }}</span>
      </div>
      <p>{{ $t('common.loading') }}</p>
    </div>
    
    <div v-else-if="error" class="error-container">
      <div class="alert alert-danger">
        <h4>{{ $t('common.error') }}</h4>
        <p>{{ error }}</p>
      </div>
      <button class="btn btn-primary" @click="refreshData">
        {{ $t('admin.prizes.tryAgain') }}
      </button>
    </div>
    
    <div v-else>
      <div class="overview-cards">
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title">{{ $t('admin.prizes.totalPrizes') }}</h5>
            <p class="card-value">{{ totalPrizes }}</p>
          </div>
        </div>
        
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title">{{ $t('admin.prizes.totalAwarded') }}</h5>
            <p class="card-value">{{ totalAwarded }}</p>
          </div>
        </div>
        
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title">{{ $t('admin.prizes.remainingPrizes') }}</h5>
            <p class="card-value">{{ totalRemaining }}</p>
          </div>
        </div>
        
        <div class="card stat-card">
          <div class="card-body">
            <h5 class="card-title">{{ $t('admin.prizes.todayAwarded') }}</h5>
            <p class="card-value">{{ totalAwardedToday }}</p>
          </div>
        </div>
      </div>
      
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ $t('admin.prizes.id') }}</th>
              <th>{{ $t('admin.prizes.name') }}</th>
              <th>{{ $t('admin.prizes.description') }}</th>
              <th>{{ $t('admin.prizes.totalQuantity') }}</th>
              <th>{{ $t('admin.prizes.remaining') }}</th>
              <th>{{ $t('admin.prizes.totalWon') }}</th>
              <th>{{ $t('admin.prizes.wonToday') }}</th>
              <th>{{ $t('admin.prizes.wonThisWeek') }}</th>
              <th>{{ $t('admin.prizes.wonThisMonth') }}</th>
              <th>{{ $t('admin.prizes.actions') }}</th>
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
              <td colspan="10" class="text-center">{{ $t('admin.prizes.noPrizesFound') }}</td>
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
            <h5 class="modal-title" id="editPrizeModalLabel">{{ $t('admin.prizes.editPrize') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updatePrize">
              <div class="mb-3">
                <label for="prizeName" class="form-label">{{ $t('admin.prizes.name') }}</label>
                <input 
                  type="text" 
                  class="form-control" 
                  id="prizeName" 
                  v-model="editedPrize.prize_name" 
                  required
                >
              </div>
              <div class="mb-3">
                <label for="prizeDescription" class="form-label">{{ $t('admin.prizes.description') }}</label>
                <textarea 
                  class="form-control" 
                  id="prizeDescription" 
                  v-model="editedPrize.prize_description"
                  rows="3" 
                ></textarea>
              </div>
              <div class="mb-3">
                <label for="prizeTotalQuantity" class="form-label">{{ $t('admin.prizes.totalQuantity') }}</label>
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
                <label for="prizeRemainingQuantity" class="form-label">{{ $t('admin.prizes.remaining') }}</label>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $t('common.cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ $t('common.save') }}</button>
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
            <h5 class="modal-title" id="winnersModalLabel">{{ $t('admin.prizes.winners') }}: {{ selectedPrize ? selectedPrize.prize_name : '' }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="loadingWinners" class="text-center my-3">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ $t('common.loading') }}</span>
              </div>
              <p>{{ $t('admin.prizes.loadingWinners') }}</p>
            </div>
            <div v-else-if="winnersError" class="alert alert-danger">
              {{ winnersError }}
            </div>
            <div v-else>
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>{{ $t('admin.winners.id') }}</th>
                      <th>{{ $t('admin.winners.name') }}</th>
                      <th>{{ $t('admin.winners.phone') }}</th>
                      <th>{{ $t('admin.winners.email') }}</th>
                      <th>{{ $t('admin.winners.wonDate') }}</th>
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
                      <td colspan="5" class="text-center">{{ $t('admin.prizes.noWinnersFound') }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ $t('common.close') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Modal } from 'bootstrap';
import { useSupabase } from '~/composables/useSupabase';
import { useTranslation } from '~/composables/useTranslation';

const { t } = useTranslation();

// Référence Supabase
let supabase;
try {
  const { supabase: supabaseInstance } = useSupabase();
  supabase = supabaseInstance;
} catch (error) {
  console.error('Error initializing Supabase:', error);
}

// État
const prizes = ref([]);
const loading = ref(true);
const error = ref(null);
const searchTerm = ref('');
const editedPrize = ref({});
const selectedPrize = ref(null);
const winners = ref([]);
const loadingWinners = ref(false);
const winnersError = ref(null);

// Modales
let editModal;
let winnersModal;

// Calculs
const filteredPrizes = computed(() => {
  if (!searchTerm.value) return prizes.value;
  
  const term = searchTerm.value.toLowerCase();
  return prizes.value.filter(prize => 
    prize.prize_name.toLowerCase().includes(term) || 
    prize.prize_description.toLowerCase().includes(term)
  );
});

const totalPrizes = computed(() => {
  return prizes.value.reduce((sum, prize) => sum + prize.total_quantity, 0);
});

const totalAwarded = computed(() => {
  return prizes.value.reduce((sum, prize) => sum + prize.total_won, 0);
});

const totalRemaining = computed(() => {
  return prizes.value.reduce((sum, prize) => sum + prize.remaining_quantity, 0);
});

const totalAwardedToday = computed(() => {
  return prizes.value.reduce((sum, prize) => sum + prize.won_today, 0);
});

// Méthodes
async function fetchPrizes() {
  loading.value = true;
  error.value = null;
  
  try {
    const { data, error: apiError } = await supabase
      .from('prize_admin_view')
      .select('*');
    
    if (apiError) throw apiError;
    
    prizes.value = data || [];
  } catch (err) {
    console.error('Error fetching prizes:', err);
    error.value = err.message || t('admin.prizes.errorFetchingPrizes');
  } finally {
    loading.value = false;
  }
}

function openEditModal(prize) {
  editedPrize.value = { ...prize };
  editModal = new Modal(document.getElementById('editPrizeModal'));
  editModal.show();
}

async function updatePrize() {
  try {
    const { error: apiError } = await supabase
      .from('prize')
      .update({
        name: editedPrize.value.prize_name,
        description: editedPrize.value.prize_description,
        total_quantity: editedPrize.value.total_quantity,
        remaining: editedPrize.value.remaining_quantity
      })
      .eq('id', editedPrize.value.prize_id);
    
    if (apiError) throw apiError;
    
    await fetchPrizes();
    editModal.hide();
  } catch (err) {
    console.error('Error updating prize:', err);
    alert(t('admin.prizes.errorUpdatingPrize'));
  }
}

function openWinnersModal(prize) {
  selectedPrize.value = prize;
  fetchWinners(prize.prize_id);
  winnersModal = new Modal(document.getElementById('winnersModal'));
  winnersModal.show();
}

async function fetchWinners(prizeId) {
  loadingWinners.value = true;
  winnersError.value = null;
  winners.value = [];
  
  try {
    const { data, error: apiError } = await supabase
      .from('entry')
      .select(`
        id as entry_id,
        won_date,
        participant:participant_id (
          id,
          name,
          phone,
          email
        )
      `)
      .eq('prize_id', prizeId)
      .eq('result', 'GAGNÉ')
      .order('won_date', { ascending: false });
    
    if (apiError) throw apiError;
    
    winners.value = data?.map(entry => ({
      entry_id: entry.entry_id,
      won_date: entry.won_date,
      participant_name: entry.participant?.name || t('admin.winners.unknownParticipant'),
      participant_phone: entry.participant?.phone || '-',
      participant_email: entry.participant?.email || '-'
    })) || [];
    
  } catch (err) {
    console.error('Error fetching winners:', err);
    winnersError.value = err.message || t('admin.prizes.errorFetchingWinners');
  } finally {
    loadingWinners.value = false;
  }
}

function formatDate(dateString) {
  if (!dateString) return '-';
  
  const options = { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  };
  
  return new Date(dateString).toLocaleDateString(undefined, options);
}

function refreshData() {
  fetchPrizes();
}

// Initialisation
onMounted(() => {
  fetchPrizes();
});
</script>

<style scoped>
.admin-prizes-container {
  padding: 2rem;
}

.page-title {
  margin-bottom: 2rem;
  color: #1d3557;
  font-weight: 700;
}

.admin-filters {
  margin-bottom: 2rem;
}

.loading-container,
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  text-align: center;
}

.overview-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background-color: #f1faee;
  border: none;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.card-value {
  font-size: 2rem;
  font-weight: 700;
  color: #1d3557;
  margin: 0;
}

.badge {
  font-size: 0.9rem;
  padding: 0.5rem 0.75rem;
}

.table {
  background-color: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table thead th {
  background-color: #1d3557;
  color: white;
  padding: 1rem;
  font-weight: 600;
}
</style>
