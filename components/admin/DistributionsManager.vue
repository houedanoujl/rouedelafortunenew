<template>
  <div class="distributions-manager">
    <h2 class="text-h4 mb-4">Répartition des lots par semaine</h2>

    <!-- Sélecteur de semaine -->
    <v-row class="mb-4">
      <v-col cols="12" md="6">
        <v-select
          v-model="selectedWeek"
          :items="weeks"
          label="Semaine"
          variant="outlined"
          density="comfortable"
          item-title="label"
          item-value="value"
          @update:model-value="fetchDistributions"
        ></v-select>
      </v-col>
      <v-col cols="12" md="6" class="d-flex justify-end align-center">
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          class="me-2"
          @click="openAddDialog"
        >
          Ajouter
        </v-btn>
        <v-btn
          color="success"
          prepend-icon="mdi-refresh"
          variant="outlined"
          @click="refreshDistributions"
        >
          Actualiser
        </v-btn>
      </v-col>
    </v-row>

    <!-- Résumé de la semaine -->
    <v-row class="mb-4">
      <v-col cols="12" md="4">
        <v-card class="summary-card" color="primary" variant="tonal">
          <v-card-text class="d-flex flex-column align-center">
            <v-icon size="48" class="mb-2">mdi-gift-outline</v-icon>
            <span class="text-h4 font-weight-bold">{{ totalPrizes }}</span>
            <span class="text-subtitle-1">Total des lots</span>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card class="summary-card" color="success" variant="tonal">
          <v-card-text class="d-flex flex-column align-center">
            <v-icon size="48" class="mb-2">mdi-gift</v-icon>
            <span class="text-h4 font-weight-bold">{{ distributedPrizes }}</span>
            <span class="text-subtitle-1">Lots distribués</span>
          </v-card-text>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card class="summary-card" color="info" variant="tonal">
          <v-card-text class="d-flex flex-column align-center">
            <v-icon size="48" class="mb-2">mdi-package-variant-closed</v-icon>
            <span class="text-h4 font-weight-bold">{{ availablePrizes }}</span>
            <span class="text-subtitle-1">Lots disponibles</span>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Tableau des distributions -->
    <v-data-table
      :headers="headers"
      :items="distributions"
      :loading="loading"
      item-value="id"
    >
      <!-- Statut du lot -->
      <template v-slot:item.distributed="{ item }">
        <v-chip
          :color="item.raw.distributed ? 'success' : 'warning'"
          size="small"
        >
          {{ item.raw.distributed ? 'Distribué' : 'Disponible' }}
        </v-chip>
      </template>

      <!-- Format de la valeur -->
      <template v-slot:item.value="{ item }">
        {{ item.raw.value }} €
      </template>

      <!-- Format de la date d'attribution -->
      <template v-slot:item.distribution_date="{ item }">
        {{ item.raw.distribution_date ? formatDate(item.raw.distribution_date) : 'Non attribué' }}
      </template>

      <!-- Actions sur chaque ligne -->
      <template v-slot:item.actions="{ item }">
        <v-icon
          v-if="!item.raw.distributed"
          size="small"
          class="me-2"
          color="success"
          @click="markAsDistributed(item.raw)"
        >
          mdi-check
        </v-icon>
        <v-icon
          size="small"
          class="me-2"
          @click="editDistribution(item.raw)"
        >
          mdi-pencil
        </v-icon>
        <v-icon
          size="small"
          color="error"
          @click="confirmDeleteSingle(item.raw)"
        >
          mdi-delete
        </v-icon>
      </template>
    </v-data-table>

    <!-- Dialogue d'ajout/modification -->
    <v-dialog v-model="dialog" max-width="500px">
      <v-card>
        <v-card-title>
          <span class="text-h5">{{ formTitle }}</span>
        </v-card-title>

        <v-card-text>
          <v-form ref="form" v-model="valid">
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-select
                    v-model="editedItem.prize_id"
                    :items="prizes"
                    item-title="name"
                    item-value="id"
                    label="Lot"
                    :rules="[rules.required]"
                  ></v-select>
                </v-col>
                <v-col cols="12">
                  <v-select
                    v-model="editedItem.week_number"
                    :items="weeks"
                    item-title="label"
                    item-value="value"
                    label="Semaine"
                    :rules="[rules.required]"
                  ></v-select>
                </v-col>
                <v-col cols="12">
                  <v-switch
                    v-model="editedItem.distributed"
                    label="Lot distribué"
                    color="success"
                  ></v-switch>
                </v-col>
                <v-col cols="12" v-if="editedItem.distributed">
                  <v-text-field
                    v-model="editedItem.winner_id"
                    label="ID du gagnant"
                    type="number"
                    min="1"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" v-if="editedItem.distributed">
                  <v-text-field
                    v-model="editedItem.distribution_date"
                    label="Date d'attribution"
                    type="date"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" variant="text" @click="closeDialog">
            Annuler
          </v-btn>
          <v-btn color="primary" @click="saveDistribution" :loading="saving">
            Enregistrer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialogue de confirmation de distribution -->
    <v-dialog v-model="dialogDistribute" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">Attribution du lot</v-card-title>
        <v-card-text>
          <p>Veuillez entrer les informations d'attribution :</p>
          <v-form ref="distributeForm" v-model="validDistribute">
            <v-container>
              <v-row>
                <v-col cols="12">
                  <v-text-field
                    v-model="distributionInfo.winner_id"
                    label="ID du participant gagnant"
                    type="number"
                    min="1"
                    :rules="[rules.required]"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="distributionInfo.distribution_date"
                    label="Date d'attribution"
                    type="date"
                    :rules="[rules.required]"
                  ></v-text-field>
                </v-col>
              </v-row>
            </v-container>
          </v-form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" variant="text" @click="dialogDistribute = false">
            Annuler
          </v-btn>
          <v-btn color="success" @click="distributeItem" :loading="distributing">
            Attribuer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialogue de confirmation de suppression -->
    <v-dialog v-model="dialogDelete" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">Confirmation de suppression</v-card-title>
        <v-card-text>
          Êtes-vous sûr de vouloir supprimer cette répartition ?
          Cette action est irréversible.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" variant="text" @click="dialogDelete = false">
            Annuler
          </v-btn>
          <v-btn color="error" @click="deleteDistribution" :loading="deleting">
            Confirmer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useDatabase } from '~/composables/useDatabase'

