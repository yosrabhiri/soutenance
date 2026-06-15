import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { tap } from 'rxjs/operators';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class StageService {
  private apiUrl = environment.apiUrl;

  // cache with BehaviorSubject
  private formDataSubject = new BehaviorSubject<any | null>(null);
  baseUrl: any;

  constructor(private http: HttpClient) {}

  // fetch only once and store in subject
  loadFormData(): Observable<any> {
    if (!this.formDataSubject.value) {
      this.http.get(`${this.apiUrl}/stage/form`, { withCredentials: true })
        .pipe(
          tap((data) => this.formDataSubject.next(data))
        )
        .subscribe(); // trigger once
    }
    return this.formDataSubject.asObservable();
  }
  getHistoriqueStages(): Observable<any[]> {
  return this.http.get<any[]>(`${this.apiUrl}/stage/historique`, { withCredentials: true });
}


  // always give components the cached observable
  getFormData(): Observable<any> {
    return this.formDataSubject.asObservable();
  }

  // save stage as before
  saveStage(stageData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/stage/save`, stageData);
  }
  getDiplomes(): Observable<any> {
    return this.http.get(`${this.apiUrl}/diplomes`);
  }

  getNiveaux(): Observable<any> {
    return this.http.get(`${this.apiUrl}/niveaux`);
  }

  getSpecialites(): Observable<any> {
    return this.http.get(`${this.apiUrl}/specialites`);
  }
  getStages(): Observable<any> {
    return this.http.get(`${this.apiUrl}/stagesListes`);
  }
  getAllStages(): Observable<any[]> {
    return this.http.get<any[]>(`${this.apiUrl}/stages`);
  }

  updateStageStatus(stageId: number, statut: string) {
    return this.http.put(`${this.apiUrl}/stages/${stageId}/update-status`, { etat_validation: statut });
  }
}
