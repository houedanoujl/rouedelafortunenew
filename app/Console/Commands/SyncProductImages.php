<?php

namespace App\Console\Commands;

use App\Models\Prize;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncProductImages extends Command
{
    /**
     * Le nom et la signature de la commande console.
     *
     * @var string
     */
    protected $signature = 'app:sync-product-images {--direction=both : Direction de synchronisation (local-to-prod, prod-to-local, both)}';

    /**
     * La description de la commande console.
     *
     * @var string
     */
    protected $description = 'Synchronise les images des produits entre l\'environnement local et la production';

    /**
     * URL de l'API de production
     * 
     * @var string
     */
    protected $productionApiUrl;

    /**
     * Token d'API pour l'authentification
     * 
     * @var string
     */
    protected $apiToken;

    /**
     * Exécute la commande console.
     */
    public function handle()
    {
        $this->productionApiUrl = env('PRODUCTION_API_URL', '');
        $this->apiToken = env('PRODUCTION_API_TOKEN', '');

        if (empty($this->productionApiUrl) || empty($this->apiToken)) {
            $this->error('La configuration de l\'API de production est manquante. Veuillez définir PRODUCTION_API_URL et PRODUCTION_API_TOKEN dans le fichier .env');
            return 1;
        }

        $direction = $this->option('direction');
        
        $this->info('Démarrage de la synchronisation des images des produits...');
        
        switch ($direction) {
            case 'local-to-prod':
                $this->syncLocalToProd();
                break;
            case 'prod-to-local':
                $this->syncProdToLocal();
                break;
            case 'both':
                $this->syncLocalToProd();
                $this->syncProdToLocal();
                break;
            default:
                $this->error('Direction invalide. Utilisez local-to-prod, prod-to-local ou both');
                return 1;
        }
        
        $this->info('Synchronisation terminée !');
        
        return 0;
    }

    /**
     * Synchronise les images locales vers la production
     */
    private function syncLocalToProd()
    {
        $this->info('Synchronisation des images locales vers la production...');
        
        // Récupérer tous les produits avec des images locales
        $prizes = Prize::whereNotNull('image_url')->get();
        
        $bar = $this->output->createProgressBar(count($prizes));
        $bar->start();
        
        foreach ($prizes as $prize) {
            try {
                if ($this->isLocalImage($prize->image_url)) {
                    // Télécharger l'image locale
                    $imageContent = $this->getLocalImageContent($prize->image_url);
                    
                    if ($imageContent) {
                        // Envoyer l'image à la production
                        $response = Http::withToken($this->apiToken)
                            ->attach('image', $imageContent, basename($prize->image_url))
                            ->post("{$this->productionApiUrl}/api/prizes/{$prize->id}/upload-image", [
                                'prize_id' => $prize->id,
                            ]);
                        
                        if ($response->successful()) {
                            $this->line(' ');
                            $this->line("✅ Image synchronisée pour {$prize->name}");
                        } else {
                            $this->line(' ');
                            $this->warn("⚠️ Échec de la synchronisation pour {$prize->name} : " . $response->body());
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->line(' ');
                $this->error("❌ Erreur lors de la synchronisation de {$prize->name} : " . $e->getMessage());
                Log::error("Erreur lors de la synchronisation de l'image du produit {$prize->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->line(' ');
        $this->info('Synchronisation locale → production terminée !');
    }

    /**
     * Synchronise les images de production vers local
     */
    private function syncProdToLocal()
    {
        $this->info('Synchronisation des images de production vers local...');
        
        try {
            // Récupérer la liste des produits depuis l'API de production
            $response = Http::withToken($this->apiToken)
                ->get("{$this->productionApiUrl}/api/prizes");
            
            if (!$response->successful()) {
                $this->error('Impossible de récupérer les produits depuis la production : ' . $response->body());
                return;
            }
            
            $productionPrizes = $response->json('data', []);
            
            $bar = $this->output->createProgressBar(count($productionPrizes));
            $bar->start();
            
            foreach ($productionPrizes as $prodPrize) {
                try {
                    // Trouver le produit local correspondant
                    $localPrize = Prize::find($prodPrize['id']);
                    
                    if (!$localPrize) {
                        $this->line(' ');
                        $this->warn("⚠️ Produit non trouvé localement : {$prodPrize['name']} (ID: {$prodPrize['id']})");
                        continue;
                    }
                    
                    // Vérifier si le produit de production a une image et si elle est différente de celle en local
                    if (!empty($prodPrize['image_url']) && $this->isRemoteImage($prodPrize['image_url'])) {
                        // Télécharger l'image depuis la production
                        $imageResponse = Http::get($prodPrize['image_url']);
                        
                        if ($imageResponse->successful()) {
                            $imageContent = $imageResponse->body();
                            $imageName = basename($prodPrize['image_url']);
                            $path = "public/prizes/{$imageName}";
                            
                            // Enregistrer l'image localement
                            Storage::put($path, $imageContent);
                            
                            // Mettre à jour l'URL de l'image dans la base de données locale
                            $localPrize->image_url = Storage::url($path);
                            $localPrize->save();
                            
                            $this->line(' ');
                            $this->line("✅ Image téléchargée pour {$localPrize->name}");
                        } else {
                            $this->line(' ');
                            $this->warn("⚠️ Impossible de télécharger l'image pour {$localPrize->name}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->line(' ');
                    $this->error("❌ Erreur lors de la synchronisation de {$prodPrize['name']} : " . $e->getMessage());
                    Log::error("Erreur lors de la synchronisation de l'image du produit {$prodPrize['id']}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->line(' ');
            $this->info('Synchronisation production → locale terminée !');
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la récupération des produits : " . $e->getMessage());
            Log::error("Erreur lors de la récupération des produits depuis la production", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Vérifie si une URL d'image est locale
     */
    private function isLocalImage($url)
    {
        return strpos($url, 'http') !== 0 || strpos($url, request()->getHost()) !== false;
    }

    /**
     * Vérifie si une URL d'image est distante
     */
    private function isRemoteImage($url)
    {
        return strpos($url, 'http') === 0 && strpos($url, request()->getHost()) === false;
    }

    /**
     * Récupère le contenu d'une image locale
     */
    private function getLocalImageContent($url)
    {
        if (strpos($url, '/storage/') !== false) {
            $path = str_replace('/storage/', 'public/', $url);
            if (Storage::exists($path)) {
                return Storage::get($path);
            }
        }
        
        // Si l'image est accessible via une URL, essayer de la récupérer
        $fullUrl = url($url);
        try {
            $response = Http::get($fullUrl);
            return $response->successful() ? $response->body() : null;
        } catch (\Exception $e) {
            $this->warn("⚠️ Impossible de récupérer l'image locale : {$url}");
            return null;
        }
    }
}
