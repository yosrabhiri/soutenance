import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
@Injectable({
  providedIn: 'root'
})
export class RapportService {
  constructor(private http: HttpClient) {}
   private apiUrl = environment.apiUrl;
  getDatesRapport(): Observable<any> {
  return this.http.get(`${this.apiUrl}/dates-rapport`);
}

updateDatesRapport(dates: any): Observable<any> {
  return this.http.post(`${this.apiUrl}/dates-rapport/update`, dates);
}


}
