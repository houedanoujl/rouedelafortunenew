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

        // Si l'entrée a déjà été jouée, rediriger directement vers la page de résultat
        if ($entry->has_played) {
            return redirect()->route('spin.result', ['entry' => $entry->id]);
        }

        return view('wheel', compact('entry'));
    }

    public function result(Entry $entry)
    {
        if (!$entry->participant) {
            abort(404);
        }

        $qrCode = $entry->qrCode;

        // Utiliser le prix déjà associé à l'entrée ou en attribuer un si c'est la première visite
        if ($entry->has_won && !$entry->prize_id) {
            // C'est la première visite : attribuer un prix aléatoire disponible
            $prize = Prize::where('stock', '>', 0)
                ->inRandomOrder()
                ->first();
            
            if ($prize) {
                // Stocker le prize_id dans l'entrée pour les visites futures
                $entry->prize_id = $prize->id;
                $entry->save();
                
                // Décrémenter le stock
                $prize->stock--;
                $prize->save();
            }
        } else {
            // Utiliser le prix déjà associé à l'entrée
            $prize = $entry->prize;
        }

        return view('result', compact('entry', 'qrCode', 'prize'));
    }
}
