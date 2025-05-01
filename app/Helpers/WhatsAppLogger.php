<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class WhatsAppLogger
{
    const LOG_FILE = 'whatsapp.log';
    const MAX_LOG_SIZE = 10485760; // 10 MB
    
    /**
     * Log un message WhatsApp envoyé avec succès
     * 
     * @param string $phone Numéro de téléphone
     * @param string $message Message envoyé
     * @param array $additional Données additionnelles
     * @return void
     */
    public static function success(string $phone, string $message, array $additional = [])
    {
        $data = array_merge([
            'status' => 'success',
            'phone' => $phone,
            'message' => self::truncateMessage($message),
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ], $additional);
        
        self::writeLog($data);
    }
    
    /**
     * Log un message WhatsApp échoué
     * 
     * @param string $phone Numéro de téléphone
     * @param string $message Message envoyé
     * @param string $error Message d'erreur
     * @param array $additional Données additionnelles
     * @return void
     */
    public static function error(string $phone, string $message, string $error, array $additional = [])
    {
        $data = array_merge([
            'status' => 'error',
            'phone' => $phone,
            'message' => self::truncateMessage($message),
            'error' => $error,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ], $additional);
        
        self::writeLog($data);
    }
    
    /**
     * Tronque un long message pour le log
     */
    private static function truncateMessage(string $message, int $length = 100): string
    {
        if (strlen($message) <= $length) {
            return $message;
        }
        
        return substr($message, 0, $length) . '...';
    }
    
    /**
     * Écrit dans le fichier log
     */
    private static function writeLog(array $data)
    {
        try {
            // Chemin complet du fichier log
            $logPath = storage_path('logs/' . self::LOG_FILE);
            
            // Vérifier si le fichier existe et est trop grand
            if (File::exists($logPath) && File::size($logPath) > self::MAX_LOG_SIZE) {
                // Renommer le fichier actuel avec un timestamp
                $timestamp = now()->format('Y-m-d-His');
                File::move($logPath, storage_path('logs/whatsapp-' . $timestamp . '.log'));
            }
            
            // Format du log
            $logLine = json_encode($data) . PHP_EOL;
            
            // Écrire dans le fichier
            File::append($logPath, $logLine);
            
            // Également écrire dans le log Laravel standard
            Log::channel('daily')->info('WhatsApp Message', $data);
            
        } catch (\Exception $e) {
            // En cas d'erreur, logger dans le log standard
            Log::error('Erreur d\'écriture du log WhatsApp', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }
}
