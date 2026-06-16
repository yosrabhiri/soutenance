<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Salle;

class SalleSeeder extends Seeder
{
    public function run(): void
    {
        $salles = [
            // Département 1
            ['nom' => 'G1', 'departement_id' => 1],
            ['nom' => 'G2', 'departement_id' => 1],
            ['nom' => 'G3', 'departement_id' => 1],
            ['nom' => 'G4', 'departement_id' => 1],

            // Département 2
            ['nom' => 'K1', 'departement_id' => 2],
            ['nom' => 'K2', 'departement_id' => 2],
            ['nom' => 'K3', 'departement_id' => 2],
            ['nom' => 'K4', 'departement_id' => 2],

            // Département 3
            ['nom' => 'J1', 'departement_id' => 3],
            ['nom' => 'J2', 'departement_id' => 3],
            ['nom' => 'J3', 'departement_id' => 3],
            ['nom' => 'J4', 'departement_id' => 3],

            // Département 4
            ['nom' => 'M1', 'departement_id' => 4],
            ['nom' => 'M2', 'departement_id' => 4],
            ['nom' => 'M3', 'departement_id' => 4],
            ['nom' => 'M4', 'departement_id' => 4],

            // Département 5
            ['nom' => 'I1', 'departement_id' => 5],
            ['nom' => 'I2', 'departement_id' => 5],
            ['nom' => 'I3', 'departement_id' => 5],
            ['nom' => 'I4', 'departement_id' => 5],
        ];

        foreach ($salles as $salle) {
            Salle::firstOrCreate(
                ['nom' => $salle['nom'], 'departement_id' => $salle['departement_id']],
                $salle
            );
        }
    }
}
