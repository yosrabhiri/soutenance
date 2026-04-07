import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { PlanningApiService } from '../../../services/planning-api';

@Component({
  selector: 'app-mes-soutenances',
  imports: [CommonModule, FormsModule],
  templateUrl: './mes-soutenances.html',
  styleUrl: './mes-soutenances.css'
})
export class MesSoutenances {
  periodes: any[] = [];
  selectedPeriodeId: number | null = null;
  mySoutenances: any[] = [];
  myTeacherName = '';
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
          this.loadMySoutenances();
        }
      },
      error: () => {
        this.errorMessage = 'Impossible de charger les périodes.';
      }
    });
  }

  loadMySoutenances() {
    if (!this.selectedPeriodeId) {
      return;
    }

    this.isLoading = true;
    this.errorMessage = '';

    this.planningApi.getMySoutenances(this.selectedPeriodeId).subscribe({
      next: (res) => {
        this.mySoutenances = res.soutenances || [];
        this.myTeacherName = res.enseignant || '';
        this.isLoading = false;
      },
      error: () => {
        this.mySoutenances = [];
        this.myTeacherName = '';
        this.errorMessage = 'Impossible de charger votre emploi de soutenance.';
        this.isLoading = false;
      }
    });
  }

  myJuryLabel(soutenance: any): string {
    return (soutenance?.jury || [])
      .map((member: any) => `${member.nom || member.enseignant_id}${member.role ? ` (${member.role})` : ''}`)
      .join(' | ');
  }
}
