<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class PrizeUploadController extends Controller
{
    /**
     * Affiche la page d'upload d'images pour les prix
     */
    public function showUploadForm()
    {
        return view('prize-upload');
    }

    /**
     * Traite l'upload d'une image de prix
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $uploadPath = public_path('assets/prizes');
        
        // Créer le dossier s'il n'existe pas
        if (!File::isDirectory($uploadPath)) {
            File::makeDirectory($uploadPath, 0777, true);
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            
            // Déplacer l'image uploadée
            $image->move($uploadPath, $filename);
            
            $imageUrl = url('assets/prizes/' . $filename);
            
            return back()->with('success', 'Image uploadée avec succès. URL: ' . $imageUrl);
        }
        
        return back()->with('error', 'Erreur lors de l\'upload de l\'image');
    }

    /**
     * Supprime une image de prix
     */
    public function deleteImage($filename)
    {
        $filePath = public_path('assets/prizes/' . $filename);
        
        if (File::exists($filePath)) {
            File::delete($filePath);
            return back()->with('success', 'Image supprimée avec succès');
        }
        
        return back()->with('error', 'L\'image n\'existe pas');
    }
}
