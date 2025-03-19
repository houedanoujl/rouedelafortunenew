# Mise à jour de la gestion des lots

Cette mise à jour ajoute une fonctionnalité de suivi des lots disponibles et permet de ne pas attribuer un lot lorsque sa quantité disponible est épuisée.

## Modifications apportées

1. Ajout des colonnes dans la table `prize` :
   - `remaining` : nombre de lots restants (INTEGER)
   - `won_date` : dates de gain de chaque lot (JSONB - format tableau)

2. Mise à jour des fonctions dans `FortuneWheel.vue` :
   - `determineWinningOutcome()` : vérifie maintenant si des lots avec `remaining > 0` sont disponibles
   - `determinePrize()` : ne sélectionne que des lots avec `remaining > 0`
   - `saveEntryToDatabase()` : met à jour `remaining` et `won_date` lors d'un gain

3. Ajout d'un déclencheur (trigger) dans la base de données :
   - Décrémente automatiquement `remaining` lorsqu'un lot est gagné
   - S'exécute après l'insertion d'une nouvelle entrée dans la table `entry`

## Scripts SQL

Deux scripts SQL ont été ajoutés pour faciliter la gestion des données (compatibles avec Supabase) :

1. `migration_add_remaining_won_date.sql` : 
   - Ajoute les colonnes `remaining` (INTEGER) et `won_date` (JSONB) à la table `prize`
   - Crée une fonction et un déclencheur pour mettre à jour automatiquement `remaining`

2. `initialize_remaining_values.sql` : 
   - Initialise les valeurs de `remaining` à partir de `total_quantity`
   - Décompte les lots déjà gagnés pour mettre à jour `remaining`
   - Construit les tableaux JSON pour `won_date` à partir des données existantes

## Exécution des scripts

Pour exécuter ces scripts, utilisez l'éditeur SQL de Supabase :

1. Dans le tableau de bord Supabase, accédez à **SQL Editor**
2. Copiez et collez le contenu de `migration_add_remaining_won_date.sql` et exécutez-le
3. Ensuite, copiez et collez le contenu de `initialize_remaining_values.sql` et exécutez-le

## Logique de fonctionnement

- Lorsqu'un participant joue, l'application vérifie s'il reste des lots disponibles (`remaining > 0`).
- Si aucun lot n'est disponible, le participant ne peut pas gagner.
- Lorsqu'un lot est gagné :
  - Sa quantité restante (`remaining`) est décrémentée automatiquement par le déclencheur
  - La date du gain est ajoutée au tableau JSON `won_date`
- Lorsque `remaining` atteint 0, le lot n'est plus attribué aux participants.

## Format des données

- `won_date` est stocké au format JSONB (JSON binaire) compatible avec Supabase
- Il contient un tableau de chaînes ISO 8601 représentant les dates de gain
- Exemple : `["2025-03-19T10:30:00Z", "2025-03-20T14:22:10Z"]`
