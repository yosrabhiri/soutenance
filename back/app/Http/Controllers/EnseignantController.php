<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enseignant;
use App\Models\Stage;
use Illuminate\Support\Facades\Auth; 

class EnseignantController extends Controller
{
 public function getEtudiantsEncadres()
{
    // récupère l'enseignant connecté
    $enseignant = Auth::guard('enseignant')->user();

    // récupère tous les stages où il est l'encadrant
    $stages = Stage::with(['etudiant', 'societe'])
        ->where('traite_par', $enseignant->Code_enseignant)
        ->get()
        ->map(function ($stage) {
            return [
                'stage_id' => $stage->id,
                'type' => $stage->type,
                'description_taches' => $stage->description_taches,
                'date_debut' => $stage->date_debut,
                'date_fin' => $stage->date_fin,
                'email'=>$stage->etudiant->email?? '',
                'validation_academique' => $stage->validation_academique,
                'etudiant_id' => $stage->etudiant->id ?? '',
                'etudiant_nom' => $stage->etudiant->nom ?? '',
                'etudiant_prenom' => $stage->etudiant->prenom ?? '',
                'specialite' => $stage->etudiant->specialite ?? '',
                'societe_nom' => $stage->societe->nom ?? '',
                'url_overleaf'=>$stage->url_overleaf ?? ''
            ];
        });

    return response()->json([
        'message' => 'Étudiants encadrés récupérés',
        'count' => $stages->count(),
        'data' => $stages
    ]);
}
public function validerStage($stageId)
{
    $enseignant = Auth::guard('enseignant')->user();
    // ⚡ récupère le stage
    $stage = Stage::find($stageId);

    if (!$stage) {
        return response()->json([
            'message' => 'Stage introuvable'
        ], 404);
    }

    // ⚡ mettre à jour la validation académique
    $stage->validation_academique = 'valide';
    $stage->save();
    if ($stage->enseignant_id != $enseignant->Code_enseignant) {
    return response()->json([
        'message' => 'Vous ne pouvez pas valider ce stage'
    ], 403);
}

    return response()->json([
        'message' => 'Stage validé avec succès',
        'stage' => $stage
    ]);
}


}
