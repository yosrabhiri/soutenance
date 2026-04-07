import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EnseignantService {
  
private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  // ⚡ Récupère tous les étudiants encadrés par l'enseignant connecté
  getEtudiantsEncadres(): Observable<any> {
    return this.http.get(`${this.apiUrl}/mes-etudiants`, { withCredentials: true });
  }
  getReunionsEtudiants():Observable<any> {
    return this.http.get(`${this.apiUrl}/reunions/etudiants`, { withCredentials: true });}

    getReunionsAvecEtudiants():Observable<any> {
    return this.http.get(`${this.apiUrl}/reunions/calendar`, { withCredentials: true });}
  // enseignant.service.ts
creerReunion(data: any) {
  return this.http.post(`${this.apiUrl}/reunions`, data);
}

  // ⚡ Valider un stage pour un étudiant
  validerStage(stageId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/stage/${stageId}/valider`, {});
  }
}