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
                return response()->json(['message' => 'Route fonctionne!', 'count' => Entry::where('has_won', true)->count()]);
            }
            
            // Augmenter les limites pour l'export
            ini_set('memory_limit', '256M');
            ini_set('max_execution_time', 300); // 5 minutes
            
            $filename = 'liste-gagnants-' . now()->format('Y-m-d-H-i') . '.csv';
            
            return response()->streamDownload(function() {
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
                
                // Traiter par chunks de 25 pour éviter surcharge
                Entry::query()
                    ->where('has_won', true)
                    ->with(['participant', 'contest', 'prize', 'qrCode'])
                    ->orderBy('created_at', 'desc')
                    ->chunk(25, function($winners) use ($file) {
                        foreach ($winners as $entry) {
                            $data = [
                                $entry->contest?->name ?? 'Non disponible',
                                $entry->participant?->first_name ?? 'Non disponible',
                                $entry->participant?->last_name ?? 'Non disponible',
                                $entry->participant?->phone ?? 'Non disponible',
                                $entry->participant?->email ?? 'Non disponible',
                                $entry->prize?->name ?? 'Non disponible',
                                $entry->prize?->value ? number_format($entry->prize->value, 2, ',', ' ') : 'Non disponible',
                                $entry->qrCode?->code ?? 'Non disponible',
                                ($entry->qrCode && $entry->qrCode->scanned) ? 'Oui' : 'Non',
                                $entry->claimed ? 'Oui' : 'Non',
                                $entry->created_at ? $entry->created_at->format('d/m/Y H:i') : 'Non disponible'
                            ];
                            
                            fputcsv($file, $data, ';');
                        }
                        
                        // Libérer la mémoire après chaque chunk
                        if (function_exists('gc_collect_cycles')) {
                            gc_collect_cycles();
                        }
                    });
                
                fclose($file);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
                'Pragma' => 'public'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur export CSV: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true)
            ]);
            
            return response()->json([
                'error' => 'Erreur lors de l\'export: ' . $e->getMessage(),
                'memory_info' => [
                    'current' => memory_get_usage(true),
                    'peak' => memory_get_peak_usage(true)
                ]
            ], 500);
        }
    }
} 