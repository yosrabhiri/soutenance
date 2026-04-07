<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Etudiant;
use App\Models\Soutenance;
use App\Models\JurySoutenance;
use App\Models\PeriodeSoutenance;
use App\Models\Enseignant;
use App\Models\Stage;
use App\Models\Salle;
use Carbon\Carbon;

class SoutenanceController extends Controller
{
    // =========================================================
    //  INITIALISATION DES DISPONIBILITÉS
    // =========================================================

    public function initialiserDisponibilitesEnseignant($enseignantId)
    {
        $enseignant = Enseignant::find($enseignantId);
        if (!$enseignant) {
            return response()->json(['message' => 'Enseignant introuvable'], 404);
        }

        $periodes = PeriodeSoutenance::all();
        $dispos   = [];

        foreach ($periodes as $periode) {
            $startDate = Carbon::parse($periode->date_debut);
            $endDate   = Carbon::parse($periode->date_fin);

            while ($startDate->lte($endDate)) {
                $dispos[] = [
                    'start' => $startDate->format('Y-m-d') . ' ' . $periode->heure_debut,
                    'end'   => $startDate->format('Y-m-d') . ' ' . $periode->heure_fin,
                ];
                $startDate->addDay();
            }
        }

        $enseignant->disponibilites = json_encode($dispos);
        $enseignant->save();

        return response()->json([
            'message'        => 'Disponibilités initialisées',
            'disponibilites' => $dispos,
        ]);
    }

    public function initialiserDisponibilitesTous()
    {
        $enseignants = Enseignant::all();
        foreach ($enseignants as $ens) {
            $this->initialiserDisponibilitesEnseignant($ens->Code_enseignant);
        }
        return response()->json(['message' => 'Disponibilités de tous les enseignants initialisées']);
    }

    public function updateDisponibilite(Request $request)
    {
        $enseignant      = $request->user();
        $dispos          = json_decode($enseignant->disponibilites, true) ?? [];
        $nouveauxDispos  = $dispos;

        foreach ($request->input('disponibilites', []) as $remove) {
            $removeStart = Carbon::parse($remove['start']);
            $removeEnd   = Carbon::parse($remove['end']);
            $temp        = [];

            foreach ($nouveauxDispos as $slot) {
                $start = Carbon::parse($slot['start']);
                $end   = Carbon::parse($slot['end']);

                if ($removeStart->lte($start) && $removeEnd->gte($end)) {
                    continue; // suppression totale
                }
                if ($removeStart->lte($start) && $removeEnd->lt($end)) {
                    $temp[] = ['start' => $removeEnd->toDateTimeString(), 'end' => $end->toDateTimeString()];
                    continue;
                }
                if ($removeStart->gt($start) && $removeEnd->gte($end)) {
                    $temp[] = ['start' => $start->toDateTimeString(), 'end' => $removeStart->toDateTimeString()];
                    continue;
                }
                if ($removeStart->gt($start) && $removeEnd->lt($end)) {
                    $temp[] = ['start' => $start->toDateTimeString(), 'end' => $removeStart->toDateTimeString()];
                    $temp[] = ['start' => $removeEnd->toDateTimeString(), 'end' => $end->toDateTimeString()];
                    continue;
                }
                $temp[] = $slot;
            }

            $nouveauxDispos = $temp;
        }

        $enseignant->disponibilites = json_encode($nouveauxDispos);
        $enseignant->save();

        return response()->json([
            'message'        => 'Disponibilités mises à jour',
            'disponibilites' => $nouveauxDispos,
        ]);
    }

    // =========================================================
    //  POINT D'ENTRÉE : GÉNÉRATION DES SOUTENANCES
    // =========================================================

