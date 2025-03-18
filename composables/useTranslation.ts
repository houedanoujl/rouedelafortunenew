import { ref, computed } from 'vue';
import frLocale from '~/locales/fr.json';

// Définir le type pour les messages de traduction
type LocaleMessages = {
  [key: string]: any;
};

export function useTranslation() {
  const locale = ref('fr');
  const messages = ref<LocaleMessages>({
    fr: frLocale
  });

  /**
   * Obtient une valeur de traduction à partir d'un chemin de clé
   * @param key - Chemin de la clé au format 'section.subsection.key'
   * @returns La valeur traduite ou la clé si non trouvée
   */
  const t = (key: string): string => {
    const path = key.split('.');
    let value: any = messages.value[locale.value];
    
    for (const segment of path) {
      if (!value || typeof value !== 'object') {
        return key; // Retourne la clé si le chemin n'est pas valide
      }
      value = value[segment];
    }
    
    return typeof value === 'string' ? value : key;
  };

  /**
   * Change la locale actuelle
   * @param newLocale - Code de la nouvelle locale
   */
  const setLocale = (newLocale: string): void => {
    if (messages.value[newLocale]) {
      locale.value = newLocale;
    } else {
      console.warn(`Locale ${newLocale} not available`);
    }
  };

  /**
   * Obtient la locale actuelle
   */
  const currentLocale = computed(() => locale.value);

  return {
    t,
    setLocale,
    currentLocale
  };
}
