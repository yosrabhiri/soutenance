import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
@Injectable({
  providedIn: 'root'
})
export class RapportService {
  constructor(private http: HttpClient) {}
   private apiUrl = 'http://localhost:8000/api';
  getDatesRapport(): Observable<any> {
  return this.http.get(`${this.apiUrl}/dates-rapport`);
}

updateDatesRapport(dates: any): Observable<any> {
  return this.http.post(`${this.apiUrl}/dates-rapport/update`, dates);
}


}