const { db } = useDatabase()

// État du composant
const distributions = ref([])
const prizes = ref([])
const loading = ref(true)
const dialog = ref(false)
const dialogDelete = ref(false)
const dialogDistribute = ref(false)
const saving = ref(false)
const deleting = ref(false)
const distributing = ref(false)
const valid = ref(false)
const validDistribute = ref(false)

// Semaines disponibles
const currentDate = new Date()
const currentWeek = currentDate.getFullYear() * 100 + Math.ceil((currentDate.getDate() + currentDate.getDay()) / 7)

const weeks = [
  { label: 'Semaine 1 (01/03/2025 - 07/03/2025)', value: 202509 },
  { label: 'Semaine 2 (08/03/2025 - 14/03/2025)', value: 202510 },
  { label: 'Semaine 3 (15/03/2025 - 21/03/2025)', value: 202511 },
  { label: 'Semaine 4 (22/03/2025 - 28/03/2025)', value: 202512 },
  { label: 'Semaine 5 (29/03/2025 - 04/04/2025)', value: 202513 }
]

const selectedWeek = ref(currentWeek)

// Statistiques pour la semaine sélectionnée
const totalPrizes = ref(0)
const distributedPrizes = ref(0)
const availablePrizes = computed(() => totalPrizes.value - distributedPrizes.value)

