<template>
  <div>
    <h2 class="text-h4 mb-4">Tableau de bord</h2>
    
    <v-row>
      <!-- Carte nombre de participants -->
      <v-col cols="12" md="4">
        <v-card class="dashboard-card">
          <v-card-text class="d-flex flex-column align-center">
            <v-icon size="48" color="primary" class="mb-2">mdi-account-group</v-icon>
            <span class="text-h4 font-weight-bold">{{ participantsCount }}</span>
            <span class="text-subtitle-1">Participants</span>
          </v-card-text>
        </v-card>
      </v-col>
      
      <!-- Carte nombre de gagnants -->
      <v-col cols="12" md="4">
        <v-card class="dashboard-card">
          <v-card-text class="d-flex flex-column align-center">
            <v-icon size="48" color="success" class="mb-2">mdi-trophy</v-icon>
            <span class="text-h4 font-weight-bold">{{ winnersCount }}</span>
            <span class="text-subtitle-1">Gagnants</span>
          </v-card-text>
        </v-card>
      </v-col>
      
      <!-- Carte nombre de lots attribués -->
      <v-col cols="12" md="4">
        <v-card class="dashboard-card">
          <v-card-text class="d-flex flex-column align-center">
            <v-icon size="48" color="accent" class="mb-2">mdi-gift</v-icon>
            <span class="text-h4 font-weight-bold">{{ prizesDistributedCount }}</span>
            <span class="text-subtitle-1">Lots attribués</span>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
    
    <v-row class="mt-4">
      <!-- Graphique participation hebdomadaire -->
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>Participation par semaine</v-card-title>
          <v-card-text>
            <div class="chart-container">
              <LineChart :chart-data="weeklyParticipationData" :options="chartOptions" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>
      
      <!-- Graphique camembert types de lots -->
      <v-col cols="12" md="6">
        <v-card>
          <v-card-title>Répartition des lots</v-card-title>
          <v-card-text>
            <div class="chart-container">
              <DoughnutChart :chart-data="prizesDistributionData" :options="chartOptions" />
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import { useDatabase } from '~/composables/useDatabase'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement } from 'chart.js'
import { Line as LineChart, Doughnut as DoughnutChart } from 'vue-chartjs'

// Enregistrer les composants Chart.js nécessaires
ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement)

const { db } = useDatabase()

// Données des statistiques
const participantsCount = ref(0)
const winnersCount = ref(0)
const prizesDistributedCount = ref(0)

// Options communes pour les graphiques
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false
}

// Données pour le graphique de participation hebdomadaire
const weeklyParticipationData = reactive({
  labels: ['Semaine 1', 'Semaine 2', 'Semaine 3', 'Semaine 4'],
  datasets: [{
    label: 'Participants',
    data: [0, 0, 0, 0],
    borderColor: 'rgb(255, 152, 0)',
    backgroundColor: 'rgba(255, 152, 0, 0.2)',
    tension: 0.3
  }]
})

// Données pour le graphique de répartition des lots
const prizesDistributionData = reactive({
  labels: ['Lot 1', 'Lot 2', 'Lot 3', 'Lot 4', 'Lot 5'],
  datasets: [{
    data: [0, 0, 0, 0, 0],
    backgroundColor: [
      'rgba(255, 152, 0, 0.8)',   // Orange
      'rgba(255, 193, 7, 0.8)',   // Jaune-orange
      'rgba(3, 169, 244, 0.8)',   // Bleu clair
      'rgba(76, 175, 80, 0.8)',   // Vert
      'rgba(156, 39, 176, 0.8)'   // Violet
    ]
  }]
})

// Charger les données au chargement du composant
onMounted(async () => {
  try {
    // Récupérer le nombre total de participants
    const { data: participantsData } = await db.query('SELECT COUNT(*) as count FROM participant')
    if (participantsData && participantsData.length > 0) {
      participantsCount.value = participantsData[0].count
    }
    
    // Récupérer le nombre total de gagnants
    const { data: winnersData } = await db.query('SELECT COUNT(*) as count FROM entry WHERE result = "win"')
    if (winnersData && winnersData.length > 0) {
      winnersCount.value = winnersData[0].count
    }
    
    // Récupérer le nombre total de lots distribués
    const { data: prizesData } = await db.query('SELECT COUNT(*) as count FROM prize_distribution WHERE distributed = 1')
    if (prizesData && prizesData.length > 0) {
      prizesDistributedCount.value = prizesData[0].count
    }
    
    // Charger les données de participation hebdomadaire
    const { data: weeklyData } = await db.query(`
      SELECT 
        WEEK(created_at) as week_number, 
        COUNT(*) as count 
      FROM participant 
      GROUP BY WEEK(created_at) 
      ORDER BY week_number
      LIMIT 4
    `)
    
    if (weeklyData && weeklyData.length > 0) {
      weeklyParticipationData.datasets[0].data = weeklyData.map((week: any) => week.count)
    }
    
    // Charger les données sur la répartition des lots
    const { data: prizeDistData } = await db.query(`
      SELECT 
        p.name, 
        COUNT(pd.id) as count 
      FROM prize p
      LEFT JOIN prize_distribution pd ON p.id = pd.prize_id
      GROUP BY p.id
      LIMIT 5
    `)
    
    if (prizeDistData && prizeDistData.length > 0) {
      prizesDistributionData.labels = prizeDistData.map((prize: any) => prize.name)
      prizesDistributionData.datasets[0].data = prizeDistData.map((prize: any) => prize.count)
    }
    
  } catch (error) {
    console.error('Erreur lors du chargement des données:', error)
  }
})
</script>

<style scoped>
.dashboard-card {
  transition: transform 0.3s;
}

.dashboard-card:hover {
  transform: translateY(-5px);
}

.chart-container {
  position: relative;
  height: 300px;
}
</style>
