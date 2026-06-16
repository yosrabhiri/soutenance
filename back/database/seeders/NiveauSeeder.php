<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Niveau;
use App\Models\Diplome;

class NiveauSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // CYCLE PREPARATOIRE INTEGRE
        $prepa = Diplome::where('nom','CYCLE PREPARATOIRE INTEGRE')->first();
        Niveau::firstOrCreate(['nom'=>'A1','diplome_id'=>$prepa->id]);
        Niveau::firstOrCreate(['nom'=>'A2','diplome_id'=>$prepa->id]);

        // INGENIEUR INFORMATIQUE
        $ing = Diplome::where('nom','INGENIEUR INFORMATIQUE')->first();
        Niveau::firstOrCreate(['nom'=>'A1','diplome_id'=>$ing->id]);
        Niveau::firstOrCreate(['nom'=>'A2','diplome_id'=>$ing->id]);
        Niveau::firstOrCreate(['nom'=>'A3','diplome_id'=>$ing->id]);

        // LICENCE NATIONALE
        $lic = Diplome::where('nom','LICENCE NATIONALE')->first();
        Niveau::firstOrCreate(['nom'=>'A1','diplome_id'=>$lic->id]);
        Niveau::firstOrCreate(['nom'=>'A2','diplome_id'=>$lic->id]);
        Niveau::firstOrCreate(['nom'=>'A3','diplome_id'=>$lic->id]);

        // MASTER
        $master = Diplome::where('nom','MASTER')->first();
        Niveau::firstOrCreate(['nom'=>'A1','diplome_id'=>$master->id]);
        Niveau::firstOrCreate(['nom'=>'A2','diplome_id'=>$master->id]);
    }
}
