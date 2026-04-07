<?php

namespace App\Services;

use App\Models\Diplome;
use App\Models\Enseignant;
use App\Models\PeriodeSoutenance;
use App\Models\Stage;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PlanningPreparationService
{
    public function buildPlanningInput(PeriodeSoutenance $periode): array
    {
        $periode->load([
            'diplome:id,nom',
            'salles:id,nom,departement_id',
            'salles.departement:id,nom,code',
            'creneaux' => fn ($query) => $query->orderBy('date')->orderBy('ordre'),
        ]);

        $enseignants = $this->getCandidateTeachersForPeriod($periode);
        $stages = $this->getConcernedStages($periode, $enseignants);

        return [
            'periode' => [
                'id' => $periode->id,
                'date_debut' => $periode->date_debut,
                'date_fin' => $periode->date_fin,
                'duree_minutes' => $periode->duree_minutes,
                'heure_debut' => $periode->heure_debut,
                'heure_fin' => $periode->heure_fin,
                'diplome' => $periode->diplome ? [
                    'id' => $periode->diplome->id,
                    'nom' => $periode->diplome->nom,
                ] : null,
            ],
            'salles' => $periode->salles->map(function ($salle) {
                return [
                    'id' => $salle->id,
                    'nom' => $salle->nom,
                    'departement' => $salle->departement ? [
                        'id' => $salle->departement->id,
                        'nom' => $salle->departement->nom,
                        'code' => $salle->departement->code,
                    ] : null,
                ];
            })->values(),
            'creneaux' => $periode->creneaux->map(function ($creneau) {
                return [
                    'id' => $creneau->id,
                    'date' => $creneau->date,
                    'code_slot' => $creneau->code_slot,
                    'heure_debut' => $creneau->heure_debut,
                    'heure_fin' => $creneau->heure_fin,
                    'ordre' => $creneau->ordre,
                ];
            })->values(),
            'enseignants' => $enseignants->values(),
            'stages' => $stages->values(),
            'meta' => [
                'nombre_salles' => $periode->salles->count(),
                'nombre_creneaux' => $periode->creneaux->count(),
                'nombre_enseignants_candidats' => $enseignants->count(),
                'nombre_stages_concernes' => $stages->count(),
            ],
        ];
    }

    public function resolveRequiredJurySizeForDiploma(?Diplome $diplome): int
    {
        $nomDiplome = mb_strtoupper(trim((string) optional($diplome)->nom));

        return match (true) {
            str_contains($nomDiplome, 'LICENCE') => 2,
            str_contains($nomDiplome, 'MASTER') => 3,
            str_contains($nomDiplome, 'INGENIEUR') => 3,
            default => throw new InvalidArgumentException('Diplome non pris en charge pour la generation du planning.'),
        };
    }

    protected function getConcernedStages(PeriodeSoutenance $periode, Collection $enseignants): Collection
    {
        $jurySize = $this->resolveRequiredJurySizeForDiploma($periode->diplome);
        return Stage::query()
            ->with([
                'Etudiant.diplome:id,nom',
                'Etudiant.specialite:id,nom,departement_id',
                'Etudiant.specialite.departement:id,nom,code',
                'Enseignant:Code_enseignant,Nom_Prenom_Enseignant,Email,departement_id',
                'Enseignant.departement:id,nom,code',
            ])
            ->where('etat_validation', 'accepte')
            ->where('validation_academique', 'valide')
            ->whereHas('Etudiant', function ($query) use ($periode) {
                $query->where('diplome_id', $periode->diplome_id);
            })
            ->orderByDesc('date_fin')
            ->orderByDesc('id')
            ->get()
            ->unique('etudiant_id')
            ->values()
            ->map(function (Stage $stage) use ($jurySize, $enseignants) {
                $etudiant = $stage->Etudiant;
                $encadrant = $stage->Enseignant;
                $specialite = optional($etudiant)->specialite;
                $departementId = optional($specialite)->departement_id;

                $juryCandidates = $enseignants
                    ->filter(function (array $teacher) use ($departementId, $stage) {
                        if (($teacher['id'] ?? null) === $stage->enseignant_id) {
                            return false;
                        }

                        if (blank($departementId)) {
                            return true;
                        }

                        return ($teacher['departement']['id'] ?? null) === $departementId;
                    })
                    ->pluck('id')
                    ->values();

                return [
                    'stage_id' => $stage->id,
                    'type_stage_source' => $stage->type,
                    'etudiant' => $etudiant ? [
                        'id' => $etudiant->id,
                        'nom' => $etudiant->nom,
                        'prenom' => $etudiant->prenom,
                        'email' => $etudiant->email,
                        'specialite' => $specialite ? [
                            'id' => $specialite->id,
                            'nom' => $specialite->nom,
                            'departement_id' => $specialite->departement_id,
                        ] : null,
                    ] : null,
                    'diplome' => optional($etudiant)->diplome ? [
                        'id' => $etudiant->diplome->id,
                        'nom' => $etudiant->diplome->nom,
                    ] : null,
                    'encadrant_academique' => $encadrant ? [
                        'id' => $encadrant->Code_enseignant,
                        'nom' => $encadrant->Nom_Prenom_Enseignant,
                        'email' => $encadrant->Email,
                        'departement' => $encadrant->departement ? [
                            'id' => $encadrant->departement->id,
                            'nom' => $encadrant->departement->nom,
                            'code' => $encadrant->departement->code,
                        ] : null,
                    ] : null,
                    'taille_jury_requise' => $jurySize,
                    'nombre_jures_a_ajouter_hors_encadrant' => max($jurySize - 1, 0),
                    'jury_candidate_ids' => $juryCandidates,
                    'contraintes_observees' => [
                        'encadrant_manquant' => blank($stage->enseignant_id),
                        'specialite_sans_departement' => blank($departementId),
                    ],
                ];
            });
    }

    protected function getCandidateTeachersForPeriod(PeriodeSoutenance $periode): Collection
    {
        $creneaux = $periode->creneaux;
        $creneauIds = $creneaux->pluck('id');

        return Enseignant::query()
            ->with([
                'departement:id,nom,code',
                'disponibilitesSoutenances' => function ($query) use ($creneauIds) {
                    $query->whereIn('creneau_id', $creneauIds);
                },
            ])
            ->orderBy('Nom_Prenom_Enseignant')
            ->get()
            ->map(function (Enseignant $enseignant) use ($creneaux) {
                $disponibilites = $enseignant->disponibilitesSoutenances->keyBy('creneau_id');

                $slots = $creneaux->map(function ($creneau) use ($disponibilites) {
                    return [
                        'creneau_id' => $creneau->id,
                        'statut' => optional($disponibilites->get($creneau->id))->statut ?? 'available',
                    ];
                })->values();

                return [
                    'id' => $enseignant->Code_enseignant,
                    'nom' => $enseignant->Nom_Prenom_Enseignant,
                    'email' => $enseignant->Email,
                    'departement' => $enseignant->departement ? [
                        'id' => $enseignant->departement->id,
                        'nom' => $enseignant->departement->nom,
                        'code' => $enseignant->departement->code,
                    ] : null,
                    'est_candidat_jury' => true,
                    'disponibilites_par_creneau' => $slots,
                ];
            });
    }
}
