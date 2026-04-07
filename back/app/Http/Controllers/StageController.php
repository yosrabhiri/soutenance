<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Societe;
use App\Models\EncadrantProfessionnel;
use App\Models\Enseignant;
use App\Models\Stage;
use App\Models\niveau;
use App\Models\specialite;
use App\Models\Diplome;
use App\Models\Etudiant;
use App\Notifications\NouvelleDemandeNotification;
use App\Notifications\ReponseDemandeNotification;
use App\Notifications\DemandeRepondue;
use Carbon\Carbon;
use App\Models\DateRapport;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StageController extends Controller
{
    // Récupère les données pour pré-remplir le formulaire
    public function formData(){
    $etudiant = Auth::guard('etudiant')->user();
    $today = Carbon::today();
    $stageEnCours = Stage::where('etudiant_id', $etudiant->id)
        ->whereDate('date_fin', '>=', $today)
        ->latest()
        ->first();

    $societes = Societe::all();
    $encadrants = Enseignant::all();

    // ⚡ Ajouter les listes de diplômes, niveaux et spécialités
    $diplomes = Diplome::all();
    $niveaux = Niveau::all();
    $specialites = Specialite::all();
    $encadrantpro = $stageEnCours ? EncadrantProfessionnel::find($stageEnCours->encadrant_professionnel_id) : null;




    return response()->json([
        'encadrantpro'=>$encadrantpro,
        'etudiant' => $etudiant,
        'stage' => $stageEnCours,
        'societes' => $societes,
        'encadrants' => $encadrants,
        'diplomes' => $diplomes,
        'niveaux' => $niveaux,
        'specialites' => $specialites,
        'isUpdate' => $stageEnCours ? true : false, // ⚡ ajoute ce flag
    ]);
}
public function historique()
{
    $etudiant = Auth::guard('etudiant')->user();

    $stages = Stage::with('societe') // ⚡ pour avoir le nom de la société
        ->where('etudiant_id', $etudiant->id)
        ->orderBy('date_debut', 'desc')
        ->get()
        ->map(function ($stage) {
            return [
                'type' => $stage->type ?? 'Non spécifié', // si tu ajoutes le champ "type"
                'societe' => $stage->societe->nom ?? 'Inconnue',
                'dateDebut' => $stage->date_debut,
                'dateFin' => $stage->date_fin,
                'statut' => $stage->validation_academique ?? $stage->etat_validation,
            ];
        });

    return response()->json($stages);
}


    public function store(Request $request)
{
    // ⚡ Validation adaptée aux champs FormData plats
    $request->validate([
        'societe_nom' => 'required|string',
        'societe_adresse' => 'required|string',
        'societe_domaine' => 'required|string',
        'societe_tel' => 'required|string',
        'societe_email' => 'required|email',
        'encadrant_nom' => 'required|string',
        'encadrant_fonction' => 'required|string',
        'encadrant_service' => 'required|string',
        'encadrant_email' => 'required|email',
       // 'enseignant_id' => 'required|exists:enseignants,Code_enseignant',
        'description_taches' => 'required|string',
        'date_debut' => 'required|date',
        'date_fin' => 'required|date|after_or_equal:date_debut',
        'fichier' => 'nullable|file|mimes:pdf,jpg,png',
        'url_overleaf'=>'nullable|url'
    ]);
    // ⚡ Récupérer l’étudiant connecté
    $etudiant = Auth::guard('etudiant')->user();
    if (!$etudiant) {
        return response()->json(['error' => 'Non authentifié'], 401);
    }
    

    // Charger son niveau et diplôme
    $niveau = $etudiant->niveau ? $etudiant->niveau->nom : null; 
    $diplome = $etudiant->diplome ? $etudiant->diplome->nom : null; 

    // Calcul de la durée
    $dateDebut = \Carbon\Carbon::parse($request->date_debut);
    $dateFin   = \Carbon\Carbon::parse($request->date_fin);
    $diffJours = $dateDebut->diffInDays($dateFin);
    $diffMois  = $diffJours / 30; // approx en mois

    // ⚡ Vérification des règles
    if (($niveau === 'A3' && ($diplome==='INGENIEUR INFORMATIQUE'||$diplome==='LICENCE NATIONALE'))||($diplome==='MASTER'&&$niveau === 'A2')) {
        if ($diffMois < 4 || $diffMois > 6) {
            return response()->json([
                'error' => 'Pour le pfe, la durée doit être entre 4 et 6 mois.'
            ], 422);
        }
    } else {
        if ($diffMois < 1) {
            return response()->json([
                'error' => 'Pour les niveaux A1/A2 avec diplôme Ing, la durée doit être d’au moins 1 mois.'
            ], 422);
        }
    }


  

    // Société : créer si nouvelle
    $societe = Societe::firstOrCreate(
        ['nom' => $request->societe_nom],
        [
            'adresse' => $request->societe_adresse,
            'secteur_activite' => $request->societe_domaine,
            'telephone' => $request->societe_tel,
            'email' => $request->societe_email,
        ]
    );

    // Encadrant professionnel
    $encadrantPro = EncadrantProfessionnel::updateOrCreate(
        ['email' => $request->encadrant_email],
        [
            'societe_id' => $societe->id,
            'nom_complet' => $request->encadrant_nom,
            'fonction' => $request->encadrant_fonction,
            'departement' => $request->encadrant_service,
        ]
    );
    $etudiant = Auth::guard('etudiant')->user()->load('niveau');

// Récupérer le nom du niveau (ex: "A3")
$niveauNom = $etudiant->niveau->nom ?? null;
$diplomeNom = $etudiant->diplome->nom ?? null;

// Déterminer automatiquement le type
 if (($niveauNom === 'A3' && ($diplomeNom==='INGENIEUR INFORMATIQUE'||$diplomeNom==='LICENCE NATIONALE'))||($diplomeNom==='MASTER' && $niveauNom === 'A2')) 
   {
    $type = 'pfe';}
else{
     $type = 'ete';}
//**************************** */
// Validation conditionnelle pour l’enseignant
if ($type === 'pfe') {
    $request->validate([
        'enseignant_id' => 'required|exists:enseignants,Code_enseignant',
    ]);
} else {
    $request->merge(['enseignant_id' => null]); // 👈 si ete, pas d’enseignant
}

$today = Carbon::today(); // uniquement date, pas l'heure

$stage = Stage::where('etudiant_id', $etudiant->id)
              ->whereDate('date_fin', '>=', $today)
              ->latest()
              ->first();
if($stage)  {
    // ⚠️ Stage encore en cours → mise à jour
    $isUpdate = true;
    $stage->update([
        'societe_id' => $societe->id,
        'encadrant_professionnel_id' => $encadrantPro->id,
        'enseignant_id' => $request->enseignant_id,
        'description_taches' => $request->description_taches,
        'date_debut' => $request->date_debut,
        'date_fin' => $request->date_fin,
        'type' => $type,
        'url_overleaf'=>$request->url_overleaf
    ]);
    
    $stageData=$stage;
    } else {
        // Sinon → création
        $isUpdate = false;
        $stage = Stage::create([
            'etudiant_id' => $etudiant->id,
            'societe_id' => $societe->id,
            'encadrant_professionnel_id' => $encadrantPro->id,
            'enseignant_id' => $request->enseignant_id,
            'description_taches' => $request->description_taches,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'type' => $type,
            'url_overleaf'=>$request->url_overleaf
        ]);
        $stageData = null; // pas d’ancien stage
        
    }


    // Upload du fichier (après création du stage)
    if ($request->hasFile('fichier')) {
        $file = $request->file('fichier');
        $path = $file->store('stages', 'public');
        $fileUrl = asset('storage/' . $path);
        $stage->chemin_document = $fileUrl;
        $stage->save(); // ⚡ important pour sauvegarder le chemin
    }

    return response()->json([
        'message' => 'Stage enregistré avec succès',
        'stage' => $stageData,
         'isUpdate' => $isUpdate, // 👈 flag utile pour le front
         'date_actuelle' => $today->toDateString(),
    'date_fin_stage' => Carbon::parse($stage->date_fin)->toDateString(),
    ]);
}
  public function Diplome() {
        
        $Diplomes = Diplome::all(); // liste des sociétés existantes
        return response()->json([
            'diplomes' => $Diplomes,
        ]);
    }
    public function niveau() {
        $niveaux = niveau::all(); // liste des sociétés existantes
        return response()->json([
            'niveaux' => $niveaux,
        ]);
    }
    public function specialite() {
       $specialites = specialite::all(); // liste des sociétés existantes
        return response()->json([
            'specialites' => $specialites,
        ]);
    }
    public function getAllStages() {
    $stages = Stage::with([
        'Etudiant.specialite',
        'societe',
        'enseignant_traitant', // ⚡ ajouter la relation
    ])->get();

    if ($stages->isEmpty()) {
        return response()->json(['message' => 'Stage non trouvé'], 404);
    }

    return response()->json($stages);
}
public function updateStatus(Request $request, $id)
{
    $stage = Stage::find($id);
    if (!$stage) {
        return response()->json(['message' => 'Stage non trouvé'], 404);
    }

    $request->validate([
        'etat_validation' => 'required|in:accepte,refuse'
    ]);

    $stage->etat_validation = $request->etat_validation;
    $stage->save();

    // notifier l'étudiant
    $etudiant = Etudiant::find($stage->etudiant_id);
    if ($etudiant) {
        $etudiant->notify(new DemandeRepondue('Votre stage a été ' . $stage->etat_validation));
    }

    return response()->json(['message' => 'Statut mis à jour', 'stage' => $stage]);
}

    public function AffecteAStage()
{
    $etudiants = \DB::table('etudiants')
        ->join('stages', 'etudiants.id', '=', 'stages.etudiant_id')
        ->join('societes', 'stages.societe_id', '=', 'societes.id')
        ->select(
            'etudiants.nom',
            'etudiants.email',
            'etudiants.prenom',
            'stages.etat_validation',
            'societes.nom as societe'
        )
        ->get();

    $result = $etudiants->map(function ($etu) {
        return [
            'nom'     => $etu->nom,
            'prenom'  => $etu->prenom,
            'email'   => $etu->email,
            'statut'  => $etu->etat_validation === 'accepte' ? 'Affecté' : 'Non affecté',
            'societe' => $etu->etat_validation === 'accepte' ? ($etu->societe ?? '—') : '—',
        ];
    });

    return response()->json($result);
}
public function deposerRapport(Request $request)
{

    $dates = DateRapport::latest()->first();

    if (!$dates) {
        return response()->json(['message' => 'Période non configurée'], 403);
    }

    $now = now();

    if ($now->lt($dates->date_ouverture) || $now->gt($dates->date_fermeture)) {
        return response()->json([
            'message' => 'Le dépôt du rapport est fermé. Période autorisée : du '
                        . \Carbon\Carbon::parse($dates->date_ouverture)->format('d/m/Y')
                        . ' au ' 
                        . \Carbon\Carbon::parse($dates->date_fermeture)->format('d/m/Y'),
        ], 403);
    }
    // Validation des données
   $request->validate([
    'rapport'    => 'required|file|mimes:pdf,doc,docx|max:10240',
    'mots_cles'  => 'nullable|array',
    'mots_cles.*'=> 'string|max:50',
    'code_sujet' => 'nullable|string|max:50',
]);

    // Récupérer l'étudiant connecté
    $etudiant = auth()->guard('etudiant')->user();

    // Récupérer le stage en cours de l'étudiant
    $query = Stage::where('etudiant_id', $etudiant->id)
                  ->where('etat_validation', 'accepte') ;
                // Vérifier validation académique uniquement pour les A3
    if ($etudiant->niveau->nom === 'A3') {
    $query->where('validation_academique', 'valide');
     }

$stage = $query->latest()->first();


    // Stocker le fichier
    $file = $request->file('rapport');
    $path = $file->store('stages', 'public'); 
    $fileUrl = asset('storage/' . $path);

    // Mettre à jour le stage
    $stage->version_pdf = $fileUrl;
    $stage->mots_cles = json_encode($request->mots_cles);
    $stage->code_sujet  = $request->code_sujet;
    $stage->save();

    return response()->json([
        'message' => 'Rapport déposé avec succès',
        'chemin_document' => $fileUrl
    ], 200);
}
public function searchRapport(Request $request)
{
    $keywords = $request->input('keywords', []);

    // Si rien n'est fourni → retourner vide
    if (empty($keywords)) {
        return response()->json([]);
    }

    $query = Stage::query();

    $keywords = $request->input('keywords', []);

    $stages = Stage::all();

    $results = $stages->filter(function ($stage) use ($keywords) {
        $motsCles = json_decode($stage->mots_cles, true) ?? [];
        // vérifier si tous les mots recherchés sont présents
        foreach ($keywords as $keyword) {
            if (!in_array($keyword, $motsCles)) {
                return false;
            }
        }
        return true;
    });

    return response()->json($results->values());
}


};