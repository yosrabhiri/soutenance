import { CommonModule } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatIconModule } from '@angular/material/icon';
import { Loading } from '../../components/loading/loading';

@Component({
  selector: 'app-rapport-recherche',
  imports: [CommonModule,FormsModule,MatIconModule,Loading],
  templateUrl: './rapport-recherche.html',
  styleUrl: './rapport-recherche.css'
})
export class RapportRecherche {
motsRecherche: string = '';
  rapports: any[] = [];
  loading: boolean = false;

  constructor(private http: HttpClient) {}

  rechercher() {
    if (!this.motsRecherche.trim()) {
      alert("Veuillez entrer au moins un mot-clé");
      return;
    }

    this.loading = true;

    // Découper la saisie par espaces ou virgules
    const keywords = this.motsRecherche
      .split(/[\s,]+/)
      .map(m => m.trim())
      .filter(m => m.length > 0);

    this.http.post<any[]>('http://localhost:8000/api/stage/search', { keywords })
  .subscribe({
    next: (res) => {
      this.rapports = res.map(r => ({
        ...r,
        mots_cles: Array.isArray(r.mots_cles) ? r.mots_cles : JSON.parse(r.mots_cles || "[]")
        
      }));
      this.loading = false;
    },

      error: (err: any) => {
        console.error(err);
        this.loading = false;
      }
    });
  }
}
