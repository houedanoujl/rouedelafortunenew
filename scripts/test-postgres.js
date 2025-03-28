const { Pool } = require('pg');

// Configuration de la connexion PostgreSQL
const pool = new Pool({
  host: process.env.POSTGRES_HOST || 'localhost',
  user: process.env.POSTGRES_USER || 'postgres',
  password: process.env.POSTGRES_PASSWORD || 'postgres',
  database: process.env.POSTGRES_DATABASE || 'rouedelafortune',
  port: parseInt(process.env.POSTGRES_PORT || '5432')
});

async function testConnection() {
  console.log('Tentative de connexion à PostgreSQL...');
  
  try {
    // Test de connexion simple
    const client = await pool.connect();
    console.log('Connexion à PostgreSQL établie avec succès!');
    
    // Vérifier si les tables existent
    const tablesResult = await client.query(`
      SELECT table_name 
      FROM information_schema.tables 
      WHERE table_schema = 'public'
      ORDER BY table_name;
    `);
    
    console.log('\nTables existantes dans la base de données:');
    if (tablesResult.rows.length === 0) {
      console.log('Aucune table trouvée.');
    } else {
      tablesResult.rows.forEach(row => {
        console.log(`- ${row.table_name}`);
      });
    }
    
    // Compter les participants
    const participantResult = await client.query('SELECT COUNT(*) FROM participant');
    console.log(`\nNombre de participants: ${participantResult.rows[0].count}`);
    
    // Compter les prix
    const prizeResult = await client.query('SELECT COUNT(*) FROM prize');
    console.log(`Nombre de prix: ${prizeResult.rows[0].count}`);
    
    // Compter les utilisateurs admin
    const adminResult = await client.query('SELECT COUNT(*) FROM admin_user');
    console.log(`Nombre d'administrateurs: ${adminResult.rows[0].count}`);
    
    // Libérer le client
    client.release();
    
    console.log('\nTest de connexion terminé avec succès!');
  } catch (error) {
    console.error('Erreur lors du test de connexion à PostgreSQL:', error);
  } finally {
    // Fermer le pool
    await pool.end();
  }
}

// Exécuter le test
testConnection();
