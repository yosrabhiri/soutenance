<?php

namespace App\Services;

use App\Models\PeriodeSoutenance;
use App\Models\CreneauSoutenance;
use Carbon\Carbon;

class GenerateCreneauxService
{
    public function generate(PeriodeSoutenance $periode): void
    {
        CreneauSoutenance::where('periode_soutenance_id', $periode->id)->delete();

        $currentDate = Carbon::parse($periode->date_debut);
        $endDate = Carbon::parse($periode->date_fin);

        $slots = [
            ['code_slot' => 'S1', 'heure_debut' => '08:30:00', 'ordre' => 1],
            ['code_slot' => 'S2', 'heure_debut' => '10:10:00', 'ordre' => 2],
            ['code_slot' => 'S3', 'heure_debut' => '11:50:00', 'ordre' => 3],
            ['code_slot' => 'S4', 'heure_debut' => '13:50:00', 'ordre' => 4],
            ['code_slot' => 'S5', 'heure_debut' => '15:30:00', 'ordre' => 5],
            ['code_slot' => 'S6', 'heure_debut' => '17:10:00', 'ordre' => 6],
        ];

        while ($currentDate->lte($endDate)) {
            foreach ($slots as $slot) {
                $start = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $slot['heure_debut']);
                $end = (clone $start)->addMinutes($periode->duree_minutes);

                CreneauSoutenance::create([
                    'periode_soutenance_id' => $periode->id,
                    'date' => $currentDate->toDateString(),
                    'code_slot' => $slot['code_slot'],
                    'heure_debut' => $start->format('H:i:s'),
                    'heure_fin' => $end->format('H:i:s'),
                    'ordre' => $slot['ordre'],
                ]);
            }

            $currentDate->addDay();
        }
    }
}