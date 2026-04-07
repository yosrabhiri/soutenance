<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Diplome;

class DiplomeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $diplomes = [
            ['nom' => 'CYCLE PREPARATOIRE INTEGRE'],
            ['nom' => 'INGENIEUR INFORMATIQUE'],
            ['nom' => 'LICENCE NATIONALE'],
            ['nom' => 'MASTER'],
        ];

        foreach ($diplomes as $diplome) {
            Diplome::firstOrCreate($diplome);
        }
    }
}
