<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LargePlanningDatasetSeeder extends Seeder
{
    public function run(): void
    {
        $departements = DB::table('departements')->get();

        if ($departements->isEmpty()) {
            $this->command?->error('Aucun departement trouve. Lance d abord les seeders de base.');
            return;
        }

        $diplomes = DB::table('diplomes')->get()->keyBy('nom');
        $niveaux = DB::table('niveaux')->get();
        $specialites = DB::table('specialites')->get();

        $licenceA3Specialites = $specialites->filter(function ($specialite) use ($niveaux, $diplomes) {
            $niveau = $niveaux->firstWhere('id', $specialite->niveau_id);
            return $niveau
                && $niveau->nom === 'A3'
                && $niveau->diplome_id === ($diplomes['LICENCE NATIONALE']->id ?? null);
        })->values();

        $masterA2Specialites = $specialites->filter(function ($specialite) use ($niveaux, $diplomes) {
            $niveau = $niveaux->firstWhere('id', $specialite->niveau_id);
            return $niveau
                && $niveau->nom === 'A2'
                && $niveau->diplome_id === ($diplomes['MASTER']->id ?? null);
        })->values();

        $ingenieurA3Specialites = $specialites->filter(function ($specialite) use ($niveaux, $diplomes) {
            $niveau = $niveaux->firstWhere('id', $specialite->niveau_id);
            return $niveau
                && $niveau->nom === 'A3'
                && $niveau->diplome_id === ($diplomes['INGENIEUR INFORMATIQUE']->id ?? null);
        })->values();

        if ($licenceA3Specialites->isEmpty() || $masterA2Specialites->isEmpty() || $ingenieurA3Specialites->isEmpty()) {
            $this->command?->error('Specialites PFE introuvables pour Licence, Master ou Ingenieur.');
            return;
        }

        $teachersByDepartment = $this->seedTeachers($departements);
        $companies = $this->seedCompanies();
        $professionalSupervisors = $this->seedProfessionalSupervisors($companies);

        $distributions = [
            [
                'prefix' => 'LIC-BULK',
                'count' => 110,
                'diplome_id' => $diplomes['LICENCE NATIONALE']->id,
                'specialites' => $licenceA3Specialites,
                'enseignants' => $teachersByDepartment,
                'societes' => $companies,
                'encadrants' => $professionalSupervisors,
            ],
            [
                'prefix' => 'ING-BULK',
                'count' => 95,
                'diplome_id' => $diplomes['INGENIEUR INFORMATIQUE']->id,
                'specialites' => $ingenieurA3Specialites,
                'enseignants' => $teachersByDepartment,
                'societes' => $companies,
                'encadrants' => $professionalSupervisors,
            ],
            [
                'prefix' => 'MAS-BULK',
                'count' => 95,
                'diplome_id' => $diplomes['MASTER']->id,
                'specialites' => $masterA2Specialites,
                'enseignants' => $teachersByDepartment,
                'societes' => $companies,
                'encadrants' => $professionalSupervisors,
            ],
        ];

        foreach ($distributions as $distribution) {
            $this->seedStudentsAndStages(
                $distribution['prefix'],
                $distribution['count'],
                $distribution['diplome_id'],
                $distribution['specialites'],
                $niveaux,
                $teachersByDepartment,
                $companies,
                $professionalSupervisors
            );
        }

        $this->command?->info('LargePlanningDatasetSeeder termine: ~300 stages PFE et un pool large d enseignants ont ete ajoutes.');
    }

    protected function seedTeachers($departements): array
    {
        $teachersByDepartment = [];

        foreach ($departements as $departement) {
            $teachersByDepartment[$departement->id] = [];

            for ($i = 1; $i <= 12; $i++) {
                $code = sprintf('ENS-BULK-%02d-%02d', $departement->id, $i);
                $nom = sprintf('Teacher%s%s', $departement->code, $i);
                $prenom = sprintf('Dept%s', $departement->code);

                DB::table('enseignants')->updateOrInsert(
                    ['Code_enseignant' => $code],
                    [
                        'password' => Hash::make('password'),
                        'NomEnseignant' => $nom,
                        'PrenomEnseignant' => $prenom,
                        'Nom_Prenom_Enseignant' => $nom . ' ' . $prenom,
                        'Email' => strtolower($code) . '@test.local',
                        'role' => 'enseignant',
                        'departement_id' => $departement->id,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

                $teachersByDepartment[$departement->id][] = $code;
            }
        }

        return $teachersByDepartment;
    }

    protected function seedCompanies(): array
    {
        $companies = [];

        for ($i = 1; $i <= 40; $i++) {
            $email = sprintf('company.bulk.%03d@test.local', $i);

            DB::table('societes')->updateOrInsert(
                ['email' => $email],
                [
                    'nom' => sprintf('BulkCompany%03d', $i),
                    'adresse' => sprintf('Zone industrielle %03d', $i),
                    'secteur_activite' => 'Engineering and Software',
                    'telephone' => sprintf('7%07d', $i),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $companies[] = DB::table('societes')->where('email', $email)->first();
        }

        return $companies;
    }

    protected function seedProfessionalSupervisors(array $companies): array
    {
        $supervisors = [];

        foreach ($companies as $index => $company) {
            $email = sprintf('pro.bulk.%03d@test.local', $index + 1);

            DB::table('encadrant_professionnels')->updateOrInsert(
                ['email' => $email],
                [
                    'societe_id' => $company->id,
                    'nom_complet' => sprintf('Supervisor Bulk %03d', $index + 1),
                    'fonction' => 'Project Lead',
                    'departement' => 'Operations',
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $supervisors[] = DB::table('encadrant_professionnels')->where('email', $email)->first();
        }

        return $supervisors;
    }

    protected function seedStudentsAndStages(
        string $prefix,
        int $count,
        int $diplomeId,
        $specialites,
        $niveaux,
        array $teachersByDepartment,
        array $companies,
        array $professionalSupervisors
    ): void {
        for ($i = 1; $i <= $count; $i++) {
            $specialite = $specialites[($i - 1) % $specialites->count()];
            $niveau = $niveaux->firstWhere('id', $specialite->niveau_id);
            $teacherPool = $teachersByDepartment[$specialite->departement_id] ?? [];

            if (empty($teacherPool)) {
                continue;
            }

            $teacherId = $teacherPool[($i - 1) % count($teacherPool)];
            $company = $companies[($i - 1) % count($companies)];
            $professionalSupervisor = $professionalSupervisors[($i - 1) % count($professionalSupervisors)];
            $studentEmail = strtolower($prefix) . sprintf('.%03d@test.local', $i);

            DB::table('etudiants')->updateOrInsert(
                ['email' => $studentEmail],
                [
                    'numero_inscription' => sprintf('%s-%03d', $prefix, $i),
                    'cin' => sprintf('%08d', 20000000 + $i + $diplomeId * 1000),
                    'date_delivrance_cin' => '2020-01-01',
                    'lieu_delivrance_cin' => 'Tunis',
                    'prenom' => sprintf('Student%s%03d', $prefix, $i),
                    'nom' => sprintf('Batch%s', $specialite->departement_id),
                    'email' => $studentEmail,
                    'password' => Hash::make('password'),
                    'adresse' => 'Adresse planning bulk',
                    'code_postal' => '1000',
                    'date_naissance' => '2000-01-01',
                    'lieu_naissance' => 'Tunisie',
                    'nationalite' => 'Tunisienne',
                    'telephone' => sprintf('5%07d', $i),
                    'etat_civil' => 'célibataire',
                    'etat_militaire' => 'non_concerné',
                    'genre' => $i % 2 === 0 ? 'féminin' : 'masculin',
                    'annee_universitaire' => '2025/2026',
                    'annee_bac' => 2021,
                    'moyenne_bac' => 14.50,
                    'session_bac' => 'principale',
                    'mention_bac' => 'bien',
                    'section_bac' => 'Technique',
                    'pays_bac' => 'Tunisie',
                    'statut_universitaire' => 'Actif',
                    'diplome_id' => $diplomeId,
                    'niveau_id' => $niveau->id,
                    'specialite_id' => $specialite->id,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $student = DB::table('etudiants')->where('email', $studentEmail)->first();

            DB::table('stages')->updateOrInsert(
                ['etudiant_id' => $student->id],
                [
                    'societe_id' => $company->id,
                    'encadrant_professionnel_id' => $professionalSupervisor->id,
                    'enseignant_id' => $teacherId,
                    'description_taches' => 'Stage PFE bulk pour tests de generation des soutenances.',
                    'date_debut' => '2026-01-15',
                    'date_fin' => '2026-06-15',
                    'etat_validation' => 'accepte',
                    'validation_academique' => 'valide',
                    'type' => 'pfe',
                    'url_overleaf' => 'https://example.test/' . strtolower($prefix) . '/' . $i,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
