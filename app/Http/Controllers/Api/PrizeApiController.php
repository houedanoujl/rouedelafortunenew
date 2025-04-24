<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PrizeApiController extends Controller
{
    /**
     * Récupérer la liste de tous les prix
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $prizes = Prize::all();
        
        return response()->json([
            'success' => true,
            'data' => $prizes
        ]);
    }
    
    /**
     * Récupérer un prix spécifique
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $prize = Prize::find($id);
        
        if (!$prize) {
            return response()->json([
                'success' => false,
                'message' => 'Prix non trouvé'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $prize
        ]);
    }
    
    /**
     * Télécharger et associer une image à un prix
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:2048'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation échouée',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $prize = Prize::find($id);
        
        if (!$prize) {
            return response()->json([
                'success' => false,
                'message' => 'Prix non trouvé'
            ], 404);
        }
        
        try {
            // Supprimer l'ancienne image si elle existe
            if ($prize->image_url && strpos($prize->image_url, '/storage/') !== false) {
                $oldPath = str_replace('/storage/', 'public/', $prize->image_url);
                if (Storage::exists($oldPath)) {
                    Storage::delete($oldPath);
                }
            }
            
            // Stocker la nouvelle image
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $path = $request->file('image')->storeAs('public/prizes', $imageName);
            
            // Mettre à jour l'URL de l'image dans la base de données
            $prize->image_url = Storage::url($path);
            $prize->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Image téléchargée avec succès',
                'data' => [
                    'image_url' => $prize->image_url
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du téléchargement de l\'image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