// Définition de l'item en cours d'édition (vide par défaut)
const defaultItem = {
  id: null,
  prize_id: null,
  week_number: currentWeek,
  distributed: false,
  winner_id: null,
  distribution_date: null,
  prize_name: '',
  value: 0
}

// Item en cours d'édition
const editedItem = ref({ ...defaultItem })
const editedIndex = ref(-1)

// Pour l'attribution d'un lot
const selectedDistribution = ref(null)
const distributionInfo = ref({
  winner_id: null,
  distribution_date: new Date().toISOString().split('T')[0]
})

// En-têtes du tableau
const headers = [
  { title: 'ID', align: 'start', key: 'id', width: '80px' },
  { title: 'Lot', key: 'prize_name' },
  { title: 'Valeur', key: 'value', width: '100px' },
  { title: 'Statut', key: 'distributed', width: '120px' },
  { title: 'Gagnant', key: 'winner_id', width: '100px' },
  { title: 'Date d\'attribution', key: 'distribution_date', width: '150px' },
  { title: 'Actions', key: 'actions', sortable: false, width: '120px' }
]

// Titre du formulaire selon le contexte (ajout ou édition)
const formTitle = computed(() => {
  return editedIndex.value === -1 ? 'Ajouter une répartition' : 'Modifier la répartition'
})

// Règles de validation du formulaire
const rules = {
  required: (v: any) => !!v || 'Ce champ est requis'
}

// Formater une date
const formatDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR')
}

// Charger les répartitions pour la semaine sélectionnée
const fetchDistributions = async () => {
  loading.value = true
  try {
    // Récupérer les répartitions pour la semaine
    const { data } = await db.query(`
      SELECT pd.*, p.name as prize_name, p.value 
      FROM prize_distribution pd
      JOIN prize p ON pd.prize_id = p.id
      WHERE pd.week_number = ?
      ORDER BY pd.id
    `, [selectedWeek.value])
    
    distributions.value = data || []
    
    // Calculer les statistiques
    totalPrizes.value = distributions.value.length
    distributedPrizes.value = distributions.value.filter((d: any) => d.distributed).length
    
  } catch (error) {
    console.error('Erreur lors du chargement des répartitions:', error)
  } finally {
    loading.value = false
  }
}

// Charger les lots disponibles
const fetchPrizes = async () => {
  try {
    const { data } = await db.query('SELECT * FROM prize WHERE stock > 0 ORDER BY name')
    prizes.value = data || []
  } catch (error) {
    console.error('Erreur lors du chargement des lots:', error)
  }
}

// Actualiser les données
const refreshDistributions = () => {
  fetchPrizes()
  fetchDistributions()
}

// Ouvrir le dialogue d'ajout
const openAddDialog = () => {
  editedIndex.value = -1
  editedItem.value = { ...defaultItem, week_number: selectedWeek.value }
  dialog.value = true
}

// Modifier une répartition
const editDistribution = (item: any) => {
  editedIndex.value = distributions.value.findIndex(d => d.id === item.id)
  editedItem.value = { ...item }
  dialog.value = true
}

// Fermer le dialogue et réinitialiser
const closeDialog = () => {
  dialog.value = false
  editedItem.value = { ...defaultItem }
  editedIndex.value = -1
}

// Marquer un lot comme distribué
const markAsDistributed = (item: any) => {
  selectedDistribution.value = item
  distributionInfo.value = {
    winner_id: null,
    distribution_date: new Date().toISOString().split('T')[0]
  }
  dialogDistribute.value = true
}

// Attribuer un lot
const distributeItem = async () => {
  if (!selectedDistribution.value) return
  
  distributing.value = true
  try {
    const { data } = await db.query(`
      UPDATE prize_distribution SET 
      distributed = 1, 
      winner_id = ?, 
      distribution_date = ? 
      WHERE id = ?
    `, [
      distributionInfo.value.winner_id,
      distributionInfo.value.distribution_date,
      selectedDistribution.value.id
    ])
    
    if (data) {
      // Mettre à jour le stock du lot
      await db.query('UPDATE prize SET stock = stock - 1 WHERE id = ?', [selectedDistribution.value.prize_id])
      
      await fetchDistributions()
      dialogDistribute.value = false
    }
  } catch (error) {
    console.error('Erreur lors de l\'attribution du lot:', error)
  } finally {
    distributing.value = false
  }
}

