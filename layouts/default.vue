<template>
  <div>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container">
        <a class="navbar-brand" href="/">Roue de la Fortune DINOR</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <li class="nav-item">
              <a class="nav-link" href="/">Accueil</a>
            </li>
          </ul>
          <div class="d-flex">
            <a href="/admin" class="btn btn-outline-primary">Administration</a>
          </div>
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
            <p class="mb-0">{{ t('footer.copyright', { year: new Date().getFullYear() }) }}</p>
          </div>
          <div class="col-md-6 text-end">
            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#termsModal">
              Conditions Générales
            </button>
            <button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#privacyModal">
              Politique de Confidentialité
            </button>
          </div>
        </div>
      </div>
    </footer>
    
    <!-- Modal Conditions Générales -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="termsModalLabel">{{ t('footer.terms') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <h4>{{ t('registration.termsModal.termsTitle') }}</h4>
            <div v-html="t('registration.termsModal.termsContent')"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('registration.termsModal.closeButton') }}</button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modal Politique de Confidentialité -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="privacyModalLabel">{{ t('footer.privacy') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <h4>{{ t('registration.termsModal.privacyTitle') }}</h4>
            <div v-html="t('registration.termsModal.privacyContent')"></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('registration.termsModal.closeButton') }}</button>
          </div>
        </div>
      </div>
    </div>
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

.navbar {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  background-color: #FCFEFFff !important; /* White */
}

.navbar-brand {
  color: #87664Bff; /* Raw Umber */
  font-weight: bold;
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

.btn-outline-primary {
  color: #FF9800; /* Orange DINOR */
  border-color: #FF9800;
}

.btn-outline-primary:hover {
  background-color: #FF9800;
  border-color: #FF9800;
  color: white;
}
</style>
