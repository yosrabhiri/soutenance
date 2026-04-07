<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PlanningInputTestSeeder extends Seeder
{
    public function run(): void
    {
        $depInfo = DB::table('departements')->where('code', 'INFO')->first();

        if (!$depInfo) {
            $this->command?->error('Departement INFO introuvable. Lance d abord les seeders de base.');
            return;
        }

        $diplomeLicence = DB::table('diplomes')->where('nom', 'LICENCE NATIONALE')->first();
        $diplomeIngenieur = DB::table('diplomes')->where('nom', 'INGENIEUR INFORMATIQUE')->first();
        $diplomeMaster = DB::table('diplomes')->where('nom', 'MASTER')->first();

        $niveauLicence = DB::table('niveaux')
            ->where('diplome_id', $diplomeLicence?->id)
            ->where('nom', 'A3')
            ->first();

        $niveauIngenieur = DB::table('niveaux')
            ->where('diplome_id', $diplomeIngenieur?->id)
            ->where('nom', 'A3')
            ->first();

        $niveauMaster = DB::table('niveaux')
            ->where('diplome_id', $diplomeMaster?->id)
            ->where('nom', 'A2')
            ->first();

        $specialiteLicence = DB::table('specialites')
            ->where('departement_id', $depInfo->id)
            ->where('niveau_id', $niveauLicence?->id)
            ->first();

        $specialiteIngenieur = DB::table('specialites')
            ->where('departement_id', $depInfo->id)
            ->where('niveau_id', $niveauIngenieur?->id)
            ->first();

        $specialiteMaster = DB::table('specialites')
            ->where('departement_id', $depInfo->id)
            ->where('niveau_id', $niveauMaster?->id)
            ->first();

        if (!$diplomeLicence || !$diplomeIngenieur || !$diplomeMaster || !$specialiteLicence || !$specialiteIngenieur || !$specialiteMaster) {
            $this->command?->error('Diplomes, niveaux ou specialites manquants. Lance d abord DatabaseSeeder.');
            return;
        }

        $enseignants = [
            [
                'Code_enseignant' => 'ENS-PLAN-001',
                'NomEnseignant' => 'Amine',
                'PrenomEnseignant' => 'Ben Salah',
                'Nom_Prenom_Enseignant' => 'Amine Ben Salah',
                'Email' => 'amine.bensalah.planning@test.local',
                'departement_id' => $depInfo->id,
            ],
            [
                'Code_enseignant' => 'ENS-PLAN-002',
                'NomEnseignant' => 'Ines',
                'PrenomEnseignant' => 'Trabelsi',
                'Nom_Prenom_Enseignant' => 'Ines Trabelsi',
                'Email' => 'ines.trabelsi.planning@test.local',
                'departement_id' => $depInfo->id,
            ],
            [
                'Code_enseignant' => 'ENS-PLAN-003',
                'NomEnseignant' => 'Karim',
                'PrenomEnseignant' => 'Mansouri',
                'Nom_Prenom_Enseignant' => 'Karim Mansouri',
                'Email' => 'karim.mansouri.planning@test.local',
                'departement_id' => $depInfo->id,
            ],
            [
                'Code_enseignant' => 'ENS-PLAN-004',
                'NomEnseignant' => 'Sarra',
                'PrenomEnseignant' => 'Jlassi',
                'Nom_Prenom_Enseignant' => 'Sarra Jlassi',
                'Email' => 'sarra.jlassi.planning@test.local',
                'departement_id' => $depInfo->id,
            ],
        ];

        foreach ($enseignants as $enseignant) {
            DB::table('enseignants')->updateOrInsert(
                ['Code_enseignant' => $enseignant['Code_enseignant']],
                array_merge($enseignant, [
                    'password' => Hash::make('password'),
                    'role' => 'enseignant',
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }

        $societes = [
            [
                'nom' => 'TechNova',
                'adresse' => 'Tunis',
                'secteur_activite' => 'Developpement logiciel',
                'telephone' => '20000001',
                'email' => 'contact@technova.test',
            ],
            [
                'nom' => 'DataFlow',
                'adresse' => 'Sousse',
                'secteur_activite' => 'Data engineering',
                'telephone' => '20000002',
                'email' => 'contact@dataflow.test',
            ],
            [
                'nom' => 'CloudAxis',
                'adresse' => 'Sfax',
                'secteur_activite' => 'Cloud',
                'telephone' => '20000003',
                'email' => 'contact@cloudaxis.test',
            ],
        ];

        foreach ($societes as $societe) {
            DB::table('societes')->updateOrInsert(
                ['email' => $societe['email']],
                array_merge($societe, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }

        $societesByEmail = DB::table('societes')
            ->whereIn('email', collect($societes)->pluck('email'))
            ->get()
            ->keyBy('email');

        $encadrantsPro = [
            [
                'email' => 'encad.pro1@test.local',
                'societe_email' => 'contact@technova.test',
                'nom_complet' => 'Hatem Rekik',
                'fonction' => 'Chef de projet',
                'departement' => 'IT',
            ],
            [
                'email' => 'encad.pro2@test.local',
                'societe_email' => 'contact@dataflow.test',
                'nom_complet' => 'Meriem Gharbi',
                'fonction' => 'Data lead',
                'departement' => 'Data',
            ],
            [
                'email' => 'encad.pro3@test.local',
                'societe_email' => 'contact@cloudaxis.test',
                'nom_complet' => 'Nader Chaabane',
                'fonction' => 'Cloud architect',
                'departement' => 'Platform',
            ],
        ];

        foreach ($encadrantsPro as $encadrant) {
            DB::table('encadrant_professionnels')->updateOrInsert(
                ['email' => $encadrant['email']],
                [
                    'societe_id' => $societesByEmail[$encadrant['societe_email']]->id,
                    'nom_complet' => $encadrant['nom_complet'],
                    'fonction' => $encadrant['fonction'],
                    'departement' => $encadrant['departement'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $encadrantsProByEmail = DB::table('encadrant_professionnels')
            ->whereIn('email', collect($encadrantsPro)->pluck('email'))
            ->get()
            ->keyBy('email');

        $etudiants = [
            [
                'email' => 'licence.planning@test.local',
                'numero_inscription' => 'LIC-PLAN-001',
                'prenom' => 'Aya',
                'nom' => 'Kefi',
                'diplome_id' => $diplomeLicence->id,
                'niveau_id' => $niveauLicence->id,
                'specialite_id' => $specialiteLicence->id,
            ],
            [
                'email' => 'licence2.planning@test.local',
                'numero_inscription' => 'LIC-PLAN-002',
                'prenom' => 'Mariem',
                'nom' => 'Sassi',
                'diplome_id' => $diplomeLicence->id,
                'niveau_id' => $niveauLicence->id,
                'specialite_id' => $specialiteLicence->id,
            ],
            [
                'email' => 'licence3.planning@test.local',
                'numero_inscription' => 'LIC-PLAN-003',
                'prenom' => 'Nour',
                'nom' => 'Abid',
                'diplome_id' => $diplomeLicence->id,
                'niveau_id' => $niveauLicence->id,
                'specialite_id' => $specialiteLicence->id,
            ],
            [
                'email' => 'ingenieur.planning@test.local',
                'numero_inscription' => 'ING-PLAN-001',
                'prenom' => 'Youssef',
                'nom' => 'Hamdi',
                'diplome_id' => $diplomeIngenieur->id,
                'niveau_id' => $niveauIngenieur->id,
                'specialite_id' => $specialiteIngenieur->id,
            ],
            [
                'email' => 'ingenieur2.planning@test.local',
                'numero_inscription' => 'ING-PLAN-002',
                'prenom' => 'Salma',
                'nom' => 'Dridi',
                'diplome_id' => $diplomeIngenieur->id,
                'niveau_id' => $niveauIngenieur->id,
                'specialite_id' => $specialiteIngenieur->id,
            ],
            [
                'email' => 'ingenieur3.planning@test.local',
                'numero_inscription' => 'ING-PLAN-003',
                'prenom' => 'Bilel',
                'nom' => 'Mrad',
                'diplome_id' => $diplomeIngenieur->id,
                'niveau_id' => $niveauIngenieur->id,
                'specialite_id' => $specialiteIngenieur->id,
            ],
            [
                'email' => 'master.planning@test.local',
                'numero_inscription' => 'MAS-PLAN-001',
                'prenom' => 'Rania',
                'nom' => 'Toumi',
                'diplome_id' => $diplomeMaster->id,
                'niveau_id' => $niveauMaster->id,
                'specialite_id' => $specialiteMaster->id,
            ],
            [
                'email' => 'master2.planning@test.local',
                'numero_inscription' => 'MAS-PLAN-002',
                'prenom' => 'Hiba',
                'nom' => 'Gharbi',
                'diplome_id' => $diplomeMaster->id,
                'niveau_id' => $niveauMaster->id,
                'specialite_id' => $specialiteMaster->id,
            ],
            [
                'email' => 'master3.planning@test.local',
                'numero_inscription' => 'MAS-PLAN-003',
                'prenom' => 'Skander',
                'nom' => 'Kallel',
                'diplome_id' => $diplomeMaster->id,
                'niveau_id' => $niveauMaster->id,
                'specialite_id' => $specialiteMaster->id,
            ],
        ];

        foreach ($etudiants as $etudiant) {
            DB::table('etudiants')->updateOrInsert(
                ['email' => $etudiant['email']],
                array_merge($etudiant, [
                    'password' => Hash::make('password'),
                    'cin' => (string) random_int(10000000, 99999999),
                    'date_delivrance_cin' => '2020-01-01',
                    'lieu_delivrance_cin' => 'Tunis',
                    'adresse' => 'Adresse de test',
                    'code_postal' => '1000',
                    'date_naissance' => '2000-01-01',
                    'lieu_naissance' => 'Tunis',
                    'nationalite' => 'Tunisienne',
                    'telephone' => '50000000',
                    'etat_civil' => 'célibataire',
                    'etat_militaire' => 'non_concerné',
                    'genre' => 'féminin',
                    'annee_universitaire' => '2025/2026',
                    'annee_bac' => 2021,
                    'moyenne_bac' => 15.50,
                    'session_bac' => 'principale',
                    'mention_bac' => 'bien',
                    'section_bac' => 'Informatique',
                    'pays_bac' => 'Tunisie',
                    'statut_universitaire' => 'Actif',
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }

        $etudiantsByEmail = DB::table('etudiants')
            ->whereIn('email', collect($etudiants)->pluck('email'))
            ->get()
            ->keyBy('email');

        $stages = [
            [
                'etudiant_email' => 'licence.planning@test.local',
                'societe_email' => 'contact@technova.test',
                'encadrant_pro_email' => 'encad.pro1@test.local',
                'enseignant_id' => 'ENS-PLAN-001',
                'date_debut' => '2026-01-15',
                'date_fin' => '2026-05-15',
                'type' => 'pfe',
            ],
            [
                'etudiant_email' => 'licence2.planning@test.local',
                'societe_email' => 'contact@dataflow.test',
                'encadrant_pro_email' => 'encad.pro2@test.local',
                'enseignant_id' => 'ENS-PLAN-002',
                'date_debut' => '2026-01-20',
                'date_fin' => '2026-05-25',
                'type' => 'pfe',
            ],
            [
                'etudiant_email' => 'licence3.planning@test.local',
                'societe_email' => 'contact@cloudaxis.test',
                'encadrant_pro_email' => 'encad.pro3@test.local',
                'enseignant_id' => 'ENS-PLAN-003',
                'date_debut' => '2026-01-18',
                'date_fin' => '2026-05-30',
                'type' => 'pfe',
            ],
            [
                'etudiant_email' => 'ingenieur.planning@test.local',
                'societe_email' => 'contact@dataflow.test',
                'encadrant_pro_email' => 'encad.pro2@test.local',
                'enseignant_id' => 'ENS-PLAN-002',
                'date_debut' => '2026-01-15',
                'date_fin' => '2026-06-15',
                'type' => 'pfe',
            ],
            [
                'etudiant_email' => 'ingenieur2.planning@test.local',
                'societe_email' => 'contact@technova.test',
                'encadrant_pro_email' => 'encad.pro1@test.local',
                'enseignant_id' => 'ENS-PLAN-001',
                'date_debut' => '2026-01-17',
                'date_fin' => '2026-06-12',
                'type' => 'pfe',
            ],
            [
                'etudiant_email' => 'ingenieur3.planning@test.local',
                'societe_email' => 'contact@cloudaxis.test',
                'encadrant_pro_email' => 'encad.pro3@test.local',
                'enseignant_id' => 'ENS-PLAN-004',
                'date_debut' => '2026-01-22',
                'date_fin' => '2026-06-20',
                'type' => 'pfe',
            ],
            [
                'etudiant_email' => 'master.planning@test.local',
                'societe_email' => 'contact@cloudaxis.test',
                'encadrant_pro_email' => 'encad.pro3@test.local',
                'enseignant_id' => 'ENS-PLAN-003',
                'date_debut' => '2026-02-01',
                'date_fin' => '2026-06-30',
                'type' => 'pfe',
            ],
            [
                'etudiant_email' => 'master2.planning@test.local',
                'societe_email' => 'contact@technova.test',
                'encadrant_pro_email' => 'encad.pro1@test.local',
                'enseignant_id' => 'ENS-PLAN-001',
                'date_debut' => '2026-02-05',
                'date_fin' => '2026-06-22',
                'type' => 'pfe',
            ],
            [
                'etudiant_email' => 'master3.planning@test.local',
                'societe_email' => 'contact@dataflow.test',
                'encadrant_pro_email' => 'encad.pro2@test.local',
                'enseignant_id' => 'ENS-PLAN-004',
                'date_debut' => '2026-02-10',
                'date_fin' => '2026-06-25',
                'type' => 'pfe',
            ],
        ];

        foreach ($stages as $stage) {
            $etudiant = $etudiantsByEmail[$stage['etudiant_email']];
            $societe = $societesByEmail[$stage['societe_email']];
            $encadrantPro = $encadrantsProByEmail[$stage['encadrant_pro_email']];

            DB::table('stages')->updateOrInsert(
                ['etudiant_id' => $etudiant->id],
                [
                    'societe_id' => $societe->id,
                    'encadrant_professionnel_id' => $encadrantPro->id,
                    'enseignant_id' => $stage['enseignant_id'],
                    'description_taches' => 'Stage de test pour la preparation du planning.',
                    'date_debut' => $stage['date_debut'],
                    'date_fin' => $stage['date_fin'],
                    'etat_validation' => 'accepte',
                    'validation_academique' => 'valide',
                    'type' => $stage['type'],
                    'url_overleaf' => 'https://example.test/' . Str::slug($etudiant->prenom . '-' . $etudiant->nom),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->command?->info('PlanningInputTestSeeder termine: enseignants, etudiants et stages de test ajoutes.');
    }
}
