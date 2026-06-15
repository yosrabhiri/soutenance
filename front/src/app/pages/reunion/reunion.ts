import { Component, LOCALE_ID, OnInit } from '@angular/core';import { f } from "../../../../node_modules/@angular/material/icon-module.d-COXCrhrh";
import { MatIconModule } from '@angular/material/icon';
import { HttpClient } from '@angular/common/http';
import { CommonModule, registerLocaleData } from '@angular/common';
import localeFr from '@angular/common/locales/fr';
import { environment } from '../../../environments/environment';

registerLocaleData(localeFr, 'fr');

@Component({
  selector: 'app-reunion',
  imports: [MatIconModule,CommonModule],
  templateUrl: './reunion.html',
  styleUrl: './reunion.css',
  providers: [{ provide: LOCALE_ID, useValue: 'fr' }]
})
export class Reunion implements OnInit {

  reunions: any[] = [];

  constructor(private http: HttpClient) {}

  ngOnInit(): void {
    this.getReunions();
  }

  getReunions() {
    this.http.get<any>(`${environment.apiUrl}/etudiant/reunion`).subscribe({
      next: (res) => {
        this.reunions = res.data;
        console.log(res,'date')
      },
      error: (err) => {
        console.error('Erreur lors de la récupération des réunions', err);
      }
    });
  }

  // 🔹 Vérifie si une réunion est passée ou à venir
  isPast(reunion: any): boolean {
    const reunionDate = new Date(`${reunion.jour}T${reunion.heure}`);
    const now = new Date();
    return reunionDate < now;
  }

}
