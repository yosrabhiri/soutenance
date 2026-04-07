<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reunion;
use Illuminate\Support\Facades\Auth;

class reunionController extends Controller
{
    // Création d'une réunion avec plusieurs étudiants
    public function store(Request $request)
    {
        // ✅ Validation des données
        $request->validate([
            'etudiants' => 'required|array|min:1', // tableau d'IDs étudiants
            'etudiants.*' => 'exists:etudiants,id',
            'jour' => 'required|date',
            'heure' => 'required|date_format:H:i:s',
            'salle' => 'nullable|string',
            'note' => 'nullable|string',
            'titre' => 'nullable|string',
        ]);

        // Récupère le code de l'enseignant connecté
        $enseignant = Auth::guard('enseignant')->user();
        $code = $enseignant->Code_enseignant;

        // Création de la réunion
        $reunion = Reunion::create([
            'code_enseignant' => $code,
            'jour' => $request->jour,
            'heure' => $request->heure,
            'salle' => $request->salle,
            'note' => $request->note,
            'titre' => $request->titre ?? null,
        ]);

        // Association des étudiants via table pivot etudiant_reunion
        $reunion->etudiants()->attach($request->etudiants);

        return response()->json([
            'message' => 'Réunion créée avec succès',
            'reunion' => $reunion,
        ], 201);
    }

    // Récupérer toutes les réunions d'un enseignant avec les étudiants
    public function index()
    {
        $enseignant = Auth::guard('enseignant')->user();
        $code = $enseignant->code_enseignant;

        $reunions = Reunion::with('etudiants')
            ->where('code_enseignant', $code)
            ->orderBy('jour', 'desc')
            ->get();

        return response()->json($reunions);
    }
     public function getReunionsEtudiants()
    {
        $enseignant = Auth::guard('enseignant')->user();

    if (!$enseignant) {
        return response()->json(['error' => 'Non authentifié'], 401);
    }

    $reunions = Reunion::with('etudiants')
        ->where('code_enseignant', $enseignant->Code_enseignant) // ✅ Corrigé
        ->get();

    $result = [];

    foreach ($reunions as $reunion) {
        foreach ($reunion->etudiants as $etudiant) {
            $result[$etudiant->id][] = [
                'jour'  => $reunion->jour,
                'heure' => $reunion->heure,
                'titre' => $reunion->titre,
                'salle' => $reunion->salle,
                'note'  => $reunion->note
            ];
        }
    }

    return response()->json(['data' => $result]);}
    public function getReunionsAvecEtudiants()
{
    $enseignant = Auth::guard('enseignant')->user();

    if (!$enseignant) {
        return response()->json(['error' => 'Non authentifié'], 401);
    }

    // Charger les réunions avec les étudiants
    $reunions = Reunion::with('etudiants')
        ->where('code_enseignant', $enseignant->Code_enseignant)
        ->get();

    $result = $reunions->map(function ($reunion) {
        return [
            'id'     => $reunion->id,
            'titre'  => $reunion->titre,
            'jour'   => $reunion->jour,
            'heure'  => $reunion->heure,
            'salle'  => $reunion->salle,
            'note'   => $reunion->note,
            'etudiants' => $reunion->etudiants->map(function ($etudiant) {
                return [
                    'id'                => $etudiant->id,
                    'nom'               => $etudiant->nom,
                    'prenom'            => $etudiant->prenom,
                    'email'             => $etudiant->email,
                    'numero_inscription'=> $etudiant->numero_inscription,
                    'specialite'         => $etudiant->specialite? $etudiant->specialite->nom : "mmmm", // 👈 Ajout spécialité
                ];
            })
        ];
    });

    return response()->json(['data' => $result]);
}
public function getReunionsEtudiantConnecte()
{
    // Récupérer l'étudiant connecté
    $etudiant = Auth::guard('etudiant')->user();

    if (!$etudiant) {
        return response()->json(['error' => 'Non authentifié'], 401);
    }

    // Charger toutes les réunions liées à cet étudiant
    $reunions = $etudiant->reunions()
        ->with('enseignant') // si tu veux aussi les infos de l’enseignant
        ->orderBy('jour', 'desc')
        ->get();

    // Structurer la réponse
    $result = $reunions->map(function ($reunion) {
        return [
            'id'     => $reunion->id,
            'titre'  => $reunion->titre,
            'jour'   => $reunion->jour,
            'heure'  => $reunion->heure,
            'salle'  => $reunion->salle,
            'note'   => $reunion->note,
            'enseignant' => [
                'code'   => $reunion->code_enseignant,
                // si tu veux aller chercher + d’infos sur l’enseignant,
                // fais une relation Reunion->enseignant dans le modèle
            ]
        ];
    });

    return response()->json(['data' => $result]);
}
}
