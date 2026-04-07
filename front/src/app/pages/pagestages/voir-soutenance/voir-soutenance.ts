import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PlanningApiService } from '../../../services/planning-api';

@Component({
  selector: 'app-voir-soutenance',
  imports: [CommonModule, FormsModule],
  templateUrl: './voir-soutenance.html',
  styleUrl: './voir-soutenance.css'
})
export class VoirSoutenance {
  periodes: any[] = [];
  selectedPeriodeId: number | null = null;
  planningInput: any = null;
  cpSatPreview: any = null;
  generationStatus: any = null;
  isLoadingPreviews = false;
  isLaunchingGeneration = false;
  errorMessage = '';
  generationMessage = '';
  private generationPollHandle: ReturnType<typeof setTimeout> | null = null;

  constructor(private planningApi: PlanningApiService) {}

  ngOnInit(): void {
    this.loadPeriodes();
  }

  loadPeriodes() {
    this.planningApi.getPeriodes().subscribe({
      next: (res) => {
        this.periodes = res.periodes || [];
        if (this.periodes.length > 0) {
          this.selectedPeriodeId = this.periodes[0].id;
          this.loadAll();
        }
      },
      error: () => {
        this.errorMessage = 'Impossible de charger les périodes.';
      }
    });
  }

  loadAll() {
    if (!this.selectedPeriodeId) {
      return;
    }

    this.errorMessage = '';
    this.generationMessage = '';
    this.planningInput = null;
    this.cpSatPreview = null;
    this.stopGenerationPolling();
    this.loadGenerationStatus();
  }

  loadGenerationStatus() {
    if (!this.selectedPeriodeId) {
      return;
    }

    this.planningApi.getGenerationStatus(this.selectedPeriodeId).subscribe({
      next: (res) => {
        this.generationStatus = res;

        if (res?.generation_status === 'queued' || res?.generation_status === 'running') {
          this.scheduleGenerationStatusRefresh();
        }
      },
      error: () => {
        this.generationStatus = null;
      }
    });
  }

  loadPreviews() {
    if (!this.selectedPeriodeId || this.isLoadingPreviews) {
      return;
    }

    this.isLoadingPreviews = true;
    this.errorMessage = '';

    this.planningApi.getPlanningInput(this.selectedPeriodeId).subscribe({
      next: (res) => {
        this.planningInput = res;
        this.loadCpSatPreview();
      },
      error: () => {
        this.errorMessage = 'Impossible de charger les données de planning.';
        this.isLoadingPreviews = false;
      }
    });
  }

  loadCpSatPreview() {
    if (!this.selectedPeriodeId) {
      return;
    }

    this.planningApi.getCpSatPreview(this.selectedPeriodeId, {
      max_time_in_seconds: 20,
      num_search_workers: 8
    }).subscribe({
      next: (res) => {
        this.cpSatPreview = res.preview;
        this.isLoadingPreviews = false;
      },
      error: () => {
        this.errorMessage = 'Impossible de charger la preview CP-SAT.';
        this.isLoadingPreviews = false;
      }
    });
  }

  launchFullGeneration(algorithm: 'cp_sat') {
    if (!this.selectedPeriodeId || this.isLaunchingGeneration) {
      return;
    }

    this.isLaunchingGeneration = true;
    this.errorMessage = '';
    this.generationMessage = '';

    this.planningApi.launchGeneration(this.selectedPeriodeId, {
      algorithm,
      max_time_in_seconds: 20,
      num_search_workers: 8
    }).subscribe({
      next: (res) => {
        this.generationStatus = res.status;
        this.generationMessage = res.message || 'Génération complète lancée.';
        this.isLaunchingGeneration = false;
        this.scheduleGenerationStatusRefresh();
      },
      error: (error) => {
        this.errorMessage = error?.error?.message || 'Impossible de lancer la génération complète.';

        if (error?.error?.hint) {
          this.errorMessage += ` ${error.error.hint}`;
        }

        this.isLaunchingGeneration = false;
        this.loadGenerationStatus();
      }
    });
  }

