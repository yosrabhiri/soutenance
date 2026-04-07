import { CommonModule } from '@angular/common';
import { Component, OnInit } from '@angular/core';
import { MatTableModule } from '@angular/material/table';
import { StageService } from '../../../services/stage-api';
import { Loading } from "../../loading/loading";

export interface StageElement {
  societe: string;
  type: string;
  dateDebut: string;
  dateFin: string;
  statut: string;
}

@Component({
  selector: 'app-historique',
  imports: [MatTableModule, CommonModule, Loading],
  templateUrl: './historique.html',
  styleUrl: './historique.css',
})
export class Historique implements OnInit {

  displayedColumns: string[] = ['type', 'societe', 'dateDebut', 'dateFin', 'statut'];
  dataSource: StageElement[] = [];  // ⚡ vide au départ
  clickedRows = new Set<StageElement>();
   loading = true; 

  constructor(private stageService: StageService) {}

  ngOnInit(): void {
    this. loading = true; 
    this.stageService.getHistoriqueStages().subscribe((data: StageElement[]) => {
      this.dataSource = data; // ⚡ remplissage depuis l’API Laravel
      console.log(data)
       this.loading = false;
    });
  }
}
