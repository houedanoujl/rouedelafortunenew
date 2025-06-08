<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WinnersExportController extends Controller
{
    public function exportCsv(Request $request)
    {
        try {
            // Test simple d'abord
            if ($request->has('test')) {
                return response()->json(['message' => 'Route fonctionne!']);
            }
            
            // Récupérer tous les gagnants
            $winners = Entry::query()
                ->where('has_won', true)
                ->with(['participant', 'contest', 'prize', 'qrCode'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Nom du fichier
            $filename = 'liste-gagnants-' . now()->format('Y-m-d-H-i') . '.csv';
            
            return response()->streamDownload(function () use ($winners) {
                $file = fopen('php://output', 'w');
                
                // Ajout du BOM UTF-8 pour Excel
                fwrite($file, "\xEF\xBB\xBF");
                
                // Entêtes du CSV
                $headers = [
                    'Concours',
                    'Prénom',
                    'Nom',
                    'Téléphone',
                    'Email',
                    'Lot gagné',
                    'Valeur (EUR)',
                    'Code QR',
                    'Scanné',
                    'Réclamé',
                    'Date de gain'
                ];
                
                fputcsv($file, $headers, ';');
                
                // Données des lignes
                foreach ($winners as $entry) {
                    $data = [
                        $entry->contest->name ?? 'Non disponible',
                        $entry->participant->first_name ?? 'Non disponible',
                        $entry->participant->last_name ?? 'Non disponible',
                        $entry->participant->phone ?? 'Non disponible',
                        $entry->participant->email ?? 'Non disponible',
                        $entry->prize->name ?? 'Non disponible',
                        $entry->prize->value ? number_format($entry->prize->value, 2, ',', ' ') : 'Non disponible',
                        $entry->qrCode->code ?? 'Non disponible',
                        ($entry->qrCode && $entry->qrCode->scanned) ? 'Oui' : 'Non',
                        $entry->claimed ? 'Oui' : 'Non',
                        $entry->created_at ? $entry->created_at->format('d/m/Y H:i') : 'Non disponible'
                    ];
                    
                    fputcsv($file, $data, ';');
                }
                
                fclose($file);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
                'Pragma' => 'public'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur export CSV: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
} 