// Enregistrer une répartition (ajout ou modification)
const saveDistribution = async () => {
  saving.value = true
  try {
    if (editedIndex.value === -1) {
      // Ajout d'une nouvelle répartition
      const { data } = await db.query(`
        INSERT INTO prize_distribution (prize_id, week_number, distributed, winner_id, distribution_date) 
        VALUES (?, ?, ?, ?, ?)
      `, [
        editedItem.value.prize_id,
        editedItem.value.week_number,
        editedItem.value.distributed ? 1 : 0,
        editedItem.value.winner_id,
        editedItem.value.distribution_date
      ])
      
      if (data && data.insertId) {
        if (editedItem.value.distributed) {
          // Mettre à jour le stock du lot
          await db.query('UPDATE prize SET stock = stock - 1 WHERE id = ?', [editedItem.value.prize_id])
        }
        
        await fetchDistributions()
      }
    } else {
      // Modification d'une répartition existante
      const currentItem = distributions.value[editedIndex.value]
      const wasDistributed = currentItem.distributed
      const isNowDistributed = editedItem.value.distributed
      
      const { data } = await db.query(`
        UPDATE prize_distribution SET 
        prize_id = ?, 
        week_number = ?, 
        distributed = ?, 
        winner_id = ?, 
        distribution_date = ? 
        WHERE id = ?
      `, [
        editedItem.value.prize_id,
        editedItem.value.week_number,
        editedItem.value.distributed ? 1 : 0,
        editedItem.value.winner_id,
        editedItem.value.distribution_date,
        editedItem.value.id
      ])
      
      if (data) {
        // Si le statut de distribution a changé, mettre à jour le stock
        if (!wasDistributed && isNowDistributed) {
          // Lot nouvellement distribué, réduire le stock
          await db.query('UPDATE prize SET stock = stock - 1 WHERE id = ?', [editedItem.value.prize_id])
        } else if (wasDistributed && !isNowDistributed) {
          // Lot non distribué finalement, augmenter le stock
          await db.query('UPDATE prize SET stock = stock + 1 WHERE id = ?', [editedItem.value.prize_id])
        }
        
        await fetchDistributions()
      }
    }
    
    closeDialog()
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement de la répartition:', error)
  } finally {
    saving.value = false
  }
}

// Confirmer la suppression d'une répartition
const confirmDeleteSingle = (item: any) => {
  selectedDistribution.value = item
  dialogDelete.value = true
}

// Supprimer une répartition
const deleteDistribution = async () => {
  if (!selectedDistribution.value) return
  
  deleting.value = true
  try {
    const { data } = await db.query('DELETE FROM prize_distribution WHERE id = ?', [selectedDistribution.value.id])
    
    if (data) {
      // Si le lot était distribué, remettre le stock à jour
      if (selectedDistribution.value.distributed) {
        await db.query('UPDATE prize SET stock = stock + 1 WHERE id = ?', [selectedDistribution.value.prize_id])
      }
      
      await fetchDistributions()
      dialogDelete.value = false
    }
  } catch (error) {
    console.error('Erreur lors de la suppression de la répartition:', error)
  } finally {
    deleting.value = false
  }
}

// Charger les données au montage du composant
onMounted(() => {
  fetchPrizes()
  fetchDistributions()
})
</script>

<style scoped>
.distributions-manager {
  padding: 10px;
}

.summary-card {
  transition: transform 0.3s;
}

.summary-card:hover {
  transform: translateY(-5px);
}
</style>
