// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  
  // Runtime config for environment variables
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_API_BASE || 'http://localhost:8888',
      supabaseUrl: process.env.SUPABASE_URL || '',
      supabaseKey: process.env.SUPABASE_KEY || '',
      databaseUrl: process.env.DATABASE_URL || 'mysql://user:password@mysql:3306/rouedelafortune',
      mockMode: process.env.MOCK_MODE === 'true',
      useMySQL: process.env.USE_MYSQL === 'true'
    }
  },
  
  // Auto-import components
  components: true,
  
  // Enable CSS assets
  css: [
    '@mdi/font/css/materialdesignicons.css',
    'vuetify/styles',
    'bootstrap/dist/css/bootstrap.min.css',
    '@/assets/css/main.css'
  ],
  
  // Client-side script setup
  app: {
    head: {
      link: [
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;500;600;700&display=swap'
        }
      ],
      script: [
        {
          src: 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
          integrity: 'sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL',
          crossorigin: 'anonymous',
          defer: true
        }
      ]
    }
  },
  
  build: {
    transpile: ['vuetify'],
  },
  
  modules: [
    '@nuxtjs/i18n'
  ],
  
  i18n: {
    locales: ['fr'],
    defaultLocale: 'fr',
    vueI18n: './i18n.config.ts'
  },
  
  // Pour résoudre le problème de routage
  pages: true
})
