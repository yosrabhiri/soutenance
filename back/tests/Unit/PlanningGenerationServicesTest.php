<?php

namespace Tests\Unit;

use App\Services\PlanningConflictChecker;
use App\Services\PlanningScoreService;
use PHPUnit\Framework\TestCase;

class PlanningGenerationServicesTest extends TestCase
{
    public function test_conflict_checker_rejects_unavailable_teacher_and_missing_encadrant(): void
    {
        $checker = new PlanningConflictChecker();
        $input = $this->planningInputFixture();

        $assignment = [
            'stage_id' => 10,
            'creneau_id' => 100,
            'salle_id' => 1,
            'jury_ids' => ['ENS-2', 'ENS-3'],
        ];

        $this->assertGreaterThan(0, $checker->countViolationsForAssignment($assignment, [], $input));
    }

    public function test_conflict_checker_rejects_room_from_another_department(): void
    {
        $checker = new PlanningConflictChecker();
        $input = $this->planningInputFixture();

        $assignment = [
            'stage_id' => 10,
            'creneau_id' => 100,
            'salle_id' => 2,
            'jury_ids' => ['ENS-1', 'ENS-2'],
        ];

        $this->assertGreaterThan(0, $checker->countViolationsForAssignment($assignment, [], $input));
    }

    public function test_conflict_checker_rejects_teacher_from_another_department(): void
    {
        $checker = new PlanningConflictChecker();
        $input = $this->planningInputFixture();

        $assignment = [
            'stage_id' => 10,
            'creneau_id' => 101,
            'salle_id' => 1,
            'jury_ids' => ['ENS-1', 'ENS-4'],
        ];

        $this->assertGreaterThan(0, $checker->countViolationsForAssignment($assignment, [], $input));
    }

    public function test_score_service_prefers_preferred_slots_to_avoid_slots(): void
    {
        $checker = new PlanningConflictChecker();
        $scoreService = new PlanningScoreService($checker);
        $input = $this->planningInputFixture();

        $preferredAssignment = [
            'stage_id' => 10,
            'creneau_id' => 100,
            'salle_id' => 1,
            'jury_ids' => ['ENS-1', 'ENS-2'],
        ];

        $avoidAssignment = [
            'stage_id' => 10,
            'creneau_id' => 101,
            'salle_id' => 1,
            'jury_ids' => ['ENS-1', 'ENS-2'],
        ];

        $this->assertGreaterThan(
            $scoreService->scoreAssignment($avoidAssignment, [$avoidAssignment], $input),
            $scoreService->scoreAssignment($preferredAssignment, [$preferredAssignment], $input)
        );
    }

    public function test_score_service_prefers_a_new_day_when_the_current_day_is_already_busy(): void
    {
        $checker = new PlanningConflictChecker();
        $scoreService = new PlanningScoreService($checker);
        $input = $this->planningInputWithTwoDaysFixture();

        $existingAssignments = [
            [
                'stage_id' => 10,
                'creneau_id' => 100,
                'salle_id' => 1,
                'jury_ids' => ['ENS-1', 'ENS-2'],
            ],
            [
                'stage_id' => 11,
                'creneau_id' => 101,
                'salle_id' => 1,
                'jury_ids' => ['ENS-3', 'ENS-4'],
            ],
        ];

        $sameDayCandidate = [
            'stage_id' => 12,
            'creneau_id' => 102,
            'salle_id' => 1,
            'jury_ids' => ['ENS-5', 'ENS-6'],
        ];

        $nextDayCandidate = [
            'stage_id' => 12,
            'creneau_id' => 200,
            'salle_id' => 1,
            'jury_ids' => ['ENS-5', 'ENS-6'],
        ];

        $this->assertGreaterThan(
            $scoreService->scoreAssignment($sameDayCandidate, [...$existingAssignments, $sameDayCandidate], $input),
            $scoreService->scoreAssignment($nextDayCandidate, [...$existingAssignments, $nextDayCandidate], $input)
        );
    }

