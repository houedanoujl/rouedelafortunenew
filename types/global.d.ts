// Déclarer le type global pour la fenêtre avec notre extension Supabase
interface Window {
  __SUPABASE_OVERRIDE?: {
    url: string;
    key: string;
  };
}
