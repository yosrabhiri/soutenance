import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatNativeDateModule } from '@angular/material/core';
import { StageService } from '../../../services/stage-api';
import { MatIconModule } from '@angular/material/icon';
import { FormsModule } from '@angular/forms';

interface Etudiant {
  nom: string;
  email: string;
  statut: 'Affecté' | 'Non affecté';
  societe: string;
  role: string;
  avatar: string;
}
@Component({
  selector: 'app-annonce-stage',
  imports: [CommonModule, MatIconModule,FormsModule],
  templateUrl: './annonce-stage.html',
  styleUrl: './annonce-stage.css'
})
export class AnnonceStage {

   selectedDate: Date = new Date(); // Date par défaut = aujourd'hui
  searchText: string = '';
  filteredEtudiants: any[] = [];
  constructor(private stageService: StageService) {}

   /* etudiants: Etudiant[] = [
      { nom: 'Darlene Robertson', email: 'trungkienspktnd@gmail.com', statut: 'Affecté', societe: 'Reporter', role: 'Reporter', avatar: 'assets/avatars/1.png' },
      { nom: 'Devon Lane', email: 'tranthuy.nute@gmail.com', statut: 'Non affecté', societe: '—', role: '', avatar: 'assets/avatars/2.png' },
      { nom: 'Cody Fisher', email: 'tienlapspktnd@gmail.com', statut: 'Affecté', societe: 'Sales Manager', role: 'Sales Manager', avatar: 'assets/avatars/3.png' },
      { nom: 'Theresa Webb', email: 'thuhang.nute@gmail.com', statut: 'Affecté', societe: 'Broadcaster', role: 'Broadcaster', avatar: 'assets/avatars/4.png' },
      { nom: 'Savannah Nguyen', email: 'manhhackt08@gmail.com', statut: 'Affecté', societe: 'Marketer', role: 'Marketer', avatar: 'assets/avatars/5.png' },
      { nom: 'Eleanor Pena', email: 'vuhaithuongnute@gmail.com', statut: 'Non affecté', societe: '—', role: '', avatar: 'assets/avatars/6.png' },
      { nom: 'Jenny Wilson', email: 'danghoang87hl@gmail.com', statut: 'Non affecté', societe: '—', role: '', avatar: 'assets/avatars/7.png' },
      { nom: 'Marvin McKinney', email: 'binhan628@gmail.com', statut: 'Affecté', societe: 'Team Editor', role: 'Team Editor', avatar: 'assets/avatars/8.png' },
      { nom: 'Cameron Williamson', email: 'ckctm12@gmail.com', statut: 'Affecté', societe: 'PPC Expert', role: 'PPC Expert', avatar: 'assets/avatars/9.png' }
    ];*/

   months = [
    { value: 0, label: 'Janvier' },
    { value: 1, label: 'Février' },
    { value: 2, label: 'Mars' },
    { value: 3, label: 'Avril' },
    { value: 4, label: 'Mai' },
    { value: 5, label: 'Juin' },
    { value: 6, label: 'Juillet' },
    { value: 7, label: 'Août' },
    { value: 8, label: 'Septembre' },
    { value: 9, label: 'Octobre' },
    { value: 10, label: 'Novembre' },
    { value: 11, label: 'Décembre' }
  ];

  //  onDateChange() {
  //   console.log('Date sélectionnée:', this.selectedDate);
  //   // Implémentez ici la logique pour filtrer le tableau
  //   // en fonction de la date sélectionnée
  //   this.filterData();
  // }

  // filterData() {
  //   // Filtrez vos données en fonction de selectedDate
  //   // Exemple simplifié :
  //   this.filteredEtudiants = this.etudiants.filter(etu => {
  //     const etuDate = new Date(etu.date); // Adaptez à votre structure de données
  //     return etuDate.toDateString() === this.selectedDate.toDateString();
  //   });
  // }

  filterTable() {
    if (!this.searchText) {
      this.filteredEtudiants = [...this.etudiants];
      return;
    }


  }
  

etudiants: any[] = [];

ngOnInit(): void {
  this.stageService.getStages().subscribe({
    next: (data) => {
      this.etudiants = data;
    },
    error: (err) => console.error(err)
  });
}


}