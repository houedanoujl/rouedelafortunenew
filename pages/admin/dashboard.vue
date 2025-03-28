<template>
  <div>
    <v-app>
      <v-navigation-drawer v-model="drawer" permanent>
        <v-list-item
          title="Administration"
          subtitle="Roue de la Fortune"
        >
          <template v-slot:prepend>
            <v-avatar color="primary">
              <span class="text-h6 text-white">{{ userInitial }}</span>
            </v-avatar>
          </template>
        </v-list-item>

        <v-divider />

        <v-list nav>
          <v-list-item
            v-for="item in navItems"
            :key="item.title"
            :value="item.value"
            :title="item.title"
            :prepend-icon="item.icon"
            @click="activeTab = item.value"
          />
        </v-list>

        <template v-slot:append>
          <v-list>
            <v-list-item title="Déconnexion" prepend-icon="mdi-logout" @click="handleLogout" />
          </v-list>
        </template>
      </v-navigation-drawer>

      <v-main>
        <v-container fluid>
          <v-row>
            <v-col cols="12">
              <v-card>
                <v-tabs v-model="activeTab" bg-color="primary">
                  <v-tab value="dashboard">Tableau de bord</v-tab>
                  <v-tab value="participants">Participants</v-tab>
                  <v-tab value="prizes">Lots</v-tab>
                  <v-tab value="distributions">Répartition</v-tab>
                </v-tabs>

                <v-card-text>
                  <v-window v-model="activeTab">
                    <!-- Tableau de bord -->
                    <v-window-item value="dashboard">
                      <DashboardSummary />
                    </v-window-item>

                    <!-- Gestion des participants -->
                    <v-window-item value="participants">
                      <ParticipantsManager />
                    </v-window-item>

                    <!-- Gestion des lots -->
                    <v-window-item value="prizes">
                      <PrizesManager />
                    </v-window-item>

                    <!-- Répartition des lots -->
                    <v-window-item value="distributions">
                      <DistributionsManager />
                    </v-window-item>
                  </v-window>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-container>
      </v-main>
    </v-app>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'

// Composants nécessaires
import DashboardSummary from '~/components/admin/DashboardSummary.vue'
import ParticipantsManager from '~/components/admin/ParticipantsManager.vue'
import PrizesManager from '~/components/admin/PrizesManager.vue'
import DistributionsManager from '~/components/admin/DistributionsManager.vue'

definePageMeta({
  middleware: 'auth'
})

const router = useRouter()
const { user, logout } = useAuth()
const drawer = ref(true)
const activeTab = ref('dashboard')

// Items de navigation
const navItems = [
  { title: 'Tableau de bord', icon: 'mdi-view-dashboard', value: 'dashboard' },
  { title: 'Participants', icon: 'mdi-account-group', value: 'participants' },
  { title: 'Lots', icon: 'mdi-gift', value: 'prizes' },
  { title: 'Répartition', icon: 'mdi-calendar-month', value: 'distributions' }
]

// Initiale de l'utilisateur pour l'avatar
const userInitial = computed(() => {
  return user.value ? user.value.charAt(0).toUpperCase() : 'A'
})

// Gestion de la déconnexion
const handleLogout = () => {
  logout()
  router.push('/admin')
}
</script>
