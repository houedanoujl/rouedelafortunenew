<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Prize;
use Illuminate\Http\Request;
use App\Helpers\TestAccountHelper;

class SpinController extends Controller
{
    public function show(Entry $entry, Request $request)
    {
        if (!$entry->participant) {
            abort(404);
        }

        // Vérifier si c'est un compte de test qui peut rejouer sans restriction
        $isTestAccount = false;
        if ($entry->participant && $entry->participant->email) {
            $isTestAccount = TestAccountHelper::isTestAccount($entry->participant->email);
            
            // Stocker dans la session pour l'affichage de la bannière
            if ($isTestAccount) {
                $companyName = TestAccountHelper::getCompanyName($entry->participant->email);
                $request->session()->put('is_test_account', true);
                $request->session()->put('test_account_company', $companyName);
            }
        }
        
        // Si l'entrée a déjà été jouée et que ce n'est PAS un compte de test, rediriger vers la page de résultat
        if ($entry->has_played && !$isTestAccount) {
            return redirect()->route('spin.result', ['entry' => $entry->id]);
        }

        return view('wheel', compact('entry'));
    }

    public function result(Entry $entry, Request $request)
    {
        if (!$entry->participant) {
            abort(404);
        }
        
        // Vérifier si c'est un compte de test
        $isTestAccount = false;
        if ($entry->participant && $entry->participant->email) {
            $isTestAccount = TestAccountHelper::isTestAccount($entry->participant->email);
            
            // Stocker dans la session pour l'affichage de la bannière
            if ($isTestAccount) {
                $companyName = TestAccountHelper::getCompanyName($entry->participant->email);
                $request->session()->put('is_test_account', true);
                $request->session()->put('test_account_company', $companyName);
            }
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

        // Générer une clé unique pour le localStorage (à des fins de vérification côté client)
        $localStorageKey = 'contest_played_' . $entry->contest_id;
        $request->session()->put('localStorageKey', $localStorageKey);

        return view('result', compact('entry', 'qrCode', 'prize'));
    }
}
