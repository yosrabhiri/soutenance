import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class PlanningApiService {
  private apiUrl = 'http://localhost:8000/api';

  constructor(private http: HttpClient) {}

  getPeriodes(): Observable<any> {
    return this.http.get(`${this.apiUrl}/periodes-soutenances`);
  }

  getSalles(): Observable<any> {
    return this.http.get(`${this.apiUrl}/salles`);
  }

  createPeriode(payload: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/periodes-soutenances`, payload);
  }

  updatePeriode(periodeId: number, payload: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/periodes-soutenances/${periodeId}`, payload);
  }

  assignerSalles(periodeId: number, salleIds: number[]): Observable<any> {
    return this.http.post(`${this.apiUrl}/periodes-soutenances/${periodeId}/salles`, {
      salle_ids: salleIds
    });
  }

  generateCreneaux(periodeId: number): Observable<any> {
    return this.http.post(`${this.apiUrl}/periodes-soutenances/${periodeId}/generate-creneaux`, {});
  }

  getCreneaux(periodeId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/periodes-soutenances/${periodeId}/creneaux`);
  }

  getMesDisponibilites(periodeId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/periodes-soutenances/${periodeId}/mes-disponibilites`);
  }

  saveMesDisponibilites(periodeId: number, disponibilites: any[]): Observable<any> {
    return this.http.post(`${this.apiUrl}/periodes-soutenances/${periodeId}/mes-disponibilites`, {
      disponibilites
    });
  }

  getPlanningInput(periodeId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/periodes-soutenances/${periodeId}/planning-input`);
  }

  getCpSatPreview(periodeId: number, params?: {
    max_time_in_seconds?: number;
    num_search_workers?: number;
  }): Observable<any> {
    const searchParams = new URLSearchParams();

    if (params?.max_time_in_seconds) {
      searchParams.set('max_time_in_seconds', String(params.max_time_in_seconds));
    }
    if (params?.num_search_workers) {
      searchParams.set('num_search_workers', String(params.num_search_workers));
    }

    const query = searchParams.toString();
    const url = query
      ? `${this.apiUrl}/periodes-soutenances/${periodeId}/cp-sat-preview?${query}`
      : `${this.apiUrl}/periodes-soutenances/${periodeId}/cp-sat-preview`;

    return this.http.get(url);
  }

  launchGeneration(periodeId: number, payload?: {
    algorithm?: 'cp_sat';
    max_time_in_seconds?: number;
    num_search_workers?: number;
  }): Observable<any> {
    return this.http.post(`${this.apiUrl}/periodes-soutenances/${periodeId}/launch-generation`, payload || {});
  }

  getGenerationStatus(periodeId: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/periodes-soutenances/${periodeId}/generation-status`);
  }

  getMySoutenances(periodeId?: number): Observable<any> {
    const url = periodeId
      ? `${this.apiUrl}/mes-soutenances?periode_id=${periodeId}`
      : `${this.apiUrl}/mes-soutenances`;

    return this.http.get(url);
  }

  getSoutenances(periodeId?: number): Observable<any> {
    const url = periodeId
      ? `${this.apiUrl}/soutenances?periode_id=${periodeId}`
      : `${this.apiUrl}/soutenances`;

    return this.http.get(url);
  }
}
