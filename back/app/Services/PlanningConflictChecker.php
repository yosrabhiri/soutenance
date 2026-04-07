<?php

namespace App\Services;

use Illuminate\Support\Collection;

class PlanningConflictChecker
{
    public function isAssignmentFeasible(array $assignment, array $currentAssignments, array $planningInput): bool
    {
        return $this->countViolationsForAssignment($assignment, $currentAssignments, $planningInput) === 0;
    }

    public function countHardViolations(array $assignments, array $planningInput): int
    {
        $violations = 0;
        $acceptedAssignments = [];

        foreach ($assignments as $assignment) {
            $violations += $this->countViolationsForAssignment($assignment, $acceptedAssignments, $planningInput);
            $acceptedAssignments[] = $assignment;
        }

        return $violations;
    }

    public function countViolationsForAssignment(array $assignment, array $currentAssignments, array $planningInput): int
    {
        $violations = 0;
        $stageById = $this->indexBy($planningInput['stages'] ?? [], 'stage_id');
        $teacherById = $this->indexBy($planningInput['enseignants'] ?? [], 'id');
        $roomById = $this->indexBy($planningInput['salles'] ?? [], 'id');

        $stage = $stageById[$assignment['stage_id']] ?? null;
        if (!$stage) {
            return 1;
        }

        $juryIds = array_values(array_unique($assignment['jury_ids'] ?? []));
        $encadrantId = $stage['encadrant_academique']['id'] ?? null;
        $requiredJurySize = (int) ($stage['taille_jury_requise'] ?? 0);
        $departementId = $stage['etudiant']['specialite']['departement_id'] ?? null;

        if (!$assignment['creneau_id'] || !$assignment['salle_id']) {
            $violations++;
        }

        $room = $roomById[$assignment['salle_id']] ?? null;
        if (!$room) {
            $violations++;
        } else {
            $roomDepartmentId = $room['departement']['id'] ?? null;

            if ($departementId && $roomDepartmentId !== $departementId) {
                $violations++;
            }
        }

        if (!$encadrantId || !in_array($encadrantId, $juryIds, true)) {
            $violations++;
        }

        if (count($juryIds) !== $requiredJurySize) {
            $violations++;
        }

        foreach ($juryIds as $teacherId) {
            $teacher = $teacherById[$teacherId] ?? null;

            if (!$teacher) {
                $violations++;
                continue;
            }

            $teacherDepartmentId = $teacher['departement']['id'] ?? null;
            if ($departementId && $teacherDepartmentId !== $departementId) {
                $violations++;
            }

            $slotStatus = $this->teacherStatusForSlot($teacher, $assignment['creneau_id']);
            if ($slotStatus === 'unavailable') {
                $violations++;
            }
        }

        foreach ($currentAssignments as $existingAssignment) {
            if (($existingAssignment['stage_id'] ?? null) === $assignment['stage_id']) {
                $violations++;
            }

            if (($existingAssignment['creneau_id'] ?? null) !== $assignment['creneau_id']) {
                continue;
            }

            if (($existingAssignment['salle_id'] ?? null) === $assignment['salle_id']) {
                $violations++;
            }

            $existingJuryIds = array_values(array_unique($existingAssignment['jury_ids'] ?? []));
            if (count(array_intersect($existingJuryIds, $juryIds)) > 0) {
                $violations++;
            }

            $existingStage = $stageById[$existingAssignment['stage_id'] ?? null] ?? null;
            $existingEncadrantId = $existingStage['encadrant_academique']['id'] ?? null;
            if ($existingEncadrantId && $encadrantId && $existingEncadrantId === $encadrantId) {
                $violations++;
            }
        }

        return $violations;
    }

    public function teacherStatusForSlot(array $teacher, int $creneauId): string
    {
        foreach ($teacher['disponibilites_par_creneau'] ?? [] as $slot) {
            if (($slot['creneau_id'] ?? null) === $creneauId) {
                return $slot['statut'] ?? 'available';
            }
        }

        return 'available';
    }

    protected function indexBy(array|Collection $rows, string $key): array
    {
        $indexed = [];

        foreach ($rows as $row) {
            if (array_key_exists($key, $row)) {
                $indexed[$row[$key]] = $row;
            }
        }

        return $indexed;
    }

}