    protected function planningInputFixture(): array
    {
        return [
            'salles' => [
                ['id' => 1, 'nom' => 'G1', 'departement' => ['id' => 1]],
                ['id' => 2, 'nom' => 'G2', 'departement' => ['id' => 2]],
            ],
            'creneaux' => [
                ['id' => 100, 'date' => '2026-04-10', 'ordre' => 1],
                ['id' => 101, 'date' => '2026-04-10', 'ordre' => 2],
            ],
            'enseignants' => [
                [
                    'id' => 'ENS-1',
                    'departement' => ['id' => 1],
                    'disponibilites_par_creneau' => [
                        ['creneau_id' => 100, 'statut' => 'preferred'],
                        ['creneau_id' => 101, 'statut' => 'available'],
                    ],
                ],
                [
                    'id' => 'ENS-2',
                    'departement' => ['id' => 1],
                    'disponibilites_par_creneau' => [
                        ['creneau_id' => 100, 'statut' => 'preferred'],
                        ['creneau_id' => 101, 'statut' => 'avoid'],
                    ],
                ],
                [
                    'id' => 'ENS-3',
                    'departement' => ['id' => 1],
                    'disponibilites_par_creneau' => [
                        ['creneau_id' => 100, 'statut' => 'unavailable'],
                        ['creneau_id' => 101, 'statut' => 'available'],
                    ],
                ],
                [
                    'id' => 'ENS-4',
                    'departement' => ['id' => 2],
                    'disponibilites_par_creneau' => [
                        ['creneau_id' => 100, 'statut' => 'available'],
                        ['creneau_id' => 101, 'statut' => 'available'],
                    ],
                ],
            ],
            'stages' => [
                [
                    'stage_id' => 10,
                    'etudiant' => [
                        'specialite' => ['departement_id' => 1],
                    ],
                    'encadrant_academique' => ['id' => 'ENS-1'],
                    'taille_jury_requise' => 2,
                    'nombre_jures_a_ajouter_hors_encadrant' => 1,
                    'jury_candidate_ids' => ['ENS-2', 'ENS-3'],
                ],
            ],
        ];
    }

    protected function planningInputWithTwoDaysFixture(): array
    {
        return [
            'salles' => [
                ['id' => 1, 'nom' => 'G1', 'departement' => ['id' => 1]],
            ],
            'creneaux' => [
                ['id' => 100, 'date' => '2026-04-10', 'ordre' => 1],
                ['id' => 101, 'date' => '2026-04-10', 'ordre' => 2],
                ['id' => 102, 'date' => '2026-04-10', 'ordre' => 3],
                ['id' => 200, 'date' => '2026-04-11', 'ordre' => 1],
            ],
            'enseignants' => [
                ['id' => 'ENS-1', 'departement' => ['id' => 1], 'disponibilites_par_creneau' => []],
                ['id' => 'ENS-2', 'departement' => ['id' => 1], 'disponibilites_par_creneau' => []],
                ['id' => 'ENS-3', 'departement' => ['id' => 1], 'disponibilites_par_creneau' => []],
                ['id' => 'ENS-4', 'departement' => ['id' => 1], 'disponibilites_par_creneau' => []],
                ['id' => 'ENS-5', 'departement' => ['id' => 1], 'disponibilites_par_creneau' => []],
                ['id' => 'ENS-6', 'departement' => ['id' => 1], 'disponibilites_par_creneau' => []],
            ],
            'stages' => [
                [
                    'stage_id' => 10,
                    'etudiant' => ['specialite' => ['departement_id' => 1]],
                    'encadrant_academique' => ['id' => 'ENS-1'],
                    'taille_jury_requise' => 2,
                    'nombre_jures_a_ajouter_hors_encadrant' => 1,
                    'jury_candidate_ids' => ['ENS-2'],
                ],
                [
                    'stage_id' => 11,
                    'etudiant' => ['specialite' => ['departement_id' => 1]],
                    'encadrant_academique' => ['id' => 'ENS-3'],
                    'taille_jury_requise' => 2,
                    'nombre_jures_a_ajouter_hors_encadrant' => 1,
                    'jury_candidate_ids' => ['ENS-4'],
                ],
                [
                    'stage_id' => 12,
                    'etudiant' => ['specialite' => ['departement_id' => 1]],
                    'encadrant_academique' => ['id' => 'ENS-5'],
                    'taille_jury_requise' => 2,
                    'nombre_jures_a_ajouter_hors_encadrant' => 1,
                    'jury_candidate_ids' => ['ENS-6'],
                ],
            ],
        ];
    }
}
