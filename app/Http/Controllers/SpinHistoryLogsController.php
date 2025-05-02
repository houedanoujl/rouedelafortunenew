<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class SpinHistoryLogsController extends Controller
{
    public function index()
    {
        $path = storage_path('app/spin_history.json');
        $logs = [];
        if (File::exists($path)) {
            $json = File::get($path);
            $logs = json_decode($json, true) ?: [];
        }
        // Tri du plus récent au plus ancien
        usort($logs, function($a, $b) {
            return strtotime($b['timestamp']) <=> strtotime($a['timestamp']);
        });
        return View::make('admin.spin-history-logs', [
            'logs' => $logs,
        ]);
    }
}
