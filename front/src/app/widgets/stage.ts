import { Component, OnInit } from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { DashboardService } from '../services/dashboard';

@Component({
  selector: 'app-stage',
  imports: [MatIcon],
  templateUrl: './stage.html',
  styleUrl: './stage.css'
})
export class Stage implements OnInit{
etudiantsAffectes: number = 0;

  constructor(private dashboardService: DashboardService) {}

  ngOnInit(): void {
    this.loadEtudiantsAffectes();
  }

  loadEtudiantsAffectes(): void {
    this.dashboardService.getEtudiantsExternes().subscribe({
      next: (data) => {
        this.etudiantsAffectes = data.value;
        console.log(this.etudiantsAffectes);
      },
      error: (err) => {
        console.error('Erreur lors du chargement des étudiants affectés', err);
      }
    });
  }
}