<template>
  <div>
    <main class="main-content">
      <slot />
    </main>
    
    <footer class="footer mt-auto py-3 bg-light">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <p class="mb-0">{{ t('footer.copyright', { year: new Date().getFullYear() }) }}</p>
          </div>
          <div class="col-md-6 text-md-end">
            <button 
              class="btn btn-link" 
              type="button" 
              data-bs-toggle="modal" 
              data-bs-target="#termsModal"
            >
              {{ t('footer.terms') }}
            </button>
            <button 
              class="btn btn-link" 
              type="button" 
              data-bs-toggle="modal" 
              data-bs-target="#privacyModal"
            >
              {{ t('footer.privacy') }}
            </button>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { useTranslation } from '~/composables/useTranslation';

const { t } = useTranslation();
</script>

<style>
.main-content {
  min-height: calc(100vh - 150px);
  padding: 2rem 0;
}

.footer {
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.05);
  background-color: #FCFEFFff !important; /* White */
  color: #87664Bff; /* Raw Umber */
}

.btn-link {
  color: #87664Bff; /* Raw Umber */
  text-decoration: none;
  padding: 0;
  margin-left: 1rem;
}

.btn-link:hover {
  text-decoration: underline;
  color: #BD2B23ff; /* Fire Brick */
}

.bg-light {
  background-color: #FCFEFFff !important; /* White */
}
</style>
