<template>
  <div class="prizes-manager">
    <h2 class="text-h4 mb-4">Gestion des lots</h2>

    <!-- Barre de recherche et boutons d'action -->
    <v-row class="mb-4">
      <v-col cols="12" md="6">
        <v-text-field
          v-model="searchQuery"
          label="Rechercher un lot"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="comfortable"
          clearable
          @update:model-value="searchPrizes"
        ></v-text-field>
      </v-col>
      <v-col cols="12" md="6" class="d-flex justify-end align-center">
        <v-btn
          color="primary"
          prepend-icon="mdi-gift-outline"
          class="me-2"
          @click="openAddDialog"
        >
          Ajouter
        </v-btn>
        <v-btn
          color="error"
          prepend-icon="mdi-delete"
          variant="outlined"
          :disabled="!selectedPrizes.length"
          @click="confirmDelete"
        >
          Supprimer
        </v-btn>
      </v-col>
    </v-row>

    <!-- Tableau des lots -->
    <v-data-table
      v-model="selectedPrizes"
      :headers="headers"
      :items="prizes"
      :loading="loading"
      item-value="id"
      show-select
    >
      <!-- Format du stock -->
      <template v-slot:item.stock="{ item }">
        <v-chip
          :color="getStockColor(item.raw.stock)"
          size="small"
        >
          {{ item.raw.stock }}
        </v-chip>
      </template>

      <!-- Format de la valeur -->
      <template v-slot:item.value="{ item }">
        {{ item.raw.value }} €
      </template>

      <!-- Actions sur chaque ligne -->
      <template v-slot:item.actions="{ item }">
        <v-icon
          size="small"
          class="me-2"
          @click="editPrize(item.raw)"
        >
          mdi-pencil
        </v-icon>
        <v-icon
          size="small"
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
                  <v-text-field
                    v-model="editedItem.name"
                    label="Nom du lot"
                    :rules="[rules.required]"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    v-model="editedItem.description"
                    label="Description"
                    rows="3"
                  ></v-textarea>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editedItem.stock"
                    label="Stock"
                    type="number"
                    min="0"
                    :rules="[rules.required, rules.numeric]"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model.number="editedItem.value"
                    label="Valeur (€)"
                    type="number"
                    min="0"
                    :rules="[rules.required, rules.numeric]"
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
          <v-btn color="primary" @click="savePrize" :loading="saving">
            Enregistrer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialogue de confirmation de suppression -->
    <v-dialog v-model="dialogDelete" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">Confirmation de suppression</v-card-title>
        <v-card-text>
          Êtes-vous sûr de vouloir supprimer 
          {{ selectedPrizes.length > 1 
            ? 'ces ' + selectedPrizes.length + ' lots' 
            : 'ce lot' }} ?
          Cette action est irréversible.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" variant="text" @click="dialogDelete = false">
            Annuler
          </v-btn>
          <v-btn color="error" @click="deletePrizes" :loading="deleting">
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
const prizes = ref([])
const loading = ref(true)
const dialog = ref(false)
const dialogDelete = ref(false)
const searchQuery = ref('')
const selectedPrizes = ref([])
const saving = ref(false)
const deleting = ref(false)
const valid = ref(false)

// Définition de l'item en cours d'édition (vide par défaut)
const defaultItem = {
  id: null,
  name: '',
  description: '',
  stock: 0,
  value: 0
}

// Item en cours d'édition
const editedItem = ref({ ...defaultItem })
const editedIndex = ref(-1)

// En-têtes du tableau
const headers = [
  { title: 'ID', align: 'start', key: 'id', width: '80px' },
  { title: 'Nom', key: 'name' },
  { title: 'Description', key: 'description' },
  { title: 'Stock', key: 'stock', width: '100px' },
  { title: 'Valeur', key: 'value', width: '100px' },
  { title: 'Actions', key: 'actions', sortable: false, width: '100px' }
]

