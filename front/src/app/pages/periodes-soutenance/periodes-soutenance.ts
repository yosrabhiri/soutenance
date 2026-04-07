import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { PlanningApiService } from '../../services/planning-api';
import { StageService } from '../../services/stage-api';

@Component({
  selector: 'app-periodes-soutenance',
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './periodes-soutenance.html',
  styleUrl: './periodes-soutenance.css'
})
export class PeriodesSoutenanceComponent implements OnInit {
  periodes: any[] = [];
  diplomes: any[] = [];
  salles: any[] = [];
  selectedPeriodeId: number | null = null;
  selectedSalleIds: number[] = [];
  creneaux: any[] = [];
  feedbackMessage = '';
  isSaving = false;

  form;

  constructor(
    private fb: FormBuilder,
    private planningApi: PlanningApiService,
    private stageApi: StageService
  ) {
    this.form = this.fb.group({
      diplome_id: [null, Validators.required],
      date_debut: ['', Validators.required],
      date_fin: ['', Validators.required],
      duree_minutes: [60, [Validators.required, Validators.min(1)]],
      heure_debut: ['08:00', Validators.required],
      heure_fin: ['18:00', Validators.required],
    });
  }

  ngOnInit(): void {
    this.loadInitialData();
  }

  loadInitialData() {
    this.loadPeriodes();
    this.loadDiplomes();
    this.loadSalles();
  }

  loadPeriodes() {
    this.planningApi.getPeriodes().subscribe({
      next: (res) => {
        this.periodes = res.periodes || [];
        if (!this.selectedPeriodeId && this.periodes.length > 0) {
          this.selectPeriode(this.periodes[0]);
        }
      },
      error: () => this.feedbackMessage = 'Impossible de charger les périodes.'
    });
  }

  loadDiplomes() {
    this.stageApi.getDiplomes().subscribe({
      next: (res) => this.diplomes = res.diplomes || [],
      error: () => this.feedbackMessage = 'Impossible de charger les diplômes.'
    });
  }

  loadSalles() {
    this.planningApi.getSalles().subscribe({
      next: (res) => this.salles = res.salles || [],
      error: () => this.feedbackMessage = 'Impossible de charger les salles.'
    });
  }

  selectPeriode(periode: any) {
    this.selectedPeriodeId = periode.id;
    this.selectedSalleIds = (periode.salles || []).map((salle: any) => salle.id);

    this.form.patchValue({
      diplome_id: periode.diplome_id,
      date_debut: periode.date_debut,
      date_fin: periode.date_fin,
      duree_minutes: periode.duree_minutes,
      heure_debut: periode.heure_debut,
      heure_fin: periode.heure_fin,
    });

    this.loadCreneaux(periode.id);
  }

  resetForm() {
    this.selectedPeriodeId = null;
    this.selectedSalleIds = [];
    this.creneaux = [];
    this.form.reset({
      diplome_id: null,
      date_debut: '',
      date_fin: '',
      duree_minutes: 60,
      heure_debut: '08:00',
      heure_fin: '18:00',
    });
  }

  savePeriode() {
    if (this.form.invalid) {
      this.feedbackMessage = 'Complète les champs obligatoires de la période.';
      return;
    }

    this.isSaving = true;
    this.feedbackMessage = '';
    const payload = this.form.getRawValue();
    const request$ = this.selectedPeriodeId
      ? this.planningApi.updatePeriode(this.selectedPeriodeId, payload)
      : this.planningApi.createPeriode(payload);

    request$.subscribe({
      next: (res) => {
        this.feedbackMessage = res.message || 'Période enregistrée.';
        this.isSaving = false;
        this.loadPeriodes();
      },
      error: (error) => {
        this.feedbackMessage = error?.error?.message || 'Erreur lors de l’enregistrement.';
        this.isSaving = false;
      }
    });
  }

  saveSalles() {
    if (!this.selectedPeriodeId) {
      this.feedbackMessage = 'Sélectionne d’abord une période.';
      return;
    }

    this.planningApi.assignerSalles(this.selectedPeriodeId, this.selectedSalleIds).subscribe({
      next: (res) => {
        this.feedbackMessage = res.message || 'Salles associées.';
        this.loadPeriodes();
      },
      error: () => this.feedbackMessage = 'Erreur lors de l’association des salles.'
    });
  }

  generateCreneaux() {
    if (!this.selectedPeriodeId) {
      this.feedbackMessage = 'Sélectionne une période avant de générer les créneaux.';
      return;
    }

    this.planningApi.generateCreneaux(this.selectedPeriodeId).subscribe({
      next: (res) => {
        this.feedbackMessage = res.message || 'Créneaux générés.';
        this.loadCreneaux(this.selectedPeriodeId!);
        this.loadPeriodes();
      },
      error: (error) => {
        this.feedbackMessage = error?.error?.message || 'Erreur lors de la génération des créneaux.';
      }
    });
  }

  loadCreneaux(periodeId: number) {
    this.planningApi.getCreneaux(periodeId).subscribe({
      next: (res) => this.creneaux = res.creneaux || [],
      error: () => this.creneaux = []
    });
  }

  onSalleToggle(salleId: number, checked: boolean) {
    if (checked) {
      this.selectedSalleIds = [...new Set([...this.selectedSalleIds, salleId])];
      return;
    }

    this.selectedSalleIds = this.selectedSalleIds.filter((id) => id !== salleId);
  }

  isSalleSelected(salleId: number): boolean {
    return this.selectedSalleIds.includes(salleId);
  }
}
