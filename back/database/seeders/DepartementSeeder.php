<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Departement;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        $departements = [
            ['code' => 'INFO', 'nom' => 'Informatique'],
            ['code' => 'GM', 'nom' => 'Génie Mécanique'],
            ['code' => 'GC', 'nom' => 'Génie Civil'],
            ['code' => 'ENER', 'nom' => 'Énergétique'],
            ['code' => 'EE', 'nom' => 'Génie Électronique'],
        ];

        foreach ($departements as $dep) {
            Departement::firstOrCreate(['code' => $dep['code']], $dep);
        }
    }
}
