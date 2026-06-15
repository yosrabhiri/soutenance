import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { environment } from '../../environments/environment';

@Injectable({ providedIn: 'root' })
export class AuthService {
  apiUrl = environment.apiUrl;


  constructor(private http: HttpClient,private router: Router) {}

  login(email: string, password: string) {
    return this.http.post(`${this.apiUrl}/login`, { email, password });
  }

  registerEtudiant(data: any) {
    return this.http.post(`${this.apiUrl}/register-etudiant`, data);
  }

  registerEnseignant(data: any) {
    return this.http.post(`${this.apiUrl}/register-enseignant`, data);
  }
   getProtectedData() {
    const token = localStorage.getItem('token'); // récupère le token stocké après login
    const headers = new HttpHeaders().set('Authorization', `Bearer ${token}`);
    return this.http.get(`${this.apiUrl}/protected-route`, { headers });
  }
  logout(): void {
    const token = localStorage.getItem('token');

    this.http.post(`${this.apiUrl}/logout`, {}, {
      headers: { Authorization: `Bearer ${token}` }
    }).subscribe({
      next: () => {
        localStorage.removeItem('token');
        this.router.navigate(['/login']); // fonctionne car Router injecté
        console.log('mrigeeel')
      },
      error: (err) => {
        console.error('Erreur lors du logout', err);
      }
    });
  }
}
