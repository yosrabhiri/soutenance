<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Etudiant;
use App\Models\Stage;

class DashboardController extends Controller
{
    // Widget 1 : Etudiants Affectés à un stage
    public function etudiantsAffectes()
    {
        $count = Stage::where('etat_validation', 'accepte')->count();

        return response()->json(['value' => $count]);
    }

    // Widget 2 : Etudiants ayant fait un stage à ISSAT
    public function etudiantsStageISSAT()
{
    $count = Stage::whereHas('societe', function ($query) {
        $query->where('nom', 'ISSAT');
    })->count();

    return response()->json(['value' => $count]);
}


    // Widget 3 : Etudiants ayant fait un stage hors ISSAT
   public function etudiantsStageExterne()
{
    $count = Stage::whereHas('societe', function ($query) {
        $query->where('nom', '!=', 'ISSAT');
    })->count();

    return response()->json(['value' => $count]);
}


    // Widget 4 : Etudiants affectés par section
    /*public function etudiantsParSection()
{
    $niveaux = Etudiant::select('niveau_id', \DB::raw('count(*) as total'))
        ->groupBy('niveau_id')
        ->with('niveau') // relation déjà définie dans ton modèle
        ->get();

    return response()->json([
        'labels' => $niveaux->map(fn($n) => $n->niveau->nom ?? "Niveau ".$n->niveau_id),
        'values' => $niveaux->pluck('total')
    ]);
}

    // Widget 5 : Exemple analytics2 (étudiants par spécialité)
    public function etudiantsParSpecialite()
{
    $specialites = Etudiant::select('specialite_id', \DB::raw('count(*) as total'))
        ->groupBy('specialite_id')
        ->with('specialite') // relation déjà définie
        ->get();

    return response()->json([
        'labels' => $specialites->map(fn($s) => $s->specialite->nom ?? "Spécialité ".$s->specialite_id),
        'values' => $specialites->pluck('total')
    ]);
}*/
// Widget 4 : Etudiants de Licence par section
// Widget Licence
public function etudiantsParSpecialiteLicence(Request $request)
{
    $niveau = $request->get('niveau'); // valeur reçue depuis le frontend, ex: "A1", "A2", "A3"

    $rows = Etudiant::whereHas('diplome', function ($q) {
                $q->where('nom', 'like', '%Licence%');
            })
            ->whereHas('stages', function ($q) {
                $q->where('etat_validation', 'accepte'); // uniquement stages validés
            })
            ->when($niveau, function($q, $niveau) {
                $q->whereHas('niveau', function($sub) use ($niveau) {
                    $sub->where('nom', $niveau); // filtre par nom du niveau
                });
            })
            ->whereNotNull('specialite_id')
            ->selectRaw('specialite_id, COUNT(*) AS total')
            ->groupBy('specialite_id')
            ->get();

    $specialites = \App\Models\Specialite::pluck('nom', 'id');

    return response()->json([
        'labels' => $rows->map(fn($r) => $specialites[$r->specialite_id] ?? ('Spécialité '.$r->specialite_id))->values(),
        'values' => $rows->pluck('total')->values(),
    ]);
}

// Widget Supérieur (Ingénieur + Master)
public function etudiantsParSpecialiteSuperieur(Request $request)
{
    $niveau = $request->get('niveau'); // filtre optionnel

    $rows = Etudiant::with('specialite')
        ->whereHas('diplome', function ($q) {
            $q->whereIn('nom', ['Master', 'INGENIEUR INFORMATIQUE']);
        })
        ->whereHas('stages', function ($q) {
            $q->where('etat_validation', 'accepte');
        })
         ->when($niveau, function($q, $niveau) {
                $q->whereHas('niveau', function($sub) use ($niveau) {
                    $sub->where('nom', $niveau); // filtre par nom du niveau
                });
            })
        ->whereNotNull('specialite_id')
        ->selectRaw('specialite_id, COUNT(*) as total')
        ->groupBy('specialite_id')
        ->get();

    $specialites = \App\Models\Specialite::pluck('nom', 'id');

    return response()->json([
        'labels' => $rows->map(fn($r) => $specialites[$r->specialite_id] ?? ('Spécialité '.$r->specialite_id))->values(),
        'values' => $rows->pluck('total')->values(),
    ]);
}

}