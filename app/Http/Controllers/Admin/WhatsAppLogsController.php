<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class WhatsAppLogsController extends Controller
{
    /**
     * Affiche les logs WhatsApp dans une interface web
     */
    public function index(Request $request)
    {
        // Vérifier l'authentification
        if (!auth()->check()) {
            return redirect()->route('filament.admin.auth.login');
        }
        
        $logPath = storage_path('logs/whatsapp.log');
        $logs = [];
        
        if (File::exists($logPath)) {
            $limit = $request->input('limit', 100);
            $statusFilter = $request->input('status', 'all');
            $typeFilter = $request->input('type', 'all');
            $search = $request->input('search', '');
            
            // Lire le fichier log
            $logContent = File::get($logPath);
            $logLines = array_filter(explode(PHP_EOL, $logContent));
            
            // Parcourir les lignes en sens inverse (les plus récentes d'abord)
            $logLines = array_reverse($logLines);
            
            $count = 0;
            
            foreach ($logLines as $line) {
                if ($count >= $limit) {
                    break;
                }
                
                $data = json_decode($line, true);
                
                if (!$data) {
                    continue;
                }
                
                // Filtrer par statut si nécessaire
                if ($statusFilter !== 'all' && $data['status'] !== $statusFilter) {
                    continue;
                }
                
                // Filtrer par type si nécessaire
                if ($typeFilter !== 'all' && (!isset($data['type']) || $data['type'] !== $typeFilter)) {
                    continue;
                }
                
                // Filtrer par recherche si nécessaire
                if (!empty($search)) {
                    $searchFields = ['phone', 'message', 'error'];
                    $found = false;
                    
                    foreach ($searchFields as $field) {
                        if (isset($data[$field]) && stripos($data[$field], $search) !== false) {
                            $found = true;
                            break;
                        }
                    }
                    
                    if (!$found) {
                        continue;
                    }
                }
                
                $logs[] = $data;
                $count++;
            }
        }
        
        return view('admin.whatsapp-logs', [
            'logs' => $logs,
            'filters' => [
                'limit' => $request->input('limit', 100),
                'status' => $request->input('status', 'all'),
                'type' => $request->input('type', 'all'),
                'search' => $request->input('search', '')
            ]
        ]);
    }
    
    /**
     * Télécharge le fichier de log complet
     */
    public function download()
    {
        // Vérifier l'authentification
        if (!auth()->check()) {
            return redirect()->route('filament.admin.auth.login');
        }
        
        $logPath = storage_path('logs/whatsapp.log');
        
        if (!File::exists($logPath)) {
            return back()->with('error', 'Fichier de log non trouvé');
        }
        
        return response()->download($logPath, 'whatsapp-logs-' . now()->format('Y-m-d-His') . '.log');
    }
    
    /**
     * Vide le fichier de log
     */
    public function clear()
    {
        // Vérifier l'authentification
        if (!auth()->check()) {
            return redirect()->route('filament.admin.auth.login');
        }
        
        $logPath = storage_path('logs/whatsapp.log');
        
        if (File::exists($logPath)) {
            // Archiver l'ancien fichier
            $archivePath = storage_path('logs/whatsapp-' . now()->format('Y-m-d-His') . '.log');
            File::copy($logPath, $archivePath);
            
            // Vider le fichier
            File::put($logPath, '');
        }
        
        return back()->with('success', 'Fichier de log WhatsApp vidé avec succès');
    }
}
