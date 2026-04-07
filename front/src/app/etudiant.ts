
import { Injectable } from '@angular/core';
export interface etudiant {
  nom: string;
  prenom: string;
  email: string;
  nomSociete: string;
  statut: string;
  lienPdf: string;
}

@Injectable({
  providedIn: 'root'
})
export class EtudiantService {
  private etudiant: etudiant[] = [
    {
      nom: 'Ben Ali',
      prenom: 'Ahmed',
      email: 'ahmed.benali@example.com',
      nomSociete: 'TechTunisia',
      statut: 'Actif',
      lienPdf: 'assets/pdfs/ahmed-benali.pdf'
    },
    {
      nom: 'Trabelsi',
      prenom: 'Sana',
      email: 'sana.trabelsi@example.com',
      nomSociete: 'WebSolutions',
      statut: 'Inactif',
      lienPdf: 'assets/pdfs/sana-trabelsi.pdf'
    },
    {
      nom: 'Mansour',
      prenom: 'Hichem',
      email: 'hichem.mansour@example.com',
      nomSociete: 'DevCorp',
      statut: 'Actif',
      lienPdf: 'assets/pdfs/hichem-mansour.pdf'
    }
  ];

  getEtudiant(): etudiant[] {
    return this.etudiant;
  }
}
