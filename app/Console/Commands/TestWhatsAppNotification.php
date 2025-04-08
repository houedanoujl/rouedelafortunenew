<?php

namespace App\Console\Commands;

use App\Services\InfobipService;
use Illuminate\Console\Command;

class TestWhatsAppNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {phone} {name} {qrcode?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test l\'envoi de notifications WhatsApp via Infobip';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');
        $name = $this->argument('name');
        $qrCode = $this->argument('qrcode') ?? 'DNR70-TEST' . rand(1000, 9999);

        $this->info("Envoi d'une notification WhatsApp de test à $name ($phone) avec le code $qrCode");
        
        try {
            $service = new InfobipService();
            $result = $service->sendWhatsAppNotification($phone, $name, $qrCode);
            
            if ($result) {
                $this->info("✅ Notification envoyée avec succès!");
                $this->info("Réponse de l'API: " . json_encode($result, JSON_PRETTY_PRINT));
            } else {
                $this->error("❌ Échec de l'envoi. Vérifiez les logs d'erreur pour plus de détails.");
            }
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'envoi: " . $e->getMessage());
        }
    }
}
