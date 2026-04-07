import { etudiant } from './../../../../etudiant';
import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatNativeDateModule } from '@angular/material/core';
import { DemandeService } from '../../../../services/demande';
import {MatButtonModule} from '@angular/material/button';
import { MatIconModule } from "@angular/material/icon";
import { FormsModule } from '@angular/forms';

interface Etudiant {
  nom: string;
  email: string;
  reponse: string;
  role: string;
  avatar: string;
}
@Component({
  selector: 'app-demand-encadrement',
  imports: [CommonModule,MatButtonModule,MatIconModule,FormsModule],
  templateUrl: './demand-encadrement.html',
  styleUrl: './demand-encadrement.css'
})
export class DemandEncadrement {

    /*selectedDate: Date = new Date(); // Date par défaut = aujourd'hui
  searchText: string = '';
  filteredEtudiants: any[] = [];

   etudiants = [
  {
    nom: 'Mohamed Hedi mabrouk',
    email: 'mabrouk@gmail.com',

    reponse: 'En attente de votre Confirmation',
    avatar: 'assets/avatars/1.png'
  },
  {
    nom: 'anas selem',
    email: 'anas.selem56@gmail.com',

    reponse: 'En attente de votre Confirmation',
    avatar: 'assets/avatars/2.png'
  },
  {
    nom: 'Saif AbdAllah',
    email: 'Saif@gmail.com',

    reponse: 'En attente de votre Confirmation',
    avatar: 'assets/avatars/3.png'
  },
  // ... autres étudiants
];*/
demandes: any[] = [];   // tableau pour stocker les demandes
popupOuvert = false;
searchText: string = '';
demandeSelectionnee: any = null;
  filtereddemandes: any[];
  constructor(private demandeService: DemandeService) {}

  ngOnInit(): void {
  this.demandeService.getDemandes().subscribe({
    next: (data) => {
      this.demandes = data.demandes.filter((d: any) => d.statut === 'en_attente');
      console.log(this.demandes);
    },
    error: (err) => {
      console.error('Erreur lors du chargement des demandes', err);
    }
  });
   this.filtereddemandes = [...this.demandes];
}

ouvrirPopup(demande: any) {
  this.demandeSelectionnee = demande;
  this.popupOuvert = true;
}

fermerPopup() {
  this.popupOuvert = false;
  this.demandeSelectionnee = null;
}

repondre(statut: string) {
  // ici tu appelles ton service pour répondre à la demande
  this.demandeService.repondreDemande(this.demandeSelectionnee.id, statut).subscribe(res => {
    this.demandeSelectionnee.statut = res.statut;
    this.fermerPopup();
  });

}
  filterTable() {
    const value = this.searchText.trim().toLowerCase();
    this.filtereddemandes = this.demandes.filter(e =>
      e.etudiant.nom.toLowerCase().includes(value) ||
      e.etudiant.email.toLowerCase().includes(value) ||
      e.etudiant.prenom.toLowerCase().includes(value) ||
      e.statut.toLowerCase().includes(value) 
    );
  }
}