    /**
     * Lance la génération pour toutes les périodes déclarées.
     *
     * Règle taille jury (Critère 3) :
     *   - Licence  (diplome_id = 3)  → 2 membres (encadrant + 1 examinateur)
     *   - Master / Ingénierie (tout autre diplome_id) → 3 membres (encadrant + président + rapporteur)
     */
    public function genererSoutenances(Request $request)
    {
        // Compteur de réciprocité unique pour TOUTE la session de génération (Critère 2)
        // $reciprocite[A][B] = nombre de fois que A a assisté à une soutenance encadrée par B
        $reciprocite = [];

        $periodes = PeriodeSoutenance::all();

        if ($periodes->isEmpty()) {
            return response()->json(['message' => 'Aucune période de soutenance définie'], 400);
        }

        $resultats = [];

        foreach ($periodes as $periode) {
            // Critère 3 : Licence → 2 membres, sinon 3
            $isLicence = ($periode->diplome_id == 3);

            // Récupérer les étudiants ayant ce diplôme et un stage validé
            $etudiants = Etudiant::where('diplome_id', $periode->diplome_id)
                ->whereHas('stages', fn($q) => $q->where('validation_academique', 'valide'))
                ->get();

            if ($etudiants->isEmpty()) {
                $resultats[] = "Diplome {$periode->diplome_id} : aucun étudiant éligible.";
                continue;
            }

            $nb = $this->creerSoutenances($etudiants, $periode, $isLicence, $reciprocite);
            $type = $isLicence ? 'Licence' : 'Master/Ingénierie';
            $resultats[] = "Diplome {$periode->diplome_id} ({$type}) : {$nb} soutenance(s) planifiée(s).";
        }

        return response()->json([
            'message'   => 'Génération terminée',
            'resultats' => $resultats,
        ]);
    }

    // =========================================================
    //  CRÉATION DES SOUTENANCES POUR UNE PÉRIODE
    // =========================================================

    /**
     * @param  \Illuminate\Support\Collection $etudiants
     * @param  PeriodeSoutenance              $periode
     * @param  bool                           $isLicence
     * @param  array                          &$reciprocite   partagé entre toutes les périodes
     * @return int  nombre de soutenances créées
     */
    private function creerSoutenances($etudiants, PeriodeSoutenance $periode, bool $isLicence, array &$reciprocite): int
    {
        $duree          = $periode->duree_minutes ?? 60;
        $date           = Carbon::parse($periode->date_debut . ' ' . $periode->heure_debut);
        $fin            = Carbon::parse($periode->date_fin   . ' ' . $periode->heure_fin);
        $nbExaminateurs = $isLicence ? 1 : 2;   // hors encadrant
        $nbCrees        = 0;

        // Rôles des examinateurs selon le type
        $rolesExaminateurs = $isLicence
            ? ['Examinateur']
            : ['Président', 'Rapporteur'];

        $salles = Salle::where('departement_id', $periode->departement_id)->get();

        if ($salles->isEmpty()) {
            return 0;
        }

        $tentativesMaxSansProgres = $etudiants->count() * 2;
        $sansProgres = 0;

        while ($date->lessThanOrEqualTo($fin) && $etudiants->isNotEmpty()) {

            foreach ($salles as $salle) {
                if ($etudiants->isEmpty()) break;

                // Vérifier salle libre pour ce créneau
                $salleOccupee = Soutenance::where('salle_id', $salle->id)
                    ->where(function ($q) use ($date, $duree) {
                        $fin = $date->copy()->addMinutes($duree);
                        $q->where('date_heure', '>=', $date->toDateTimeString())
                          ->where('date_heure', '<',  $fin->toDateTimeString());
                    })
                    ->exists();

                if ($salleOccupee) continue;

                $etudiant = $etudiants->shift();
                $stage    = $etudiant->stages()
                    ->where('validation_academique', 'valide')
                    ->latest()
                    ->first();

                if (!$stage || !$stage->enseignant_id) {
                    // Pas d'encadrant → on ne peut pas planifier
                    $sansProgres++;
                    continue;
                }

                $encadrantId = $stage->enseignant_id;

                // ── Critère 1 : L'encadrant doit être disponible ──────────
                if (!$this->estDisponible($encadrantId, $date, $duree)) {
                    $etudiants->push($etudiant); // reporter l'étudiant
                    $sansProgres++;
                    if ($sansProgres >= $tentativesMaxSansProgres) break 2;
                    continue;
                }

                // ── Critères 1 & 2 : Sélection des examinateurs ──────────
                $juryIds              = [$encadrantId];
                $examinateursChoisis  = [];

                for ($i = 0; $i < $nbExaminateurs; $i++) {
                    $exam = $this->selectionnerMembreJury(
                        $encadrantId,
                        $juryIds,
                        $periode->departement_id,
                        $date,
                        $duree,
                        $reciprocite
                    );

                    if (!$exam) break;

                    $juryIds[]             = $exam->Code_enseignant;
                    $examinateursChoisis[] = $exam;
                }

                // Si jury incomplet → reporter l'étudiant
                if (count($examinateursChoisis) < $nbExaminateurs) {
                    $etudiants->push($etudiant);
                    $sansProgres++;
                    if ($sansProgres >= $tentativesMaxSansProgres) break 2;
                    continue;
                }

                // ── Créer la soutenance ───────────────────────────────────
                $soutenance = Soutenance::create([
                    'stage_id'   => $stage->id,
                    'salle_id'   => $salle->id,
                    'date_heure' => $date->toDateTimeString(),
                ]);

                // Ajouter l'encadrant au jury
                JurySoutenance::create([
                    'soutenance_id' => $soutenance->id,
                    'enseignant_id' => $encadrantId,
                    'role'          => 'Encadrant',
                ]);

                // Marquer l'encadrant occupé (Critère 1)
                $this->marquerOccupe($encadrantId, $date, $duree);

                // Ajouter les examinateurs au jury
                foreach ($examinateursChoisis as $idx => $exam) {
                    $role = $rolesExaminateurs[$idx] ?? 'Examinateur';

                    JurySoutenance::create([
                        'soutenance_id' => $soutenance->id,
                        'enseignant_id' => $exam->Code_enseignant,
                        'role'          => $role,
                    ]);

                    // Critère 2 : mettre à jour le compteur de réciprocité
                    // exam vient d'assister à une soutenance encadrée par encadrant
                    $reciprocite[$exam->Code_enseignant][$encadrantId]
                        = ($reciprocite[$exam->Code_enseignant][$encadrantId] ?? 0) + 1;

                    // Marquer l'examinateur occupé (Critère 1)
                    $this->marquerOccupe($exam->Code_enseignant, $date, $duree);
                }

                $nbCrees++;
                $sansProgres = 0; // progression réelle → reset
            }

            // Passer au créneau suivant
            $date->addMinutes($duree);
        }

        return $nbCrees;
    }

