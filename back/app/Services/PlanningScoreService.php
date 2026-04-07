<?php

namespace App\Services;

use Illuminate\Support\Collection;

class PlanningScoreService
{
    public function __construct(
        protected PlanningConflictChecker $conflictChecker
    ) {
    }

    public function scoreSolution(array $assignments, array $planningInput): int
    {
        $hardViolations = $this->conflictChecker->countHardViolations($assignments, $planningInput);
        $softScore = 0;

        foreach ($assignments as $assignment) {
            $softScore += $this->scoreAssignment($assignment, $assignments, $planningInput);
        }

        return $softScore - ($hardViolations * 100000);
    }

    public function scoreAssignment(array $assignment, array $allAssignments, array $planningInput): int
    {
        $teachers = $this->indexBy($planningInput['enseignants'] ?? [], 'id');
        $stages = $this->indexBy($planningInput['stages'] ?? [], 'stage_id');
        $slots = $this->indexBy($planningInput['creneaux'] ?? [], 'id');
        $stage = $stages[$assignment['stage_id']] ?? null;
        $slot = $slots[$assignment['creneau_id']] ?? null;

        if (!$stage || !$slot) {
            return -100000;
        }

        $score = 0;

        foreach (array_values(array_unique($assignment['jury_ids'] ?? [])) as $teacherId) {
            $teacher = $teachers[$teacherId] ?? null;
            if (!$teacher) {
                $score -= 1000;
                continue;
            }

            $status = $this->conflictChecker->teacherStatusForSlot($teacher, $assignment['creneau_id']);
            $score += match ($status) {
                'preferred' => 30,
                'available' => 10,
                'avoid' => -20,
                'unavailable' => -100000,
                default => 0,
            };

            $score -= $this->teacherLoadPenalty($teacherId, $allAssignments);
        }

        $score -= $this->sameDayLoadPenalty((string) $slot['date'], $allAssignments, $planningInput);
        $score += $this->newDayUsageBonus((string) $slot['date'], $allAssignments, $planningInput);

        return $score;
    }

    protected function teacherLoadPenalty(string $teacherId, array $assignments): int
    {
        $load = 0;

        foreach ($assignments as $assignment) {
            if (in_array($teacherId, $assignment['jury_ids'] ?? [], true)) {
                $load++;
            }
        }

        return max(0, $load - 1) * 4;
    }

    protected function sameDayLoadPenalty(string $date, array $assignments, array $planningInput): int
    {
        $slotDates = $this->slotDates($planningInput);
        $sameDayAssignments = 0;

        foreach ($assignments as $assignment) {
            if (($slotDates[$assignment['creneau_id']] ?? null) === $date) {
                $sameDayAssignments++;
            }
        }

        return max(0, $sameDayAssignments - 1) * 12;
    }

    protected function newDayUsageBonus(string $date, array $assignments, array $planningInput): int
    {
        $slotDates = $this->slotDates($planningInput);

        foreach ($assignments as $assignment) {
            if (($slotDates[$assignment['creneau_id']] ?? null) === $date) {
                return 0;
            }
        }

        return 15;
    }

    protected function slotDates(array $planningInput): array
    {
        $dates = [];

        foreach ($planningInput['creneaux'] ?? [] as $creneau) {
            if (isset($creneau['id'], $creneau['date'])) {
                $dates[$creneau['id']] = $creneau['date'];
            }
        }

        return $dates;
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
