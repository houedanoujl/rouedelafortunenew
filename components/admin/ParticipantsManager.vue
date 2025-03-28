<template>
  <div class="participants-manager">
    <h2 class="text-h4 mb-4">Gestion des participants</h2>

    <!-- Barre de recherche et boutons d'action -->
    <v-row class="mb-4">
      <v-col cols="12" md="6">
        <v-text-field
          v-model="searchQuery"
          label="Rechercher un participant"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="comfortable"
          clearable
          @update:model-value="searchParticipants"
        ></v-text-field>
      </v-col>
      <v-col cols="12" md="6" class="d-flex justify-end align-center">
        <v-btn
          color="primary"
          prepend-icon="mdi-account-plus"
          class="me-2"
          @click="openAddDialog"
        >
          Ajouter
        </v-btn>
        <v-btn
          color="error"
          prepend-icon="mdi-delete"
          variant="outlined"
          :disabled="!selectedParticipants.length"
          @click="confirmDelete"
        >
          Supprimer
        </v-btn>
      </v-col>
    </v-row>

    <!-- Tableau des participants -->
    <v-data-table
      v-model="selectedParticipants"
      :headers="headers"
      :items="participants"
      :loading="loading"
      item-value="id"
      show-select
    >
      <!-- Actions sur chaque ligne -->
      <template v-slot:item.actions="{ item }">
        <v-icon
          size="small"
          class="me-2"
          @click="editParticipant(item.raw)"
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
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="editedItem.first_name"
                    label="Prénom"
                    :rules="[rules.required]"
                  ></v-text-field>
                </v-col>
                <v-col cols="12" sm="6">
                  <v-text-field
                    v-model="editedItem.last_name"
                    label="Nom"
                    :rules="[rules.required]"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="editedItem.phone"
                    label="Téléphone"
                    :rules="[rules.required, rules.phone]"
                  ></v-text-field>
                </v-col>
                <v-col cols="12">
                  <v-text-field
                    v-model="editedItem.email"
                    label="Email"
                    :rules="[rules.email]"
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
          <v-btn color="primary" @click="saveParticipant" :loading="saving">
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
          {{ selectedParticipants.length > 1 
            ? 'ces ' + selectedParticipants.length + ' participants' 
            : 'ce participant' }} ?
          Cette action est irréversible.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="secondary" variant="text" @click="dialogDelete = false">
            Annuler
          </v-btn>
          <v-btn color="error" @click="deleteParticipants" :loading="deleting">
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
const participants = ref([])
const loading = ref(true)
const dialog = ref(false)
const dialogDelete = ref(false)
const searchQuery = ref('')
const selectedParticipants = ref([])
const saving = ref(false)
const deleting = ref(false)
const valid = ref(false)

// Définition de l'item en cours d'édition (vide par défaut)
const defaultItem = {
  id: null,
  first_name: '',
  last_name: '',
  phone: '',
  email: '',
  created_at: new Date().toISOString()
}

// Item en cours d'édition
const editedItem = ref({ ...defaultItem })
const editedIndex = ref(-1)

// En-têtes du tableau
const headers = [
  { title: 'ID', align: 'start', key: 'id' },
  { title: 'Prénom', key: 'first_name' },
  { title: 'Nom', key: 'last_name' },
  { title: 'Téléphone', key: 'phone' },
  { title: 'Email', key: 'email' },
  { title: 'Date d\'inscription', key: 'created_at' },
  { title: 'Actions', key: 'actions', sortable: false }
]

// Titre du formulaire selon le contexte (ajout ou édition)
const formTitle = computed(() => {
  return editedIndex.value === -1 ? 'Ajouter un participant' : 'Modifier le participant'
})

// Règles de validation du formulaire
const rules = {
  required: (v: string) => !!v || 'Ce champ est requis',
  email: (v: string) => !v || /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'Adresse email invalide',
  phone: (v: string) => /^[0-9]{10}$/.test(v) || 'Numéro de téléphone invalide (10 chiffres)'
}

// Charger les participants
const fetchParticipants = async () => {
  loading.value = true
  try {
    const { data } = await db.query('SELECT * FROM participant ORDER BY created_at DESC')
    participants.value = data || []
  } catch (error) {
    console.error('Erreur lors du chargement des participants:', error)
  } finally {
    loading.value = false
  }
}

// Rechercher des participants
const searchParticipants = async () => {
  if (!searchQuery.value) {
    fetchParticipants()
    return
  }
  
  loading.value = true
  try {
    const { data } = await db.query(`
      SELECT * FROM participant 
      WHERE first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?
      ORDER BY created_at DESC
    `, [`%${searchQuery.value}%`, `%${searchQuery.value}%`, `%${searchQuery.value}%`, `%${searchQuery.value}%`])
    participants.value = data || []
  } catch (error) {
    console.error('Erreur lors de la recherche de participants:', error)
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

// Modifier un participant
const editParticipant = (item: any) => {
  editedIndex.value = participants.value.findIndex(p => p.id === item.id)
  editedItem.value = { ...item }
  dialog.value = true
}

// Fermer le dialogue et réinitialiser
const closeDialog = () => {
  dialog.value = false
  editedItem.value = { ...defaultItem }
  editedIndex.value = -1
}

// Enregistrer un participant (ajout ou modification)
const saveParticipant = async () => {
  saving.value = true
  try {
    if (editedIndex.value === -1) {
      // Ajout d'un nouveau participant
      const { data } = await db.query(`
        INSERT INTO participant (first_name, last_name, phone, email) 
        VALUES (?, ?, ?, ?)
      `, [editedItem.value.first_name, editedItem.value.last_name, editedItem.value.phone, editedItem.value.email])
      
      if (data && data.insertId) {
        await fetchParticipants()
      }
    } else {
      // Modification d'un participant existant
      const { data } = await db.query(`
        UPDATE participant SET 
        first_name = ?, 
        last_name = ?, 
        phone = ?, 
        email = ? 
        WHERE id = ?
      `, [
        editedItem.value.first_name, 
        editedItem.value.last_name, 
        editedItem.value.phone, 
        editedItem.value.email, 
        editedItem.value.id
      ])
      
      if (data) {
        await fetchParticipants()
      }
    }
    
    closeDialog()
  } catch (error) {
    console.error('Erreur lors de l\'enregistrement du participant:', error)
  } finally {
    saving.value = false
  }
}

// Confirmer la suppression d'un seul participant
const confirmDeleteSingle = (item: any) => {
  selectedParticipants.value = [item]
  dialogDelete.value = true
}

// Confirmer la suppression des participants sélectionnés
const confirmDelete = () => {
  dialogDelete.value = true
}

// Supprimer les participants sélectionnés
const deleteParticipants = async () => {
  deleting.value = true
  try {
    const ids = selectedParticipants.value.map(p => p.id).join(',')
    const { data } = await db.query(`DELETE FROM participant WHERE id IN (${ids})`)
    
    if (data) {
      await fetchParticipants()
    }
    
    dialogDelete.value = false
    selectedParticipants.value = []
  } catch (error) {
    console.error('Erreur lors de la suppression des participants:', error)
  } finally {
    deleting.value = false
  }
}

// Charger les participants au montage du composant
onMounted(() => {
  fetchParticipants()
})
</script>

<style scoped>
.participants-manager {
  padding: 10px;
}
</style>
