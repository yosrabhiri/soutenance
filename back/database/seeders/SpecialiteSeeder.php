<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Niveau;
use App\Models\Specialite;
use App\Models\Departement;

class SpecialiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ------------------ Départements ------------------
        $depInfo = Departement::where('code', 'INFO')->first();
        $depMeca = Departement::where('code', 'GM')->first();
        $depEner = Departement::where('code', 'ENER')->first();
        $depEEA  = Departement::where('code', 'EE')->first();
        $depGC   = Departement::where('code','GC')->first();

        // ------------------ CYCLE PREPARATOIRE INTEGRE ------------------
        $prepaA1 = Niveau::where('nom','A1')->where('diplome_id',1)->first();
        $prepaA2 = Niveau::where('nom','A2')->where('diplome_id',1)->first();

        Specialite::create([
            'niveau_id' => $prepaA1->id,
            'nom' => 'A1-Cycle Préparatoire-Scientifique-Informatique [Intégré] (Prepa-A1)',
            'departement_id' => $depInfo->id
        ]);
        Specialite::create([
            'niveau_id' => $prepaA2->id,
            'nom' => 'A2-Cycle Préparatoire-Scientifique-Informatique [Intégré] (Prepa-A2)',
            'departement_id' => $depInfo->id
        ]);

        // ------------------ INGENIEUR INFORMATIQUE ------------------
        $ingA1 = Niveau::where('nom','A1')->where('diplome_id',2)->first();
        $ingA2 = Niveau::where('nom','A2')->where('diplome_id',2)->first();
        $ingA3 = Niveau::where('nom','A3')->where('diplome_id',2)->first();

        Specialite::create([
            'niveau_id' => $ingA1->id,
            'nom' => 'A1-DNI-Informatique-Tronc Commun [DNI: Diplôme National d\'Ingénieur] (FI: Formation d\'Ingénieurs)',
            'departement_id' => $depInfo->id
        ]);

        $ing_specialites = [
            ['nom' => 'A2-DNI-Informatique-Génie Logiciel [DNI: Diplôme National d\'Ingénieur] (ING-A2-GL)', 'dep' => $depInfo],
            ['nom' => 'A2-DNI-Informatique-Informatique Industrielle (ING-A2-II)', 'dep' => $depInfo],
            ['nom' => 'A3-DNI-Informatique-Génie Logiciel (ING-A3-GL)', 'dep' => $depInfo],
            ['nom' => 'A3-DNI-Informatique-Informatique Industrielle (ING-A3-II)', 'dep' => $depInfo],
        ];

        foreach($ing_specialites as $spec){
            Specialite::create([
                'niveau_id' => ($spec['nom'][0] === 'A2') ? $ingA2->id : $ingA3->id,
                'nom' => $spec['nom'],
                'departement_id' => $spec['dep']->id
            ]);
        }

        // ------------------ LICENCE NATIONALE ------------------
        $licA1 = Niveau::where('nom','A1')->where('diplome_id',3)->first();
        $licA2 = Niveau::where('nom','A2')->where('diplome_id',3)->first();
        $licA3 = Niveau::where('nom','A3')->where('diplome_id',3)->first();

        $licA1_specialites = [
            ['nom'=>'A1-Licence en EEA-Tronc Commun (LEEA-A1)','dep'=>$depEEA],
            ['nom'=>'A1-Licence en Electro-Mécanique-Tronc Commun (LEM-A1)','dep'=>$depMeca],
            ['nom'=>'A1-Licence en Génie Civil-Tronc Commun (LGC-A1)','dep'=>$depGC],
            ['nom'=>'A1-Licence en Genie Energétique-Froid et Climatisation (LGEnerg-A1)','dep'=>$depEner],
            ['nom'=>'A1-Licence en Génie Mécanique-Tronc Commun (LGM-A1)','dep'=>$depMeca],
            ['nom'=>'A1-Licence en Ingénièrie des Systèmes de l\'Informatique-Systèmes Embarqués et IoT (LISI-A1)','dep'=>$depInfo],
            ['nom'=>'A1-Licence en Sciences de l\'Informatique-Génie Logiciel et SI (LSI-A1)','dep'=>$depInfo]
        ];
        foreach($licA1_specialites as $spec){
            Specialite::create([
                'niveau_id'=>$licA1->id,
                'nom'=>$spec['nom'],
                'departement_id'=>$spec['dep']->id
            ]);
        }

        $licA2_specialites = [
            ['nom'=>'A2-Licence en EEA-Automatique et Informatique Industrielles','dep'=>$depEEA],
            ['nom'=>'A2-Licence en EEA-Electronique Industrielle','dep'=>$depEEA],
            ['nom'=>'A2-Licence en EEA-Systèmes Embarqués','dep'=>$depEEA],
            ['nom'=>'A2-Licence en Electro-Mécanique-Mécatronique Automobile','dep'=>$depMeca],
            ['nom'=>'A2-Licence en Electro-Mécanique-Mécatronique Industrielle','dep'=>$depMeca],
            ['nom'=>'A2-Licence en Génie Civil-Bâtiments','dep'=>$depGC],
            ['nom'=>'A2-Licence en Génie Civil-Ponts et chaussées','dep'=>$depGC],
            ['nom'=>'A2-Licence en Genie Energétique-Froid et Climatisation','dep'=>$depEner],
            ['nom'=>'A2-Licence en Génie Mécanique-Conception et Production intégrées','dep'=>$depMeca],
            ['nom'=>'A2-Licence en Génie Mécanique-Productique','dep'=>$depMeca],
            ['nom'=>'A2-Licence en Ingénièrie des Systèmes de l\'Informatique-Systèmes Embarqués et internet des objets','dep'=>$depInfo],
            ['nom'=>'A2-Licence en Sciences de l\'Informatique-Génie Logiciel et SI','dep'=>$depInfo]
        ];
        foreach($licA2_specialites as $spec){
            Specialite::create([
                'niveau_id'=>$licA2->id,
                'nom'=>$spec['nom'],
                'departement_id'=>$spec['dep']->id
            ]);
        }

        $licA3_specialites = [
            ['nom'=>'A3-Licence en EEA-Automatique et Informatique Industrielles','dep'=>$depEEA],
            ['nom'=>'A3-Licence en EEA-Electronique Industrielle','dep'=>$depEEA],
            ['nom'=>'A3-Licence en EEA-Systèmes Embarqués','dep'=>$depEEA],
            ['nom'=>'A3-Licence en Electro-Mécanique-Mécatronique Automobile','dep'=>$depMeca],
            ['nom'=>'A3-Licence en Electro-Mécanique-Mécatronique Industrielle','dep'=>$depMeca],
            ['nom'=>'A3-Licence en Génie Civil-Bâtiments','dep'=>$depGC],
            ['nom'=>'A3-Licence en Génie Civil-Ponts et chaussées','dep'=>$depGC],
            ['nom'=>'A3-Licence en Genie Energétique-Froid et Climatisation','dep'=>$depEner],
            ['nom'=>'A3-Licence en Génie Mécanique-Conception et Production intégrées','dep'=>$depMeca],
            ['nom'=>'A3-Licence en Génie Mécanique-Productique','dep'=>$depMeca],
            ['nom'=>'A3-Licence en Ingénièrie des Systèmes de l\'Informatique-Systèmes Embarqués et internet des objets','dep'=>$depInfo],
            ['nom'=>'A3-Licence en Sciences de l\'Informatique-Génie Logiciel et SI','dep'=>$depInfo]
        ];
        foreach($licA3_specialites as $spec){
            Specialite::create([
                'niveau_id'=>$licA3->id,
                'nom'=>$spec['nom'],
                'departement_id'=>$spec['dep']->id
            ]);
        }

        // ------------------ MASTERS ------------------
        $masterA1 = Niveau::where('nom','A1')->where('diplome_id',4)->first();
        $masterA2 = Niveau::where('nom','A2')->where('diplome_id',4)->first();

        $masterA1_specialites = [
            ['nom'=>'A1-Mastère Professionnel en Génie Mécanique-Tronc Commun','dep'=>$depMeca],
            ['nom'=>'A1-Mastère Recherche en Génie Mécanique-Tronc Commun','dep'=>$depMeca],
            ['nom'=>'A1-Mastère Recherche-Systèmes pérvasifs intelligents [INFO]','dep'=>$depInfo],
            ['nom'=>'A1-Mastère Recherche-Microsystèmes et Système Electroniques Embarquées-Tronc Commun','dep'=>$depEEA],
            ['nom'=>'A1-Master Professionnel en Maîtrise et Exploitation Rationnelle de l’Energie','dep'=>$depEner]
        ];
        foreach($masterA1_specialites as $spec){
            Specialite::create([
                'niveau_id'=>$masterA1->id,
                'nom'=>$spec['nom'],
                'departement_id'=>$spec['dep']->id
            ]);
        }

        $masterA2_specialites = [
            ['nom'=>'A2-Master Professionnel en Maîtrise et Exploitation Rationnelle de l’Energie-Efficacité Energétique dans les bâtiments','dep'=>$depEner],
            ['nom'=>'A2-Mastère Professionel en Génie Mécanique-Génie des Procédés de Production Mécanique','dep'=>$depMeca],
            ['nom'=>'A2-Mastère Recherche en Génie Mécanique-Mécanique des Matériaux','dep'=>$depMeca],
            ['nom'=>'A2-Mastère Recherche en Génie Mécanique-Systèmes Mécaniques','dep'=>$depMeca],
            ['nom'=>'A2-Mastère Recherche-Systèmes pérvasifs intelligents [INFO]','dep'=>$depInfo],
            ['nom'=>'A2-Mastère Recherche-Microsystèmes et Système Electroniques Embarquées-Systèmes Electroniques Embarqués','dep'=>$depEEA],
            ['nom'=>'A2-Mastère Recherche-Microsystèmes et Système Electroniques Embarquées-Microsystèmes Electroniques','dep'=>$depEEA],
            ['nom'=>'A2-Mastère Recherche-Microsystème et Système Electroniques Embarquée-Mobilité Durable et Energies Propres','dep'=>$depEEA],
            ['nom'=>'A2-Mastère Professionel en Génie Mécanique-Productique et Automatique Industrielle','dep'=>$depMeca],
            ['nom'=>'A2-Master Professionnel en Maîtrise et Exploitation Rationnelle de l’Energie-Efficacité Energétique dans l\'Industrie','dep'=>$depEner]
        ];
        foreach($masterA2_specialites as $spec){
            Specialite::create([
                'niveau_id'=>$masterA2->id,
                'nom'=>$spec['nom'],
                'departement_id'=>$spec['dep']->id
            ]);
        }
    }
}
