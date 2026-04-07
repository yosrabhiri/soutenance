import { etudiant } from './../../../../etudiant';

import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatNativeDateModule } from '@angular/material/core';
import { f } from "../../../../../../node_modules/@angular/material/icon-module.d-COXCrhrh";
import { FormsModule } from '@angular/forms';
import { MatTableModule } from '@angular/material/table';
import { MatIconModule } from "@angular/material/icon";
import { EnseignantService } from './../../../../services/enseignant-service';

import { StatutDialogue } from './statut-dialogue/statut-dialogue';
import {MatButtonModule} from '@angular/material/button';
import {MatDialog, MatDialogModule} from '@angular/material/dialog';
import {MatCheckboxModule} from '@angular/material/checkbox';
import { ReunionDialogue } from './reunion-dialogue/reunion-dialogue';
import { OpenCalendarModalComponent } from './open-calendar-modal/open-calendar-modal';

interface Etudiant {
  nom: string;
  email: string;
  statut: 'valide' | 'en attente';
  societe: string;
  role: string;
  avatar: string;
  //pdfLink:string | undefined
}

@Component({
  selector: 'app-encadrement',
  imports: [CommonModule, MatInputModule, FormsModule,MatButtonModule,MatCheckboxModule,
    MatTableModule, MatIconModule],
  templateUrl: './encadrement.html',
  styleUrl: './encadrement.css'
})
export class Encadrement implements OnInit {
  etudiants: any[] = []; // données brutes depuis l'API
  filteredEtudiants: any[] = []; // affichage filtré
  searchText: string = '';
  displayedColumns: string[] = ['nom', 'email', 'statut', 'societe', 'specialite', 'overleafLink'];
selectedEtudiants: any[] = [];
allMeetings: { title: string; start: Date; studentName: string }[] = [];
  constructor(private EnseignantService: EnseignantService, private dialog: MatDialog) {}

  ngOnInit(): void {
    this.loadEtudiants();
  }
  allSelected: boolean = false;

// Toggle pour cocher/décocher tous les étudiants
toggleSelectAll() {
  this.filteredEtudiants.forEach(e => e.selected = this.allSelected);
}

// Quand on coche/décoche un étudiant individuel
onSelectChange(etu: any) {
  if (etu.selected) {
    // Si coché et pas déjà dans le tableau
    if (!this.selectedEtudiants.includes(etu)) {
      this.selectedEtudiants.push(etu);
    }
  } else {
    // Si décoché, retirer du tableau
    this.selectedEtudiants = this.selectedEtudiants.filter(e => e !== etu);
  }

  // Mettre à jour le checkbox global
  this.allSelected = this.filteredEtudiants.every(e => e.selected);

  console.log('Étudiants sélectionnés:', this.selectedEtudiants);
}

  // ⚡ Récupération des étudiants encadrés via API
  loadEtudiants() {
    this.EnseignantService.getEtudiantsEncadres().subscribe(
      (res: any) => {
        console.log(res.data,'rrrrrr****')
        // ta réponse : { message, count, data }
        this.etudiants = res.data.map((e: any) => ({
            stage_id: e.stage_id, 
          nom: e.etudiant_prenom + ' ' + e.etudiant_nom,
          email: e.email ?? e.email, // si tu as l'email dans ta relation
          statut: e.validation_academique,
          etudiant_id: e.etudiant_id,
          societe: e.societe_nom,
          specialite: e.specialite?.nom ?? '', // récupère le nom de la spécialité
          avatar: 'assets/images/avatar.jpg', // si tu veux afficher un avatar par défaut
          pdfLink: e.chemin_document,
          overleafLink: e.url_overleaf
        }));

        this.filteredEtudiants = [...this.etudiants];
      this.EnseignantService.getReunionsEtudiants().subscribe(
  (resReu: any) => {
    const reunionsData = resReu.data || {};
    console.log(resReu,"opopop")

    this.filteredEtudiants = this.filteredEtudiants.map(e => {
      const meetings = reunionsData[String(e.etudiant_id)] || [];

      e.hasFutureMeeting = meetings.some((m: { jour: string }) => {
        const meetingDate = new Date(m.jour);
        const now = new Date();

        return meetingDate.getTime() > now.getTime();
      });

      return e;
    });

    console.log('filteredEtudiants avec hasFutureMeeting:', this.filteredEtudiants);
    // Create array for the calendar
    this.allMeetings = Object.keys(reunionsData).flatMap(studentId => {
      const student = this.filteredEtudiants.find(e => e.etudiant_id == +studentId);
      const studentName = student ? student.nom : 'Étudiant inconnu';

      return reunionsData[studentId].map((m: any) => ({
        title: m.titre || 'Réunion',
        start: new Date(m.jour),
        salle: m.salle,
        heure:m.heure,

        studentName
      }));
    });

    console.log('Meetings for calendar:', this.allMeetings);
  },
  
  (err: any) => console.error('Erreur récupération réunions', err)
);

    
      },
      (      err: any) => console.error(err)
    );
  }





