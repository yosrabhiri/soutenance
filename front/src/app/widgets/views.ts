import { Component, OnInit } from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { DashboardService } from '../services/dashboard';

@Component({
  selector: 'app-views',
  imports: [MatIcon],
  templateUrl: './views.html',
  styleUrl: './views.css'
})
export class Views implements OnInit{
etudiantsAffectes: number = 0;

  constructor(private dashboardService: DashboardService) {}

  ngOnInit(): void {
    this.loadEtudiantsAffectes();
  }

  loadEtudiantsAffectes(): void {
    this.dashboardService.getEtudiantsISSAT().subscribe({
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