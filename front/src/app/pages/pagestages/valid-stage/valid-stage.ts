import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatNativeDateModule } from '@angular/material/core';
import { StageService } from '../../../services/stage-api';
import { MatIconModule } from '@angular/material/icon';
import { FormsModule, NgModel } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';

@Component({
  selector: 'app-valid-stage',
  imports: [CommonModule,MatIconModule,FormsModule,MatButtonModule],
  templateUrl: './valid-stage.html',
  styleUrls: ['./valid-stage.css']
})
export class ValidStage implements OnInit {
  etudiants: any[] = [];
  etudiantsFiltres: any[] = [];
  searchText: string = '';

  // pour le popup
  selectedStage: any = null;
  showPopup: boolean = false;

  constructor(private stageService: StageService) { }

  ngOnInit() {
    this.loadStages();
  }

  loadStages() {
    this.stageService.getAllStages().subscribe((data: any) => {
      this.etudiants = data.map((stage: any) => ({
        id: stage.id,
        nom: stage.etudiant.nom + ' ' + stage.etudiant.prenom,
        email: stage.etudiant.email,
        filiere: stage.etudiant.specialite?.nom,
        societe: stage.societe?.nom || '—',
        statut: stage.etat_validation,
        avatar: 'assets/avatars/default.png',
        traite_par: stage.enseignant_traitant
          ? stage.enseignant_traitant.NomEnseignant + ' ' + stage.enseignant_traitant.PrenomEnseignant
          : '—'
      }));
      this.etudiantsFiltres = [...this.etudiants];
    });
  }

  filterTable() {
    const txt = this.searchText.toLowerCase();
    this.etudiantsFiltres = this.etudiants.filter(etu =>
      etu.nom.toLowerCase().includes(txt) ||
      etu.email.toLowerCase().includes(txt) ||
      (etu.filiere && etu.filiere.toLowerCase().includes(txt)) ||
      (etu.societe && etu.societe.toLowerCase().includes(txt)) ||
      (etu.traite_par && etu.traite_par.toLowerCase().includes(txt)) ||
      (etu.statut && etu.statut.toLowerCase().includes(txt))
    );
  }

  // renommer selectedStage -> etuSelectionne
  etuSelectionne: any = null;
  popupOuvert: boolean = false;

  // puis dans les méthodes
  ouvrirPopup(stage: any) {
    this.etuSelectionne = { ...stage };
    this.popupOuvert = true;
  }

  fermerPopup() {
    this.popupOuvert = false;
    this.etuSelectionne = null;
  }

  repondreStage(statut: string) {
    if (!this.etuSelectionne) return;

    // Appel du service pour mettre à jour le statut du stage
    this.stageService.updateStageStatus(this.etuSelectionne.id, statut).subscribe({
      next: (res: any) => {
        console.log(this.etuSelectionne.id)
        // Met à jour le statut dans la liste locale
        const idx = this.etudiants.findIndex(e => e.id === this.etuSelectionne.id);
        if (idx > -1) {
          this.etudiants[idx].statut = res.statut || statut;
        }

        // Rafraîchit la liste filtrée pour le tableau
        this.etudiantsFiltres = [...this.etudiants];

        // Ferme le popup
        this.fermerPopup();
      },
      error: (err) => console.error('Erreur mise à jour statut du stage', err)
    });
  }

}