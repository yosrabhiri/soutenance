import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PlanningApiService } from '../../../services/planning-api';

@Component({
  selector: 'app-toutes-soutenances',
  imports: [CommonModule, FormsModule],
  templateUrl: './toutes-soutenances.html',
  styleUrl: './toutes-soutenances.css'
})
export class ToutesSoutenances {
  periodes: any[] = [];
  selectedPeriodeId: number | null = null;
  soutenances: any[] = [];
  isLoading = false;
  errorMessage = '';

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
          this.loadSoutenances();
        }
      },
      error: () => {
        this.errorMessage = 'Impossible de charger les périodes.';
      }
    });
  }

  loadSoutenances() {
    if (!this.selectedPeriodeId) {
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';

    this.planningApi.getSoutenances(this.selectedPeriodeId).subscribe({
      next: (res) => {
        this.soutenances = res.soutenances || [];
        this.isLoading = false;
      },
      error: () => {
        this.soutenances = [];
        this.errorMessage = 'Impossible de charger les soutenances.';
        this.isLoading = false;
      }
    });
  }

  studentLabel(soutenance: any): string {
    const prenom = soutenance?.etudiant?.prenom || '';
    const nom = soutenance?.etudiant?.nom || '';

    return `${prenom} ${nom}`.trim() || 'Étudiant non défini';
  }

  juryMembers(soutenance: any): any[] {
    return soutenance?.jury || [];
  }

  roleClass(role: string | null | undefined): string {
    const normalizedRole = (role || '').toLowerCase();

    if (normalizedRole === 'encadrant') {
      return 'badge-encadrant';
    }

    if (normalizedRole === 'président' || normalizedRole === 'president') {
      return 'badge-president';
    }

    if (normalizedRole === 'rapporteur') {
      return 'badge-rapporteur';
    }

    if (normalizedRole === 'examinateur') {
      return 'badge-examinateur';
    }

    return 'badge-default';
  }
}
