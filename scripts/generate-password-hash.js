// Script pour générer un hash bcrypt pour un nouveau mot de passe
const bcrypt = require('bcryptjs');

// Remplacez ce mot de passe par celui que vous souhaitez utiliser
const newPassword = 'votre_nouveau_mot_de_passe';

// Générer le hash avec un salt de 10 rounds
const passwordHash = bcrypt.hashSync(newPassword, 10);

console.log('Nouveau mot de passe:', newPassword);
console.log('Hash bcrypt correspondant:', passwordHash);
console.log('\nRemplacez la valeur dans db/init-mysql-admin.sql par ce hash.');
