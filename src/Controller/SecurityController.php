namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    // ...

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Ce code ne sera jamais exécuté car Symfony gère la déconnexion avant d'arriver ici
        throw new \LogicException('Cette méthode peut rester vide - elle sera interceptée par la clé de déconnexion du pare-feu');
    }
}
