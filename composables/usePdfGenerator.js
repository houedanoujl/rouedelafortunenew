// Utilisation d'une importation dynamique pour éviter les problèmes de CJS/ESM
export function usePdfGenerator() {
  /**
   * Génère un PDF à partir d'un élément DOM
   * @param {HTMLElement} element - L'élément DOM à convertir en PDF
   * @param {Object} options - Options de génération
   * @param {string} options.filename - Nom du fichier PDF (sans extension)
   * @param {string} options.title - Titre à ajouter en haut du PDF
   * @param {string} options.subtitle - Sous-titre à ajouter sous le titre
   * @param {Object} options.participant - Données du participant
   * @param {Object} options.prize - Données du prix
   */
  const generatePdfFromElement = async (element, options = {}) => {
    if (!element) {
      console.error('Élément DOM non fourni pour la génération du PDF');
      return;
    }

    const {
      filename = 'qrcode-dinor',
      title = 'Votre QR Code DINOR',
      subtitle = 'Félicitations pour votre lot !',
      participant = null,
      prize = null
    } = options;

    try {
      // Importation dynamique des modules
      const html2canvas = await import('html2canvas').then(m => m.default || m);
      const jsPDF = await import('jspdf').then(m => m.default || m);

      // Création du canvas à partir de l'élément DOM
      const canvas = await html2canvas(element, {
        scale: 2, // Meilleure qualité
        useCORS: true,
        logging: false,
        backgroundColor: '#ffffff'
      });

      // Dimensions de la page A4
      const pageWidth = 210;
      const pageHeight = 297;
      
      // Création du PDF au format A4
      const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
      });

      // Ajouter le titre
      pdf.setFont('helvetica', 'bold');
      pdf.setFontSize(24);
      pdf.setTextColor(38, 50, 56); // Couleur foncée
      pdf.text(title, pageWidth / 2, 20, { align: 'center' });

      // Ajouter le sous-titre
      pdf.setFont('helvetica', 'normal');
      pdf.setFontSize(16);
      pdf.setTextColor(96, 125, 139); // Couleur plus claire
      pdf.text(subtitle, pageWidth / 2, 30, { align: 'center' });

      // Ajouter les informations du participant si disponibles
      if (participant) {
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(14);
        pdf.setTextColor(76, 175, 80); // Vert
        pdf.text('Informations du participant', pageWidth / 2, 45, { align: 'center' });
        
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(12);
        pdf.setTextColor(33, 33, 33); // Presque noir
        
        let y = 55;
        if (participant.first_name && participant.last_name) {
          pdf.text(`Nom: ${participant.first_name} ${participant.last_name}`, 20, y);
          y += 7;
        }
        if (participant.phone) {
          pdf.text(`Téléphone: ${participant.phone}`, 20, y);
          y += 7;
        }
      }

      // Ajouter les informations du lot si disponibles
      if (prize) {
        pdf.setFont('helvetica', 'bold');
        pdf.setFontSize(14);
        pdf.setTextColor(76, 175, 80); // Vert
        pdf.text('Détails du lot', pageWidth / 2, participant ? 75 : 45, { align: 'center' });
        
        pdf.setFont('helvetica', 'normal');
        pdf.setFontSize(12);
        pdf.setTextColor(33, 33, 33); // Presque noir
        
        let y = participant ? 85 : 55;
        if (prize.name) {
          pdf.text(`Lot: ${prize.name}`, 20, y);
          y += 7;
        }
        if (prize.description) {
          pdf.text(`Description: ${prize.description}`, 20, y);
          y += 7;
        }
      }

      // Calculer la position du QR code
      const qrY = (participant || prize) ? 105 : 45;
      
      // Dimensions du QR code dans le PDF
      const qrWidth = 100; // mm
      const qrHeight = (canvas.height * qrWidth) / canvas.width;

      // Ajouter le QR code centré
      const imageData = canvas.toDataURL('image/png');
      pdf.addImage(
        imageData, 
        'PNG', 
        (pageWidth - qrWidth) / 2, // Centrer horizontalement
        qrY, 
        qrWidth, 
        qrHeight
      );

      // Ajouter un pied de page
      pdf.setFont('helvetica', 'italic');
      pdf.setFontSize(10);
      pdf.setTextColor(158, 158, 158); // Gris clair
      pdf.text(
        'Présentez ce QR code pour récupérer votre lot. Valable une fois uniquement.',
        pageWidth / 2,
        pageHeight - 20,
        { align: 'center' }
      );
      
      pdf.text(
        `Généré le ${new Date().toLocaleDateString()}`,
        pageWidth / 2,
        pageHeight - 15,
        { align: 'center' }
      );

      // Télécharger le PDF
      pdf.save(`${filename}.pdf`);
      
      return true;
    } catch (error) {
      console.error('Erreur lors de la génération du PDF:', error);
      return false;
    }
  };

  return {
    generatePdfFromElement
  };
}
