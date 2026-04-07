import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class DemandeService {
  private apiUrl = 'http://127.0.0.1:8000/api'; // ton backend Laravel

  constructor(private http: HttpClient) {}

  // créer une demande (par étudiant)
  createDemande(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/demandes`, data);
  }

  // liste des demandes d’un enseignant
  getDemandes(): Observable<any> {
    return this.http.get(`${this.apiUrl}/demandes`);
  }

  // répondre à une demande
  repondreDemande(id: number, statut: string): Observable<any> {
    return this.http.post(`${this.apiUrl}/demandes/${id}/repondre`, { statut });
  }
}
