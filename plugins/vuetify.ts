// plugins/vuetify.ts
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

export default defineNuxtPlugin(nuxtApp => {
  const vuetify = createVuetify({
    components,
    directives,
    theme: {
      defaultTheme: 'light',
      themes: {
        light: {
          dark: false,
          colors: {
            primary: '#FF9800',   // Orange - Couleur DINOR
            secondary: '#FFC107', // Jaune-orange
            accent: '#03A9F4',    // Bleu clair
            error: '#FF5252',     // Rouge
            info: '#2196F3',      // Bleu
            success: '#4CAF50',   // Vert
            warning: '#FFC107'    // Jaune-orange
          }
        }
      }
    }
  })

  nuxtApp.vueApp.use(vuetify)
})