// Titre du formulaire selon le contexte (ajout ou édition)
const formTitle = computed(() => {
  return editedIndex.value === -1 ? 'Ajouter un lot' : 'Modifier le lot'
})

// Règles de validation du formulaire
const rules = {
  required: (v: any) => !!v || 'Ce champ est requis',
  numeric: (v: number) => v >= 0 || 'La valeur doit être positive'
}

// Couleur selon le niveau de stock
const getStockColor = (stock: number) => {
  if (stock <= 0) return 'error'
  if (stock < 5) return 'warning'
  return 'success'
}

// Charger les lots
const fetchPrizes = async () => {
  loading.value = true
  try {
    const { data } = await db.query('SELECT * FROM prize ORDER BY name')
    prizes.value = data || []
  } catch (error) {
    console.error('Erreur lors du chargement des lots:', error)
  } finally {
    loading.value = false
  }
}

// Rechercher des lots
const searchPrizes = async () => {
  if (!searchQuery.value) {
    fetchPrizes()
    return
  }
  
  loading.value = true
  try {
    const { data } = await db.query(`
      SELECT * FROM prize 
      WHERE name LIKE ? OR description LIKE ?
      ORDER BY name
    `, [`%${searchQuery.value}%`, `%${searchQuery.value}%`])
    prizes.value = data || []
  } catch (error) {
    console.error('Erreur lors de la recherche de lots:', error)
  } finally {
    loading.value = false
  }
}

// Ouvrir le dialogue d'ajout
const openAddDialog = () => {
  editedIndex.value = -1
  editedItem.value = { ...defaultItem }
  dialog.value = true
}

// Modifier un lot
const editPrize = (item: any) => {
  editedIndex.value = prizes.value.findIndex(p => p.id === item.id)
  editedItem.value = { ...item }
  dialog.value = true
}

// Fermer le dialogue et réinitialiser
const closeDialog = () => {
  dialog.value = false
  editedItem.value = { ...defaultItem }
  editedIndex.value = -1
}

// Enregistrer un lot (ajout ou modification)
const savePrize = async () => {
  saving.value = true
  try {
    if (editedIndex.value === -1) {
      // Ajout d'un nouveau lot
      const { data } = await db.query(`
        INSERT INTO prize (name, description, stock, value) 
        VALUES (?, ?, ?, ?)
      `, [
        editedItem.value.name, 
        editedItem.value.description, 
        editedItem.value.stock, 
        editedItem.value.value
      ])
      
      if (data && data.insertId) {
        await fetchPrizes()
      }
    } else {
      // Modification d'un lot existant
      const { data } = await db.query(`
        UPDATE prize SET 
        name = ?, 
        description = ?, 
        stock = ?, 
        value = ? 
        WHERE id = ?
      `, [
        editedItem.value.name, 
        editedItem.value.description, 
        editedItem.value.stock, 
        editedItem.value.value, 
        editedItem.value.id
      ])
      
      if (data) {
        await fetchPrizes()
      }
    }
    
    closeDialog()
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement du lot:', error)
  } finally {
    saving.value = false
  }
}

// Confirmer la suppression d'un seul lot
const confirmDeleteSingle = (item: any) => {
  selectedPrizes.value = [item]
  dialogDelete.value = true
}

// Confirmer la suppression des lots sélectionnés
const confirmDelete = () => {
  dialogDelete.value = true
}

// Supprimer les lots sélectionnés
const deletePrizes = async () => {
  deleting.value = true
  try {
    const ids = selectedPrizes.value.map(p => p.id).join(',')
    const { data } = await db.query(`DELETE FROM prize WHERE id IN (${ids})`)
    
    if (data) {
      await fetchPrizes()
    }
    
    dialogDelete.value = false
    selectedPrizes.value = []
  } catch (error) {
    console.error('Erreur lors de la suppression des lots:', error)
  } finally {
    deleting.value = false
  }
}

// Charger les lots au montage du composant
onMounted(() => {
  fetchPrizes()
})
</script>

<style scoped>
.prizes-manager {
  padding: 10px;
}
</style>
