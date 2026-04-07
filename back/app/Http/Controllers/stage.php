<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Societe;
use App\Models\EncadrantProfessionnel;
use App\Models\Enseignant;
use App\Models\Stage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StageController extends Controller
{
    // Récupère les données pour pré-remplir le formulaire
    public function formData()
    {
        $etudiant = Auth::guard('etudiant')->user(); // on suppose relation User->Etudiant
        $societes = Societe::all(); // liste des sociétés existantes
        $encadrants = Enseignant::all(); // liste des enseignants

        return response()->json([
            'etudiant' => $etudiant,
            'societes' => $societes,
            'encadrants' => $encadrants,
        ]);
    }

    // Enregistre le stage
    public function store(Request $request)
    {
        $request->validate([
            'societe.nom' => 'required|string',
            'societe.adresse' => 'required|string',
            'societe.secteur_activite' => 'required|string',
            'societe.telephone' => 'required|string',
            'societe.email' => 'required|email',
            'encadrant_pro.nom_complet' => 'required|string',
            'encadrant_pro.fonction' => 'required|string',
            'encadrant_pro.departement' => 'required|string',
            'encadrant_pro.email' => 'required|email',
            'enseignant_id' => 'required|exists:enseignant,id',
            'description_taches' => 'required|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'document' => 'nullable|file|mimes:pdf,jpg,png',
        ]);

        $etudiant = Auth::guard('etudiant')->user();

        // Société : créer si nouvelle ou récupérer existante
        $societe = Societe::firstOrCreate(
            ['nom' => $request->societe['nom']],
            [
                'adresse' => $request->societe['adresse'],
                'secteur_activite' => $request->societe['secteur_activite'],
                'telephone' => $request->societe['telephone'],
                'email' => $request->societe['email'],
                'site_web' => $request->societe['site_web'] ?? null,
                'lien_linkedin' => $request->societe['linkedin'] ?? null,
            ]
        );

        // Encadrant professionnel
        $encadrantPro = EncadrantProfessionnel::updateOrCreate(
            ['email' => $request->encadrant_pro['email']], // si déjà existe par email
            [
                'societe_id' => $societe->id,
                'nom_complet' => $request->encadrant_pro['nom_complet'],
                'fonction' => $request->encadrant_pro['fonction'],
                'departement' => $request->encadrant_pro['departement']?? 'Non précisé',
            ]
        );

        // Upload document
        /*$cheminDoc = null;
        if ($request->hasFile('document')) {
            $cheminDoc = $request->file('document')->store('stages_docs', 'public');
        }*/

        // Création du stage
        $stage = Stage::create([
            'etudiant_id' => $etudiant->id,
            'societe_id' => $societe->id,
            'encadrant_professionnel_id' => $encadrantPro->id,
            'enseignant_id' => $request->enseignant_id,
            'description_taches' => $request->description_taches,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            //'chemin_document' => $cheminDoc,
        ]);

        return response()->json(['message' => 'Stage enregistré avec succès', 'stage' => $stage]);
    }
}
