export default defineI18nConfig(() => ({
  legacy: false,
  locale: 'fr',
  messages: {
    fr: {
      app: {
        title: 'Jeu dinor 70 ans',
        subtitle: 'Tentez votre chance et gagnez des prix incroyables !',
        footer: {
          copyright: '2023 Jeu dinor 70 ans. Tous droits réservés.',
          disclaimer: 'Ce jeu est soumis à des conditions générales. Veuillez consulter le règlement pour plus d\'informations.'
        },
        buttons: {
          newParticipant: 'Nouveau participant',
          backToHome: 'Retour à l\'accueil',
          tryAnotherParticipant: 'Essayer avec un autre numéro',
          viewPrizeDetails: 'Voir les détails du lot'
        },
      },
      registration: {
        title: 'Inscription au Jeu dinor 70 ans',
        description: 'Remplissez le formulaire ci-dessous pour participer et tenter de gagner des lots exceptionnels !',
        fields: {
          firstName: {
            label: 'Prénom',
            placeholder: 'Votre prénom'
          },
          lastName: {
            label: 'Nom',
            placeholder: 'Votre nom'
          },
          phone: {
            label: 'Téléphone',
            placeholder: 'Votre numéro de téléphone'
          },
          email: {
            label: 'Email',
            placeholder: 'Votre adresse email (optionnel)'
          },
          terms: {
            label: 'J\'accepte les conditions générales et la politique de confidentialité',
            viewLink: 'Voir le détail'
          }
        },
        termsModal: {
          title: 'Conditions générales et politique de confidentialité',
          tabTerms: 'Conditions générales',
          tabPrivacy: 'Politique de confidentialité',
          termsTitle: 'Conditions générales d\'utilisation',
          termsContent: '<p>Bienvenue dans le "Jeu dinor 70 ans". En participant à ce jeu, vous acceptez les conditions suivantes :</p><p>1. <strong>Admissibilité :</strong> Ce jeu est ouvert à toute personne physique majeure au moment de sa participation.</p><p>2. <strong>Participation :</strong> Chaque participant ne peut jouer qu\'une seule fois avec le même numéro de téléphone sur une période définie.</p><p>3. <strong>Prix :</strong> Les prix sont définis à l\'avance et ne sont pas négociables. Ils ne peuvent être ni échangés, ni remboursés.</p><p>4. <strong>Attribution des prix :</strong> L\'attribution des prix est aléatoire et dépend uniquement du résultat de la roue.</p><p>5. <strong>Réclamation :</strong> Les gagnants seront contactés par SMS pour récupérer leur prix. Tout prix non réclamé dans les 30 jours sera perdu.</p><p>6. <strong>Responsabilité :</strong> L\'organisateur ne peut être tenu responsable des problèmes techniques ou de communication empêchant la participation.</p>',
          privacyTitle: 'Politique de confidentialité',
          privacyContent: '<p>La protection de vos données personnelles est importante pour nous. Cette politique de confidentialité explique comment nous utilisons et protégeons vos informations.</p><p>1. <strong>Données collectées :</strong> Nous collectons votre nom, prénom, numéro de téléphone et email (facultatif) uniquement pour administrer le jeu.</p><p>2. <strong>Utilisation des données :</strong> Vos données sont utilisées exclusivement pour :</p><ul><li>Vérifier votre éligibilité au jeu</li><li>Vous contacter en cas de gain</li><li>Prévenir les participations multiples</li></ul><p>3. <strong>Conservation :</strong> Vos données sont conservées pendant une durée maximale de 12 mois après votre participation.</p><p>4. <strong>Partage :</strong> Nous ne partageons jamais vos données avec des tiers à des fins commerciales.</p><p>5. <strong>Vos droits :</strong> Vous disposez d\'un droit d\'accès, de rectification et de suppression de vos données personnelles.</p><p>6. <strong>Contact :</strong> Pour toute question relative à vos données, veuillez nous contacter à l\'adresse indiquée sur notre site.</p>',
          closeButton: 'Fermer'
        },
        buttons: {
          submit: 'S\'inscrire et jouer',
          loading: 'Chargement...'
        },
        messages: {
          required: 'Veuillez remplir tous les champs obligatoires et accepter les conditions.',
          success: 'Inscription réussie !',
          error: 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.',
          demoSuccess: 'Inscription réussie en mode démonstration !',
          networkError: 'Erreur de connexion réseau. Vérifiez votre connexion internet et réessayez.',
          databaseError: 'Erreur d\'accès à la base de données. Veuillez réessayer ultérieurement.',
          validationError: 'Certaines informations saisies sont incorrectes. Veuillez vérifier et réessayer.',
          unknownError: 'Une erreur inconnue est survenue. Veuillez contacter l\'administrateur.',
          serverSideOnly: 'Cette opération ne peut être effectuée que côté serveur. Un instant, s\'il vous plaît.'
        }
      },
      footer: {
        terms: 'Conditions générales',
        privacy: 'Politique de confidentialité',
        copyright: ' 2023 Jeu dinor 70 ans'
      },
      errors: {
        mysql: {
          clientSide: 'Impossible d\'accéder à la base de données depuis le navigateur. Opération redirigée vers le serveur.'
        }
      }
    }
  }
}))
