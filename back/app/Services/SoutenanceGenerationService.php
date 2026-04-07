<?php

namespace App\Services;

use App\Models\JurySoutenance;
use App\Models\PeriodeSoutenance;
use App\Models\soutenance as Soutenance;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SoutenanceGenerationService
{
    public function __construct(
        protected PlanningPreparationService $preparationService,
        protected PythonCpSatPlanningGenerator $cpSatGenerator
    ) {
    }

    public function generateAndPersist(PeriodeSoutenance $periode, array $options = []): array
    {
        $algorithm = $options['algorithm'] ?? 'cp_sat';

        if ($periode->salles()->count() === 0) {
            throw new InvalidArgumentException('Associez d’abord au moins une salle à cette période.');
        }

        if ($periode->creneaux()->count() === 0) {
            throw new InvalidArgumentException('Générez d’abord les créneaux de cette période.');
        }

        $planningInput = $this->preparationService->buildPlanningInput($periode);
        $result = match ($algorithm) {
            'cp_sat' => $this->cpSatGenerator->generate($planningInput, [
                'max_time_in_seconds' => (float) ($options['max_time_in_seconds'] ?? 20),
                'num_search_workers' => (int) ($options['num_search_workers'] ?? 8),
            ]),
            default => throw new InvalidArgumentException('Algorithme de génération non pris en charge.'),
        };

        if (($result['hard_violations'] ?? 0) > 0) {
            throw new InvalidArgumentException('La génération a produit des conflits bloquants. Ajustez les salles, créneaux ou disponibilités.');
        }

        if (!empty($result['unassigned_stage_ids'] ?? [])) {
            throw new InvalidArgumentException('La génération complète n’a pas pu affecter tous les stages.');
        }

        DB::transaction(function () use ($periode, $result, $planningInput) {
            $slotsById = collect($planningInput['creneaux'] ?? [])->keyBy('id');
            $existingSoutenances = Soutenance::query()
                ->where('periode_soutenance_id', $periode->id)
                ->pluck('id');

            if ($existingSoutenances->isNotEmpty()) {
                JurySoutenance::query()->whereIn('soutenance_id', $existingSoutenances)->delete();
            }

            Soutenance::query()->where('periode_soutenance_id', $periode->id)->delete();

            foreach ($result['assignments'] ?? [] as $assignment) {
                $creneau = $slotsById->get($assignment['creneau_id']);
                $soutenance = Soutenance::create([
                    'stage_id' => $assignment['stage_id'],
                    'periode_soutenance_id' => $periode->id,
                    'creneau_id' => $assignment['creneau_id'],
                    'date_heure' => sprintf(
                        '%s %s',
                        $creneau['date'] ?? $periode->date_debut,
                        $creneau['heure_debut'] ?? $periode->heure_debut
                    ),
                    'salle_id' => $assignment['salle_id'],
                ]);

                foreach (array_values($assignment['jury_ids'] ?? []) as $index => $teacherId) {
                    JurySoutenance::create([
                        'soutenance_id' => $soutenance->id,
                        'enseignant_id' => $teacherId,
                        'role' => $index === 0 ? 'encadrant' : 'jury',
                    ]);
                }
            }
        });

        return [
            'algorithm' => $result['algorithm'] ?? $algorithm,
            'score' => $result['score'] ?? null,
            'hard_violations' => $result['hard_violations'] ?? null,
            'assignments_count' => count($result['assignments'] ?? []),
            'unassigned_stage_ids' => $result['unassigned_stage_ids'] ?? [],
            'meta' => $result['meta'] ?? null,
        ];
    }
}
