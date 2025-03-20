// Script pour exporter les données de Supabase vers des fichiers JSON locaux
const { createClient } = require('@supabase/supabase-js');
const fs = require('fs');
const path = require('path');

// Créer le dossier d'export s'il n'existe pas
const exportDir = path.join(__dirname, '..', 'supabase-local', 'data');
if (!fs.existsSync(exportDir)) {
  fs.mkdirSync(exportDir, { recursive: true });
}

// Paramètres Supabase (utilisez vos propres valeurs)
const supabaseUrl = process.env.SUPABASE_URL || 'https://qwlzxerivnbuxejqxjyu.supabase.co';
const supabaseKey = process.env.SUPABASE_KEY || 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InF3bHp4ZXJpdm5idXhlanF4anl1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3MzczODUxMTIsImV4cCI6MjA1Mjk2MTExMn0.0zbZQwhTjx0YfE-j18vHDM1rPmmOT9PLznVjNzE2Fhk';

// Liste des tables à exporter
const tables = ['participant', 'prize', 'entry', 'contest', 'qr_codes'];

// Fonction principale pour exporter les données
async function exportData() {
  try {
    // Initialiser le client Supabase
    const supabase = createClient(supabaseUrl, supabaseKey);
    console.log('Connexion à Supabase établie');
    
    // Exporter chaque table
    for (const table of tables) {
      console.log(`Exportation de la table: ${table}`);
      
      // Récupérer toutes les données de la table
      const { data, error } = await supabase
        .from(table)
        .select('*');
      
      if (error) {
        console.error(`Erreur lors de l'exportation de ${table}:`, error);
        continue;
      }
      
      if (!data || data.length === 0) {
        console.log(`Aucune donnée trouvée dans la table ${table}`);
        continue;
      }
      
      // Écrire les données dans un fichier JSON
      const filePath = path.join(exportDir, `${table}.json`);
      fs.writeFileSync(filePath, JSON.stringify(data, null, 2));
      
      console.log(`✅ ${data.length} lignes exportées vers ${filePath}`);
    }
    
    console.log('Exportation terminée avec succès!');
  } catch (error) {
    console.error('Erreur lors de l\'exportation:', error);
  }
}

// Exécuter la fonction d'exportation
exportData();
