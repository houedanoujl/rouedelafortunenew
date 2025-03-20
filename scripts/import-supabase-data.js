// Script pour importer les données JSON dans la base de données Supabase locale
const { createClient } = require('@supabase/supabase-js');
const fs = require('fs');
const path = require('path');

// Paramètres de la base de données locale
const supabaseUrl = 'http://localhost:8000';
const supabaseKey = 'your-super-secret-jwt-token-with-at-least-32-characters'; // Doit correspondre à GOTRUE_JWT_SECRET

// Liste des tables à importer dans l'ordre pour respecter les dépendances
const tables = ['participant', 'prize', 'contest', 'entry', 'qr_codes'];

// Dossier contenant les fichiers JSON exportés
const dataDir = path.join(__dirname, '..', 'supabase-local', 'data');

// Fonction pour vider une table
async function clearTable(supabase, tableName) {
  console.log(`Vidage de la table: ${tableName}`);
  
  try {
    const { error } = await supabase.rpc('truncate_table', { table_name: tableName });
    
    if (error) {
      console.error(`Échec du vidage de la table ${tableName}:`, error);
      // Méthode alternative
      const { error: deleteError } = await supabase
        .from(tableName)
        .delete()
        .not('id', 'is', null);
      
      if (deleteError) {
        console.error(`Échec de la suppression des données de ${tableName}:`, deleteError);
      }
    }
  } catch (e) {
    console.error(`Exception lors du vidage de ${tableName}:`, e);
  }
}

// Fonction principale d'importation
async function importData() {
  try {
    // Vérifier que le répertoire de données existe
    if (!fs.existsSync(dataDir)) {
      console.error(`Le répertoire de données n'existe pas: ${dataDir}`);
      return;
    }
    
    // Initialiser le client Supabase local
    const supabase = createClient(supabaseUrl, supabaseKey);
    console.log('Connexion à Supabase local établie');
    
    // Désactiver temporairement les contraintes de clé étrangère
    // Note: Cette fonctionnalité nécessite des droits administrateur
    try {
      await supabase.rpc('disable_foreign_keys');
      console.log('Contraintes de clé étrangère désactivées');
    } catch (e) {
      console.log('Impossible de désactiver les contraintes de clé étrangère:', e);
      console.log('L\'importation continuera mais pourrait échouer si l\'ordre des tables n\'est pas correct');
    }
    
    // Importer chaque table
    for (const table of tables) {
      const filePath = path.join(dataDir, `${table}.json`);
      
      // Vérifier si le fichier JSON existe
      if (!fs.existsSync(filePath)) {
        console.log(`Fichier non trouvé pour la table ${table}: ${filePath}`);
        continue;
      }
      
      // Lire les données
      const jsonData = fs.readFileSync(filePath, 'utf8');
      const data = JSON.parse(jsonData);
      
      if (!data || data.length === 0) {
        console.log(`Aucune donnée à importer pour la table ${table}`);
        continue;
      }
      
      // Vider la table avant importation
      await clearTable(supabase, table);
      
      console.log(`Importation de ${data.length} lignes dans la table ${table}`);
      
      // Importer les données par lots de 100 pour éviter les limitations
      const batchSize = 100;
      for (let i = 0; i < data.length; i += batchSize) {
        const batch = data.slice(i, i + batchSize);
        
        const { error } = await supabase
          .from(table)
          .insert(batch)
          .select();
        
        if (error) {
          console.error(`Erreur lors de l'importation du lot ${i / batchSize + 1} dans ${table}:`, error);
        } else {
          console.log(`Lot ${i / batchSize + 1}/${Math.ceil(data.length / batchSize)} importé dans ${table}`);
        }
      }
    }
    
    // Réactiver les contraintes de clé étrangère
    try {
      await supabase.rpc('enable_foreign_keys');
      console.log('Contraintes de clé étrangère réactivées');
    } catch (e) {
      console.log('Impossible de réactiver les contraintes de clé étrangère:', e);
    }
    
    console.log('Importation terminée avec succès!');
  } catch (error) {
    console.error('Erreur lors de l\'importation:', error);
  }
}

// Fonction utilitaire pour créer la procédure stockée de gestion des contraintes
async function createUtilityFunctions(supabase) {
  // Création de fonctions utilitaires si nécessaires
  try {
    // Créer une fonction pour désactiver les contraintes de clé étrangère
    await supabase.rpc('create_disable_fk_function');
  } catch (e) {
    console.log('Les fonctions utilitaires existent peut-être déjà:', e);
  }
}

// Exécuter la fonction d'importation
importData();
