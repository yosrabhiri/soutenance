import { MatButtonModule } from '@angular/material/button';
import { CommonModule } from '@angular/common';
import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { MatIconModule } from '@angular/material/icon';
import { PlanningApiService } from '../../services/planning-api';

interface Periode {
  id: number;
  diplome: { id: number; nom: string };
  date_debut: string;
  date_fin: string;
  heure_debut: string;
  heure_fin: string;
  duree_minutes: number;
}
interface CreneauDisponibilite {
  creneau_id: number;
  date: string;
  code_slot: string;
  heure_debut: string;
  heure_fin: string;
  statut: 'available' | 'unavailable' | 'preferred' | 'avoid';
}

interface DisponibiliteDayGroup {
  date: string;
  label: string;
  expanded: boolean;
  slots: CreneauDisponibilite[];
}

@Component({
  selector: 'app-dispos',
  imports: [CommonModule, ReactiveFormsModule, MatIconModule,MatButtonModule],
  templateUrl: './dispos.html',
  styleUrl: './dispos.css'
})
export class Dispos implements OnInit {
  @Input() enseignantId!: number;
  form!: FormGroup;
  periodes: Periode[] = [];
  disponibilites: CreneauDisponibilite[] = [];
  disponibilitesByDay: DisponibiliteDayGroup[] = [];
  selectedPeriodeId: number | null = null;
  isLoading = false;
  isSaving = false;
  feedbackMessage = '';
  readonly statuses = ['preferred', 'available', 'avoid', 'unavailable'] as const;
  readonly statusLabels: Record<CreneauDisponibilite['statut'], string> = {
    preferred: 'Préféré',
    available: 'Disponible',
    avoid: 'À éviter',
    unavailable: 'Indisponible'
  };

  constructor(
    private fb: FormBuilder,
    private planningApi: PlanningApiService
  ) {}

  ngOnInit(): void {
    this.form = this.fb.group({
      periodeId: [null]
    });

    this.loadPeriodes();
  }

  loadPeriodes() {
    this.planningApi.getPeriodes().subscribe({
      next: (res) => {
        this.periodes = res.periodes || [];
        if (this.periodes.length > 0) {
          const firstPeriodeId = this.periodes[0].id;
          this.form.patchValue({ periodeId: firstPeriodeId });
          this.onPeriodeChange(firstPeriodeId);
        }
      },
      error: () => {
        this.feedbackMessage = 'Impossible de charger les périodes.';
      }
    });
  }

  onPeriodeChange(rawPeriodeId: number | string | null) {
    const periodeId = Number(rawPeriodeId);
    if (!periodeId) {
      this.selectedPeriodeId = null;
      this.disponibilites = [];
      this.disponibilitesByDay = [];
      return;
    }

    this.selectedPeriodeId = periodeId;
    this.isLoading = true;
    this.feedbackMessage = '';

    this.planningApi.getMesDisponibilites(periodeId).subscribe({
      next: (res) => {
        this.disponibilites = res.disponibilites || [];
        this.disponibilitesByDay = this.groupDisponibilitesByDay(this.disponibilites);
        this.isLoading = false;
      },
      error: () => {
        this.feedbackMessage = 'Impossible de charger les disponibilités pour cette période.';
        this.disponibilites = [];
        this.disponibilitesByDay = [];
        this.isLoading = false;
      }
    });
  }

  updateStatut(creneauId: number, statut: string) {
    this.disponibilites = this.disponibilites.map((item) =>
      item.creneau_id === creneauId
        ? { ...item, statut: statut as CreneauDisponibilite['statut'] }
        : item
    );
    this.disponibilitesByDay = this.groupDisponibilitesByDay(this.disponibilites, this.disponibilitesByDay);
  }

  save() {
    if (!this.selectedPeriodeId) {
      this.feedbackMessage = 'Sélectionnez une période avant de sauvegarder.';
      return;
    }

    this.isSaving = true;
    this.feedbackMessage = '';

    const payload = this.disponibilites.map((item) => ({
      creneau_id: item.creneau_id,
      statut: item.statut
    }));

    this.planningApi.saveMesDisponibilites(this.selectedPeriodeId, payload).subscribe({
      next: (res) => {
        this.feedbackMessage = res.message || 'Disponibilités enregistrées avec succès.';
        this.isSaving = false;
      },
      error: () => {
        this.feedbackMessage = 'Erreur lors de l’enregistrement des disponibilités.';
        this.isSaving = false;
      }
    });
  }
  sections = {
  dispos: true,
  horaires: true,
  periodes: true
};

toggleSection(section: 'dispos' | 'horaires' | 'periodes') {
  this.sections[section] = !this.sections[section];
}

toggleDay(date: string) {
  this.disponibilitesByDay = this.disponibilitesByDay.map((group) =>
    group.date === date
      ? { ...group, expanded: !group.expanded }
      : group
  );
}

expandAllDays() {
  this.disponibilitesByDay = this.disponibilitesByDay.map((group) => ({ ...group, expanded: true }));
}

collapseAllDays() {
  this.disponibilitesByDay = this.disponibilitesByDay.map((group) => ({ ...group, expanded: false }));
}

selectedPeriode(): Periode | undefined {
  return this.periodes.find((periode) => periode.id === this.selectedPeriodeId);
}

dayStatusCount(group: DisponibiliteDayGroup, status: CreneauDisponibilite['statut']): number {
  return group.slots.filter((slot) => slot.statut === status).length;
}

statusLabel(status: CreneauDisponibilite['statut']): string {
  return this.statusLabels[status];
}

formatDateLabel(date: string): string {
  const parsed = new Date(`${date}T00:00:00`);

  return new Intl.DateTimeFormat('fr-FR', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric'
  }).format(parsed);
}

private groupDisponibilitesByDay(
  disponibilites: CreneauDisponibilite[],
  previousGroups: DisponibiliteDayGroup[] = []
) {
  const previousExpansion = new Map(previousGroups.map((group) => [group.date, group.expanded]));
  const grouped = new Map<string, CreneauDisponibilite[]>();

  disponibilites.forEach((slot) => {
    const daySlots = grouped.get(slot.date) || [];
    daySlots.push(slot);
    grouped.set(slot.date, daySlots);
  });

  return Array.from(grouped.entries()).map(([date, slots], index) => ({
    date,
    label: this.formatDateLabel(date),
    expanded: previousExpansion.get(date) ?? index === 0,
    slots
  }));
}

trackByCreneau(_: number, item: CreneauDisponibilite) {
  return item.creneau_id;
}

trackByDay(_: number, item: DisponibiliteDayGroup) {
  return item.date;
}

totalStatusCount(status: CreneauDisponibilite['statut']): number {
  return this.disponibilites.filter((slot) => slot.statut === status).length;
}

}
