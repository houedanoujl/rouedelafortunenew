<template>
  <div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
      <div class="container">
        <NuxtLink class="navbar-brand" to="/">
          <img src="/assets/images/logo.png" alt="Logo" height="40" v-if="logoExists">
          <span v-else>La Roue de la Fortune</span>
        </NuxtLink>
        
        <button 
          class="navbar-toggler" 
          type="button" 
          data-bs-toggle="collapse" 
          data-bs-target="#navbarContent"
          aria-controls="navbarContent" 
          aria-expanded="false" 
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <NuxtLink class="nav-link" to="/">
                {{ $t('nav.home') }}
              </NuxtLink>
            </li>
            
            <li class="nav-item">
              <NuxtLink class="nav-link" to="/admin/prizes">
                {{ $t('nav.admin.prizes') }}
              </NuxtLink>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    
    <main class="main-content">
      <slot />
    </main>
    
    <footer class="footer mt-auto py-3 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <p class="mb-0">{{ $t('footer.copyright', { year: new Date().getFullYear() }) }}</p>
          </div>
          <div class="col-md-6 text-md-end">
            <button 
              class="btn btn-link" 
              type="button" 
              data-bs-toggle="modal" 
              data-bs-target="#termsModal"
            >
              {{ $t('footer.terms') }}
            </button>
            <button 
              class="btn btn-link" 
              type="button" 
              data-bs-toggle="modal" 
              data-bs-target="#privacyModal"
            >
              {{ $t('footer.privacy') }}
            </button>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useTranslation } from '~/composables/useTranslation';

const { t } = useTranslation();
const logoExists = ref(false);

onMounted(() => {
  // Vérifier si le logo existe
  const img = new Image();
  img.onload = () => { logoExists.value = true; };
  img.onerror = () => { logoExists.value = false; };
  img.src = '/assets/images/logo.png';
});
</script>

<style>
.navbar {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.main-content {
  min-height: calc(100vh - 150px);
}

.footer {
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.05);
}

.btn-link {
  color: #1d3557;
  text-decoration: none;
  padding: 0;
  margin-left: 1rem;
}

.btn-link:hover {
  text-decoration: underline;
}
</style>
