<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Prize;
use Illuminate\Http\Request;

class SpinController extends Controller
{
    public function show(Entry $entry)
    {
        if (!$entry->participant) {
            abort(404);
        }

        return view('wheel', compact('entry'));
    }

    public function result(Entry $entry)
    {
        if (!$entry->participant) {
            abort(404);
        }

        $qrCode = $entry->qrCode;
        $prize = null;

        if ($entry->has_won) {
            // Sélectionner un prix aléatoire disponible
            $prize = Prize::where('stock', '>', 0)
                ->inRandomOrder()
                ->first();
            
            if ($prize) {
                $prize->stock--;
                $prize->save();
            }
        }

        return view('result', compact('entry', 'qrCode', 'prize'));
    }
}