  filterTable() {
    const value = this.searchText.trim().toLowerCase();
    this.filteredEtudiants = this.etudiants.filter(e =>
      e.nom.toLowerCase().includes(value) ||
      e.email.toLowerCase().includes(value) ||
      e.societe.toLowerCase().includes(value) ||
      e.statut.toLowerCase().includes(value) ||
      e.specialite.toLowerCase().includes(value)
    );
  }

  openOverleaf(overleafLink: string) {
    if (overleafLink) {
      window.open(overleafLink, '_blank');
    } else {
      console.warn('Lien Overleaf non disponible');
    }
  }

  confirmStatut(etu: any) {
    const dialogRef = this.dialog.open(StatutDialogue, {
      width: '300px',
      data: { etudiant: etu }
    });

    dialogRef.afterClosed().subscribe(result => {
      if (result === 'confirm') {
        // ⚡ Appel API pour valider le stage
        this.EnseignantService.validerStage(etu.stage_id).subscribe(() => {
          etu.statut = 'valide';
        });
      }
    })
  }
  planifierReunion() {
  if (this.selectedEtudiants.length === 0) {
    alert("Sélectionne au moins un étudiant !");
    return;
  }

  const dialogRef = this.dialog.open(ReunionDialogue, {
    width: '400px',
    data: { selectedEtudiants: this.selectedEtudiants }
  });
  

  dialogRef.afterClosed().subscribe(result => {
    if (result) {
      // ⚡ Appel API pour créer la réunion
      this.EnseignantService.creerReunion(result).subscribe({
        next: (res: any) => {
          console.log('Réunion créée:', res);
          alert('Réunion créée avec succès !');
          // vider la sélection
          this.selectedEtudiants = [];
          this.allSelected = false;
          this.filteredEtudiants.forEach(e => e.selected = false);
        },
        error: (err: any) => console.error(err)
      });
    }
  });
}
planifieropen() {
  this.EnseignantService.getReunionsAvecEtudiants().subscribe(
    (res: any) => {
      const reunions = res.data || [];
      console.log(res,"44444")

      // Chaque réunion devient un seul "event"
      const meetings = reunions.map((reunion: any) => ({
        id: reunion.id,
        title: reunion.titre,
        start: new Date(reunion.jour + 'T' + reunion.heure),
        heure: reunion.heure,
        salle: reunion.salle || 'Non spécifiée',
        note: reunion.note || '',
        etudiants: reunion.etudiants || []  ,// 👈 garder la liste des étudiants
        specialite:reunion.specialite
      }));

      this.dialog.open(OpenCalendarModalComponent, {
        width: '60%',
        height:'90%',
        data: { events: meetings }
      });
    },
    (err: any) => {
      console.error('Erreur récupération réunions', err);
    }
  );
}



}