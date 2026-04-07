<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Enseignant;
use App\Models\Etudiant;
use App\Models\demandeEncadrement;
use App\Models\Stage;
use App\Notifications\NouvelleDemandeNotification;
use App\Notifications\ReponseDemandeNotification;
use App\Notifications\DemandeRepondue;
class DemandeController extends Controller
{
    // Étudiant envoie une demande
    public function store(Request $request ) {
        $demande = demandeEncadrement::create([
            'etudiant_id' => auth()->id(),
            'enseignant_id' => $request->enseignant_id,
            //'message' => "demande encadrement par ".auth()->user()->nom."".auth()->user()->prenom,
        ]);

        // notifier l’enseignant
        // ⚡ récupérer l’étudiant connecté
    $etudiant = auth()->user();
        //dd($etudiant);
    // 1️⃣ Notifier l’enseignant encadreur choisi
    $enseignant = Enseignant::find($request->enseignant_id);
    if ($enseignant && $enseignant->role === "enseignant") {
        $message = "Demande d'encadrement par " . $etudiant->nom . " " . $etudiant->prenom;
        $enseignant->notify(new DemandeRepondue($message,"/home/s/demencad"));
    }

    // 2️⃣ Notifier aussi le directeur de stage (tous les directeurs si plusieurs)
    $directeurs = Enseignant::where('role', 'directeur_stage')->get();
    foreach ($directeurs as $directeur) {
        $message = "Demande de validation du stage par " . $etudiant->nom . " " . $etudiant->prenom;
        $directeur->notify(new DemandeRepondue($message,"/home/s/valideStage"));
    }
        return response()->json($demande, 201);
    }

    // Enseignant récupère toutes ses demandes
    public function index() {
    $user = auth()->user();

    if ($user->role !== 'enseignant') {
        return response()->json([
            'message' => 'Accès refusé'
        ], 403);
    }

    // Récupérer les demandes de cet enseignant avec infos étudiant
    $demandes = demandeEncadrement::where('enseignant_id', $user->Code_enseignant)
        ->with('etudiant')
        ->get();

    // Réponse JSON structurée
    return response()->json([
        'enseignant' => $user,   // infos enseignant connecté
        'demandes'   => $demandes // liste des demandes
    ], 200);
}


    // Enseignant répond
    public function repondre(Request $request, $id)
{
    $demande = demandeEncadrement::findOrFail($id);
    $demande->statut = $request->statut; // acceptee/refusee
    $enseignant = auth()->user();
    //$demande->message = ucfirst($request->statut) . " par " . $enseignant->NomEnseignant . " " . $enseignant->PrenomEnseignant;
    $demande->save();

    // notifier l’étudiant
    $etudiant = Etudiant::find($demande->etudiant_id);
    $etudiant->notify(new DemandeRepondue(ucfirst($request->statut) . " par " . $enseignant->NomEnseignant . " " . $enseignant->PrenomEnseignant));

    // si accepté, on met à jour la ligne Stage existante
    if ($request->statut === 'acceptee') {
        $enseignant = auth()->user(); // enseignant connecté
        // récupérer le stage de l’étudiant
        $stage = Stage::where('etudiant_id', $demande->etudiant_id)->first();
        if ($stage) {
            $stage->traite_par = $enseignant->Code_enseignant; 
            $stage->save();
        }
    }

    return response()->json($demande);
}

}