    // =========================================================
    //  CRITÈRE 1 : DISPONIBILITÉ
    // =========================================================

    /**
     * Vérifie si un enseignant a un slot libre couvrant [date, date+duree].
     * S'appuie sur le champ JSON `disponibilites` mis à jour en temps réel.
     */
    private function estDisponible(string $enseignantId, Carbon $date, int $duree): bool
    {
        $ens = Enseignant::find($enseignantId);
        if (!$ens) return false;

        $dispos = json_decode($ens->disponibilites, true);
        if (empty($dispos)) return false;

        $finSoutenance = $date->copy()->addMinutes($duree);

        foreach ($dispos as $slot) {
            $start = Carbon::parse($slot['start']);
            $end   = Carbon::parse($slot['end']);

            if ($date->gte($start) && $finSoutenance->lte($end)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retire le créneau [date, date+duree] des disponibilités de l'enseignant.
     * Appelé après chaque affectation pour garantir le Critère 1.
     */
    private function marquerOccupe(string $enseignantId, Carbon $date, int $duree): void
    {
        $ens = Enseignant::find($enseignantId);
        if (!$ens) return;

        $dispos        = json_decode($ens->disponibilites, true) ?? [];
        $finSoutenance = $date->copy()->addMinutes($duree);
        $nouveauxDispos = [];

        foreach ($dispos as $slot) {
            $start = Carbon::parse($slot['start']);
            $end   = Carbon::parse($slot['end']);

            // Slot qui couvre exactement le créneau → on le découpe
            if ($date->gte($start) && $finSoutenance->lte($end)) {
                if ($date->gt($start)) {
                    $nouveauxDispos[] = [
                        'start' => $start->toDateTimeString(),
                        'end'   => $date->toDateTimeString(),
                    ];
                }
                if ($finSoutenance->lt($end)) {
                    $nouveauxDispos[] = [
                        'start' => $finSoutenance->toDateTimeString(),
                        'end'   => $end->toDateTimeString(),
                    ];
                }
            } else {
                // Slot sans chevauchement → conserver tel quel
                $nouveauxDispos[] = $slot;
            }
        }

        $ens->disponibilites = json_encode($nouveauxDispos);
        $ens->save();
    }

    // =========================================================
    //  CRITÈRES 1 & 2 : SÉLECTION D'UN MEMBRE DU JURY
    // =========================================================

    /**
     * Sélectionne le meilleur candidat pour compléter le jury d'une soutenance
     * encadrée par $encadrantId, en respectant :
     *
     *   Critère 1 — disponibilité : le candidat doit être libre sur ce créneau.
     *
     *   Critère 2 — réciprocité :
     *     Si candidat A a assisté à n soutenances de l'encadrant B,
     *     alors B doit assister à n soutenances de A.
     *     → On prioritise les candidats envers qui l'encadrant est "endetté".
     *
     *   Score = reciprocite[encadrant][candidat] - reciprocite[candidat][encadrant]
     *   Un score positif signifie que l'encadrant a participé davantage aux
     *   jurys du candidat → le candidat "doit" en retour participer ici.
     *   On maximise ce score pour rééquilibrer les échanges.
     *
     * @param  string  $encadrantId
     * @param  array   $juryIdsExclus    IDs déjà dans le jury (pour ce tour)
     * @param  int     $departementId
     * @param  Carbon  $date
     * @param  int     $duree
     * @param  array   &$reciprocite
     * @return Enseignant|null
     */
    private function selectionnerMembreJury(
        string  $encadrantId,
        array   $juryIdsExclus,
        int     $departementId,
        Carbon  $date,
        int     $duree,
        array   &$reciprocite
    ): ?Enseignant {

        $candidats = Enseignant::where('departement_id', $departementId)
            ->whereNotIn('Code_enseignant', $juryIdsExclus)
            ->get();

        $meilleurCandidat = null;
        $meilleurScore    = PHP_INT_MIN;

        foreach ($candidats as $candidat) {
            $candidatId = $candidat->Code_enseignant;

            // ── Critère 1 : disponibilité ────────────────────────────
            if (!$this->estDisponible($candidatId, $date, $duree)) {
                continue;
            }

            // ── Critère 2 : score de réciprocité ─────────────────────
            // reciprocite[encadrant][candidat] : nb fois que encadrant a assisté
            //   à une soutenance encadrée par candidat (dette de candidat envers encadrant)
            // reciprocite[candidat][encadrant] : nb fois que candidat a assisté
            //   à une soutenance encadrée par encadrant
            //
            // Score = dette du candidat envers l'encadrant
            //       = (fois où encadrant a rendu service au candidat)
            //       - (fois où candidat a rendu service à l'encadrant)
            // → Score élevé = le candidat devrait participer pour rééquilibrer.

            $nbEncadrantDansJuryCandidat = $reciprocite[$encadrantId][$candidatId] ?? 0;
            $nbCandidatDansJuryEncadrant = $reciprocite[$candidatId][$encadrantId] ?? 0;

            $score = $nbEncadrantDansJuryCandidat - $nbCandidatDansJuryEncadrant;

            if ($score > $meilleurScore) {
                $meilleurScore    = $score;
                $meilleurCandidat = $candidat;
            }
        }

        return $meilleurCandidat;
    }

    // =========================================================
    //  LECTURE
    // =========================================================

    /**
     * Liste toutes les soutenances avec jury + salle.
     */
    public function index()
    {
        $soutenances = Soutenance::with([
            'stage.etudiant',
            'salle',
            'jurySoutenances.enseigant',
        ])->get();

        return response()->json($soutenances);
    }
}