  generationStatusLabel(): string {
    const status = this.generationStatus?.generation_status;

    return ({
      idle: 'Inactive',
      queued: 'En attente',
      running: 'En cours',
      completed: 'Terminée',
      failed: 'Échouée',
    } as Record<string, string>)[status] || 'Inconnue';
  }

  private scheduleGenerationStatusRefresh() {
    this.stopGenerationPolling();
    this.generationPollHandle = setTimeout(() => this.loadGenerationStatus(), 5000);
  }

  private stopGenerationPolling() {
    if (this.generationPollHandle) {
      clearTimeout(this.generationPollHandle);
      this.generationPollHandle = null;
    }
  }

  stageLabel(stageId: number): string {
    const stage = this.planningInput?.stages?.find((item: any) => item.stage_id === stageId);
    if (!stage?.etudiant) {
      return `Stage #${stageId}`;
    }

    return `${stage.etudiant.prenom} ${stage.etudiant.nom}`;
  }

  roomLabel(salleId: number): string {
    return this.planningInput?.salles?.find((item: any) => item.id === salleId)?.nom || `Salle #${salleId}`;
  }

  slotLabel(creneauId: number): string {
    const creneau = this.planningInput?.creneaux?.find((item: any) => item.id === creneauId);
    if (!creneau) {
      return `Créneau #${creneauId}`;
    }

    return `${creneau.date} | ${creneau.code_slot} | ${creneau.heure_debut}-${creneau.heure_fin}`;
  }

  teacherLabel(teacherId: string): string {
    return this.planningInput?.enseignants?.find((item: any) => item.id === teacherId)?.nom || teacherId;
  }

  juryCandidateLabels(stage: any): string {
    const candidateIds = stage?.jury_candidate_ids || [];
    if (candidateIds.length === 0) {
      return 'Aucun candidat';
    }

    return candidateIds.map((id: string) => this.teacherLabel(id)).join(' | ');
  }

  stageDiagnostics(stage: any): string[] {
    const diagnostics: string[] = [];
    const candidateIds = stage?.jury_candidate_ids || [];
    const requiredExtra = Number(stage?.nombre_jures_a_ajouter_hors_encadrant || 0);
    const constraints = stage?.contraintes_observees || {};

    if (!stage?.encadrant_academique?.id) {
      diagnostics.push('Encadrant académique manquant.');
    }

    if (constraints.encadrant_manquant) {
      diagnostics.push('Le stage n’a pas d’encadrant académique exploitable.');
    }

    if (constraints.specialite_sans_departement) {
      diagnostics.push('La spécialité de l’étudiant n’a pas de département.');
    }

    if (candidateIds.length < requiredExtra) {
      diagnostics.push(`Pas assez de jurés candidats: ${candidateIds.length} trouvé(s) pour ${requiredExtra} requis.`);
    }

    const unavailableCandidates = candidateIds.filter((teacherId: string) => {
      const teacher = this.planningInput?.enseignants?.find((item: any) => item.id === teacherId);
      const slots = teacher?.disponibilites_par_creneau || [];

      return slots.length > 0 && slots.every((slot: any) => slot.statut === 'unavailable');
    });

    if (unavailableCandidates.length === candidateIds.length && candidateIds.length > 0) {
      diagnostics.push('Tous les candidats jury sont indisponibles sur cette période.');
    }

    if (diagnostics.length === 0) {
      diagnostics.push('Stage prêt pour tentative d’affectation, blocage à analyser via les disponibilités ou les conflits de créneau.');
    }

    return diagnostics;
  }

  unassignedStages(preview: any): any[] {
    const ids = preview?.unassigned_stage_ids || [];
    return (this.planningInput?.stages || []).filter((stage: any) => ids.includes(stage.stage_id));
  }

  ngOnDestroy(): void {
    this.stopGenerationPolling();
  }
}
