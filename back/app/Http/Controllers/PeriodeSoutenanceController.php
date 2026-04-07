<?php
namespace App\Http\Controllers;

use App\Jobs\GenerateFinalSoutenancesJob;
use App\Models\PeriodeSoutenance;
use App\Models\Enseignant;
use App\Models\Salle;
use Illuminate\Http\Request;
use App\Models\CreneauSoutenance;
use App\Services\GenerateCreneauxService;
use App\Services\PlanningPreparationService;
use App\Services\PythonCpSatPlanningGenerator;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class PeriodeSoutenanceController extends Controller
{
    public function index()
    {
        $periodes = PeriodeSoutenance::with([
            'diplome:id,nom',
            'salles:id,nom,departement_id',
            'salles.departement:id,nom,code',
        ])->withCount('creneaux')->get();

        return response()->json([
            'periodes' => $periodes
        ]);
    }

public function salles()
{
    $salles = Salle::with('departement:id,nom,code')
        ->orderBy('nom')
        ->get(['id', 'nom', 'departement_id']);

    return response()->json([
        'salles' => $salles
    ]);
}

    

public function mesDisponibilitesParPeriode(Request $request, $periodeId)
{
   // $enseignant = $request->user();
   $enseignant = $request->user() ?? \App\Models\Enseignant::first();

    $creneaux = CreneauSoutenance::where('periode_soutenance_id', $periodeId)
        ->orderBy('date')
        ->orderBy('ordre')
        ->get();

    $dispos = $enseignant->disponibilitesSoutenances()
        ->whereHas('creneau', function ($query) use ($periodeId) {
            $query->where('periode_soutenance_id', $periodeId);
        })
        ->get()
        ->keyBy('creneau_id');

    $resultat = $creneaux->map(function ($creneau) use ($dispos) {
        $dispo = $dispos->get($creneau->id);

        return [
            'creneau_id' => $creneau->id,
            'date' => $creneau->date,
            'code_slot' => $creneau->code_slot,
            'heure_debut' => $creneau->heure_debut,
            'heure_fin' => $creneau->heure_fin,
            'statut' => $dispo ? $dispo->statut : 'available',
        ];
    });

    return response()->json([
        'enseignant_id' => $enseignant->Code_enseignant,
        'enseignant' => $enseignant->Nom_Prenom_Enseignant,
        'periode_soutenance_id' => (int) $periodeId,
        'disponibilites' => $resultat
    ]);
}
public function enregistrerMesDisponibilites(Request $request, $periodeId)
{
   // $enseignant = $request->user();
   $enseignant = $request->user() ?? \App\Models\Enseignant::first();

    $validated = $request->validate([
        'disponibilites' => ['required', 'array'],
        'disponibilites.*.creneau_id' => ['required', 'integer', 'exists:creneaux_soutenances,id'],
        'disponibilites.*.statut' => ['required', 'in:available,unavailable,preferred,avoid'],
    ]);

    foreach ($validated['disponibilites'] as $item) {
        $creneau = \App\Models\CreneauSoutenance::find($item['creneau_id']);

        if ((int) $creneau->periode_soutenance_id !== (int) $periodeId) {
            continue;
        }

        \App\Models\EnseignantDisponibilite::updateOrCreate(
            [
                'enseignant_id' => $enseignant->Code_enseignant,
                'creneau_id' => $item['creneau_id'],
            ],
            [
                'statut' => $item['statut'],
            ]
        );
    }

    return response()->json([
        'message' => 'Disponibilités enregistrées avec succès.'
    ]);
}
public function assignerSalles(Request $request, $periodeId)
{
    $validated = $request->validate([
        'salle_ids' => ['required', 'array', 'min:1'],
        'salle_ids.*' => ['integer', 'exists:salles,id'],
    ]);

    $periode = PeriodeSoutenance::findOrFail($periodeId);
    $periode->salles()->sync($validated['salle_ids']);

    return response()->json([
        'message' => 'Salles associées avec succès à la période.',
        'periode_id' => $periode->id,
        'salle_ids' => $periode->salles()->pluck('salles.id'),
    ]);
}
public function genererCreneaux($periodeId, GenerateCreneauxService $service)
{
    $periode = PeriodeSoutenance::findOrFail($periodeId);

    if ($periode->salles()->count() === 0) {
        return response()->json([
            'message' => 'Associez d’abord au moins une salle à cette période.'
        ], 422);
    }

    $service->generate($periode);

    return response()->json([
        'message' => 'Créneaux générés avec succès.',
        'periode_id' => $periode->id,
        'nombre_creneaux' => $periode->creneaux()->count(),
    ]);
}
public function creneaux($periodeId)
{
    $periode = PeriodeSoutenance::with(['creneaux' => function ($query) {
        $query->orderBy('date')->orderBy('ordre');
    }])->findOrFail($periodeId);

    return response()->json([
        'periode_id' => $periode->id,
        'creneaux' => $periode->creneaux,
    ]);
}
public function planningInput($periodeId, PlanningPreparationService $service)
{
    $periode = PeriodeSoutenance::findOrFail($periodeId);

    try {
        $payload = $service->buildPlanningInput($periode);
    } catch (InvalidArgumentException $exception) {
        return response()->json([
            'message' => $exception->getMessage(),
        ], 422);
    }

    return response()->json($payload);
}
public function cpSatPreview(Request $request, $periodeId, PlanningPreparationService $preparationService, PythonCpSatPlanningGenerator $generator)
{
    $periode = PeriodeSoutenance::findOrFail($periodeId);

    try {
        $planningInput = $preparationService->buildPlanningInput($periode);
    } catch (InvalidArgumentException $exception) {
        return response()->json([
            'message' => $exception->getMessage(),
        ], 422);
    }

    $planningInput = $this->limitPlanningStagesForPreview($planningInput, $request->query('stage_limit'));

    return response()->json([
        'periode_id' => $periode->id,
        'planning_input_meta' => $planningInput['meta'] ?? [],
        'preview' => $generator->generate($planningInput, [
            'max_time_in_seconds' => (float) $request->query('max_time_in_seconds', 20),
            'num_search_workers' => (int) $request->query('num_search_workers', 8),
        ]),
    ]);
}
public function launchGeneration(Request $request, $periodeId)
{
    $validated = $request->validate([
        'algorithm' => ['nullable', 'in:cp_sat'],
        'max_time_in_seconds' => ['nullable', 'numeric', 'min:1', 'max:300'],
        'num_search_workers' => ['nullable', 'integer', 'min:1', 'max:32'],
    ]);

    if (Config::get('queue.default') === 'sync') {
        return response()->json([
            'message' => 'La génération complète asynchrone nécessite une queue Laravel non sync.',
            'hint' => 'Passez QUEUE_CONNECTION=database puis lancez php artisan queue:work.',
        ], 409);
    }

    $periode = PeriodeSoutenance::findOrFail($periodeId);

    if (in_array($periode->generation_status, ['queued', 'running'], true)) {
        return response()->json([
            'message' => 'Une génération est déjà en cours pour cette période.',
            'status' => $this->generationStatusPayload($periode->fresh()),
        ], 409);
    }

    $options = [
        'algorithm' => $validated['algorithm'] ?? 'cp_sat',
        'max_time_in_seconds' => (float) ($validated['max_time_in_seconds'] ?? 20),
        'num_search_workers' => (int) ($validated['num_search_workers'] ?? 8),
    ];

    $periode->update([
        'generation_status' => 'queued',
        'generation_algorithm' => $options['algorithm'],
        'generation_started_at' => null,
        'generation_finished_at' => null,
        'generation_error' => null,
        'generation_meta' => null,
    ]);

    GenerateFinalSoutenancesJob::dispatch($periode->id, $options);

    return response()->json([
        'message' => 'Génération complète mise en file d’attente.',
        'status' => $this->generationStatusPayload($periode->fresh()),
    ], 202);
}
public function generationStatus($periodeId)
{
    $periode = PeriodeSoutenance::findOrFail($periodeId);

    return response()->json($this->generationStatusPayload($periode));
}
public function mesSoutenances(Request $request)
{
    $enseignant = $request->user() ?? \App\Models\Enseignant::first();
    $periodeId = $request->query('periode_id');

    $query = \App\Models\soutenance::query()
        ->with([
            'stage.Etudiant:id,prenom,nom,email,specialite_id',
            'stage.Etudiant.specialite:id,nom,departement_id',
            'salle:id,nom,departement_id',
            'creneau:id,date,code_slot,heure_debut,heure_fin,periode_soutenance_id',
            'jurySoutenances.enseignant:Code_enseignant,Nom_Prenom_Enseignant',
        ])
        ->whereHas('jurySoutenances', function ($query) use ($enseignant) {
            $query->where('enseignant_id', $enseignant->Code_enseignant);
        });

    if ($periodeId) {
        $query->where('periode_soutenance_id', $periodeId);
    }

    $soutenances = $query
        ->orderBy('date_heure')
        ->get()
        ->map(function ($soutenance) use ($enseignant) {
            $teacherMembership = $soutenance->jurySoutenances
                ->firstWhere('enseignant_id', $enseignant->Code_enseignant);

            return [
                'id' => $soutenance->id,
                'stage_id' => $soutenance->stage_id,
                'periode_soutenance_id' => $soutenance->periode_soutenance_id,
                'date_heure' => $soutenance->date_heure,
                'role' => $teacherMembership?->role,
                'salle' => $soutenance->salle ? [
                    'id' => $soutenance->salle->id,
                    'nom' => $soutenance->salle->nom,
                ] : null,
                'creneau' => $soutenance->creneau ? [
                    'id' => $soutenance->creneau->id,
                    'date' => $soutenance->creneau->date,
                    'code_slot' => $soutenance->creneau->code_slot,
                    'heure_debut' => $soutenance->creneau->heure_debut,
                    'heure_fin' => $soutenance->creneau->heure_fin,
                ] : null,
                'etudiant' => $soutenance->stage?->Etudiant ? [
                    'id' => $soutenance->stage->Etudiant->id,
                    'prenom' => $soutenance->stage->Etudiant->prenom,
                    'nom' => $soutenance->stage->Etudiant->nom,
                    'email' => $soutenance->stage->Etudiant->email,
                    'specialite' => $soutenance->stage->Etudiant->specialite ? [
                        'id' => $soutenance->stage->Etudiant->specialite->id,
                        'nom' => $soutenance->stage->Etudiant->specialite->nom,
                        'departement_id' => $soutenance->stage->Etudiant->specialite->departement_id,
                    ] : null,
                ] : null,
                'jury' => $soutenance->jurySoutenances->map(function ($juryMember) {
                    return [
                        'enseignant_id' => $juryMember->enseignant_id,
                        'nom' => $juryMember->enseignant?->Nom_Prenom_Enseignant,
                        'role' => $juryMember->role,
                    ];
                })->values(),
            ];
        })
        ->values();

    return response()->json([
        'enseignant_id' => $enseignant->Code_enseignant,
        'enseignant' => $enseignant->Nom_Prenom_Enseignant,
        'soutenances' => $soutenances,
    ]);
}
public function soutenances(Request $request)
{
    $periodeId = $request->query('periode_id');

    $query = \App\Models\soutenance::query()
        ->with([
            'stage.Etudiant:id,prenom,nom,email,specialite_id',
            'stage.Etudiant.specialite:id,nom,departement_id',
            'salle:id,nom,departement_id',
            'creneau:id,date,code_slot,heure_debut,heure_fin,periode_soutenance_id',
            'jurySoutenances.enseignant:Code_enseignant,Nom_Prenom_Enseignant',
        ]);

    if ($periodeId) {
        $query->where('periode_soutenance_id', $periodeId);
    }

    $soutenances = $query
        ->orderBy('date_heure')
        ->get()
        ->map(function ($soutenance) {
            return [
                'id' => $soutenance->id,
                'stage_id' => $soutenance->stage_id,
                'periode_soutenance_id' => $soutenance->periode_soutenance_id,
                'date_heure' => $soutenance->date_heure,
                'salle' => $soutenance->salle ? [
                    'id' => $soutenance->salle->id,
                    'nom' => $soutenance->salle->nom,
                ] : null,
                'creneau' => $soutenance->creneau ? [
                    'id' => $soutenance->creneau->id,
                    'date' => $soutenance->creneau->date,
                    'code_slot' => $soutenance->creneau->code_slot,
                    'heure_debut' => $soutenance->creneau->heure_debut,
                    'heure_fin' => $soutenance->creneau->heure_fin,
                ] : null,
                'etudiant' => $soutenance->stage?->Etudiant ? [
                    'id' => $soutenance->stage->Etudiant->id,
                    'prenom' => $soutenance->stage->Etudiant->prenom,
                    'nom' => $soutenance->stage->Etudiant->nom,
                    'email' => $soutenance->stage->Etudiant->email,
                    'specialite' => $soutenance->stage->Etudiant->specialite ? [
                        'id' => $soutenance->stage->Etudiant->specialite->id,
                        'nom' => $soutenance->stage->Etudiant->specialite->nom,
                        'departement_id' => $soutenance->stage->Etudiant->specialite->departement_id,
                    ] : null,
                ] : null,
                'jury' => $soutenance->jurySoutenances
                    ->map(function ($juryMember) {
                        return [
                            'enseignant_id' => $juryMember->enseignant_id,
                            'nom' => $juryMember->enseignant?->Nom_Prenom_Enseignant,
                            'role' => $juryMember->role,
                        ];
                    })
                    ->sortBy(function ($juryMember) {
                        return match ($juryMember['role']) {
                            'Encadrant' => 0,
                            'Président' => 1,
                            'Rapporteur' => 2,
                            'Examinateur' => 3,
                            default => 4,
                        };
                    })
                    ->values(),
            ];
        })
        ->values();

    return response()->json([
        'periode_id' => $periodeId ? (int) $periodeId : null,
        'total' => $soutenances->count(),
        'soutenances' => $soutenances,
    ]);
}
public function store(Request $request)
{
    $validated = $request->validate([
        'diplome_id' => ['required', 'integer', 'exists:diplomes,id'],
        'date_debut' => ['required', 'date'],
        'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
        'duree_minutes' => ['required', 'integer', 'min:1'],
        'heure_debut' => ['required', 'date_format:H:i'],
        'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
    ]);

    $periode = PeriodeSoutenance::create($validated);

    return response()->json([
        'message' => 'Période créée avec succès',
        'periode' => $periode
    ], 201);
}
public function update(Request $request, $periodeId)
{
    $validated = $request->validate([
        'diplome_id' => ['required', 'integer', 'exists:diplomes,id'],
        'date_debut' => ['required', 'date'],
        'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
        'duree_minutes' => ['required', 'integer', 'min:1'],
        'heure_debut' => ['required', 'date_format:H:i'],
        'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
    ]);

    $periode = PeriodeSoutenance::findOrFail($periodeId);
    $periode->update($validated);

    return response()->json([
        'message' => 'Période mise à jour avec succès',
        'periode' => $periode->fresh('diplome:id,nom'),
    ]);
}

protected function limitPlanningStagesForPreview(array $planningInput, $requestedLimit = null): array
{
    $maxPreviewStages = 60;
    $requestedLimit = (int) ($requestedLimit ?? $maxPreviewStages);
    $stageLimit = max(1, min($requestedLimit, $maxPreviewStages));

    $stages = collect($planningInput['stages'] ?? []);
    $totalStages = $stages->count();
    $limitedStages = $stages->take($stageLimit)->values();

    $planningInput['stages'] = $limitedStages;
    $planningInput['meta'] = array_merge($planningInput['meta'] ?? [], [
        'nombre_stages_concernes' => $limitedStages->count(),
        'nombre_stages_total_avant_limite' => $totalStages,
        'stage_limit_preview' => $stageLimit,
        'preview_stage_limited' => $totalStages > $limitedStages->count(),
    ]);

    return $planningInput;
}

protected function generationStatusPayload(PeriodeSoutenance $periode): array
{
    return [
        'periode_id' => $periode->id,
        'queue_connection' => Config::get('queue.default'),
        'generation_status' => $periode->generation_status ?? 'idle',
        'generation_algorithm' => $periode->generation_algorithm,
        'generation_started_at' => optional($periode->generation_started_at)?->toISOString(),
        'generation_finished_at' => optional($periode->generation_finished_at)?->toISOString(),
        'generation_error' => $periode->generation_error,
        'generation_meta' => $periode->generation_meta,
        'soutenances_count' => \App\Models\soutenance::query()
            ->where('periode_soutenance_id', $periode->id)
            ->count(),
    ];
}

}

