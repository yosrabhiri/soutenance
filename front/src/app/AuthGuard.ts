import { Injectable } from '@angular/core';
import { CanActivate, Router, UrlTree } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AuthGuard implements CanActivate {

  constructor(private router: Router) {}

  canActivate(): boolean | UrlTree {
    const token = localStorage.getItem('token'); // Vérifie le token
    if (token) {
      return true; // L'utilisateur est connecté
    } else {
      return this.router.parseUrl('/unauthorized'); // Affiche la page "non autorisé"
    }
  }
}
